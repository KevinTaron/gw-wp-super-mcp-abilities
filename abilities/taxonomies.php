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

    // 3. Create Term
    if ( gw_mcp_is_ability_active( 'write_terms' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/create-term',
            array(
                'category'    => 'gw',
                'label'       => 'Create Taxonomy Term',
                'description' => 'Creates a new term (like a category or tag) in a specific taxonomy.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'taxonomy' => array(
                            'type'        => 'string',
                            'description' => 'The slug of the taxonomy (e.g., category, post_tag).',
                        ),
                        'term_name' => array(
                            'type'        => 'string',
                            'description' => 'The name of the new term.',
                        ),
                        'description' => array(
                            'type'        => 'string',
                            'description' => 'Optional description for the term.',
                        ),
                        'parent' => array(
                            'type'        => 'integer',
                            'description' => 'Optional ID of the parent term.',
                        ),
                    ),
                    'required' => [ 'taxonomy', 'term_name' ]
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_create_term_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 4. Update Term
    if ( gw_mcp_is_ability_active( 'write_terms' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/update-term',
            array(
                'category'    => 'gw',
                'label'       => 'Update Taxonomy Term',
                'description' => 'Updates an existing term.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'term_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the term to update.',
                        ),
                        'taxonomy' => array(
                            'type'        => 'string',
                            'description' => 'The taxonomy of the term.',
                        ),
                        'term_name' => array(
                            'type'        => 'string',
                            'description' => 'The new name of the term.',
                        ),
                        'description' => array(
                            'type'        => 'string',
                            'description' => 'The new description.',
                        ),
                    ),
                    'required' => [ 'term_id', 'taxonomy' ]
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_update_term_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 5. Delete Term
    if ( gw_mcp_is_ability_active( 'write_terms' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/delete-term',
            array(
                'category'    => 'gw',
                'label'       => 'Delete Taxonomy Term',
                'description' => 'Deletes an existing term permanently.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'term_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the term to delete.',
                        ),
                        'taxonomy' => array(
                            'type'        => 'string',
                            'description' => 'The taxonomy of the term.',
                        ),
                    ),
                    'required' => [ 'term_id', 'taxonomy' ]
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_delete_term_execute',
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

function gw_create_term_execute( $input ) {
    $taxonomy  = sanitize_key( $input['taxonomy'] );
    $term_name = sanitize_text_field( $input['term_name'] );

    if ( ! taxonomy_exists( $taxonomy ) ) {
        return new WP_Error( 'invalid_taxonomy', 'The requested taxonomy does not exist.' );
    }

    $args = array();
    if ( isset( $input['description'] ) ) {
        $args['description'] = sanitize_textarea_field( $input['description'] );
    }
    if ( isset( $input['parent'] ) ) {
        $args['parent'] = intval( $input['parent'] );
    }

    $result = wp_insert_term( $term_name, $taxonomy, $args );

    if ( is_wp_error( $result ) ) {
        return $result;
    }

    return array(
        'term_id'  => $result['term_id'],
        'taxonomy' => $taxonomy,
        'message'  => 'Term created successfully.'
    );
}

function gw_update_term_execute( $input ) {
    $term_id  = intval( $input['term_id'] );
    $taxonomy = sanitize_key( $input['taxonomy'] );

    if ( ! taxonomy_exists( $taxonomy ) ) {
        return new WP_Error( 'invalid_taxonomy', 'The requested taxonomy does not exist.' );
    }

    $args = array();
    if ( isset( $input['term_name'] ) && ! empty( $input['term_name'] ) ) {
        $args['name'] = sanitize_text_field( $input['term_name'] );
    }
    if ( isset( $input['description'] ) ) {
        $args['description'] = sanitize_textarea_field( $input['description'] );
    }

    $result = wp_update_term( $term_id, $taxonomy, $args );

    if ( is_wp_error( $result ) ) {
        return $result;
    }

    return array(
        'term_id'  => $result['term_id'],
        'taxonomy' => $taxonomy,
        'message'  => 'Term updated successfully.'
    );
}

function gw_delete_term_execute( $input ) {
    $term_id  = intval( $input['term_id'] );
    $taxonomy = sanitize_key( $input['taxonomy'] );

    if ( ! taxonomy_exists( $taxonomy ) ) {
        return new WP_Error( 'invalid_taxonomy', 'The requested taxonomy does not exist.' );
    }

    $result = wp_delete_term( $term_id, $taxonomy );

    if ( is_wp_error( $result ) ) {
        return $result;
    }
    
    if ( false === $result || 0 === $result ) {
        return new WP_Error( 'delete_failed', 'Term could not be deleted (it might not exist or is a default term).' );
    }

    return array(
        'term_id'  => $term_id,
        'taxonomy' => $taxonomy,
        'message'  => 'Term deleted successfully.'
    );
}
