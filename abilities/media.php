<?php
/**
 * Media Abilities
 * 
 * Upload, list, read, update, delete and manage WordPress media library items.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gw_mcp_register_abilities', 'gw_mcp_register_media_abilities' );

function gw_mcp_register_media_abilities() {

    // 1. List Media
    if ( gw_mcp_is_ability_active( 'list_media' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/list-media',
            array(
                'category'    => 'gw',
                'label'       => 'List Media Library Items',
                'description' => 'Returns media library items (images, PDFs, videos, etc.) with filters for type, search and pagination.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'per_page' => array(
                            'type'        => 'integer',
                            'description' => 'Number of items to return. Default 50.',
                            'default'     => 50,
                        ),
                        'page' => array(
                            'type'        => 'integer',
                            'description' => 'Page number for pagination. Default 1.',
                            'default'     => 1,
                        ),
                        'mime_type' => array(
                            'type'        => 'string',
                            'description' => 'Filter by MIME type. Examples: "image", "image/jpeg", "application/pdf", "video", "audio".',
                        ),
                        'search' => array(
                            'type'        => 'string',
                            'description' => 'Optional search term to filter media by title or filename.',
                        ),
                        'orderby' => array(
                            'type'        => 'string',
                            'description' => 'Order by: date, title, modified. Default: date.',
                            'default'     => 'date',
                        ),
                        'order' => array(
                            'type'        => 'string',
                            'description' => 'Sort order: ASC or DESC. Default: DESC.',
                            'default'     => 'DESC',
                        ),
                    ),
                ),
                'output_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'media' => array( 'type' => 'array' ),
                        'total' => array( 'type' => 'integer' ),
                        'pages' => array( 'type' => 'integer' ),
                    ),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_list_media_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 2. Read Media Details
    if ( gw_mcp_is_ability_active( 'read_media_details' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/read-media-details',
            array(
                'category'    => 'gw',
                'label'       => 'Get Media Details',
                'description' => 'Returns full details of a media item including all metadata (alt text, caption, description, dimensions, file size, MIME type, available sizes).',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'attachment_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the media attachment.',
                        ),
                    ),
                    'required' => [ 'attachment_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_read_media_details_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 3. Upload Media (from URL or Base64)
    if ( gw_mcp_is_ability_active( 'upload_media' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/upload-media',
            array(
                'category'    => 'gw',
                'label'       => 'Upload Media (URL or Base64)',
                'description' => 'Adds a file to the WordPress media library. Supports two modes: (1) Provide a "url" to download a file from the web — works for any file size, this is the simplest approach when a public URL exists. (2) Provide "base64_data" with raw base64-encoded content and a "filename" — ONLY for very small files (under ~200 KB). IMPORTANT: For any base64 upload larger than ~200 KB, you MUST use the chunked upload workflow instead (gw/start-chunked-upload → gw/append-upload-chunk → gw/finish-chunked-upload). The chunked workflow is the recommended default for all base64 file uploads to avoid transfer issues.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'url' => array(
                            'type'        => 'string',
                            'description' => 'Option A: A public URL to download the file from (e.g. https://example.com/photo.jpg). Do NOT provide base64_data when using this.',
                        ),
                        'base64_data' => array(
                            'type'        => 'string',
                            'description' => 'Option B: Raw base64-encoded file content (without data URI prefix). WARNING: Only use this for very small files under ~200 KB. For anything larger, use the chunked upload workflow (gw/start-chunked-upload) instead. When using this, you MUST also provide "filename" with an extension (e.g. "icon.png"). Do NOT provide url when using this.',
                        ),
                        'filename' => array(
                            'type'        => 'string',
                            'description' => 'Required when using base64_data. The desired filename with extension, e.g. "product-photo.jpg", "document.pdf". The extension determines the MIME type.',
                        ),
                        'title' => array(
                            'type'        => 'string',
                            'description' => 'Optional title for the media item. If omitted, the filename is used.',
                        ),
                        'alt_text' => array(
                            'type'        => 'string',
                            'description' => 'Optional alt text for images (important for SEO and accessibility).',
                        ),
                        'caption' => array(
                            'type'        => 'string',
                            'description' => 'Optional caption (shown below images in themes).',
                        ),
                        'description' => array(
                            'type'        => 'string',
                            'description' => 'Optional long description for the media item.',
                        ),
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'Optional post ID to attach this media to as a child.',
                        ),
                    ),
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_upload_media_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 4. Update Media Metadata
    if ( gw_mcp_is_ability_active( 'update_media' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/update-media',
            array(
                'category'    => 'gw',
                'label'       => 'Update Media Metadata',
                'description' => 'Updates metadata of an existing media item: title, alt text, caption, description. Can also change the parent post.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'attachment_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the media attachment to update.',
                        ),
                        'title' => array(
                            'type'        => 'string',
                            'description' => 'New title for the media item.',
                        ),
                        'alt_text' => array(
                            'type'        => 'string',
                            'description' => 'New alt text (used for SEO and accessibility).',
                        ),
                        'caption' => array(
                            'type'        => 'string',
                            'description' => 'New caption for the media item.',
                        ),
                        'description' => array(
                            'type'        => 'string',
                            'description' => 'New description for the media item.',
                        ),
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'Attach this media to a different post (set parent).',
                        ),
                    ),
                    'required' => [ 'attachment_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_update_media_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 5. Delete Media
    if ( gw_mcp_is_ability_active( 'delete_media' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/delete-media',
            array(
                'category'    => 'gw',
                'label'       => 'Delete Media',
                'description' => 'Permanently deletes a media item from the library including all generated thumbnail sizes.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'attachment_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the media attachment to delete.',
                        ),
                        'force' => array(
                            'type'        => 'boolean',
                            'description' => 'Whether to bypass trash and force permanent deletion. Default true.',
                            'default'     => true,
                        ),
                    ),
                    'required' => [ 'attachment_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_delete_media_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 6. Set Featured Image
    if ( gw_mcp_is_ability_active( 'set_featured_image' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/set-featured-image',
            array(
                'category'    => 'gw',
                'label'       => 'Set Featured Image',
                'description' => 'Sets or removes the featured image (post thumbnail) for a post, page or CPT. Provide an attachment_id to set, or set remove=true to remove.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the post/page/CPT.',
                        ),
                        'attachment_id' => array(
                            'type'        => 'integer',
                            'description' => 'The ID of the media attachment to set as featured image.',
                        ),
                        'remove' => array(
                            'type'        => 'boolean',
                            'description' => 'Set to true to remove the featured image instead of setting one.',
                            'default'     => false,
                        ),
                    ),
                    'required' => [ 'post_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_set_featured_image_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 7. Bulk Update Media Metadata
    if ( gw_mcp_is_ability_active( 'bulk_update_media_meta' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/bulk-update-media-meta',
            array(
                'category'    => 'gw',
                'label'       => 'Bulk Update Media Metadata',
                'description' => 'Updates metadata (alt text, title, caption, description) for multiple media items at once. Useful for mass-updating alt texts for SEO.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'items' => array(
                            'type'        => 'array',
                            'description' => 'Array of objects, each containing attachment_id and the fields to update (alt_text, title, caption, description).',
                            'items' => array(
                                'type'       => 'object',
                                'properties' => array(
                                    'attachment_id' => array(
                                        'type'        => 'integer',
                                        'description' => 'The ID of the media attachment.',
                                    ),
                                    'title' => array(
                                        'type'        => 'string',
                                        'description' => 'New title.',
                                    ),
                                    'alt_text' => array(
                                        'type'        => 'string',
                                        'description' => 'New alt text.',
                                    ),
                                    'caption' => array(
                                        'type'        => 'string',
                                        'description' => 'New caption.',
                                    ),
                                    'description' => array(
                                        'type'        => 'string',
                                        'description' => 'New description.',
                                    ),
                                ),
                                'required' => [ 'attachment_id' ],
                            ),
                        ),
                    ),
                    'required' => [ 'items' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_bulk_update_media_meta_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    /* ── Chunked Upload (3 steps) ──────────────── */

    // 8. Start Chunked Upload
    if ( gw_mcp_is_ability_active( 'upload_media' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/start-chunked-upload',
            array(
                'category'    => 'gw',
                'label'       => 'Start Chunked Upload',
                'description' => 'The recommended way to upload files via base64 data. Use this for ALL base64 uploads except very small files (under ~200 KB). Starts a chunked file upload session and returns an upload_id. Workflow: (1) call this to get an upload_id, (2) call gw/append-upload-chunk multiple times — split the base64 data into chunks of ~500 KB each, (3) call gw/finish-chunked-upload to assemble the file and import it into the media library. This avoids transfer issues that occur when sending large base64 strings in a single request.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'filename' => array(
                            'type'        => 'string',
                            'description' => 'The desired filename with extension, e.g. "large-photo.jpg", "brochure.pdf". Required.',
                        ),
                        'total_chunks' => array(
                            'type'        => 'integer',
                            'description' => 'Optional. The expected total number of chunks. Used for progress tracking.',
                        ),
                    ),
                    'required' => [ 'filename' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_start_chunked_upload_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 9. Append Upload Chunk
    if ( gw_mcp_is_ability_active( 'upload_media' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/append-upload-chunk',
            array(
                'category'    => 'gw',
                'label'       => 'Append Upload Chunk',
                'description' => 'Appends a base64-encoded chunk of data to an ongoing chunked upload session. Call this multiple times in sequence after start-chunked-upload. Each chunk should be around 500KB of base64 data (roughly 375KB decoded). Send chunks in order — each chunk is appended to the file.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'upload_id' => array(
                            'type'        => 'string',
                            'description' => 'The upload_id returned by start-chunked-upload.',
                        ),
                        'chunk_data' => array(
                            'type'        => 'string',
                            'description' => 'A chunk of the file as raw base64-encoded string (no data URI prefix). Keep each chunk around 500KB of base64 text.',
                        ),
                        'chunk_index' => array(
                            'type'        => 'integer',
                            'description' => 'The 1-based index of this chunk (for tracking). First chunk = 1.',
                        ),
                    ),
                    'required' => [ 'upload_id', 'chunk_data', 'chunk_index' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_append_upload_chunk_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }

    // 10. Finish Chunked Upload
    if ( gw_mcp_is_ability_active( 'upload_media' ) && function_exists( 'wp_register_ability' ) ) {
        wp_register_ability(
            'gw/finish-chunked-upload',
            array(
                'category'    => 'gw',
                'label'       => 'Finish Chunked Upload',
                'description' => 'Finalizes a chunked upload session. The assembled file is imported into the WordPress media library. Optionally set title, alt_text, caption, description and post_id. Call this after all chunks have been appended via append-upload-chunk.',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'upload_id' => array(
                            'type'        => 'string',
                            'description' => 'The upload_id returned by start-chunked-upload.',
                        ),
                        'title' => array(
                            'type'        => 'string',
                            'description' => 'Optional title for the media item.',
                        ),
                        'alt_text' => array(
                            'type'        => 'string',
                            'description' => 'Optional alt text for images.',
                        ),
                        'caption' => array(
                            'type'        => 'string',
                            'description' => 'Optional caption.',
                        ),
                        'description' => array(
                            'type'        => 'string',
                            'description' => 'Optional description.',
                        ),
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => 'Optional post ID to attach this media to.',
                        ),
                    ),
                    'required' => [ 'upload_id' ],
                ),
                'permission_callback' => '__return_true',
                'execute_callback'    => 'gw_finish_chunked_upload_execute',
                'meta' => array( 'mcp' => array( 'public' => true ) ),
            )
        );
    }
}


