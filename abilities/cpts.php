<?php
/**
 * Custom Post Types Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_cpts_abilities' );

function gw_mcp_register_cpts_abilities() {

    // 1. List CPTs
    if ( gw_mcp_is_ability_active( 'list_cpts' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/list-cpts',
            array(
                'category'    => 'gw',
                'label'       => 'List Custom Post Types',
                'description' => 'Returns all public Custom Post Types (CPTs) registered in the WordPress installation.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(),
                ),
                'output_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'cpts' => array( 'type' => 'array' ),
                    ),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_list_cpts_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 2. Read CPT Posts
    if ( gw_mcp_is_ability_active( 'read_cpt_posts' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-cpt-posts',
            array(
                'category'    => 'gw',
                'label'       => 'Get CPT Posts',
                'description' => 'Returns published posts from a specific Custom Post Type.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_type' => array(
                            'type'        => 'string',
                            'description' => 'Slug of the custom post type.',
                        ),
                        'per_page' => array(
                            'type'        => 'integer',
                            'description' => 'Number of CPT posts to return. Default 50.',
                            'default'     => 50,
                        ),
                    ),
                    'required' => [ 'post_type' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_cpt_posts_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 3. Create CPT Post
    if ( gw_mcp_is_ability_active( 'create_cpt_post' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/create-cpt-post',
            array(
                'category'            => 'gw',
                'label'               => 'Create CPT Post',
                'description'         => 'Creates a new post of a requested Custom Post Type.',
                'input_schema'        => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_type' => array(
                            'type'        => 'string',
                            'description' => 'The slug of the custom post type.',
                        ),
                        'title' => array(
                            'type'        => 'string',
                            'description' => 'The title of the post.',
                        ),
                        'content' => array(
                            'type'        => 'string',
                            'description' => 'The HTML body content.',
                        ),
                        'status' => array(
                            'type'        => 'string',
                            'description' => 'Post status: draft, publish, private.',
                            'default'     => 'publish',
                        ),
                    ),
                    'required' => [ 'post_type', 'title', 'content' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_create_cpt_post_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

}

function gw_list_cpts_execute( $input ) {
    $cpts = get_post_types( array( 'public' => true ), 'objects' );
    $output = array();

    foreach ( $cpts as $slug => $cpt ) {
        $output[] = array(
            'slug'        => $slug,
            'name'        => $cpt->label,
            'description' => $cpt->description,
            'hierarchical'=> $cpt->hierarchical,
        );
    }

    return array( 'cpts' => $output );
}

function gw_read_cpt_posts_execute( $input ) {
    $per_page  = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 50;
    $post_type = sanitize_key( $input['post_type'] );

    $args = array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );

    $posts = array();
    foreach ( $query->posts as $post ) {
        $posts[] = array(
            'id'        => $post->ID,
            'title'     => $post->post_title,
            'url'       => get_permalink( $post->ID ),
            'date'      => get_the_date( 'Y-m-d', $post->ID ),
            'excerpt'   => wp_trim_words( $post->post_content, 40 ),
            'post_type' => $post->post_type,
        );
    }

    return array(
        'posts' => $posts,
        'total' => $query->found_posts,
    );
}

function gw_create_cpt_post_execute( $input ) {
    $post_type = sanitize_key( $input['post_type'] );

    // Check if CPT exists
    if ( ! post_type_exists( $post_type ) ) {
        return new WP_Error( 'invalid_cpt', 'The provided post type does not exist.' );
    }

    $post_data = array(
        'post_title'   => sanitize_text_field( $input['title'] ),
        'post_content' => wp_kses_post( $input['content'] ),
        'post_status'  => isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'publish',
        'post_type'    => $post_type,
    );

    $post_id = wp_insert_post( $post_data, true );

    if ( is_wp_error( $post_id ) ) {
        return $post_id;
    }

    return array(
        'id'        => $post_id,
        'post_type' => $post_type,
        'url'       => get_permalink( $post_id ),
        'message'   => 'CPT Post successfully created.',
    );
}
