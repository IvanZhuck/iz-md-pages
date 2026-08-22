<?php

/**
 * Admin developer hooks reference info block template.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="iz-md-info-block is-collapsed" id="iz-md-hooks-reference" data-default-state="collapsed">
    <div class="iz-md-info-block-header" role="button" tabindex="0" aria-expanded="false">
        <h3>
            <span class="dashicons dashicons-editor-code" aria-hidden="true"></span>
            <?php esc_html_e('Developer Hooks & Filters Reference', 'iz-md-pages'); ?>
        </h3>
        <button type="button" class="iz-md-info-block-toggle" aria-label="<?php esc_attr_e('Toggle developer hooks reference', 'iz-md-pages'); ?>">
            <span class="iz-md-info-block-toggle-text" data-text-expand="<?php esc_attr_e('Expand', 'iz-md-pages'); ?>" data-text-collapse="<?php esc_attr_e('Collapse', 'iz-md-pages'); ?>"><?php esc_html_e('Expand', 'iz-md-pages'); ?></span>
            <span class="dashicons dashicons-arrow-up" aria-hidden="true"></span>
        </button>
    </div>
    <div class="iz-md-info-block-body iz-md-docs">
        <p class="description">
            <?php esc_html_e('Use the following WordPress filter hooks to extend, customize, and override Markdown generation, custom placeholders, and block editor rendering in your theme or custom plugin.', 'iz-md-pages'); ?>
        </p>

        <div class="iz-md-docs-grid-single">
            <div>
                <strong><?php esc_html_e('1. Custom Placeholders Hooks', 'iz-md-pages'); ?></strong>
                <ul>
                    <li>
                        <code>iz_md_render_custom_placeholder_{$tag}</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Evaluate a specific placeholder tag (e.g. {%reading_time:200%}).', 'iz-md-pages'); ?>
                            <br><code>apply_filters("iz_md_render_custom_placeholder_{$tag}", $replacement, $args, $post, $template)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_render_custom_placeholder</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Dynamically evaluate any unrecognized placeholder tag.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_render_custom_placeholder', $replacement, $tag, $args, $post, $template)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_grouped_placeholders</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Register custom placeholder groups in admin reference UI.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_grouped_placeholders', $groups)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_pages_placeholders</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Modify predefined static placeholders replacement dictionary.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_pages_placeholders', $placeholders, $post, $template)</code>
                        </div>
                    </li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('2. Block Editor (Gutenberg) Hooks', 'iz-md-pages'); ?></strong>
                <ul>
                    <li>
                        <code>iz_md_render_block_{$blockName}</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Override Markdown output for a specific block (e.g. core/quote).', 'iz-md-pages'); ?>
                            <br><code>apply_filters("iz_md_render_block_{$blockName}", $override, $block, $post)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_render_block</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter or override rendering for any block in post content.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_render_block', $override, $block, $post)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_block_html</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter HTML of a block prior to Markdown conversion.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_block_html', $html, $block, $post)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_render_blocks_content</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter assembled Markdown content from all post blocks.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_render_blocks_content', $content, $blocks, $post)</code>
                        </div>
                    </li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('3. Fields & HTML Converter Filters', 'iz-md-pages'); ?></strong>
                <ul>
                    <li>
                        <code>iz_md_render_post_meta</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Override or filter custom post field / meta values.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_render_post_meta', $value, $metaKey, $post, $metaValue, $leading)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_placeholder_taxonomy_terms</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter taxonomy term names before joining with separator.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_placeholder_taxonomy_terms', $termNames, $post, $taxonomy, $sep, $leading)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_placeholder_comments</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter rendered Markdown comments output.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_placeholder_comments', $result, $comments, $post)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_render_post_field</code> / <code>iz_md_render_author_field</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter individual post core and author fields.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_render_post_field', $value, $fieldName, $post)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_pages_convert_tag</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Override conversion rule for specific HTML tags.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_pages_convert_tag', $override, $tagName, $element, $innerText)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_placeholder_render_post_content</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter classic editor HTML before Markdown conversion.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_placeholder_render_post_content', $htmlContent, $post)</code>
                        </div>
                    </li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('4. Post & Post Type Templates Hooks', 'iz-md-pages'); ?></strong>
                <ul>
                    <li>
                        <code>iz_md_post_template_{$postId}</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Override Markdown template for a specific post by its ID.', 'iz-md-pages'); ?>
                            <br><code>apply_filters("iz_md_post_template_{$postId}", $template, $post)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_post_type_template_{$postType}</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Override Markdown template for a specific post type (e.g. post, page).', 'iz-md-pages'); ?>
                            <br><code>apply_filters("iz_md_post_type_template_{$postType}", $template, $postType)</code>
                        </div>
                    </li>
                </ul>
            </div>

            <div>
                <strong><?php esc_html_e('5. Rendered Page Output Filters', 'iz-md-pages'); ?></strong>
                <ul>
                    <li>
                        <code>iz_md_page_content_{$postId}</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter final assembled Markdown content for a specific post by its ID.', 'iz-md-pages'); ?>
                            <br><code>apply_filters("iz_md_page_content_{$postId}", $content, $post)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_page_content_{$postType}</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter final assembled Markdown content for a specific post type (e.g. post, page).', 'iz-md-pages'); ?>
                            <br><code>apply_filters("iz_md_page_content_{$postType}", $content, $post)</code>
                        </div>
                    </li>
                    <li>
                        <code>iz_md_page_content</code>
                        <div class="iz-md-docs-hook-desc">
                            <?php esc_html_e('Filter final assembled Markdown content for any post type.', 'iz-md-pages'); ?>
                            <br><code>apply_filters('iz_md_page_content', $content, $post)</code>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
