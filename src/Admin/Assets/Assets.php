<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Assets;

use IZMDPages\Admin\Settings\DocumentationSettingsPage;
use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Admin\Settings\TemplatesSettingsPage;

/**
 * Base abstract class for managing administration assets.
 */
abstract class Assets
{
    /**
     * Get the main plugin file path.
     *
     * @return string
     */
    protected function getPluginFilePath(): string
    {
        return dirname(__DIR__, 3) . '/iz-md-pages.php';
    }

    /**
     * Get the relative path to the assets build directory.
     *
     * @return string
     */
    protected function getBuildDirPath(): string
    {
        return 'assets/build';
    }

    /**
     * Enqueue a plugin stylesheet by name.
     *
     * @param string $name Style handle identifier / filename without extension (e.g., 'settings').
     * @param array<int, string> $deps Optional array of style dependencies.
     */
    protected function enqueueStyle(string $name, array $deps = []): void
    {
        $pluginFile = $this->getPluginFilePath();
        $buildDir = $this->getBuildDirPath();
        $cssRelativePath = $buildDir . '/css/' . $name . '.css';
        $cssPath = dirname($pluginFile) . '/' . $cssRelativePath;
        $cssUrl = plugins_url($cssRelativePath, $pluginFile);
        $cssVersion = file_exists($cssPath) ? (string) filemtime($cssPath) : '';

        wp_enqueue_style(
            'iz-md-' . $name,
            $cssUrl,
            $deps,
            $cssVersion
        );
    }

    /**
     * Enqueue a plugin script by name.
     *
     * @param string $name Script handle identifier / filename without extension (e.g., 'settings').
     * @param array<int, string> $deps Optional array of script dependencies.
     * @param bool $inFooter Whether to enqueue the script before </body> instead of in the <head>.
     */
    protected function enqueueScript(string $name, array $deps = [], bool $inFooter = true): void
    {
        $pluginFile = $this->getPluginFilePath();
        $buildDir = $this->getBuildDirPath();
        $jsRelativePath = $buildDir . '/js/' . $name . '.bundle.js';
        $jsPath = dirname($pluginFile) . '/' . $jsRelativePath;
        $jsUrl = plugins_url($jsRelativePath, $pluginFile);
        $jsVersion = file_exists($jsPath) ? (string) filemtime($jsPath) : '';

        wp_enqueue_script(
            'iz-md-' . $name,
            $jsUrl,
            $deps,
            $jsVersion,
            $inFooter
        );
    }

    /**
     * Determine if current admin screen is one of the plugin settings pages.
     *
     * @param string $hook Current admin page hook suffix.
     * @return bool True if current page is a plugin settings page, false otherwise.
     */
    protected function isPluginPage(string $hook = ''): bool
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required for read-only admin screen routing checks.
        $currentPage = isset($_GET['page']) && is_string($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        $settingsPages = [
            SettingsPage::PAGE_SLUG,
            TemplatesSettingsPage::PAGE_SLUG,
            DocumentationSettingsPage::PAGE_SLUG,
        ];

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $screenId = $screen ? (string) $screen->id : $hook;

        return in_array($currentPage, $settingsPages, true)
            || strpos($screenId, SettingsPage::PAGE_SLUG) !== false
            || strpos($screenId, TemplatesSettingsPage::PAGE_SLUG) !== false
            || strpos($screenId, DocumentationSettingsPage::PAGE_SLUG) !== false;
    }

    /**
     * Determine if current admin screen displays the Markdown Page meta box.
     *
     * @param string $hook Current admin page hook suffix.
     * @return bool True if current screen has the meta box enabled, false otherwise.
     */
    protected function isMetaBoxScreen(string $hook): bool
    {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return false;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen === null || empty($screen->post_type)) {
            return false;
        }

        $enabledTypes = (array) get_option(SettingsPage::OPTION_KEY, ['post', 'page']);

        return in_array($screen->post_type, $enabledTypes, true);
    }
}
