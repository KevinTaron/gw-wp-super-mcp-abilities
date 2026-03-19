<?php
/**
 * Plugin Name:       Gutwerker MCP Abilities
 * Plugin URI:        https://github.com/gutwerker/gw-super-mcp-abilities
 * Description:       Exposes WordPress content (posts, pages, CPTs, media, metadata & taxonomies) to AI assistants via the Model Context Protocol (MCP).
 * Version:           1.4.1
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Requires Plugins:  mcp-adapter, abilities-api
 * Author:            Gutwerker
 * Author URI:        https://gutwerker.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gw-mcp-abilities
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Settings Page
if ( file_exists( plugin_dir_path( __FILE__ ) . 'admin/settings-page.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';
}

// 2. Registrator Class
if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/class-gw-mcp-registrator.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-gw-mcp-registrator.php';
}

// 3. Load Abilities (Only if their dependencies or files exist)
$abilities_dir = plugin_dir_path( __FILE__ ) . 'abilities/';

$ability_files = [
    'posts.php',
    'pages.php',
    'cpts.php',
    'meta.php',
    'taxonomies.php',
    'media.php',
    'seo.php',
    'options.php',
    'site-info.php',
    'search.php',
    'gutenberg.php',
];

foreach ( $ability_files as $file ) {
    if ( file_exists( $abilities_dir . $file ) ) {
        require_once $abilities_dir . $file;
    }
}

// Initialize the Registrator
if ( class_exists( 'GW_MCP_Registrator' ) ) {
    GW_MCP_Registrator::init();
}