/* ─────────────────────────────────────────────
 * EXECUTE CALLBACKS
 * ───────────────────────────────────────────── */

/**
 * 1. List Media
 */
function gw_list_media_execute( $input ) {
    $per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 50;
    $page     = isset( $input['page'] ) ? intval( $input['page'] ) : 1;

    $args = array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => isset( $input['orderby'] ) ? sanitize_text_field( $input['orderby'] ) : 'date',
        'order'          => isset( $input['order'] ) ? sanitize_text_field( $input['order'] ) : 'DESC',
    );

    if ( ! empty( $input['mime_type'] ) ) {
        $args['post_mime_type'] = sanitize_text_field( $input['mime_type'] );
    }

    if ( ! empty( $input['search'] ) ) {
        $args['s'] = sanitize_text_field( $input['search'] );
    }

    $query = new WP_Query( $args );

    $media = array();
    foreach ( $query->posts as $attachment ) {
        $metadata = wp_get_attachment_metadata( $attachment->ID );

        $item = array(
            'id'        => $attachment->ID,
            'title'     => $attachment->post_title,
            'filename'  => basename( get_attached_file( $attachment->ID ) ),
            'url'       => wp_get_attachment_url( $attachment->ID ),
            'mime_type' => $attachment->post_mime_type,
            'date'      => get_the_date( 'Y-m-d H:i:s', $attachment->ID ),
            'alt_text'  => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
        );

        // Add dimensions for images
        if ( is_array( $metadata ) && isset( $metadata['width'] ) ) {
            $item['width']  = $metadata['width'];
            $item['height'] = $metadata['height'];
        }

        // Add file size
        $file_path = get_attached_file( $attachment->ID );
        if ( $file_path && file_exists( $file_path ) ) {
            $item['file_size'] = size_format( filesize( $file_path ) );
        }

        $media[] = $item;
    }

    return array(
        'media' => $media,
        'total' => $query->found_posts,
        'pages' => $query->max_num_pages,
    );
}

