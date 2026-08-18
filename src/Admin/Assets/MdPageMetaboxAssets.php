<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Assets;

use IZMDPages\Admin\Settings\SettingsPage;

/**
 * Handles registering and enqueuing CSS and JavaScript assets for the Markdown Page meta box.
 */
class MdPageMetaboxAssets extends Assets
{
    /**
     * Register WordPress hooks.
     */
    public function init(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueMetaBoxAssets']);
    }

    /**
     * Enqueue CSS styles and JavaScript assets on screens with the Markdown Page meta box.
     *
     * @param string $hook Current admin page hook suffix.
     */
    public function enqueueMetaBoxAssets(string $hook): void
    {
        if (!$this->isMetaBoxScreen($hook)) {
            return;
        }

        $this->enqueueStyle('md-page-meta-box');
        $this->enqueueScript('md-page-meta-box');
    }
}
