<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Settings;

use IZMDPages\Core\Placeholder\PlaceholderRenderer;

/**
 * Handles administration templates settings page for IZ MD Pages.
 */
class TemplatesSettingsPage extends Settings
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
     * Get the template string configured for a specific post type or default template.
     *
     * @param string $postType Post type slug.
     * @return string Markdown template string.
     */
    public static function getTemplateForPostType(string $postType): string
    {
        $templates = (array) get_option(self::OPTION_KEY, []);
        return isset($templates[$postType]) && is_string($templates[$postType]) && $templates[$postType] !== ''
            ? $templates[$postType]
            : self::DEFAULT_TEMPLATE;
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
     * Add sub-menu item under IZ MD Pages menu.
     */
    public function addSettingsPage(): void
    {
        add_submenu_page(
            self::PARENT_SLUG,
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
            'groupedPlaceholders' => PlaceholderRenderer::getGroupedPlaceholders(),
        ];

        $this->templateRenderer->render('admin/settings/templates-page.php', $data);
    }
}