/**
 * 2. Read Media Details
 */
function gw_read_media_details_execute( $input ) {
    $attachment_id = intval( $input['attachment_id'] );
    $attachment    = get_post( $attachment_id );

    if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
        return new WP_Error( 'not_found', 'Media item not found.' );
    }

    $metadata  = wp_get_attachment_metadata( $attachment_id );
    $file_path = get_attached_file( $attachment_id );

    $result = array(
        'id'          => $attachment->ID,
        'title'       => $attachment->post_title,
        'filename'    => basename( $file_path ),
        'url'         => wp_get_attachment_url( $attachment_id ),
        'mime_type'   => $attachment->post_mime_type,
        'date'        => get_the_date( 'Y-m-d H:i:s', $attachment_id ),
        'modified'    => get_the_modified_date( 'Y-m-d H:i:s', $attachment_id ),
        'alt_text'    => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
        'caption'     => $attachment->post_excerpt,
        'description' => $attachment->post_content,
        'parent_id'   => $attachment->post_parent,
        'author'      => get_the_author_meta( 'display_name', $attachment->post_author ),
    );

    // Image-specific metadata
    if ( is_array( $metadata ) ) {
        if ( isset( $metadata['width'] ) ) {
            $result['width']  = $metadata['width'];
            $result['height'] = $metadata['height'];
        }

        // Available image sizes
        if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
            $sizes = array();
            foreach ( $metadata['sizes'] as $size_name => $size_data ) {
                $sizes[ $size_name ] = array(
                    'width'     => $size_data['width'],
                    'height'    => $size_data['height'],
                    'url'       => wp_get_attachment_image_url( $attachment_id, $size_name ),
                    'mime_type' => $size_data['mime-type'],
                );
            }
            $result['sizes'] = $sizes;
        }

        // Video/Audio metadata
        if ( isset( $metadata['length_formatted'] ) ) {
            $result['duration'] = $metadata['length_formatted'];
        }
        if ( isset( $metadata['bitrate'] ) ) {
            $result['bitrate'] = $metadata['bitrate'];
        }
    }

    // File size
    if ( $file_path && file_exists( $file_path ) ) {
        $result['file_size']       = size_format( filesize( $file_path ) );
        $result['file_size_bytes'] = filesize( $file_path );
    }

    // Check if used as featured image somewhere
    global $wpdb;
    $used_as_thumbnail = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = %d",
            $attachment_id
        )
    );
    if ( ! empty( $used_as_thumbnail ) ) {
        $result['used_as_featured_image_in'] = array_map( 'intval', $used_as_thumbnail );
    }

    return $result;
}

