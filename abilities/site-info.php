<?php
/**
 * Site Info Abilities (Plugins, Themes, Menus)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_site_info_abilities' );

function gw_mcp_register_site_info_abilities() {

    // 1. List Plugins
    if ( gw_mcp_is_ability_active( 'read_site_info' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/list-plugins',
            array(
                'category'    => 'gw',
                'label'       => 'List Plugins',
                'description' => 'Returns a list of all installed plugins and their active status.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_list_plugins_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 2. List Themes
    if ( gw_mcp_is_ability_active( 'read_site_info' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/list-themes',
            array(
                'category'    => 'gw',
                'label'       => 'List Themes',
                'description' => 'Returns a list of available themes and identifies the active one.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_list_themes_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 3. Get Menus
    if ( gw_mcp_is_ability_active( 'read_site_info' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/get-menus',
            array(
                'category'    => 'gw',
                'label'       => 'Get Menus',
                'description' => 'Returns registered nav menus and their items.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_get_menus_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}

function gw_list_plugins_execute( $input ) {
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $all_plugins = get_plugins();
    $active_plugins = get_option( 'active_plugins', array() );
    
    $plugins_list = array();

    foreach ( $all_plugins as $plugin_path => $plugin_data ) {
        $plugins_list[] = array(
            'name'        => $plugin_data['Name'],
            'plugin_path' => $plugin_path,
            'version'     => $plugin_data['Version'],
            'is_active'   => in_array( $plugin_path, $active_plugins, true ),
            'description' => wp_strip_all_tags( $plugin_data['Description'] ),
        );
    }

    return array(
        'total' => count( $plugins_list ),
        'plugins' => $plugins_list
    );
}

function gw_list_themes_execute( $input ) {
    $themes = wp_get_themes();
    $active_theme = wp_get_theme();

    $themes_list = array();

    foreach ( $themes as $stylesheet => $theme ) {
        $themes_list[] = array(
            'name'       => $theme->get( 'Name' ),
            'stylesheet' => $stylesheet,
            'version'    => $theme->get( 'Version' ),
            'is_active'  => ( $stylesheet === $active_theme->get_stylesheet() ),
        );
    }

    return array(
        'total'  => count( $themes_list ),
        'themes' => $themes_list
    );
}

function gw_get_menus_execute( $input ) {
    $menus = wp_get_nav_menus();
    $menus_list = array();

    foreach ( $menus as $menu ) {
        $items = wp_get_nav_menu_items( $menu->term_id );
        $menu_items = array();
        
        if ( $items ) {
            foreach ( $items as $item ) {
                $menu_items[] = array(
                    'id'    => $item->ID,
                    'title' => $item->title,
                    'url'   => $item->url,
                    'type'  => $item->type_label,
                );
            }
        }

        $menus_list[] = array(
            'menu_id'   => $menu->term_id,
            'name'      => $menu->name,
            'locations' => get_nav_menu_locations(), // This might need parsing to match term_id
            'items'     => $menu_items
        );
    }

    return array(
        'total' => count( $menus_list ),
        'menus' => $menus_list
    );
}
