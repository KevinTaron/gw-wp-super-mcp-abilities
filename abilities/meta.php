<?php
/**
 * Meta Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_meta_abilities' );

function gw_mcp_register_meta_abilities() {

    // 1. Read Metadata
    if ( gw_mcp_is_ability_active( 'read_metadata' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-metadata',
            array(
                'category'    => 'gw',
                'label'       => 'Get Post/Page Metadata',
                'description' => 'Returns metadata of any specific post, page, or CPT.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the post/page.',
                        ),
                        'include_hidden' => array(
                            'type'        => 'boolean',
                            'description' => 'Whether to include keys starting with _ (underscore). Default false.',
                            'default'     => false,
                        )
                    ),
                    'required' => [ 'post_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_metadata_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 2. Write Metadata
    if ( gw_mcp_is_ability_active( 'write_metadata' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/write-metadata',
            array(
                'category'    => 'gw',
                'label'       => 'Write Post/Page Metadata',
                'description' => 'Creates, updates, or deletes metadata (custom fields) of any post, page, or CPT. Pass a key-value object. To delete a meta key, set its value to the string "_delete".',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the post/page/CPT.',
                        ),
                        'meta' => array(
                            'type'        => 'object',
                            'description' => 'Key-value pairs of metadata to set. Use the string "_delete" as value to remove a meta key.',
                        ),
                    ),
                    'required' => [ 'post_id', 'meta' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_write_metadata_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}

function gw_read_metadata_execute( $input ) {
    $post_id        = intval( $input['post_id'] );
    $include_hidden = isset( $input['include_hidden'] ) ? (bool) $input['include_hidden'] : false;

    // Validate post exists
    $post = get_post( $post_id );
    if ( ! $post ) {
        return new WP_Error( 'not_found', 'Content not found.' );
    }

    $all_meta   = get_post_meta( $post_id );
    $clean_meta = array();

    if ( is_array( $all_meta ) ) {
        foreach ( $all_meta as $key => $values ) {
            // Filter out hidden custom fields if $include_hidden is false
            if ( ! $include_hidden && strpos( $key, '_' ) === 0 ) {
                continue;
            }

            // Simplification: if only one value exists, extract it out of the nested array (WP defaults to arrays)
            if ( count( $values ) === 1 ) {
                $clean_meta[ $key ] = maybe_unserialize( $values[0] );
            } else {
                $clean_meta[ $key ] = array_map( 'maybe_unserialize', $values );
            }
        }
    }

    return array(
        'post_id'   => $post_id,
        'post_type' => $post->post_type,
        'meta'      => $clean_meta
    );
}

function gw_write_metadata_execute( $input ) {
    $post_id = intval( $input['post_id'] );
    $meta    = $input['meta'];

    // Validate post exists
    $post = get_post( $post_id );
    if ( ! $post ) {
        return new WP_Error( 'not_found', 'Content not found.' );
    }

    if ( ! is_array( $meta ) && ! is_object( $meta ) ) {
        return new WP_Error( 'invalid_meta', 'The "meta" parameter must be a key-value object.' );
    }

    $updated = array();
    $deleted = array();
    $errors  = array();

    foreach ( (array) $meta as $key => $value ) {
        $sanitized_key = sanitize_key( $key );

        if ( empty( $sanitized_key ) ) {
            $errors[] = "Invalid meta key: '{$key}'";
            continue;
        }

        // Delete if value is the special string "_delete"
        if ( $value === '_delete' ) {
            $result = delete_post_meta( $post_id, $sanitized_key );
            if ( $result ) {
                $deleted[] = $sanitized_key;
            } else {
                $errors[] = "Could not delete key '{$sanitized_key}' (may not exist).";
            }
            continue;
        }

        // Sanitize scalar values, allow arrays/objects to pass through (they get serialized by WP)
        $sanitized_value = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $value;

        $result = update_post_meta( $post_id, $sanitized_key, $sanitized_value );
        if ( false !== $result ) {
            $updated[ $sanitized_key ] = $sanitized_value;
        } else {
            $errors[] = "Failed to update key '{$sanitized_key}'.";
        }
    }

    return array(
        'post_id' => $post_id,
        'updated' => $updated,
        'deleted' => $deleted,
        'errors'  => $errors,
        'message' => sprintf(
            '%d meta field(s) updated, %d deleted.',
            count( $updated ),
            count( $deleted )
        ),
    );
}
