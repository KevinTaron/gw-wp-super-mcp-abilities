<?php
/**
 * Global Search Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_search_abilities' );

function gw_mcp_register_search_abilities() {

    // 1. Search Content
    if ( gw_mcp_is_ability_active( 'search_content' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/search-content',
            array(
                'category'    => 'gw',
                'label'       => 'Search Content',
                'description' => 'Performs a global search across all post types (posts, pages, CPTs) for a specific keyword.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'query' => array(
                            'type'        => 'string',
                            'description' => 'The search term.',
                        ),
                        'post_types' => array(
                            'type'        => 'array',
                            'items'       => [ 'type' => 'string' ],
                            'description' => 'Optional array of post types to limit the search. Defaults to all public types.',
                        ),
                        'per_page' => array(
                            'type'        => 'integer',
                            'description' => 'Number of results to return. Default 20.',
                            'default'     => 20,
                        ),
                    ),
                    'required' => [ 'query' ]
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_search_content_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}

function gw_search_content_execute( $input ) {
    $search_query = sanitize_text_field( $input['query'] );
    $per_page     = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 20;

    $args = array(
        's'              => $search_query,
        'posts_per_page' => $per_page,
        'post_status'    => 'any', // Include drafts if they exist
    );

    if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
        $args['post_type'] = array_map( 'sanitize_text_field', $input['post_types'] );
    } else {
        $args['post_type'] = 'any';
    }

    $query = new WP_Query( $args );
    $results = array();

    foreach ( $query->posts as $post ) {
        $results[] = array(
            'id'        => $post->ID,
            'title'     => $post->post_title,
            'type'      => $post->post_type,
            'status'    => $post->post_status,
            'url'       => get_permalink( $post->ID ),
            'excerpt'   => has_excerpt( $post->ID ) ? get_the_excerpt( $post ) : wp_trim_words( $post->post_content, 20 ),
            'date'      => get_the_date( 'Y-m-d', $post->ID ),
        );
    }

    return array(
        'query'   => $search_query,
        'total'   => $query->found_posts,
        'results' => $results,
    );
}
