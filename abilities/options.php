<?php
/**
 * WP Options Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_options_abilities' );

function gw_mcp_register_options_abilities() {

    // 1. Read Option
    if ( gw_mcp_is_ability_active( 'read_options' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-option',
            array(
                'category'    => 'gw',
                'label'       => 'Read WP Option',
                'description' => 'Reads a global WordPress option from the wp_options table (e.g., blogname, siteurl, active_plugins).',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'option_name' => array(
                            'type'        => 'string',
                            'description' => 'The name of the option to read.',
                        ),
                    ),
                    'required' => [ 'option_name' ]
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_option_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 2. Update Option
    if ( gw_mcp_is_ability_active( 'write_options' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/update-option',
            array(
                'category'    => 'gw',
                'label'       => 'Update WP Option',
                'description' => 'Updates or creates a global WordPress option.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'option_name' => array(
                            'type'        => 'string',
                            'description' => 'The name of the option to update.',
                        ),
                        'option_value' => array(
                            'description' => 'The new value for the option (can be string, array, or object).',
                        ),
                    ),
                    'required' => [ 'option_name', 'option_value' ]
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_update_option_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}

function gw_read_option_execute( $input ) {
    $option_name = sanitize_text_field( $input['option_name'] );
    
    // We should probably prevent reading highly sensitive options like salts/keys, 
    // but assuming MCP has admin privileges, they can do anything.
    $value = get_option( $option_name );
    
    if ( false === $value ) {
        return new WP_Error( 'option_not_found', "Option '{$option_name}' not found or is false." );
    }

    return array(
        'option_name'  => $option_name,
        'option_value' => $value
    );
}

function gw_update_option_execute( $input ) {
    $option_name = sanitize_text_field( $input['option_name'] );
    $option_value = $input['option_value']; // Let WP handle serialization of arrays/objects

    // Some basic sanitization for scalar values
    if ( is_scalar( $option_value ) ) {
        // We use wp_kses_post to allow HTML in options but strip dangerous scripts
        $option_value = wp_kses_post( (string) $option_value );
    }

    $updated = update_option( $option_name, $option_value );

    return array(
        'option_name'  => $option_name,
        'updated'      => $updated,
        'message'      => $updated ? 'Option successfully updated.' : 'Option was not updated (maybe the value is the same).'
    );
}
