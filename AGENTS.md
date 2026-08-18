# Developer Guidelines & Agent Rules for IZ MD Pages

This document defines architectural principles, coding standards, and development guidelines for the **IZ MD Pages** WordPress plugin. All developers and AI coding agents MUST follow these instructions when contributing code to this repository.

---

## 1. Project Overview & Architecture

- **Plugin Name:** IZ MD Pages
- **Namespace:** `IZMDPages\`
- **Autoloading:** PSR-4 standard (`IZMDPages\` maps to `src/`)
- **Main Plugin Entry:** `iz-md-pages.php`

### Folder Structure
```text
iz-md-pages/
├── index.php                      # Directory listing protection
├── iz-md-pages.php                # Plugin entry point & PSR-4 autoloader
├── AGENTS.md                      # Developer guidelines (this file)
├── src/
│   ├── Admin/
│   │   ├── Assets/
│   │   │   ├── Assets.php                 # Base administration assets controller
│   │   │   ├── MdPageMetaboxAssets.php    # Meta box assets controller
│   │   │   └── SettingsAssets.php         # Settings assets controller
│   │   ├── MetaBoxes/
│   │   │   └── MdPageMetaBox.php          # Markdown page meta box controller
│   │   └── Settings/
│   │       ├── Settings.php               # Base settings controller
│   │       ├── SettingsPage.php           # General settings controller
│   │       └── TemplatesSettingsPage.php  # Templates settings controller
│   └── Core/
│       ├── Converter/
│       │   └── HtmlToMarkdownConverter.php # HTML to Markdown converter
│       ├── MdPages/
│       │   ├── BlockRenderer.php           # Gutenberg block renderer & overrides
│       │   └── MdPagesOutput.php           # Endpoint routing & MD output
│       ├── Placeholder/
│       │   └── PlaceholderRenderer.php     # Template placeholder renderer
│       └── Template/
│           └── TemplateRenderer.php        # Template rendering engine
└── templates/
    └── admin/
        ├── info/ 
        │   ├── markdown-reference.php     # Markdown syntax guide HTML view
        │   └── placeholders-reference.php  # Placeholders reference HTML view
        ├── meta-boxes/
        │   └── md-page-meta-box.php       # Meta box HTML view
        ├── nav/
        │   └── main-menu.php              # Shared admin tabs navigation
        └── settings/
            ├── settings-page.php          # General settings HTML view
            └── templates-page.php         # Templates settings HTML view
```

---

## 2. PHP Compatibility Requirements

- **PHP Version Target:** **PHP >= 7.4**
- **Strict Types:** EVERY PHP file MUST declare strict types as the very first statement:
  ```php
  <?php

  declare(strict_types=1);
  ```
---

## 3. Coding Standards (PSR-12)

All code MUST strictly adhere to the **PSR-12 Extended Coding Style**:

1. **Indentation & Line Endings:**
   - 4 spaces for indentation everywhere (DO NOT use tabs).
   - Unix LF (`\n`) line endings.
   - UTF-8 encoding without BOM.

2. **Braces Placement:**
   - Class and method opening braces `{` MUST be placed on a new line:
     ```php
     class SettingsPage
     {
         public function init(): void
         {
             // ...
         }
     }
     ```
   - Control structure braces (`if`, `foreach`, `switch`) MUST be placed on the same line:
     ```php
     if ($condition) {
         // ...
     }
     ```

3. **Naming Conventions:**
   - Classes, interfaces, traits: `PascalCase` (e.g., `TemplateRenderer`)
   - Class methods and variables: `camelCase` (e.g., `getTargetPostTypes`, `$enabledTypes`)
   - Class constants: `UPPER_CASE_WITH_UNDERSCORES` with explicit visibility:
     ```php
     public const OPTION_KEY = 'iz_md_enabled_post_types';
     ```

4. **Type Hinting & Arrays:**
   - Explicit return types (`: void`, `: string`, `: array`) are MANDATORY for all methods.
   - Short array syntax `[]` MUST be used instead of legacy `array()`.

---

## 4. Template & UI Architecture

1. **Separation of Concerns:**
   - PHP class files in `src/` MUST NOT contain embedded HTML markup.
   - All HTML views MUST reside under `templates/`.

2. **Template Rendering:**
   - Templates MUST be rendered exclusively using `IZMDPages\Core\Template\TemplateRenderer`.
   - Example usage:
     ```php
     $this->templateRenderer->render('admin/settings/settings-page.php', $data);
     ```

---

## 5. Commenting & Documentation Conventions

1. **Language:**
   - ALL code comments, PHPDoc blocks (`/** ... */`), `@param`, `@return`, and `@var` annotations MUST be written in **English**.

2. **DocBlocks:**
   - Public methods, properties, and classes MUST have descriptive PHPDoc blocks explaining their purpose and parameter types.

---

## 6. Security & WordPress Guidelines

- **Input Sanitization:** Sanitize all incoming user data via `sanitize_key()`, `sanitize_text_field()`, or custom sanitization callbacks.
- **Output Escaping:** Escape all variables in template files using WordPress helper functions (`esc_html()`, `esc_attr()`, `esc_url()`).
- **Access Control:** Always verify user capabilities (`current_user_can('manage_options')`) before executing administrative actions.
