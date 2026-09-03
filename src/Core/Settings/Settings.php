<?php

declare(strict_types=1);

namespace IZMDPages\Core\Settings;

/**
 * Handles global plugin settings, configuration, and plugin path helpers.
 */
class Settings
{
    /**
     * Get the absolute path to the main plugin file.
     *
     * @return string Main plugin file path.
     */
    public static function getPluginFilePath(): string
    {
        return dirname(__DIR__, 3) . '/iz-md-pages.php';
    }

    /**
     * Get the plugin basename for WordPress hooks and action links.
     *
     * @return string Plugin basename (e.g., 'iz-md-pages/iz-md-pages.php').
     */
    public static function getPluginBaseName(): string
    {
        return plugin_basename(self::getPluginFilePath());
    }
}
