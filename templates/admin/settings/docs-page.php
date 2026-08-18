<?php

declare(strict_types=1);

/**
 * Admin documentation page template.
 *
 * @var string                               $currentTab            Current active tab identifier.
 * @var array<int, string>                   $supportedPlaceholders Supported placeholder tokens.
 * @var array<string, array<string, string>> $groupedPlaceholders   Grouped placeholder definitions.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap iz-md-settings-wrap iz-md-docs-wrap">
    <h1><?php echo esc_html__('IZ MD Documentation', 'iz-md-pages'); ?></h1>

    <?php $this->render('admin/nav/main-menu.php', ['currentTab' => $currentTab ?? 'docs']); ?>

    <?php $this->render('admin/info/overview.php'); ?>
    <?php $this->render('admin/info/user-guide.php'); ?>
    <?php $this->render('admin/info/placeholders-reference.php', ['groupedPlaceholders' => $groupedPlaceholders]); ?>
    <?php $this->render('admin/info/markdown-reference.php'); ?>
    <?php $this->render('admin/info/hooks-reference.php'); ?>
</div>