/**
 * 3. Upload Media from URL or Base64
 */
function gw_upload_media_execute( $input ) {
    // We need these WP functions for side-loading
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $has_url    = ! empty( $input['url'] );
    $has_base64 = ! empty( $input['base64_data'] );

    if ( ! $has_url && ! $has_base64 ) {
        return new WP_Error( 'missing_source', 'Either "url" or "base64_data" (with "filename") is required.' );
    }

    if ( $has_url && $has_base64 ) {
        return new WP_Error( 'conflicting_source', 'Provide either "url" or "base64_data", not both.' );
    }

    $parent_post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;

    /* ── Mode A: Upload from URL ────────────────────── */
    if ( $has_url ) {
        $url = esc_url_raw( $input['url'] );
        if ( empty( $url ) ) {
            return new WP_Error( 'invalid_url', 'A valid URL is required.' );
        }

        $tmp_file = download_url( $url, 60 );

        if ( is_wp_error( $tmp_file ) ) {
            return new WP_Error( 'download_failed', 'Could not download file from URL: ' . $tmp_file->get_error_message() );
        }

        // Extract filename from URL
        $url_path = wp_parse_url( $url, PHP_URL_PATH );
        $filename = basename( $url_path );
        if ( empty( $filename ) || strpos( $filename, '.' ) === false ) {
            $mime = mime_content_type( $tmp_file );
            $ext  = gw_mcp_mime_to_ext( $mime );
            $filename = 'media-upload-' . time() . '.' . $ext;
        }
    }

    /* ── Mode B: Upload from Base64 ─────────────────── */
    if ( $has_base64 ) {
        if ( empty( $input['filename'] ) ) {
            return new WP_Error( 'missing_filename', 'A "filename" with extension is required when uploading via base64_data (e.g. "image.png").' );
        }

        $filename = sanitize_file_name( $input['filename'] );

        // Strip optional data URI prefix (e.g. "data:image/png;base64,")
        $base64 = $input['base64_data'];
        if ( strpos( $base64, ',' ) !== false ) {
            $base64 = substr( $base64, strpos( $base64, ',' ) + 1 );
        }

        $decoded = base64_decode( $base64, true );
        if ( $decoded === false ) {
            return new WP_Error( 'invalid_base64', 'The base64_data could not be decoded. Ensure it is valid base64 without line breaks.' );
        }

        // Write to temp file
        $tmp_file = wp_tempnam( $filename );
        $written  = file_put_contents( $tmp_file, $decoded );

        if ( $written === false ) {
            return new WP_Error( 'write_failed', 'Could not write decoded data to temporary file.' );
        }
    }

    $file_array = array(
        'name'     => sanitize_file_name( $filename ),
        'tmp_name' => $tmp_file,
    );

    // Sideload the file into the media library
    $attachment_id = media_handle_sideload( $file_array, $parent_post_id );

    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $tmp_file );
        return $attachment_id;
    }

    // Set optional metadata
    $post_update  = array( 'ID' => $attachment_id );
    $needs_update = false;

    if ( ! empty( $input['title'] ) ) {
        $post_update['post_title'] = sanitize_text_field( $input['title'] );
        $needs_update = true;
    }

    if ( ! empty( $input['caption'] ) ) {
        $post_update['post_excerpt'] = sanitize_text_field( $input['caption'] );
        $needs_update = true;
    }

    if ( ! empty( $input['description'] ) ) {
        $post_update['post_content'] = sanitize_text_field( $input['description'] );
        $needs_update = true;
    }

    if ( $needs_update ) {
        wp_update_post( $post_update );
    }

    if ( ! empty( $input['alt_text'] ) ) {
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $input['alt_text'] ) );
    }

    return array(
        'id'        => $attachment_id,
        'url'       => wp_get_attachment_url( $attachment_id ),
        'filename'  => basename( get_attached_file( $attachment_id ) ),
        'mime_type' => get_post_mime_type( $attachment_id ),
        'message'   => 'Media successfully uploaded.',
    );
}

