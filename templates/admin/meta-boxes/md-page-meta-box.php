<?php

declare(strict_types=1);

/**
 * Meta box view for Markdown Page settings.
 *
 * @var \WP_Post $post Current post object.
 */

if (!defined('ABSPATH')) {
    exit;
}

wp_nonce_field($nonceAction, $nonceName);
?>
<div class="iz-md-meta-box-content">
    <!-- Meta box placeholder content -->
    <p class="description">
        <?php echo esc_html__('Markdown page settings for this post.', 'iz-md-pages'); ?>
    </p>
</div>
