<?php
/**
 * Gutenberg & FSE (Full Site Editing) Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_gutenberg_abilities' );

function gw_mcp_register_gutenberg_abilities() {

    // 1. List Block Patterns
    if ( gw_mcp_is_ability_active( 'read_gutenberg' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/list-block-patterns',
            array(
                'category'    => 'gw',
                'label'       => 'List Block Patterns',
                'description' => 'Returns all registered Gutenberg Block Patterns (reusable HTML/Block structures). Use the content to generate rich posts via update-post.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_list_block_patterns_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 2. Read Templates (FSE)
    if ( gw_mcp_is_ability_active( 'read_gutenberg' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-templates',
            array(
                'category'    => 'gw',
                'label'       => 'Read FSE Templates',
                'description' => 'Lists block templates (wp_template and wp_template_part) used by the active block theme.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'type' => array(
                            'type'        => 'string',
                            'description' => 'Filter by template type: wp_template (page layouts) or wp_template_part (headers, footers). Default: wp_template',
                            'default'     => 'wp_template'
                        ),
                    ),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_templates_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}

function gw_list_block_patterns_execute( $input ) {
    if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
        return new WP_Error( 'not_supported', 'Gutenberg Block Patterns are not supported on this installation.' );
    }

    $registry = WP_Block_Patterns_Registry::get_instance();
    $patterns = $registry->get_all_registered();
    
    $output = array();

    foreach ( $patterns as $pattern ) {
        $output[] = array(
            'name'       => $pattern['name'],
            'title'      => $pattern['title'],
            'categories' => isset( $pattern['categories'] ) ? $pattern['categories'] : array(),
            'content'    => $pattern['content'], // The raw block markup
        );
    }

    return array(
        'total'    => count( $output ),
        'patterns' => $output
    );
}

function gw_read_templates_execute( $input ) {
    // In block themes, templates are custom post types: wp_template and wp_template_part
    $template_type = isset( $input['type'] ) && $input['type'] === 'wp_template_part' ? 'wp_template_part' : 'wp_template';

    $args = array(
        'post_type'      => $template_type,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    $query = new WP_Query( $args );
    $output = array();

    if ( ! empty( $query->posts ) ) {
        foreach ( $query->posts as $post ) {
            $output[] = array(
                'id'      => $post->ID,
                'slug'    => $post->post_name,
                'title'   => $post->post_title,
                'content' => $post->post_content,
            );
        }
    } else {
        // Fallback: If no db templates, maybe get from theme files (block themes)
        if ( function_exists( 'get_block_templates' ) ) {
            $templates = get_block_templates( array(), $template_type );
            foreach ( $templates as $t ) {
                $output[] = array(
                    'id'      => $t->id,
                    'slug'    => $t->slug,
                    'title'   => $t->title,
                    'content' => $t->content,
                );
            }
        }
    }

    return array(
        'type'      => $template_type,
        'total'     => count( $output ),
        'templates' => $output
    );
}
