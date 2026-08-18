<?php

declare(strict_types=1);

/**
 * Admin Markdown syntax reference info block template.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="iz-md-info-block is-collapsed" id="iz-md-markdown-reference" data-default-state="collapsed">
    <div class="iz-md-info-block-header" role="button" tabindex="0" aria-expanded="false">
        <h3>
            <span class="dashicons dashicons-shortcode" aria-hidden="true"></span>
            <?php esc_html_e('Markdown Syntax Guide', 'iz-md-pages'); ?>
        </h3>
        <button type="button" class="iz-md-info-block-toggle" aria-label="<?php esc_attr_e('Toggle Markdown syntax reference', 'iz-md-pages'); ?>">
            <span class="iz-md-info-block-toggle-text" data-text-expand="<?php esc_attr_e('Expand', 'iz-md-pages'); ?>" data-text-collapse="<?php esc_attr_e('Collapse', 'iz-md-pages'); ?>"><?php esc_html_e('Expand', 'iz-md-pages'); ?></span>
            <span class="dashicons dashicons-arrow-up" aria-hidden="true"></span>
        </button>
    </div>
    <div class="iz-md-info-block-body iz-md-docs">
        <p class="description">
            <?php esc_html_e('Quick reference for standard Markdown syntax to help structure your templates and content.', 'iz-md-pages'); ?>
        </p>

        <div class="iz-md-docs-grid">
            <div>
                <strong><?php esc_html_e('Headings', 'iz-md-pages'); ?></strong>
                <ul>
                    <li><code># <?php esc_html_e('Heading 1', 'iz-md-pages'); ?></code></li>
                    <li><code>## <?php esc_html_e('Heading 2', 'iz-md-pages'); ?></code></li>
                    <li><code>### <?php esc_html_e('Heading 3', 'iz-md-pages'); ?></code></li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('Emphasis & Text Formatting', 'iz-md-pages'); ?></strong>
                <ul>
                    <li><code>**<?php esc_html_e('Bold Text', 'iz-md-pages'); ?>**</code> &mdash; <strong><?php esc_html_e('Bold Text', 'iz-md-pages'); ?></strong></li>
                    <li><code>*<?php esc_html_e('Italic Text', 'iz-md-pages'); ?>*</code> &mdash; <em><?php esc_html_e('Italic Text', 'iz-md-pages'); ?></em></li>
                    <li><code>~~<?php esc_html_e('Strikethrough', 'iz-md-pages'); ?>~~</code> &mdash; <del><?php esc_html_e('Strikethrough', 'iz-md-pages'); ?></del></li>
                    <li><code>`<?php esc_html_e('Inline code', 'iz-md-pages'); ?>`</code> &mdash; <code>inline code</code></li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('Lists', 'iz-md-pages'); ?></strong>
                <ul>
                    <li><code>- <?php esc_html_e('Bullet list item', 'iz-md-pages'); ?></code> (<?php esc_html_e('or', 'iz-md-pages'); ?> <code>*</code>)</li>
                    <li><code>1. <?php esc_html_e('Numbered list item', 'iz-md-pages'); ?></code></li>
                    <li><code>&nbsp;&nbsp;- <?php esc_html_e('Nested item (indent 2-4 spaces)', 'iz-md-pages'); ?></code></li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('Links & Images', 'iz-md-pages'); ?></strong>
                <ul>
                    <li><code>[<?php esc_html_e('Link title', 'iz-md-pages'); ?>](https://example.com)</code></li>
                    <li><code>![<?php esc_html_e('Alt text', 'iz-md-pages'); ?>](https://example.com/image.jpg)</code></li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('Blockquotes & Dividers', 'iz-md-pages'); ?></strong>
                <ul>
                    <li><code>&gt; <?php esc_html_e('Blockquote line', 'iz-md-pages'); ?></code></li>
                    <li><code>---</code> &mdash; <?php esc_html_e('Horizontal divider rule', 'iz-md-pages'); ?></li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('Code Blocks & Tables', 'iz-md-pages'); ?></strong>
                <ul>
                    <li><code>```language\ncode\n```</code> &mdash; <?php esc_html_e('Fenced code block', 'iz-md-pages'); ?></li>
                    <li><code>| Col 1 | Col 2 |\n| --- | --- |\n| Val 1 | Val 2 |</code> &mdash; <?php esc_html_e('Table', 'iz-md-pages'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
