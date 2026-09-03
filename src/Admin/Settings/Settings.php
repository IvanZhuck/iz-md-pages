<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Settings;

use IZMDPages\Core\Settings\CoreSettings;
use IZMDPages\Core\Template\TemplateRenderer;

/**
 * Base abstract class for administration settings pages.
 */
abstract class Settings
{
    /**
     * Parent menu slug for settings pages.
     */
    public const PARENT_SLUG = 'iz-md-settings';

    /**
     * Menu icon for top-level admin menu.
     */
    public const MENU_ICON = 'dashicons-media-document';

    /**
     * Template renderer instance.
     */
    protected TemplateRenderer $templateRenderer;

    /**
     * Settings constructor.
     *
     * @param TemplateRenderer|null $templateRenderer Template renderer instance.
     */
    public function __construct(?TemplateRenderer $templateRenderer = null)
    {
        $this->templateRenderer = $templateRenderer ?? new TemplateRenderer();
    }

    /**
     * Register WordPress hooks.
     */
    public function init(): void
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    /**
     * Retrieve all target public post types (standard and custom).
     *
     * @return array<string, \WP_Post_Type> Map of post type names to post type objects.
     */
    protected function getTargetPostTypes(): array
    {
        return CoreSettings::getTargetPostTypes();
    }

    /**
     * Register settings via WordPress Settings API.
     */
    abstract public function registerSettings(): void;

    /**
     * Add settings page/sub-menu in WordPress admin menu.
     */
    abstract public function addSettingsPage(): void;

    /**
     * Render administrative settings page HTML template.
     */
    abstract public function renderSettingsPage(): void;
}
