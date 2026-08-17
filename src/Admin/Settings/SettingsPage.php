<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Settings;

use IZMDPages\Core\Template\TemplateRenderer;

/**
 * Handles administration settings page for IZ MD Pages.
 */
class SettingsPage
{
    /**
     * Option key for enabled post types in wp_options table.
     */
    public const OPTION_KEY = 'iz_md_enabled_post_types';

    /**
     * Option key for URL suffix format in wp_options table.
     */
    public const OPTION_SUFFIX_KEY = 'iz_md_url_suffix_type';

    /**
     * Page slug for the general settings page.
     */
    public const PAGE_SLUG = 'iz-md-settings';

    /**
     * Settings group name for WordPress Settings API.
     */
    public const SETTINGS_GROUP = 'iz_md_settings_group';

    /**
     * Template renderer instance.
     */
    private TemplateRenderer $templateRenderer;

    /**
     * SettingsPage constructor.
     *
     * @param TemplateRenderer|null $templateRenderer Template renderer instance.
     */
    public function __construct()
    {
        $this->templateRenderer = new TemplateRenderer();
    }

    /**
     * Register WordPress hooks.
     */
    public function init(): void
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('update_option_' . self::OPTION_KEY, 'flush_rewrite_rules');
        add_action('update_option_' . self::OPTION_SUFFIX_KEY, 'flush_rewrite_rules');
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
                'sanitize_callback' => [$this, 'sanitizeEnabledPostTypes'],
                'default' => ['post', 'page'],
            ]
        );

        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_SUFFIX_KEY,
            [
                'type' => 'string',
                'sanitize_callback' => [$this, 'sanitizeUrlSuffixType'],
                'default' => 'endpoint',
            ]
        );
    }

    /**
     * Sanitize URL suffix type option.
     *
     * @param mixed $input Value submitted from settings form.
     * @return string Sanitized suffix type.
     */
    public function sanitizeUrlSuffixType(mixed $input): string
    {
        $allowed = ['endpoint', 'query_var'];
        return is_string($input) && in_array($input, $allowed, true) ? $input : 'endpoint';
    }

    /**
     * Sanitize array of enabled post types.
     *
     * @param mixed $input Values submitted from settings form.
     * @return array<int, string> Sanitized post type slugs.
     */
    public function sanitizeEnabledPostTypes(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        $validPostTypes = array_keys($this->getTargetPostTypes());

        $sanitized = [];
        foreach ($input as $postType) {
            if (is_string($postType)) {
                $slug = sanitize_key($postType);
                if (in_array($slug, $validPostTypes, true)) {
                    $sanitized[] = $slug;
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
     * Add top-level menu page and sub-menu item in WordPress admin menu.
     */
    public function addSettingsPage(): void
    {
        add_menu_page(
            __('IZ MD Settings', 'iz-md-pages'),
            __('IZ MD Pages', 'iz-md-pages'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderSettingsPage'],
            'dashicons-media-document'
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __('IZ MD Settings', 'iz-md-pages'),
            __('General', 'iz-md-pages'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Render administrative settings page HTML template.
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $data = [
            'currentTab' => 'general',
            'postTypes' => $this->getTargetPostTypes(),
            'enabledTypes' => (array) get_option(self::OPTION_KEY, ['post', 'page']),
            'suffixType' => (string) get_option(self::OPTION_SUFFIX_KEY, 'endpoint'),
            'settingsGroup' => self::SETTINGS_GROUP,
            'optionKey' => self::OPTION_KEY,
            'optionSuffixKey' => self::OPTION_SUFFIX_KEY,
        ];

        $this->templateRenderer->render('admin/settings/settings-page.php', $data);
    }
}
