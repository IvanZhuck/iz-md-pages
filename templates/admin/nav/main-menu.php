<?php

declare(strict_types=1);

/**
 * Main admin navigation menu template.
 *
 * @var string $currentTab Current active tab identifier.
 */

if (!defined('ABSPATH')) {
    exit;
}

$currentTab = $currentTab ?? 'general';
?>
<nav class="nav-tab-wrapper wp-clearfix" style="margin-bottom: 20px;">
    <a href="<?php echo esc_url(admin_url('admin.php?page=iz-md-settings')); ?>" class="nav-tab <?php echo $currentTab === 'general' ? 'nav-tab-active' : ''; ?>">
        <?php esc_html_e('General', 'iz-md-pages'); ?>
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=iz-md-templates')); ?>" class="nav-tab <?php echo $currentTab === 'templates' ? 'nav-tab-active' : ''; ?>">
        <?php esc_html_e('Templates', 'iz-md-pages'); ?>
    </a>
</nav>
