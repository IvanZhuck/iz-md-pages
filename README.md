# IZ MD Pages

Generate and serve clean, lightweight, and AI/LLM-friendly Markdown versions of your WordPress posts, pages, and custom post types.

---

## Overview

**IZ MD Pages** transforms your WordPress website into a dual-format content hub by generating and serving clean, fast, and structured **Markdown** pages alongside standard HTML.

Optimized for **AI search engines, Large Language Models (LLMs), developer documentation, CLI tools, and feed readers**, IZ MD Pages enables instant retrieval of your content in plain text Markdown with zero boilerplate.


## Key Features

* **Dedicated Markdown Endpoints & Query Params**: Access the Markdown version of any post or page seamlessly via clean URLs (e.g. `https://example.com/sample-post/md`) or query parameters (`https://example.com/sample-post/?md`), with automated canonical 301 redirects.
* **Automatic Discovery Link Headers**: Injects standard `<link rel="alternate" type="text/markdown">` into your `<head>`, making Markdown endpoints automatically discoverable by web crawlers, AI assistants, and browser extensions.
* **Advanced Gutenberg & Classic Editor Conversion**: Converts Gutenberg blocks (headings, paragraphs, lists, quotes, tables, code blocks, images, embeds) and classic HTML into clean standard Markdown.
* **Flexible Post Type Templates**: Customize layout templates for each enabled post type using powerful placeholders (e.g., `# {%post_title%}\n\n{%post_content%}`).
* **Universal Header & Footer Templates**: Configure global Markdown headers and footers (such as metadata banners, licensing, or call-to-actions) appended to all rendered Markdown pages.
* **Rich Placeholder Engine**:
  * **Core Fields**: `{%post_title%}`, `{%post_content%}`, `{%post_excerpt%}`, `{%post_url%}`, `{%post_date%}`, `{%post_date_gmt%}`, `{%post_time%}`, `{%post_modified%}`, `{%thumbnail_url%}`, `{%thumbnail_id%}`.
  * **Author Info**: `{%author_name%}`, `{%author_email%}`, `{%author_url%}`, `{%author_bio%}`, `{%author_first_name%}`, `{%author_last_name%}`.
  * **Taxonomies**: `{%categories%}`, `{%tags%}`, `{%taxonomy:taxonomy_name%}` with customizable separators and prefixing (`\n*`, `\t`, `:before`, `:leading`).
  * **Comments & Counts**: `{%comments%}`, `{%comments_count%}`.
  * **Custom Fields (Post Meta)**: `{%meta:key%}` with recursive formatting for complex nested arrays and objects.
* **Per-Post Granular Controls**: Dedicated meta box on edit screens to disable Markdown generation for specific posts or provide custom manual Markdown overrides with a single click reset.
* **Front Page Support**: Optional Markdown rendering for static homepages.
* **Developer-Friendly & Extensible**: Over 15 WordPress filter hooks allowing full customization of template rendering, block conversions, custom placeholder evaluation, and final page output.
* **High Performance**: Pure PHP conversion with no external dependencies or remote API calls.

## Developer Guide

For developers contributing to this plugin or extending it in local environments, several Composer and pnpm scripts are configured to streamline testing, linting, and asset compilation.

### Prerequisites
* **PHP >= 7.4**
* **Composer**
* **pnpm**

```bash
# Install PHP development dependencies (PHPUnit, PHP_CodeSniffer)
composer install

# Install frontend build dependencies (Webpack, Babel, Sass, ESLint, Stylelint)
pnpm install
```

### Composer Commands (`composer.json`)

```bash
# Run unit tests
composer test

# Run code style analysis
composer phpcs

# Automatically fix code style errors
composer phpcbf
```

### PNPM Commands (`package.json`)

```bash
# Compile all front-end assets (JS and CSS)
pnpm build-scripts
pnpm build-styles

# Run linters
pnpm eslint
pnpm stylelint

# Auto-fix linting issues
pnpm eslint-fix
pnpm stylelint-fix
```

## License

This plugin is licensed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.txt).