/**
 * Helper: Map MIME type to file extension.
 */
function gw_mcp_mime_to_ext( $mime ) {
    $map = array(
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/gif'       => 'gif',
        'image/webp'      => 'webp',
        'image/svg+xml'   => 'svg',
        'image/avif'      => 'avif',
        'application/pdf' => 'pdf',
        'video/mp4'       => 'mp4',
        'video/webm'      => 'webm',
        'audio/mpeg'      => 'mp3',
        'audio/ogg'       => 'ogg',
    );
    return isset( $map[ $mime ] ) ? $map[ $mime ] : 'bin';
}

/**
 * 4. Update Media Metadata
 */
function gw_update_media_execute( $input ) {
    $attachment_id = intval( $input['attachment_id'] );
    $attachment    = get_post( $attachment_id );

    if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
        return new WP_Error( 'not_found', 'Media item not found.' );
    }

    $post_update  = array( 'ID' => $attachment_id );
    $needs_update = false;
    $changes      = array();

    if ( isset( $input['title'] ) ) {
        $post_update['post_title'] = sanitize_text_field( $input['title'] );
        $needs_update = true;
        $changes[] = 'title';
    }

    if ( isset( $input['caption'] ) ) {
        $post_update['post_excerpt'] = sanitize_text_field( $input['caption'] );
        $needs_update = true;
        $changes[] = 'caption';
    }

    if ( isset( $input['description'] ) ) {
        $post_update['post_content'] = sanitize_text_field( $input['description'] );
        $needs_update = true;
        $changes[] = 'description';
    }

    if ( isset( $input['post_id'] ) ) {
        $post_update['post_parent'] = intval( $input['post_id'] );
        $needs_update = true;
        $changes[] = 'parent_post';
    }

    if ( $needs_update ) {
        $result = wp_update_post( $post_update, true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
    }

    if ( isset( $input['alt_text'] ) ) {
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $input['alt_text'] ) );
        $changes[] = 'alt_text';
    }

    return array(
        'id'      => $attachment_id,
        'url'     => wp_get_attachment_url( $attachment_id ),
        'updated' => $changes,
        'message' => 'Media metadata successfully updated.',
    );
}

