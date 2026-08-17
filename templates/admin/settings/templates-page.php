<?php

declare(strict_types=1);

/**
 * Admin templates settings page template.
 *
 * @var string $currentTab Current active tab identifier.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('IZ MD Templates', 'iz-md-pages'); ?></h1>

    <?php $this->render('admin/nav/main-menu.php', ['currentTab' => $currentTab ?? 'templates']); ?>

    <div class="iz-md-templates-content" style="margin-top: 20px;">
        <p class="description">
            <?php esc_html_e('Configure Markdown output templates for each post type here.', 'iz-md-pages'); ?>
        </p>
    </div>
</div>
