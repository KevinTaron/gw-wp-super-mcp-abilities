# Gutwerker MCP Abilities

**Give your AI assistant full access to your WordPress content — safely and selectively.**

A WordPress plugin that exposes posts, pages, custom post types, taxonomies, metadata and the media library to AI assistants via the [Model Context Protocol (MCP)](https://modelcontextprotocol.io/).

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue?logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple?logo=php)
![License](https://img.shields.io/badge/License-GPLv2-green)
![Version](https://img.shields.io/badge/Version-1.3.0-orange)

---

## ✨ Features

### 📖 Content Reading
- **Posts** — List and read blog posts with full content, excerpts and metadata
- **Pages** — Browse all published pages with hierarchy information
- **Custom Post Types** — Discover and read any registered CPT (products, events, etc.)
- **Metadata** — Access custom fields for any post, page or CPT
- **Taxonomies & Terms** — Explore categories, tags and custom taxonomies

### 🖼️ Media Library
- **List Media** — Browse the media library with filters (images, PDFs, videos, audio)
- **Media Details** — View all metadata: ALT text, dimensions, file size, available sizes
- **Upload Media** — Import files from URL directly into the media library
- **Update Metadata** — Change ALT text, title, caption and description
- **Delete Media** — Remove media items permanently
- **Featured Images** — Set or remove post thumbnails
- **Bulk Update** — Mass-update ALT texts and metadata for SEO optimization

### ✏️ Content Writing
- **Create Posts** — Generate new blog posts from your AI assistant
- **Update Posts** — Edit existing posts, pages and CPTs
- **Create Pages** — Generate new pages including sub-pages
- **Duplicate Pages** — Clone pages with all metadata
- **Create CPT Posts** — Generate content for any custom post type

### 🔒 Security by Default
- **Read-only by default** — All writing abilities are disabled on first install
- **Granular control** — Enable/disable each ability individually
- **Settings UI** — Beautiful admin interface with toggle switches

---

## 📋 Requirements

- WordPress **6.0** or higher
- PHP **7.4** or higher
- [WordPress MCP Server](https://github.com/developer-wp/developer-wp) or compatible MCP adapter

---

## 🚀 Installation

### Manual Installation

1. Download the latest release from [GitHub Releases](https://github.com/gutwerker/gw-super-mcp-abilities/releases)
2. Upload the `gw-super-mcp-abilities` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **Settings → MCP Abilities** to configure

### Via Git

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/gutwerker/gw-super-mcp-abilities.git
```

Then activate the plugin in your WordPress admin.

---

## ⚙️ Configuration

Navigate to **Settings → MCP Abilities** in your WordPress admin panel.

Each ability can be individually toggled on or off:

| Category | Abilities | Default |
|---|---|---|
| **Posts** | Read Posts, Read Post Details, Create Post, Update Post | Read ✅ / Write ❌ |
| **Pages** | Read Pages, Create Page, Duplicate Page | Read ✅ / Write ❌ |
| **CPTs** | List CPTs, Read CPT Posts, Create CPT Post | Read ✅ / Write ❌ |
| **Metadata & Taxonomies** | Read Metadata, Read Taxonomies, Read Terms | All ✅ |
| **Media** | List, Details, Upload, Update, Delete, Featured Image, Bulk Update | Read ✅ / Write ❌ |

> **Note:** On first install (before saving settings), only **read-only** abilities are active. Writing abilities must be explicitly enabled.

---

## 🧩 Available Abilities

### Posts
| Slug | Description |
|---|---|
| `gw/read-posts` | Returns published blog posts with title, URL, date, excerpt and ID |
| `gw/read-post-details` | Returns complete details of a specific blog post by ID |
| `gw/create-post` | Creates a new blog post |
| `gw/update-post` | Updates an existing post, page or CPT |

### Pages
| Slug | Description |
|---|---|
| `gw/read-pages` | Returns published pages with title, URL and date |
| `gw/create-page` | Creates a new WordPress page |
| `gw/duplicate-page` | Duplicates an existing page with all metadata |

### Custom Post Types
| Slug | Description |
|---|---|
| `gw/list-cpts` | Lists all public Custom Post Types |
| `gw/read-cpt-posts` | Returns posts from a specific CPT |
| `gw/create-cpt-post` | Creates a new post for any CPT |

### Metadata & Taxonomies
| Slug | Description |
|---|---|
| `gw/read-metadata` | Returns custom fields for any post/page |
| `gw/read-taxonomies` | Lists all registered taxonomies |
| `gw/read-terms` | Returns terms within a specific taxonomy |

### Media Library
| Slug | Description |
|---|---|
| `gw/list-media` | Lists media library items with filters |
| `gw/read-media-details` | Full metadata of a media item (ALT, sizes, etc.) |
| `gw/upload-media` | Imports a file from URL into the media library |
| `gw/update-media` | Updates title, ALT text, caption, description |
| `gw/delete-media` | Permanently deletes a media item |
| `gw/set-featured-image` | Sets or removes the featured image of a post |
| `gw/bulk-update-media-meta` | Batch-updates metadata for multiple media items |

---

## 📁 File Structure

```
gw-super-mcp-abilities/
├── gw-super-mcp-abilities.php   # Main plugin file
├── index.php                     # Security (silence is golden)
├── README.md                     # This file
├── CHANGELOG.md                  # Version history
├── LICENSE                       # GPLv2 License
├── abilities/
│   ├── posts.php                 # Post abilities
│   ├── pages.php                 # Page abilities
│   ├── cpts.php                  # Custom Post Type abilities
│   ├── meta.php                  # Metadata abilities
│   ├── taxonomies.php            # Taxonomy & Terms abilities
│   └── media.php                 # Media library abilities
├── admin/
│   └── settings-page.php         # Settings UI
└── includes/
    └── class-gw-mcp-registrator.php  # Ability registration handler
```

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **GNU General Public License v2.0 or later** — see the [LICENSE](LICENSE) file for details.

---

## 🏢 Credits

Developed by [Gutwerker](https://gutwerker.de) — WordPress Agentur aus Deutschland.