/**
 * 5. Delete Media
 */
function gw_delete_media_execute( $input ) {
    $attachment_id = intval( $input['attachment_id'] );
    $attachment    = get_post( $attachment_id );

    if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
        return new WP_Error( 'not_found', 'Media item not found.' );
    }

    $force = isset( $input['force'] ) ? (bool) $input['force'] : true;

    $title = $attachment->post_title;
    $url   = wp_get_attachment_url( $attachment_id );

    $deleted = wp_delete_attachment( $attachment_id, $force );

    if ( ! $deleted ) {
        return new WP_Error( 'delete_failed', 'Could not delete the media item.' );
    }

    return array(
        'id'      => $attachment_id,
        'title'   => $title,
        'url'     => $url,
        'message' => 'Media item permanently deleted.',
    );
}

/**
 * 6. Set Featured Image
 */
function gw_set_featured_image_execute( $input ) {
    $post_id = intval( $input['post_id'] );
    $post    = get_post( $post_id );

    if ( ! $post ) {
        return new WP_Error( 'not_found', 'Post not found.' );
    }

    $remove = isset( $input['remove'] ) && $input['remove'];

    if ( $remove ) {
        delete_post_thumbnail( $post_id );
        return array(
            'post_id' => $post_id,
            'message' => 'Featured image removed.',
        );
    }

    if ( ! isset( $input['attachment_id'] ) ) {
        return new WP_Error( 'missing_param', 'Either attachment_id or remove=true is required.' );
    }

    $attachment_id = intval( $input['attachment_id'] );
    $attachment    = get_post( $attachment_id );

    if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
        return new WP_Error( 'invalid_attachment', 'The provided attachment_id is not a valid media item.' );
    }

    $result = set_post_thumbnail( $post_id, $attachment_id );

    if ( ! $result ) {
        return new WP_Error( 'failed', 'Could not set featured image.' );
    }

    return array(
        'post_id'       => $post_id,
        'attachment_id'  => $attachment_id,
        'thumbnail_url'  => get_the_post_thumbnail_url( $post_id, 'full' ),
        'message'        => 'Featured image successfully set.',
    );
}

/**
 * 7. Bulk Update Media Metadata
 */
function gw_bulk_update_media_meta_execute( $input ) {
    if ( ! isset( $input['items'] ) || ! is_array( $input['items'] ) ) {
        return new WP_Error( 'invalid_input', 'An array of items is required.' );
    }

    $results  = array();
    $success  = 0;
    $failed   = 0;

    foreach ( $input['items'] as $item ) {
        if ( ! isset( $item['attachment_id'] ) ) {
            $failed++;
            $results[] = array(
                'error' => 'Missing attachment_id in item.',
            );
            continue;
        }

        $attachment_id = intval( $item['attachment_id'] );
        $attachment    = get_post( $attachment_id );

        if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
            $failed++;
            $results[] = array(
                'id'    => $attachment_id,
                'error' => 'Media item not found.',
            );
            continue;
        }

        $post_update  = array( 'ID' => $attachment_id );
        $needs_update = false;
        $changes      = array();

        if ( isset( $item['title'] ) ) {
            $post_update['post_title'] = sanitize_text_field( $item['title'] );
            $needs_update = true;
            $changes[] = 'title';
        }

        if ( isset( $item['caption'] ) ) {
            $post_update['post_excerpt'] = sanitize_text_field( $item['caption'] );
            $needs_update = true;
            $changes[] = 'caption';
        }

        if ( isset( $item['description'] ) ) {
            $post_update['post_content'] = sanitize_text_field( $item['description'] );
            $needs_update = true;
            $changes[] = 'description';
        }

        if ( $needs_update ) {
            wp_update_post( $post_update );
        }

        if ( isset( $item['alt_text'] ) ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $item['alt_text'] ) );
            $changes[] = 'alt_text';
        }

        $success++;
        $results[] = array(
            'id'      => $attachment_id,
            'updated' => $changes,
        );
    }

    return array(
        'results'       => $results,
        'total'         => count( $input['items'] ),
        'success_count' => $success,
        'failed_count'  => $failed,
        'message'       => sprintf( '%d of %d media items updated.', $success, count( $input['items'] ) ),
    );
}


