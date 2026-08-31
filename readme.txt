=== IZ MD Pages ===
Contributors: IvanZhuck
Donate link: https://izhuck.ru/
Tags: seo, md, markdown, ai, content
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Generate and serve clean, lightweight, and AI/LLM-friendly Markdown versions of your WordPress posts, pages, and custom post types.

== Description ==

**IZ MD Pages** transforms your WordPress website into a dual-format content hub by generating and serving clean, fast, and structured **Markdown** pages alongside standard HTML. 

Optimized for **AI search engines, LLMs, developer documentation, CLI tools, and feed readers**, IZ MD Pages enables instant retrieval of your content in plain text Markdown with zero boilerplate.

### Key Features

* **Dedicated Markdown Endpoints & Query Params**: Access the Markdown version of any post or page seamlessly via clean URLs (e.g. `https://example.com/sample-post/md`) or query parameters (`https://example.com/sample-post/?md`), with automated canonical 301 redirects.
* **Automatic Discovery Link Headers**: Injects standard `<link rel="alternate" type="text/markdown">` into your `<head>`, making Markdown endpoints automatically discoverable by web crawlers, AI assistants, and browser extensions.
* **Advanced Gutenberg & Classic Editor Conversion**: Converts Gutenberg blocks (headings, paragraphs, lists, quotes, tables, code blocks, images, embeds) and classic HTML into clean standard Markdown.
* **Flexible Post Type Templates**: Customize layout templates for each enabled post type using powerful placeholders (e.g., `# {%post_title%}\n\n{%post_content%}`).
* **Universal Header & Footer Templates**: Configure global Markdown headers and footers (such as metadata banners, licensing, or call-to-actions) appended to all rendered Markdown pages.
* **Rich Placeholder Engine**:
  * **Core Fields**: `{%post_title%}`, `{%post_content%}`, `{%post_excerpt%}`, `{%post_url%}`, `{%post_date%}`, `{%post_time%}`, `{%post_date_time%}`, `{%post_date_time_gmt%}`, `{%post_date_time_gmt_iso%}`, `{%thumbnail_url%}`, `{%thumbnail_id%}`, etc.
  * **Author Info**: `{%author_name%}`, `{%author_email%}`, `{%author_url%}`, `{%author_bio%}`, `{%author_first_name%}`, `{%author_last_name%}`.
  * **Taxonomies**: `{%categories%}`, `{%tags%}`, `{%taxonomy:taxonomy_name%}` with customizable separators and prefixing (`\n*`, `\t`, `:before`, `:leading`).
  * **Comments & Counts**: `{%comments%}`, `{%comments_count%}`.
  * **Custom Fields (Post Meta)**: `{%meta:key%}` with recursive formatting for complex nested arrays and objects.
* **Per-Post Granular Controls**: Dedicated meta box on edit screens to disable Markdown generation for specific posts or provide custom manual Markdown overrides with a single click reset.
* **Developer-Friendly & Extensible**: Over 15 WordPress filter hooks allowing full customization of template rendering, block conversions, custom placeholder evaluation, and final page output.
* **High Performance**: Pure PHP conversion with no external dependencies or remote API calls.

== Screenshots ==

1. General settings page
2. Templates settings page
3. Documentation page
4. Markdown meta box

== How to contribute ==

If you would like to help with the development of this plugin, please visit its GitHub repository: [https://github.com/IvanZhuck/iz-md-pages](https://github.com/IvanZhuck/iz-md-pages). The repository contains the source code and tools for development.

== Installation ==

### Automatic Installation
1. Log in to your WordPress admin dashboard.
2. Navigate to **Plugins &rarr; Add New**.
3. Search for **IZ MD Pages**.
4. Click **Install Now** and then **Activate**.

### Manual Installation
1. Download the plugin ZIP archive.
2. Upload the uncompressed `iz-md-pages` directory to your `/wp-content/plugins/` directory.
3. Activate the plugin through the **Plugins** menu in WordPress.

### Post-Installation Setup
1. Go to **Settings &rarr; IZ MD Pages** to configure enabled post types, URL format (Endpoint `/md` or Query Var `?md`), and front page options.
2. Visit **Settings &rarr; IZ MD Pages &rarr; Templates** to customize post type Markdown templates, headers, and footers.
3. Refer to **Settings &rarr; IZ MD Pages &rarr; Documentation** for detailed guides, syntax references, and developer hooks.

== Frequently Asked Questions ==

= How do visitors and AI agents access the Markdown version of a post? =
By default, you can append `/md` to any post or page permalink (e.g. `https://example.com/my-article/md`). You can also switch the URL format to query parameter mode (`?md`) in the plugin settings.

= Does this affect my site's regular HTML frontend or SEO? =
No. Your standard HTML pages remain completely untouched. The plugin only outputs Markdown when the dedicated `/md` endpoint or `?md` parameter is requested. Furthermore, discovery tags (`<link rel="alternate" type="text/markdown">`) are added to your HTML head to inform crawlers of the alternate format.

= Can I disable Markdown generation for specific posts? =
Yes. On any post or page edit screen, locate the **Markdown Page** meta box and check **Disable Markdown generation for this post**.

= Can I write custom Markdown manually for a specific post instead of using the template? =
Yes. In the **Markdown Page** meta box, check **Use custom Markdown for this post** and enter your custom Markdown in the textarea.

= Are custom taxonomies and ACF / post meta supported in templates? =
Yes! You can use `{%taxonomy:your_tax_name%}` for any custom taxonomy, and `{%meta:your_field_key%}` for any custom post field or ACF value.

= Can I override templates or Markdown conversion in my theme code? =
Yes. IZ MD Pages provides extensive filter hooks such as `iz_md_post_type_template_{$postType}`, `iz_md_post_template_{$postId}`, `iz_md_render_block_{$blockName}`, `iz_md_render_custom_placeholder_{$tag}`, and `iz_md_page_content`.

== Changelog ==

= 1.1.0 =
* Added `:links` modifiers for taxonomy placeholders (`{%categories:links%}`, `{%tags:links%}`, `{%taxonomy:<name>:links%}`) to output terms as Markdown links to their archive pages.
* Enhanced date and time placeholders with expanded post date and modified date/time formats (`{%post_date_time%}`, `{%post_date_time_gmt%}`, `{%post_date_time_gmt_iso%}`, `{%post_date_gmt_iso%}`, `{%post_modified_date%}`, `{%post_modified_time%}`, `{%post_modified_date_time%}`, `{%post_modified_date_time_gmt%}`, `{%post_modified_date_time_gmt_iso%}`, `{%post_modified_gmt_iso%}`).
* Added slash escaping (`addslashes`) when rendering template textarea fields and reset button attributes on settings pages and the post meta box.
* Added documentation for taxonomy archive links in the admin reference block.

= 1.0.0 =
* The first public release.
* Support for `/md` URL endpoint and `?md` query variable routing with canonical 301 redirection.
* Gutenberg block renderer and HTML-to-Markdown converter.
* Flexible post type templates with customizable header and footer options.
* Advanced placeholder engine supporting core post attributes, author fields, taxonomies, comments, and custom post meta.
* Post edit screen meta box with enable/disable toggles and manual Markdown editing.
* Automatic discovery alternate link header insertion.
* Complete developer filter hooks architecture.
