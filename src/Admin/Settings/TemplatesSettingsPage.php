<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Settings;

use IZMDPages\Core\Placeholder\PlaceholderRenderer;
use IZMDPages\Core\Template\TemplateRenderer;

/**
 * Handles administration templates settings page for IZ MD Pages.
 */
class TemplatesSettingsPage
{
    /**
     * Option key for post type templates in wp_options table.
     */
    public const OPTION_KEY = 'iz_md_templates';

    /**
     * Settings group name for WordPress Settings API.
     */
    public const SETTINGS_GROUP = 'iz_md_templates_group';

    /**
     * Page slug for the templates settings page.
     */
    public const PAGE_SLUG = 'iz-md-templates';

    /**
     * Default template string used when no specific template is defined.
     */
    public const DEFAULT_TEMPLATE = "# {%post_title%}\n\n{%post_content%}";

    /**
     * Template renderer instance.
     */
    private TemplateRenderer $templateRenderer;

    /**
     * TemplatesSettingsPage constructor.
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
     * Register settings via WordPress Settings API.
     */
    public function registerSettings(): void
    {
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_KEY,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitizeTemplates'],
                'default' => [],
            ]
        );
    }

    /**
     * Sanitize templates configuration array.
     *
     * @param mixed $input Value submitted from settings form.
     * @return array<string, string> Sanitized map of post type to template string.
     */
    public function sanitizeTemplates(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        $validPostTypes = array_keys($this->getTargetPostTypes());
        $sanitized = [];

        foreach ($input as $postType => $template) {
            if (is_string($postType) && is_string($template)) {
                $slug = sanitize_key($postType);
                if (in_array($slug, $validPostTypes, true)) {
                    $sanitized[$slug] = wp_unslash($template);
                }
            }
        }

        return $sanitized;
    }

    /**
     * Retrieve all target public post types (standard and custom).
     *
     * @return array<string, \WP_Post_Type> Map of post type names to post type objects.
     */
    public function getTargetPostTypes(): array
    {
        $postTypes = get_post_types(
            [
                'public' => true,
            ],
            'objects'
        );

        // Exclude media attachments
        unset($postTypes['attachment']);

        return $postTypes;
    }

    /**
     * Add sub-menu item under IZ MD Pages menu.
     */
    public function addSettingsPage(): void
    {
        add_submenu_page(
            SettingsPage::PAGE_SLUG,
            __('IZ MD Templates', 'iz-md-pages'),
            __('Templates', 'iz-md-pages'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Render administrative templates settings page HTML template.
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $data = [
            'currentTab' => 'templates',
            'settingsGroup' => self::SETTINGS_GROUP,
            'optionKey' => self::OPTION_KEY,
            'postTypes' => $this->getTargetPostTypes(),
            'templates' => (array) get_option(self::OPTION_KEY, []),
            'defaultTemplate' => self::DEFAULT_TEMPLATE,
            'supportedPlaceholders' => PlaceholderRenderer::getSupportedPlaceholders(),
        ];

        $this->templateRenderer->render('admin/settings/templates-page.php', $data);
    }
}
