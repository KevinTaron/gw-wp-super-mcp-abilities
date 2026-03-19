<?php
/**
 * Class to manage registration of MCP Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GW_MCP_Registrator {
    
    public static function init() {
        add_action( 'wp_abilities_api_categories_init', [ __CLASS__, 'register_category' ] );
        add_action( 'wp_abilities_api_init', [ __CLASS__, 'register_abilities' ] );
    }

    public static function register_category() {
        if ( function_exists( 'wp_register_ability_category' ) ) {
            wp_register_ability_category(
                'gw',
                array(
                    'label'       => 'Gutwerker',
                    'description' => 'Abilities for GW MCP content overview and editing.',
                )
            );
        }
    }

    public static function register_abilities() {
        // Here we trigger all our sub-files if they hook into an action, or just call their init functions
        do_action( 'gw_mcp_register_abilities' );
    }
}
