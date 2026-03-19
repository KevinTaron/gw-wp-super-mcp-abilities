<?php
/**
 * Post Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_posts_abilities' );

function gw_mcp_register_posts_abilities() {

    // 1. Read Posts
    if ( gw_mcp_is_ability_active( 'read_posts' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-posts', // Renamed slightly to fit naming convention
            array(
                'category'    => 'gw',
                'label'       => 'Get Blog Posts',
                'description' => 'Returns published blog posts with title, URL, date, excerpt and ID.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'per_page' => array(
                            'type'        => 'integer',
                            'description' => 'Number of posts to return. Default 50.',
                            'default'     => 50,
                        ),
                        'category_name' => array(
                            'type'        => 'string',
                            'description' => 'Optional slug of category to filter posts by.',
                        )
                    ),
                ),
                'output_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'posts' => array( 'type' => 'array' ),
                        'total' => array( 'type' => 'integer' ),
                    ),
                ),
                'permission_callback' => '__return_true', // Public Data
                'execute_callback'    => 'gw_get_posts_execute',
                'meta' => array(
                    'mcp' => array(
                        'public' => true,
                    ),
                ),
            )
        );
    }

    // 2. Read Post Details
    if ( gw_mcp_is_ability_active( 'read_post_details' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-post-details',
            array(
                'category'            => 'gw',
                'label'               => 'Get Post Details',
                'description'         => 'Returns complete details of a specific blog post by ID, including its full content.',
                'input_schema'        => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the post to read.',
                        ),
                    ),
                    'required' => [ 'post_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_post_details_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 3. Create Post
    if ( gw_mcp_is_ability_active( 'create_post' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/create-post',
            array(
                'category'            => 'gw',
                'label'               => 'Create Blog Post',
                'description'         => 'Creates a new blog post returning its ID and URL.',
                'input_schema'        => array(
                    'type'       => 'object',
                    'properties' => array(
                        'title' => array(
                            'type'        => 'string',
                            'description' => 'The title of the post.',
                        ),
                        'content' => array(
                            'type'        => 'string',
                            'description' => 'The HTML body content of the post.',
                        ),
                        'status' => array(
                            'type'        => 'string',
                            'description' => 'Post status: draft, publish, private.',
                            'default'     => 'draft',
                        ),
                        'categories' => array(
                            'type'        => 'array',
                            'items'       => [ 'type' => 'integer' ],
                            'description' => 'Array of category IDs.',
                        )
                    ),
                    'required' => [ 'title', 'content' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_create_post_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 4. Update Post/Page/CPT
    if ( gw_mcp_is_ability_active( 'update_post' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/update-post',
            array(
                'category'            => 'gw',
                'label'               => 'Update Post / Page / CPT',
                'description'         => 'Updates an existing post of any type. Provide the post_id and any fields you want to update (all are optional except post_id).',
                'input_schema'        => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the post/page/CPT to update.',
                        ),
                        'title' => array(
                            'type'        => 'string',
                            'description' => 'The updated title.',
                        ),
                        'content' => array(
                            'type'        => 'string',
                            'description' => 'The updated HTML content.',
                        ),
                        'status' => array(
                            'type'        => 'string',
                            'description' => 'Post status: draft, publish, private.',
                        ),
                    ),
                    'required' => [ 'post_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_update_post_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}

function gw_get_posts_execute( $input ) {
    $per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 50;

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ( isset( $input['category_name'] ) && ! empty( $input['category_name'] ) ) {
        $args['category_name'] = sanitize_text_field( $input['category_name'] );
    }

    $query = new WP_Query( $args );

    $posts = array();
    foreach ( $query->posts as $post ) {
        $posts[] = array(
            'id'      => $post->ID,
            'title'   => $post->post_title,
            'url'     => get_permalink( $post->ID ),
            'date'    => get_the_date( 'Y-m-d', $post->ID ),
            'excerpt' => has_excerpt( $post->ID )
                ? get_the_excerpt( $post )
                : wp_trim_words( $post->post_content, 40 ),
        );
    }

    return array(
        'posts' => $posts,
        'total' => $query->found_posts,
    );
}

function gw_read_post_details_execute( $input ) {
    $post_id = intval( $input['post_id'] );
    $post    = get_post( $post_id );

    if ( ! $post || $post->post_type !== 'post' ) {
        return new WP_Error( 'not_found', 'Post not found or not a blog post' );
    }

    return array(
        'id'      => $post->ID,
        'title'   => $post->post_title,
        'content' => $post->post_content,
        'status'  => $post->post_status,
        'url'     => get_permalink( $post->ID ),
        'date'    => get_the_date( 'Y-m-d H:i:s', $post->ID ),
        'author'  => get_the_author_meta( 'display_name', $post->post_author ),
    );
}

function gw_create_post_execute( $input ) {
    $post_data = array(
        'post_title'   => sanitize_text_field( $input['title'] ),
        'post_content' => wp_kses_post( $input['content'] ),
        'post_status'  => isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'draft',
        'post_type'    => 'post',
    );

    if ( isset( $input['categories'] ) && is_array( $input['categories'] ) ) {
        $post_data['post_category'] = array_map( 'intval', $input['categories'] );
    }

    $post_id = wp_insert_post( $post_data, true );

    if ( is_wp_error( $post_id ) ) {
        return $post_id;
    }

    return array(
        'id'      => $post_id,
        'url'     => get_permalink( $post_id ),
        'message' => 'Post successfully created.',
    );
}

function gw_update_post_execute( $input ) {
    $post_id = intval( $input['post_id'] );
    $post    = get_post( $post_id );

    if ( ! $post ) {
        return new WP_Error( 'not_found', 'Content not found.' );
    }

    $post_data = array(
        'ID' => $post_id,
    );

    if ( isset( $input['title'] ) && ! empty( $input['title'] ) ) {
        $post_data['post_title'] = sanitize_text_field( $input['title'] );
    }

    if ( isset( $input['content'] ) ) {
        $post_data['post_content'] = wp_kses_post( $input['content'] );
    }

    if ( isset( $input['status'] ) && ! empty( $input['status'] ) ) {
        $post_data['post_status'] = sanitize_text_field( $input['status'] );
    }

    $updated_post_id = wp_update_post( $post_data, true );

    if ( is_wp_error( $updated_post_id ) ) {
        return $updated_post_id;
    }

    return array(
        'id'      => $updated_post_id,
        'url'     => get_permalink( $updated_post_id ),
        'message' => 'Content successfully updated.',
    );
}
