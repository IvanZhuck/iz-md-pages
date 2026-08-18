<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Settings;

/**
 * Handles administration settings page for IZ MD Pages.
 */
class SettingsPage extends Settings
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
     * Option key for enabling/disabling Markdown version on the front page.
     */
    public const OPTION_FRONT_PAGE_KEY = 'iz_md_enable_front_page';

    /**
     * Page slug for the general settings page.
     */
    public const PAGE_SLUG = 'iz-md-settings';

    /**
     * Settings group name for WordPress Settings API.
     */
    public const SETTINGS_GROUP = 'iz_md_settings_group';

    /**
     * Register WordPress hooks.
     */
    public function init(): void
    {
        parent::init();
        add_action('update_option_' . self::OPTION_KEY, 'flush_rewrite_rules');
        add_action('update_option_' . self::OPTION_SUFFIX_KEY, 'flush_rewrite_rules');
        add_action('update_option_' . self::OPTION_FRONT_PAGE_KEY, 'flush_rewrite_rules');
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

        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_FRONT_PAGE_KEY,
            [
                'type' => 'boolean',
                'sanitize_callback' => [$this, 'sanitizeFrontPageOption'],
                'default' => 1,
            ]
        );
    }

    /**
     * Sanitize front page enable option.
     *
     * @param mixed $input Value submitted from settings form.
     * @return int 1 if enabled, 0 otherwise.
     */
    public function sanitizeFrontPageOption(mixed $input): int
    {
        return !empty($input) ? 1 : 0;
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
            self::MENU_ICON
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

        $showOnFront = (string) get_option('show_on_front', 'posts');
        $frontPageId = (int) get_option('page_on_front', 0);
        $isStaticFrontPage = ($showOnFront === 'page' && $frontPageId > 0);

        $data = [
            'currentTab' => 'general',
            'postTypes' => $this->getTargetPostTypes(),
            'enabledTypes' => (array) get_option(self::OPTION_KEY, ['post', 'page']),
            'suffixType' => (string) get_option(self::OPTION_SUFFIX_KEY, 'endpoint'),
            'frontPageEnabled' => (bool) get_option(self::OPTION_FRONT_PAGE_KEY, 1),
            'isStaticFrontPage' => $isStaticFrontPage,
            'readingSettingsUrl' => admin_url('options-reading.php'),
            'settingsGroup' => self::SETTINGS_GROUP,
            'optionKey' => self::OPTION_KEY,
            'optionSuffixKey' => self::OPTION_SUFFIX_KEY,
            'optionFrontPageKey' => self::OPTION_FRONT_PAGE_KEY,
        ];

        $this->templateRenderer->render('admin/settings/settings-page.php', $data);
    }
}
