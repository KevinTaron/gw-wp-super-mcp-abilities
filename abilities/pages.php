<?php
/**
 * Page Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_pages_abilities' );

function gw_mcp_register_pages_abilities() {

    // 1. Read Pages
    if ( gw_mcp_is_ability_active( 'read_pages' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-pages',
            array(
                'category'    => 'gw',
                'label'       => 'Get Pages',
                'description' => 'Returns published pages with title, URL, date, and excerpt.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'per_page' => array(
                            'type'        => 'integer',
                            'description' => 'Number of pages to return. Default 50.',
                            'default'     => 50,
                        ),
                    ),
                ),
                'output_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'pages' => array( 'type' => 'array' ),
                        'total' => array( 'type' => 'integer' ),
                    ),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_pages_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 2. Create Page
    if ( gw_mcp_is_ability_active( 'create_page' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/create-page',
            array(
                'category'            => 'gw',
                'label'               => 'Create Page',
                'description'         => 'Creates a new WordPress page returning its ID and URL.',
                'input_schema'        => array(
                    'type'       => 'object',
                    'properties' => array(
                        'title' => array(
                            'type'        => 'string',
                            'description' => 'The title of the page.',
                        ),
                        'content' => array(
                            'type'        => 'string',
                            'description' => 'The HTML body content of the page.',
                        ),
                        'status' => array(
                            'type'        => 'string',
                            'description' => 'Page status: draft, publish, private.',
                            'default'     => 'publish',
                        ),
                        'parent_id' => array(
                            'type'        => 'integer',
                            'description' => 'Optional parent page ID.',
                            'default'     => 0,
                        )
                    ),
                    'required' => [ 'title', 'content' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_create_page_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 3. Duplicate Page
    if ( gw_mcp_is_ability_active( 'duplicate_page' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/duplicate-page',
            array(
                'category'            => 'gw',
                'label'               => 'Duplicate Page',
                'description'         => 'Duplicates an existing page and its metadata returning the new ID.',
                'input_schema'        => array(
                    'type'       => 'object',
                    'properties' => array(
                        'page_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the page to duplicate.',
                        ),
                    ),
                    'required' => [ 'page_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_duplicate_page_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}

function gw_read_pages_execute( $input ) {
    $per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 50;

    $args = array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );

    $pages = array();
    foreach ( $query->posts as $post ) {
        $pages[] = array(
            'id'      => $post->ID,
            'title'   => $post->post_title,
            'url'     => get_permalink( $post->ID ),
            'date'    => get_the_date( 'Y-m-d', $post->ID ),
            'excerpt' => wp_trim_words( $post->post_content, 40 ),
        );
    }

    return array(
        'pages' => $pages,
        'total' => $query->found_posts,
    );
}

function gw_create_page_execute( $input ) {
    $post_data = array(
        'post_title'   => sanitize_text_field( $input['title'] ),
        'post_content' => wp_kses_post( $input['content'] ),
        'post_status'  => isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'publish',
        'post_parent'  => isset( $input['parent_id'] ) ? intval( $input['parent_id'] ) : 0,
        'post_type'    => 'page',
    );

    $page_id = wp_insert_post( $post_data, true );

    if ( is_wp_error( $page_id ) ) {
        return $page_id;
    }

    return array(
        'id'      => $page_id,
        'url'     => get_permalink( $page_id ),
        'message' => 'Page successfully created.',
    );
}

function gw_duplicate_page_execute( $input ) {
    $page_id = intval( $input['page_id'] );
    $post    = get_post( $page_id );

    if ( ! $post || $post->post_type !== 'page' ) {
        return new WP_Error( 'not_found', 'Page not found.' );
    }

    $new_post_args = array(
        'post_title'     => 'Kopie von ' . $post->post_title,
        'post_content'   => $post->post_content,
        'post_status'    => 'draft', // Immer als Draft duplizieren, sicherheitshalber
        'post_type'      => $post->post_type,
        'post_author'    => get_current_user_id() ?: $post->post_author,
        'post_parent'    => $post->post_parent,
        'post_password'  => $post->post_password,
        'post_excerpt'   => $post->post_excerpt,
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
    );

    $new_post_id = wp_insert_post( $new_post_args );

    if ( is_wp_error( $new_post_id ) || empty( $new_post_id ) ) {
        return new WP_Error( 'duplicate_failed', 'Failed to duplicate page.' );
    }

    // Copy Taxonomies
    $taxonomies = get_object_taxonomies( $post->post_type );
    foreach ( $taxonomies as $taxonomy ) {
        $post_terms = wp_get_object_terms( $page_id, $taxonomy, array( 'fields' => 'slugs' ) );
        wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
    }

    // Copy Post Meta
    $post_meta_infos = get_post_meta( $page_id );
    if ( count( $post_meta_infos ) != 0 ) {
        foreach ( $post_meta_infos as $meta_key => $meta_values ) {
            foreach ( $meta_values as $meta_value ) {
                $meta_value = maybe_unserialize( $meta_value );
                add_post_meta( $new_post_id, $meta_key, $meta_value );
            }
        }
    }

    return array(
        'id'      => $new_post_id,
        'url'     => get_permalink( $new_post_id ) ?: admin_url( 'post.php?post=' . $new_post_id . '&action=edit' ),
        'message' => 'Page successfully duplicated as draft.',
    );
}
