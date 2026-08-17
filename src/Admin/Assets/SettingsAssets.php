<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Assets;

/**
 * Handles registering and enqueuing administration CSS and JavaScript assets.
 */
class SettingsAssets extends Assets
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

        $this->enqueueStyle('settings');
        $this->enqueueScript('settings');
    }
}
