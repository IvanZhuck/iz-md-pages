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
     * Option key for universal header template.
     */
    public const OPTION_HEADER_TEMPLATE_KEY = 'iz_md_header_template';

    /**
     * Option key for universal footer template.
     */
    public const OPTION_FOOTER_TEMPLATE_KEY = 'iz_md_footer_template';

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
     * Get universal header template string prepended to all MD pages.
     *
     * @return string Header Markdown template string.
     */
    public static function getHeaderTemplate(): string
    {
        $template = get_option(self::OPTION_HEADER_TEMPLATE_KEY, '');
        return is_string($template) ? $template : '';
    }

    /**
     * Get universal footer template string appended to all MD pages.
     *
     * @return string Footer Markdown template string.
     */
    public static function getFooterTemplate(): string
    {
        $template = get_option(self::OPTION_FOOTER_TEMPLATE_KEY, '');
        return is_string($template) ? $template : '';
    }

    /**
     * Get the template string configured for a specific post type or default template.
     * Applies filter hooks allowing developers to override the template dynamically.
     *
     * @param string $postType Post type slug.
     * @return string Markdown template string.
     */
    public static function getTemplateForPostType(string $postType): string
    {
        $templates = (array) get_option(self::OPTION_KEY, []);
        $template = isset($templates[$postType]) && is_string($templates[$postType]) && $templates[$postType] !== ''
            ? $templates[$postType]
            : self::DEFAULT_TEMPLATE;

        /**
         * Filter the Markdown template for a specific post type.
         *
         * @param string $template Markdown template string.
         * @param string $postType Post type slug.
         */
        return (string) apply_filters("iz_md_post_type_template_{$postType}", $template, $postType);
    }

    /**
     * Check if a template for a specific post type is overridden via filter hook.
     *
     * @param string $postType Post type slug.
     * @return bool True if overridden via hook, false otherwise.
     */
    public static function isTemplateOverridden(string $postType): bool
    {
        return has_filter("iz_md_post_type_template_{$postType}") !== false;
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

        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_HEADER_TEMPLATE_KEY,
            [
                'type' => 'string',
                'sanitize_callback' => [$this, 'sanitizeHeaderFooterTemplate'],
                'default' => '',
            ]
        );

        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_FOOTER_TEMPLATE_KEY,
            [
                'type' => 'string',
                'sanitize_callback' => [$this, 'sanitizeHeaderFooterTemplate'],
                'default' => '',
            ]
        );
    }

    /**
     * Sanitize header or footer template string.
     *
     * @param mixed $input Value submitted from settings form.
     * @return string Sanitized template string.
     */
    public function sanitizeHeaderFooterTemplate(mixed $input): string
    {
        return is_string($input) ? wp_kses_post(wp_unslash($input)) : '';
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
        $existingTemplates = (array) get_option(self::OPTION_KEY, []);
        $sanitized = [];

        foreach ($validPostTypes as $postType) {
            if (self::isTemplateOverridden($postType)) {
                if (isset($existingTemplates[$postType]) && is_string($existingTemplates[$postType])) {
                    $sanitized[$postType] = wp_kses_post($existingTemplates[$postType]);
                }
                continue;
            }

            if (isset($input[$postType]) && is_string($input[$postType])) {
                $sanitized[$postType] = wp_kses_post(wp_unslash($input[$postType]));
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
            'optionHeaderTemplateKey' => self::OPTION_HEADER_TEMPLATE_KEY,
            'optionFooterTemplateKey' => self::OPTION_FOOTER_TEMPLATE_KEY,
            'postTypes' => $this->getTargetPostTypes(),
            'templates' => (array) get_option(self::OPTION_KEY, []),
            'headerTemplate' => self::getHeaderTemplate(),
            'footerTemplate' => self::getFooterTemplate(),
            'defaultTemplate' => self::DEFAULT_TEMPLATE,
            'supportedPlaceholders' => PlaceholderRenderer::getSupportedPlaceholders(),
            'groupedPlaceholders' => PlaceholderRenderer::getGroupedPlaceholders(),
        ];

        $this->templateRenderer->render('admin/settings/templates-page.php', $data);
    }
}
