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
