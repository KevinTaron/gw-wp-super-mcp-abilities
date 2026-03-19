<?php
/**
 * SEO Abilities (Rank Math & Yoast SEO)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_seo_abilities' );

function gw_mcp_is_seo_plugin_active() {
    if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
        return true;
    }
    
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ) {
        return true;
    }

    if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) || is_plugin_active( 'seo-by-rank-math-pro/rank-math-pro.php' ) ) {
        return true;
    }
    
    return false;
}

function gw_mcp_register_seo_abilities() {
    // Only register if RankMath or Yoast is active
    if ( ! gw_mcp_is_seo_plugin_active() ) {
        return;
    }

    // 1. Read SEO Meta
    if ( gw_mcp_is_ability_active( 'read_seo' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-seo',
            array(
                'category'    => 'gw',
                'label'       => 'Read SEO Meta (Rank Math / Yoast)',
                'description' => 'Reads the SEO title, meta description, and focus keyword for a specific post. Automatically detects Yoast SEO or Rank Math.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the post/page.',
                        ),
                    ),
                    'required' => [ 'post_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_seo_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 2. Update SEO Meta
    if ( gw_mcp_is_ability_active( 'update_seo' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/update-seo',
            array(
                'category'    => 'gw',
                'label'       => 'Update SEO Meta (Rank Math / Yoast)',
                'description' => 'Updates the SEO title, meta description, and focus keyword for a specific post. Automatically detects Yoast SEO or Rank Math.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the post/page.',
                        ),
                        'focus_keyword' => array(
                            'type'        => 'string',
                            'description' => 'The focus keyword for the post.',
                        ),
                        'seo_title' => array(
                            'type'        => 'string',
                            'description' => 'The SEO title (SERP title).',
                        ),
                        'seo_description' => array(
                            'type'        => 'string',
                            'description' => 'The SEO meta description (SERP snippet).',
                        ),
                    ),
                    'required' => [ 'post_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_update_seo_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}

function gw_update_seo_execute( $input ) {
    $post_id = intval( $input['post_id'] );
    $post    = get_post( $post_id );
    
    if ( ! $post ) {
        return new WP_Error( 'not_found', 'Content not found.' );
    }

    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $is_yoast    = defined('WPSEO_VERSION') || is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' );
    $is_rankmath = defined('RANK_MATH_VERSION') || is_plugin_active( 'seo-by-rank-math/rank-math.php' ) || is_plugin_active( 'seo-by-rank-math-pro/rank-math-pro.php' );

    $updated = array();
    $plugin_used = array();

    if ( $is_yoast ) {
        $plugin_used[] = 'Yoast SEO';
    }
    if ( $is_rankmath ) {
        $plugin_used[] = 'Rank Math';
    }

    if ( empty( $plugin_used ) ) {
        return new WP_Error( 'no_seo_plugin', 'Neither Yoast SEO nor Rank Math are active.' );
    }

    // Focus Keyword
    if ( isset( $input['focus_keyword'] ) ) {
        $keyword = sanitize_text_field( $input['focus_keyword'] );
        if ( $is_yoast ) {
            update_post_meta( $post_id, '_yoast_wpseo_focuskw', $keyword );
        }
        if ( $is_rankmath ) {
            update_post_meta( $post_id, 'rank_math_focus_keyword', $keyword );
        }
        $updated['focus_keyword'] = $keyword;
    }

    // SEO Title
    if ( isset( $input['seo_title'] ) ) {
        $title = sanitize_text_field( $input['seo_title'] );
        if ( $is_yoast ) {
            update_post_meta( $post_id, '_yoast_wpseo_title', $title );
        }
        if ( $is_rankmath ) {
            update_post_meta( $post_id, 'rank_math_title', $title );
        }
        $updated['seo_title'] = $title;
    }

    // SEO Description
    if ( isset( $input['seo_description'] ) ) {
        $description = sanitize_textarea_field( $input['seo_description'] );
        if ( $is_yoast ) {
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $description );
        }
        if ( $is_rankmath ) {
            update_post_meta( $post_id, 'rank_math_description', $description );
        }
        $updated['seo_description'] = $description;
    }

    return array(
        'post_id' => $post_id,
        'plugin'  => implode( ' and ', $plugin_used ),
        'updated' => $updated,
        'message' => 'SEO metadata successfully updated.'
    );
}

function gw_read_seo_execute( $input ) {
    $post_id = intval( $input['post_id'] );
    $post    = get_post( $post_id );
    
    if ( ! $post ) {
        return new WP_Error( 'not_found', 'Content not found.' );
    }

    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $is_yoast    = defined('WPSEO_VERSION') || is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' );
    $is_rankmath = defined('RANK_MATH_VERSION') || is_plugin_active( 'seo-by-rank-math/rank-math.php' ) || is_plugin_active( 'seo-by-rank-math-pro/rank-math-pro.php' );

    $plugin_used = array();
    $seo_data = array(
        'focus_keyword' => '',
        'seo_title' => '',
        'seo_description' => ''
    );

    if ( $is_yoast ) {
        $plugin_used[] = 'Yoast SEO';
        $seo_data['focus_keyword'] = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
        $seo_data['seo_title'] = get_post_meta( $post_id, '_yoast_wpseo_title', true );
        $seo_data['seo_description'] = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
    } 
    
    if ( $is_rankmath && empty($seo_data['focus_keyword']) && empty($seo_data['seo_title']) && empty($seo_data['seo_description']) ) {
        // Fallback to rankmath if Yoast data is empty (in case both installed, or Yoast not installed)
        if ( ! in_array('Rank Math', $plugin_used) ) {
            $plugin_used[] = 'Rank Math';
        }
        $seo_data['focus_keyword'] = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
        $seo_data['seo_title'] = get_post_meta( $post_id, 'rank_math_title', true );
        $seo_data['seo_description'] = get_post_meta( $post_id, 'rank_math_description', true );
    }

    if ( empty( $plugin_used ) ) {
        return new WP_Error( 'no_seo_plugin', 'Neither Yoast SEO nor Rank Math are active.' );
    }

    return array(
        'post_id' => $post_id,
        'plugin'  => implode( ' and ', $plugin_used ),
        'seo' => $seo_data
    );
}
