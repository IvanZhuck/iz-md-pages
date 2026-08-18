<?php

declare(strict_types=1);

/**
 * Admin overview & features info block template.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="iz-md-info-block-static" id="iz-md-overview">
    <div class="iz-md-info-block-header">
        <h3>
            <span class="dashicons dashicons-info" aria-hidden="true"></span>
            <?php esc_html_e('About IZ MD Pages', 'iz-md-pages'); ?>
        </h3>
    </div>
    <div class="iz-md-info-block-body iz-md-docs">
        <p>
            <?php esc_html_e('IZ MD Pages is designed to generate clean, lightweight, and AI/LLM-friendly Markdown versions of your WordPress posts, pages and custom post types.', 'iz-md-pages'); ?>
        </p>
        <p>
            <?php esc_html_e('The plugin serves Markdown content via a dedicated URL endpoint (/md) or query parameter (?md), automatically converting Gutenberg blocks and classic editor content while inserting alternate link tags for discovery. You can configure flexible Markdown templates with placeholders for each post type, set custom Markdown per post, or customize output using developer filter hooks.', 'iz-md-pages'); ?>
        </p>
    </div>
</div>