/* ─────────────────────────────────────────────
 * CHUNKED UPLOAD CALLBACKS
 * ───────────────────────────────────────────── */

/**
 * 8. Start Chunked Upload
 *
 * Creates a unique upload session with a temp file.
 * Session data is stored in a transient (1 hour TTL).
 */
function gw_start_chunked_upload_execute( $input ) {
    if ( empty( $input['filename'] ) ) {
        return new WP_Error( 'missing_filename', 'A filename with extension is required.' );
    }

    $filename     = sanitize_file_name( $input['filename'] );
    $upload_id    = wp_generate_uuid4();
    $upload_dir   = wp_upload_dir();
    $tmp_dir      = trailingslashit( $upload_dir['basedir'] ) . 'gw-mcp-chunks/';

    // Ensure temp directory exists
    if ( ! file_exists( $tmp_dir ) ) {
        wp_mkdir_p( $tmp_dir );
        // Prevent directory listing
        file_put_contents( $tmp_dir . '.htaccess', 'Deny from all' );
        file_put_contents( $tmp_dir . 'index.php', '<?php // silence is golden' );
    }

    $tmp_file = $tmp_dir . $upload_id . '_' . $filename;

    // Create empty file
    $handle = fopen( $tmp_file, 'wb' );
    if ( $handle === false ) {
        return new WP_Error( 'file_error', 'Could not create temporary upload file.' );
    }
    fclose( $handle );

    // Store session data in transient (1 hour TTL)
    $session = array(
        'upload_id'    => $upload_id,
        'filename'     => $filename,
        'tmp_file'     => $tmp_file,
        'total_chunks' => isset( $input['total_chunks'] ) ? intval( $input['total_chunks'] ) : 0,
        'received'     => 0,
        'bytes'        => 0,
        'started_at'   => current_time( 'mysql' ),
    );

    set_transient( 'gw_mcp_upload_' . $upload_id, $session, HOUR_IN_SECONDS );

    return array(
        'upload_id'    => $upload_id,
        'filename'     => $filename,
        'total_chunks' => $session['total_chunks'],
        'message'      => 'Chunked upload session started. Now send chunks via gw/append-upload-chunk, then call gw/finish-chunked-upload.',
    );
}

/**
 * 9. Append Upload Chunk
 *
 * Decodes a base64 chunk and appends it directly to the temp file.
 * This keeps memory usage low since we never hold the full file in RAM.
 */
