<?php
/**
 * Taxonomy and Terms Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_tax_abilities' );

function gw_mcp_register_tax_abilities() {

    // 1. Read Taxonomies
    if ( gw_mcp_is_ability_active( 'read_taxonomies' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-taxonomies',
            array(
                'category'    => 'gw',
                'label'       => 'Get Taxonomies',
                'description' => 'Returns all taxonomies (e.g. category, post_tag) registered for a specific post type or globally.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_type' => array(
                            'type'        => 'string',
                            'description' => 'Optional slug of the post type to get specific taxonomies for.',
                        ),
                    ),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_taxonomies_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 2. Read Terms
    if ( gw_mcp_is_ability_active( 'read_terms' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-terms',
            array(
                'category'    => 'gw',
                'label'       => 'Get Terms',
                'description' => 'Returns list of terms (like specific categories or tags) within a requested taxonomy.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'taxonomy' => array(
                            'type'        => 'string',
                            'description' => 'The slug of the taxonomy (e.g., category, post_tag, product_cat).',
                            'default'     => 'category',
                        ),
                        'hide_empty' => array(
                            'type'        => 'boolean',
                            'description' => 'Whether to hide terms not assigned to any posts. Default: false.',
                            'default'     => false,
                        )
                    ),
                    'required' => [ 'taxonomy' ]
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_terms_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}

function gw_read_taxonomies_execute( $input ) {
    $args = array(
        'public' => true,
    );

    if ( isset( $input['post_type'] ) && ! empty( $input['post_type'] ) ) {
        $args['object_type'] = array( sanitize_text_field( $input['post_type'] ) );
    }

    $taxonomies = get_taxonomies( $args, 'objects' );
    $output = array();

    foreach ( $taxonomies as $slug => $tax ) {
        $output[] = array(
            'slug'         => $slug,
            'name'         => $tax->label,
            'hierarchical' => $tax->hierarchical,
        );
    }

    return array( 'taxonomies' => $output );
}

function gw_read_terms_execute( $input ) {
    $taxonomy   = isset( $input['taxonomy'] ) ? sanitize_key( $input['taxonomy'] ) : 'category';
    $hide_empty = isset( $input['hide_empty'] ) ? (bool) $input['hide_empty'] : false;

    if ( ! taxonomy_exists( $taxonomy ) ) {
        return new WP_Error( 'invalid_taxonomy', 'The requested taxonomy does not exist.' );
    }

    $terms = get_terms( array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => $hide_empty,
    ) );

    if ( is_wp_error( $terms ) ) {
        return $terms;
    }

    $output = array();
    foreach ( $terms as $term ) {
        $output[] = array(
            'id'    => $term->term_id,
            'name'  => $term->name,
            'slug'  => $term->slug,
            'count' => $term->count,
        );
    }

    return array( 'terms' => $output );
}
