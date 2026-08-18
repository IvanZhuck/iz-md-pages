<?php

declare(strict_types=1);

/**
 * Admin user guide / how-to instructions info block template.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="iz-md-info-block is-collapsed" id="iz-md-user-guide" data-default-state="collapsed">
    <div class="iz-md-info-block-header" role="button" tabindex="0" aria-expanded="false">
        <h3>
            <span class="dashicons dashicons-editor-help" aria-hidden="true"></span>
            <?php esc_html_e('How to Use the Plugin', 'iz-md-pages'); ?>
        </h3>
        <button type="button" class="iz-md-info-block-toggle" aria-label="<?php esc_attr_e('Toggle plugin usage guide', 'iz-md-pages'); ?>">
            <span class="iz-md-info-block-toggle-text" data-text-expand="<?php esc_attr_e('Expand', 'iz-md-pages'); ?>" data-text-collapse="<?php esc_attr_e('Collapse', 'iz-md-pages'); ?>"><?php esc_html_e('Expand', 'iz-md-pages'); ?></span>
            <span class="dashicons dashicons-arrow-up" aria-hidden="true"></span>
        </button>
    </div>
    <div class="iz-md-info-block-body iz-md-docs">
        <p class="description">
            <?php esc_html_e('Follow these simple steps to configure, customize, and start serving Markdown versions of your content:', 'iz-md-pages'); ?>
        </p>

        <div class="iz-md-docs-grid-single">
            <div>
                <strong><?php esc_html_e('Step 1: Configure General Settings', 'iz-md-pages'); ?></strong>
                <ul>
                    <li>
                        <?php esc_html_e('Navigate to Settings &rarr; IZ MD Pages &rarr; General.', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e('URL Format:', 'iz-md-pages'); ?></strong>
                        <?php esc_html_e('Choose between clean permalink endpoints (e.g. /my-post/md) or GET query parameters (e.g. /my-post/?md).', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Front Page MD Version:', 'iz-md-pages'); ?></strong>
                        <?php esc_html_e('Enable or disable Markdown output for your static homepage (e.g. /md).', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Post Types:', 'iz-md-pages'); ?></strong>
                        <?php esc_html_e('Select which post types (Posts, Pages, custom post types) should have Markdown pages generated.', 'iz-md-pages'); ?>
                    </li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('Step 2: Customize Markdown Templates', 'iz-md-pages'); ?></strong>
                <ul>
                    <li>
                        <?php esc_html_e('Navigate to Settings &rarr; IZ MD Pages &rarr; Templates.', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <?php esc_html_e('Define a Markdown layout for each enabled post type using placeholders like {%post_title%}, {%post_content%}, {%post_date%}, {%author_name%}, {%categories%}, and {%meta:key%}.', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <?php esc_html_e('If a template is dynamically filtered by a theme or custom plugin hook, the field is automatically locked to avoid conflicting edits.', 'iz-md-pages'); ?>
                    </li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('Step 3: Manage Content per Post / Page', 'iz-md-pages'); ?></strong>
                <ul>
                    <li>
                        <?php esc_html_e('Open any post or page in the WordPress block editor or classic editor.', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <?php esc_html_e('Locate the "Markdown Page" meta box below the main editor area.', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Disable MD version:', 'iz-md-pages'); ?></strong>
                        <?php esc_html_e('Check this box to disable the Markdown endpoint for that specific post (returns 301 redirect to normal URL).', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Set MD page text manually:', 'iz-md-pages'); ?></strong>
                        <?php esc_html_e('Check this to write custom Markdown or a bespoke placeholder template for this single post.', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Preview:', 'iz-md-pages'); ?></strong>
                        <?php esc_html_e('Click "View Markdown version" in the meta box footer to inspect the generated Markdown in a new tab.', 'iz-md-pages'); ?>
                    </li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('Step 4: AI & Machine Discovery', 'iz-md-pages'); ?></strong>
                <ul>
                    <li>
                        <?php esc_html_e('The plugin automatically injects a <link rel="alternate" type="text/markdown" href="..."> tag into the HTML <head> of enabled posts.', 'iz-md-pages'); ?>
                    </li>
                    <li>
                        <?php esc_html_e('AI assistants, LLM tools, search engine crawlers, and RSS/data scrapers can easily discover and consume pure text Markdown without HTML clutter.', 'iz-md-pages'); ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