function gw_append_upload_chunk_execute( $input ) {
    $upload_id = sanitize_text_field( $input['upload_id'] );
    $session   = get_transient( 'gw_mcp_upload_' . $upload_id );

    if ( ! $session ) {
        return new WP_Error( 'invalid_session', 'Upload session not found or expired. Start a new session with gw/start-chunked-upload.' );
    }

    if ( empty( $input['chunk_data'] ) ) {
        return new WP_Error( 'missing_data', 'chunk_data is required.' );
    }

    // Strip optional data URI prefix
    $base64 = $input['chunk_data'];
    if ( strpos( $base64, ',' ) !== false ) {
        $base64 = substr( $base64, strpos( $base64, ',' ) + 1 );
    }

    // Decode
    $decoded = base64_decode( $base64, true );
    if ( $decoded === false ) {
        return new WP_Error( 'invalid_base64', 'Could not decode chunk_data. Ensure it is valid base64.' );
    }

    // Append to temp file
    $handle = fopen( $session['tmp_file'], 'ab' );
    if ( $handle === false ) {
        return new WP_Error( 'file_error', 'Could not open temporary file for writing.' );
    }

    $written = fwrite( $handle, $decoded );
    fclose( $handle );

    if ( $written === false ) {
        return new WP_Error( 'write_error', 'Could not write chunk to temporary file.' );
    }

    // Update session
    $chunk_index = isset( $input['chunk_index'] ) ? intval( $input['chunk_index'] ) : $session['received'] + 1;
    $session['received']++;
    $session['bytes'] += $written;

    // Refresh transient TTL
    set_transient( 'gw_mcp_upload_' . $upload_id, $session, HOUR_IN_SECONDS );

    $response = array(
        'upload_id'      => $upload_id,
        'chunk_index'    => $chunk_index,
        'chunks_received'=> $session['received'],
        'bytes_total'    => $session['bytes'],
        'bytes_total_hr' => size_format( $session['bytes'] ),
    );

    if ( $session['total_chunks'] > 0 ) {
        $response['progress'] = round( ( $session['received'] / $session['total_chunks'] ) * 100 ) . '%';
    }

    $response['message'] = sprintf(
        'Chunk %d received (%s written so far). %s',
        $chunk_index,
        size_format( $session['bytes'] ),
        $session['total_chunks'] > 0 && $session['received'] >= $session['total_chunks']
            ? 'All chunks received — call gw/finish-chunked-upload to finalize.'
            : 'Send more chunks or call gw/finish-chunked-upload when done.'
    );

    return $response;
}

/**
 * 10. Finish Chunked Upload
 *
 * Imports the assembled temp file into the media library
 * and cleans up the session + temp file.
 */
function gw_finish_chunked_upload_execute( $input ) {
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $upload_id = sanitize_text_field( $input['upload_id'] );
    $session   = get_transient( 'gw_mcp_upload_' . $upload_id );

    if ( ! $session ) {
        return new WP_Error( 'invalid_session', 'Upload session not found or expired.' );
    }

    $tmp_file = $session['tmp_file'];
    $filename = $session['filename'];

    if ( ! file_exists( $tmp_file ) ) {
        delete_transient( 'gw_mcp_upload_' . $upload_id );
        return new WP_Error( 'file_missing', 'Temporary upload file not found. The session may have been cleaned up.' );
    }

    $file_size = filesize( $tmp_file );
    if ( $file_size === 0 ) {
        @unlink( $tmp_file );
        delete_transient( 'gw_mcp_upload_' . $upload_id );
        return new WP_Error( 'empty_file', 'No data was received. The file is empty.' );
    }

    $parent_post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;

    $file_array = array(
        'name'     => sanitize_file_name( $filename ),
        'tmp_name' => $tmp_file,
    );

    // Import into media library
    $attachment_id = media_handle_sideload( $file_array, $parent_post_id );

    // Clean up session
    delete_transient( 'gw_mcp_upload_' . $upload_id );

    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $tmp_file );
        return $attachment_id;
    }

    // Set optional metadata
    $post_update  = array( 'ID' => $attachment_id );
    $needs_update = false;

    if ( ! empty( $input['title'] ) ) {
        $post_update['post_title'] = sanitize_text_field( $input['title'] );
        $needs_update = true;
    }

    if ( ! empty( $input['caption'] ) ) {
        $post_update['post_excerpt'] = sanitize_text_field( $input['caption'] );
        $needs_update = true;
    }

    if ( ! empty( $input['description'] ) ) {
        $post_update['post_content'] = sanitize_text_field( $input['description'] );
        $needs_update = true;
    }

    if ( $needs_update ) {
        wp_update_post( $post_update );
    }

    if ( ! empty( $input['alt_text'] ) ) {
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $input['alt_text'] ) );
    }

    return array(
        'id'             => $attachment_id,
        'url'            => wp_get_attachment_url( $attachment_id ),
        'filename'       => basename( get_attached_file( $attachment_id ) ),
        'mime_type'      => get_post_mime_type( $attachment_id ),
        'file_size'      => size_format( $file_size ),
        'chunks_received'=> $session['received'],
        'message'        => 'Chunked upload complete. Media successfully imported into the library.',
    );
}
