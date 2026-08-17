<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Assets;

use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Admin\Settings\TemplatesSettingsPage;

/**
 * Handles registering and enqueuing administration CSS and JavaScript assets.
 */
class AdminAssets
{
    /**
     * Register WordPress hooks.
     */
    public function init(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueSettingsAssets']);
    }

    /**
     * Enqueue CSS styles and JavaScript assets on plugin settings pages.
     *
     * @param string $hook Current admin page hook suffix.
     */
    public function enqueueSettingsAssets(string $hook): void
    {
        if (!$this->isPluginPage($hook)) {
            return;
        }

        $pluginFile = dirname(__DIR__, 3) . '/iz-md-pages.php';
        $cssPath = dirname(__DIR__, 3) . '/assets/build/scss/settings.css';
        $jsPath = dirname(__DIR__, 3) . '/assets/build/js/settings.bundle.js';

        $cssUrl = plugins_url('assets/build/scss/settings.css', $pluginFile);
        $jsUrl = plugins_url('assets/build/js/settings.bundle.js', $pluginFile);

        $cssVersion = file_exists($cssPath) ? (string) filemtime($cssPath) : '';
        $jsVersion = file_exists($jsPath) ? (string) filemtime($jsPath) : '';

        wp_enqueue_style(
            'iz-md-settings',
            $cssUrl,
            [],
            $cssVersion
        );

        wp_enqueue_script(
            'iz-md-settings',
            $jsUrl,
            [],
            $jsVersion,
            true
        );
    }

    /**
     * Determine if current admin screen is one of the plugin settings pages.
     *
     * @param string $hook Current admin page hook suffix.
     * @return bool True if current page is a plugin settings page, false otherwise.
     */
    public function isPluginPage(string $hook = ''): bool
    {
        $currentPage = isset($_GET['page']) && is_string($_GET['page']) ? sanitize_key($_GET['page']) : '';
        $settingsPages = [
            SettingsPage::PAGE_SLUG,
            TemplatesSettingsPage::PAGE_SLUG,
        ];

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $screenId = $screen ? (string) $screen->id : $hook;

        return in_array($currentPage, $settingsPages, true)
            || strpos($screenId, SettingsPage::PAGE_SLUG) !== false
            || strpos($screenId, TemplatesSettingsPage::PAGE_SLUG) !== false;
    }
}
