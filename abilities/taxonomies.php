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
                'label'       => 'Get Taxonomies (Categories, Tags, Custom)',
                'description' => 'Returns all taxonomy types registered for a post type or globally. (Note: In WordPress, "Categories" and "Tags" are just built-in taxonomies).',
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

    // 2. Read Terms (Categories/Tags)
    if ( gw_mcp_is_ability_active( 'read_terms' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-terms',
            array(
                'category'    => 'gw',
                'label'       => 'Get Terms (Categories, Tags)',
                'description' => 'Returns list of actual terms (like your specific Categories, Tags, or custom taxonomy items) within a requested taxonomy.',
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

    // 3. Create Term (Category/Tag)
    if ( gw_mcp_is_ability_active( 'write_terms' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/create-term',
            array(
                'category'    => 'gw',
                'label'       => 'Create Category / Tag / Term',
                'description' => 'Creates a new term (like a new Category or Tag) in a specific taxonomy.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'taxonomy' => array(
                            'type'        => 'string',
                            'description' => 'The slug of the taxonomy (e.g., category, post_tag).',
                        ),
                        'term_name' => array(
                            'type'        => 'string',
                            'description' => 'The name of the new term (e.g. "News" or "Updates").',
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

    // 4. Update Term (Category/Tag)
    if ( gw_mcp_is_ability_active( 'write_terms' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/update-term',
            array(
                'category'    => 'gw',
                'label'       => 'Update Category / Tag / Term',
                'description' => 'Updates an existing term (e.g. renaming a Category).',
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

    // 5. Delete Term (Category/Tag)
    if ( gw_mcp_is_ability_active( 'write_terms' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/delete-term',
            array(
                'category'    => 'gw',
                'label'       => 'Delete Category / Tag / Term',
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

    // 6. Assign Terms to Post
    if ( gw_mcp_is_ability_active( 'write_terms' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/assign-terms',
            array(
                'category'    => 'gw',
                'label'       => 'Assign Terms (Categories/Tags) to Post',
                'description' => 'Assigns categories, tags, or custom terms to a specific post.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the post.',
                        ),
                        'taxonomy' => array(
                            'type'        => 'string',
                            'description' => 'The taxonomy to set terms for (e.g. category, post_tag).',
                        ),
                        'terms' => array(
                            'type'        => 'array',
                            'items'       => [ 'type' => 'integer' ],
                            'description' => 'Array of Term IDs to assign. Example: [12, 14]',
                        ),
                        'append' => array(
                            'type'        => 'boolean',
                            'description' => 'If true, appends terms instead of replacing existing ones. Default false.',
                            'default'     => false
                        ),
                    ),
                    'required' => [ 'post_id', 'taxonomy', 'terms' ]
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_assign_terms_execute',
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

function gw_assign_terms_execute( $input ) {
    $post_id  = intval( $input['post_id'] );
    $taxonomy = sanitize_key( $input['taxonomy'] );
    $append   = isset( $input['append'] ) ? (bool) $input['append'] : false;

    // Validate post exists
    if ( ! get_post( $post_id ) ) {
        return new WP_Error( 'not_found', 'Content not found.' );
    }

    if ( ! taxonomy_exists( $taxonomy ) ) {
        return new WP_Error( 'invalid_taxonomy', 'The requested taxonomy does not exist.' );
    }

    $terms = array();
    if ( isset( $input['terms'] ) && is_array( $input['terms'] ) ) {
        $terms = array_map( 'intval', $input['terms'] );
    }

    $result = wp_set_object_terms( $post_id, $terms, $taxonomy, $append );

    if ( is_wp_error( $result ) ) {
        return $result;
    }

    return array(
        'post_id'  => $post_id,
        'taxonomy' => $taxonomy,
        'assigned' => $result,
        'message'  => 'Terms assigned successfully.'
    );
}
