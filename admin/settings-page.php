<?php
/**
 * Settings Page for GW MCP Abilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Option Name
define( 'GW_MCP_OPTION_NAME', 'gw_mcp_active_abilities' );

/**
 * Register the settings.
 */
add_action( 'admin_init', 'gw_mcp_register_settings' );
function gw_mcp_register_settings() {
    register_setting( 'gw_mcp_settings_group', GW_MCP_OPTION_NAME );
}

/**
 * Add Menu Page.
 */
add_action( 'admin_menu', 'gw_mcp_add_menu_page' );
function gw_mcp_add_menu_page() {
    add_options_page(
        'MCP Abilities Settings',
        'MCP Abilities',
        'manage_options',
        'gw-mcp-settings',
        'gw_mcp_render_settings_page'
    );
}

/**
 * Inject the styles exclusively on this settings page.
 */
add_action( 'admin_head', 'gw_mcp_admin_styles' );
function gw_mcp_admin_styles() {
    $screen = get_current_screen();
    if ( $screen->id !== 'settings_page_gw-mcp-settings' ) {
        return;
    }
    ?>
    <style>
        .gw-mcp-wrap {
            max-width: 1000px;
            margin: 20px auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        .gw-mcp-header {
            background: #fff;
            padding: 24px 32px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border-left: 5px solid #0073aa; /* Standard WP Blue but feels premium */
        }
        .gw-mcp-header h1 {
            margin: 0 0 10px;
            font-size: 24px;
            font-weight: 600;
            color: #1e1e1e;
        }
        .gw-mcp-header p {
            margin: 0;
            font-size: 15px;
            color: #646970;
        }
        .gw-mcp-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }
        .gw-mcp-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            border: 1px solid #e2e4e7;
        }
        .gw-mcp-card h2 {
            margin-top: 0;
            font-size: 18px;
            color: #1d2327;
            border-bottom: 1px solid #f0f0f1;
            padding-bottom: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .gw-mcp-card h2 span.dashicons {
            margin-right: 8px;
            color: #2271b1;
        }
        .gw-mcp-toggle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f6f7f7;
        }
        .gw-mcp-toggle-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .gw-mcp-toggle-info strong {
            display: block;
            font-size: 14px;
            color: #2c3338;
            margin-bottom: 4px;
        }
        .gw-mcp-toggle-info span {
            display: block;
            font-size: 12px;
            color: #8c8f94;
        }
        
        /* Modern UI Switch Toggle */
        .gw-toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }
        .gw-toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .gw-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .3s ease;
            border-radius: 34px;
        }
        .gw-toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s ease;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        input:checked + .gw-toggle-slider {
            background-color: #2271b1;
        }
        input:focus + .gw-toggle-slider {
            box-shadow: 0 0 1px #2271b1;
        }
        input:checked + .gw-toggle-slider:before {
            transform: translateX(20px);
        }

        .gw-mcp-actions {
            background: #fff;
            padding: 16px 24px;
            border-radius: 8px;
            border: 1px solid #e2e4e7;
            text-align: right;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .gw-mcp-actions .button-primary {
            background: #2271b1;
            border-color: #2271b1;
            font-size: 15px;
            padding: 6px 24px;
        }
    </style>
    <?php
}

/**
 * HTML Output of the Custom Settings Page.
 */
