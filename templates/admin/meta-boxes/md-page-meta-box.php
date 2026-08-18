<?php

declare(strict_types=1);

/**
 * Meta box view for Markdown Page settings.
 *
 * @var \WP_Post $post                Current post object.
 * @var string   $nonceAction         Nonce action string.
 * @var string   $nonceName           Nonce field name.
 * @var string   $fieldDisabled       Disable checkbox field name.
 * @var string   $fieldManualEnabled  Checkbox field name.
 * @var string   $fieldManualContent  Textarea field name.
 * @var bool     $isDisabled          Whether MD version is disabled for this page.
 * @var bool     $isManual            Whether manual Markdown mode is active.
 * @var string   $manualContent       Custom Markdown content.
 * @var string   $defaultTemplate     Default template for current post type.
 */

if (!defined('ABSPATH')) {
    exit;
}

wp_nonce_field($nonceAction, $nonceName);
?>
<div class="iz-md-meta-box-content">
    <p>
        <label for="<?php echo esc_attr($fieldDisabled); ?>" class="checkbox-label">
            <input
                type="checkbox"
                name="<?php echo esc_attr($fieldDisabled); ?>"
                id="<?php echo esc_attr($fieldDisabled); ?>"
                value="1"
                <?php checked($isDisabled); ?>
            />
            <?php echo esc_html__('Disable MD version for this page', 'iz-md-pages'); ?>
        </label>
    </p>

    <p>
        <label for="<?php echo esc_attr($fieldManualEnabled); ?>" class="checkbox-label">
            <input
                type="checkbox"
                name="<?php echo esc_attr($fieldManualEnabled); ?>"
                id="<?php echo esc_attr($fieldManualEnabled); ?>"
                value="1"
                <?php checked($isManual); ?>
            />
            <?php echo esc_html__('Set MD page text manually', 'iz-md-pages'); ?>
        </label>
    </p>

    <div
        class="iz-md-manual-content-wrapper<?php echo $isManual ? '' : ' is-hidden'; ?>"
        id="iz-md-manual-content-wrapper"
        <?php echo $isManual ? '' : 'style="display: none;"'; ?>
    >
        <p>
            <label for="<?php echo esc_attr($fieldManualContent); ?>" class="screen-reader-text">
                <?php echo esc_html__('Markdown content', 'iz-md-pages'); ?>
            </label>
            <textarea
                name="<?php echo esc_attr($fieldManualContent); ?>"
                id="<?php echo esc_attr($fieldManualContent); ?>"
                rows="10"
                style="width: 100%; font-family: monospace; resize: vertical;"
                placeholder="<?php echo esc_attr__('# Enter markdown here...', 'iz-md-pages'); ?>"
            ><?php echo esc_textarea($manualContent); ?></textarea>
        </p>
        <p class="iz-md-manual-content-actions">
            <button
                type="button"
                class="button button-secondary iz-md-reset-default-btn"
                id="iz-md-reset-default-btn"
                data-default-template="<?php echo esc_attr($defaultTemplate); ?>"
                data-confirm-message="<?php echo esc_attr__('Are you sure you want to reset the content to the default template for this post type?', 'iz-md-pages'); ?>"
            >
                <span class="dashicons dashicons-image-rotate" aria-hidden="true"></span>
                <?php echo esc_html__('Reset to default template', 'iz-md-pages'); ?>
            </button>
        </p>
        <p class="description">
            <?php esc_html_e('You can use template placeholders here (e.g. {%post_title%}, {%post_content%}, {%author_name%}, {%categories%}, etc.).', 'iz-md-pages'); ?>
        </p>
    </div>
</div>
