<?php

/**
 * Plugin Name: IZ MD Pages
 * Description: WordPress plugin for generating and serving Markdown pages.
 * Version: 1.0.0
 * Author: Ivan Zhuck
 * Author URI: https://izhuck.ru/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: iz-md-pages
 */

declare(strict_types=1);

use IZMDPages\Admin\Assets\MdPageMetaboxAssets;
use IZMDPages\Admin\Assets\SettingsAssets;
use IZMDPages\Admin\MetaBoxes\MdPageMetaBox;
use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Admin\Settings\TemplatesSettingsPage;
use IZMDPages\Core\MdPages\MdPagesOutput;

/**
 * Register PSR-4 autoloader for IZMDPages namespace.
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'IZMDPages\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * Plugin initialization.
 */
$adminAssets = new SettingsAssets();
$adminAssets->init();

$metaBoxAssets = new MdPageMetaboxAssets();
$metaBoxAssets->init();

$settingsPage = new SettingsPage();
$settingsPage->init();

$templatesSettingsPage = new TemplatesSettingsPage();
$templatesSettingsPage->init();

$mdPagesOutput = new MdPagesOutput();
$mdPagesOutput->init();

$mdPageMetaBox = new MdPageMetaBox();
$mdPageMetaBox->init();

/**
 * Plugin activation hook: register rewrite endpoints and flush rewrite rules.
 */
function iz_md_activate(): void
{
    $mdPagesOutput = new MdPagesOutput();
    $mdPagesOutput->addRewriteEndpoints();
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'iz_md_activate');

/**
 * Plugin deactivation hook: flush rewrite rules.
 */
function iz_md_deactivate(): void
{
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'iz_md_deactivate');