function gw_mcp_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options = get_option( GW_MCP_OPTION_NAME, [] );

    // Define structure for UI rendering
    $sections = [
        'posts' => [
            'icon'  => 'dashicons-admin-post',
            'title' => 'Beiträge (Posts)',
            'fields'=> [
                'read_posts'        => [ 'label' => 'Posts lesen', 'desc' => 'gw/read-posts - Erlaubt es, die neuesten Blog-Einträge auszulesen.' ],
                'read_post_details' => [ 'label' => 'Post Details lesen', 'desc' => 'gw/read-post-details - Läd den kompletten Inhalt und Meta eines Beitrags.' ],
                'create_post'       => [ 'label' => 'Posts erstellen', 'desc' => 'gw/create-post - Generiert automatisch neue Blog-Artikel aus dem Chat heraus.' ],
                'update_post'       => [ 'label' => 'Beiträge aktualisieren', 'desc' => 'gw/update-post - Bearbeitet bestehende Posts, Seiten oder CPTs anstatt sie neu anzulegen.' ],
            ]
        ],
        'pages' => [
            'icon'  => 'dashicons-admin-page',
            'title' => 'Seiten (Pages)',
            'fields'=> [
                'read_pages'     => [ 'label' => 'Seiten lesen', 'desc' => 'gw/read-pages - Analysiert die angelegten statischen Seiten.' ],
                'create_page'    => [ 'label' => 'Seiten erstellen', 'desc' => 'gw/create-page - Kann komplett neue Seiten inkl. Unterseiten generieren.' ],
                'duplicate_page' => [ 'label' => 'Seiten duplizieren', 'desc' => 'gw/duplicate-page - Kopiert als Blueprint Seiten inkl. aller Meta-Werte.' ],
            ]
        ],
        'cpts' => [
            'icon'  => 'dashicons-portfolio',
            'title' => 'Custom Post Types (CPTs)',
            'fields'=> [
                'list_cpts'      => [ 'label' => 'CPTs auflisten', 'desc' => 'gw/list-cpts - Findet heraus, welche CPTs dieses System besitzt.' ],
                'read_cpt_posts' => [ 'label' => 'CPTs lesen', 'desc' => 'gw/read-cpt-posts - CPT-Einträge via KI analysieren.' ],
                'create_cpt_post'=> [ 'label' => 'CPT Post erstellen', 'desc' => 'gw/create-cpt-post - Neue CPTs wie Produkte oder Events generieren.' ],
            ]
        ],
        'meta' => [
            'icon'  => 'dashicons-database',
            'title' => 'Metadaten & Taxonomien',
            'fields'=> [
                'read_metadata'   => [ 'label' => 'Metadaten lesen', 'desc' => 'gw/read-metadata - Direkter Zugriff auf WordPress Custom Fields.' ],
                'write_metadata'  => [ 'label' => 'Metadaten schreiben', 'desc' => 'gw/write-metadata - Erstellt, ändert oder löscht Custom Fields.' ],
                'read_taxonomies' => [ 'label' => 'Taxonomien auslesen', 'desc' => 'gw/read-taxonomies - Prüft welche Taxonomien es gibt (inkl. Categories/Tags).' ],
                'read_terms'      => [ 'label' => 'Begriffe lesen', 'desc' => 'gw/read-terms - Listet Kategorien, Tags und IDs auf.' ],
                'write_terms'     => [ 'label' => 'Taxonomien bearbeiten', 'desc' => 'Erstellt, ändert, löscht und weist Kategorien und Tags zu (assign-terms).' ],
            ]
        ],
        'seo' => [
            'icon'  => 'dashicons-search',
            'title' => 'SEO (Rank Math / Yoast)',
            'fields'=> [
                'read_seo'   => [ 'label' => 'SEO Metadaten lesen', 'desc' => 'gw/read-seo - Liest Focus Keyword, Title und Meta Description.' ],
                'update_seo' => [ 'label' => 'SEO Metadaten anpassen', 'desc' => 'gw/update-seo - Direkter Zugriff auf Focus Keyword und SERP Snippet Optimierung, falls ein SEO Plugin aktiv ist.' ],
            ]
        ],
        'media' => [
            'icon'  => 'dashicons-admin-media',
            'title' => 'Medien (Media Library)',
            'fields'=> [
                'list_media'           => [ 'label' => 'Medien auflisten', 'desc' => 'gw/list-media - Listet Bilder, PDFs, Videos etc. aus der Mediathek auf.' ],
                'read_media_details'   => [ 'label' => 'Medien-Details lesen', 'desc' => 'gw/read-media-details - Zeigt alle Metadaten eines Medien-Elements (ALT, Größen, Dateigröße).' ],
                'upload_media'         => [ 'label' => 'Medien hochladen', 'desc' => 'gw/upload-media + chunked-upload - Lädt Dateien per URL, Base64 oder Chunked Upload (für große Dateien) hoch.' ],
                'update_media'         => [ 'label' => 'Medien aktualisieren', 'desc' => 'gw/update-media - Ändert Titel, ALT-Text, Beschreibung oder Bildunterschrift.' ],
                'delete_media'         => [ 'label' => 'Medien löschen', 'desc' => 'gw/delete-media - Entfernt Medien-Elemente permanent aus der Bibliothek.' ],
                'set_featured_image'   => [ 'label' => 'Beitragsbild setzen', 'desc' => 'gw/set-featured-image - Setzt oder entfernt das Beitragsbild (Featured Image).' ],
                'bulk_update_media_meta' => [ 'label' => 'Bulk Medien-Metadaten', 'desc' => 'gw/bulk-update-media-meta - Aktualisiert ALT-Texte und Metadaten für mehrere Medien gleichzeitig.' ],
            ]
        ],
        'options' => [
            'icon'  => 'dashicons-admin-generic',
            'title' => 'WordPress Optionen',
            'fields'=> [
                'read_options'  => [ 'label' => 'Optionen lesen', 'desc' => 'gw/read-option - Liest globale Einstellungen aus wp_options.' ],
                'write_options' => [ 'label' => 'Optionen schreiben', 'desc' => 'gw/update-option - Verändert globale Einstellungen.' ],
            ]
        ],
        'site_info' => [
            'icon'  => 'dashicons-admin-site',
            'title' => 'Site Struktur (Plugins/Themes/Menüs)',
            'fields'=> [
                'read_site_info' => [ 'label' => 'Site Info auslesen', 'desc' => 'gw/list-plugins, gw/list-themes, gw/get-menus - Analysiert die WordPress-Architektur.' ],
            ]
        ],
        'search' => [
            'icon'  => 'dashicons-search',
            'title' => 'Globale Suche',
            'fields'=> [
                'search_content' => [ 'label' => 'Inhalte suchen', 'desc' => 'gw/search-content - Durchsucht alle Inhalte nach Keywords.' ],
            ]
        ],
        'gutenberg' => [
            'icon'  => 'dashicons-layout',
            'title' => 'Gutenberg & FSE',
            'fields'=> [
                'read_gutenberg' => [ 'label' => 'Patterns & Templates lesen', 'desc' => 'gw/list-block-patterns, gw/read-templates - Gibt Layouts und Pattern zurück, die die KI verbauen kann.' ],
            ]
        ],
    ];

    ?>
    <div class="wrap gw-mcp-wrap">
        
        <div class="gw-mcp-header">
            <h1>Gutwerker MCP Abilities - Settings</h1>
            <p>Konfiguriere, auf welche WordPress-Komponenten die Künstliche Intelligenz (oder das MCP System) Zugriff hat. Bestimme gezielt, welche Endpunkte veröffentlicht werden.</p>
        </div>

        <?php settings_errors(); ?>

        <form action="options.php" method="post">
            <?php settings_fields( 'gw_mcp_settings_group' ); ?>
            
            <div class="gw-mcp-grid">
                <?php foreach ( $sections as $section_key => $section_data ) : ?>
                    <div class="gw-mcp-card">
                        <h2><span class="dashicons <?php echo esc_attr( $section_data['icon'] ); ?>"></span> <?php echo esc_html( $section_data['title'] ); ?></h2>
                        
                        <div class="gw-mcp-card-content">
                            <?php foreach ( $section_data['fields'] as $field_id => $field_data ) : 
                                $is_checked = isset( $options[ $field_id ] ) && ! empty( $options[ $field_id ] );
                            ?>
                                <div class="gw-mcp-toggle-row">
                                    <div class="gw-mcp-toggle-info">
                                        <strong><?php echo esc_html( $field_data['label'] ); ?></strong>
                                        <span><?php echo esc_html( $field_data['desc'] ); ?></span>
                                    </div>
                                    <div class="gw-mcp-toggle-action">
                                        <label class="gw-toggle-switch">
                                            <input type="checkbox" name="<?php echo esc_attr( GW_MCP_OPTION_NAME ); ?>[<?php echo esc_attr( $field_id ); ?>]" value="1" <?php checked( $is_checked, true ); ?>>
                                            <span class="gw-toggle-slider round"></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="gw-mcp-actions">
                <?php submit_button( 'Änderungen übernehmen', 'primary', 'submit', false ); ?>
            </div>
        </form>
    </div>
    <?php
}

/**
 * Helper function to check if a specific ability is enabled.
 */
function gw_mcp_is_ability_active( $ability_id ) {
    $options = get_option( GW_MCP_OPTION_NAME, false );
    
    // Lesende Abilities sind standardmäßig aktiv,
    // schreibende Abilities sind standardmäßig deaktiviert (Sicherheit).
    if ( $options === false ) {
        $read_only_defaults = array(
            'read_posts',
            'read_post_details',
            'read_pages',
            'list_cpts',
            'read_cpt_posts',
            'read_metadata',
            'read_taxonomies',
            'read_terms',
            'list_media',
            'read_media_details',
            'update_seo',
            'read_seo',
            'read_options',
            'read_site_info',
            'search_content',
            'read_gutenberg',
        );
        return in_array( $ability_id, $read_only_defaults, true );
    }
    
    return ( isset( $options[ $ability_id ] ) && ! empty( $options[ $ability_id ] ) );
}
