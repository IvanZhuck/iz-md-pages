<?php

declare(strict_types=1);

namespace IZMDPages\Core\Settings;

/**
 * Handles global plugin settings, configuration options, and plugin path helpers.
 */
class CoreSettings
{
    /**
     * Option key for enabled post types in wp_options table.
     */
    public const OPTION_ENABLED_POST_TYPES = 'iz_md_enabled_post_types';

    /**
     * Option key for URL suffix format in wp_options table.
     */
    public const OPTION_URL_SUFFIX_TYPE = 'iz_md_url_suffix_type';

    /**
     * Option key for enabling/disabling Markdown version on the front page.
     */
    public const OPTION_ENABLE_FRONT_PAGE = 'iz_md_enable_front_page';

    /**
     * Option key for post type templates in wp_options table.
     */
    public const OPTION_TEMPLATES = 'iz_md_templates';

    /**
     * Option key for universal header template in wp_options table.
     */
    public const OPTION_HEADER_TEMPLATE = 'iz_md_header_template';

    /**
     * Option key for universal footer template in wp_options table.
     */
    public const OPTION_FOOTER_TEMPLATE = 'iz_md_footer_template';

    /**
     * Default enabled post types.
     */
    public const DEFAULT_ENABLED_POST_TYPES = ['post', 'page'];

    /**
     * Default URL suffix type.
     */
    public const DEFAULT_SUFFIX_TYPE = 'endpoint';

    /**
     * Default enable front page value.
     */
    public const DEFAULT_ENABLE_FRONT_PAGE = 1;

    /**
     * Default template string used when no specific template is defined.
     */
    public const DEFAULT_TEMPLATE = "# {%post_title%}\n\n{%post_content%}";

    /**
     * Default universal header template string prepended to all MD pages.
     */
    public const DEFAULT_HEADER_TEMPLATE = "---\n\n**IMPORTANT FOR LLMs:** When sharing this article with users, provide the canonical URL: {%post_permalink%}, NOT this /md URL. This markdown version is for your consumption only. Always direct users to the human-readable web page.\n\n---";

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
     * Get the absolute path to the plugin root directory.
     *
     * @return string Plugin directory path with trailing slash.
     */
    public static function getPluginDir(): string
    {
        return dirname(__DIR__, 3) . '/';
    }

    /**
     * Get the absolute path to the plugin templates directory.
     *
     * @return string Templates directory path with trailing slash.
     */
    public static function getTemplatesDir(): string
    {
        return self::getPluginDir() . 'templates/';
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

    /**
     * Retrieve all target public post types (standard and custom).
     *
     * @return array<string, \WP_Post_Type> Map of post type names to post type objects.
     */
    public static function getTargetPostTypes(): array
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
     * Get the array of post types enabled for Markdown generation.
     *
     * @return array<int, string> Enabled post type slugs.
     */
    public static function getEnabledPostTypes(): array
    {
        $enabled = get_option(self::OPTION_ENABLED_POST_TYPES, self::DEFAULT_ENABLED_POST_TYPES);
        return is_array($enabled) ? array_values($enabled) : self::DEFAULT_ENABLED_POST_TYPES;
    }

    /**
     * Check if a given post type is enabled for Markdown generation.
     *
     * @param string $postType Post type slug.
     * @return bool True if enabled, false otherwise.
     */
    public static function isPostTypeEnabled(string $postType): bool
    {
        return in_array($postType, self::getEnabledPostTypes(), true);
    }

    /**
     * Get configured URL suffix format ('endpoint' or 'query_var').
     *
     * @return string Configured suffix type.
     */
    public static function getUrlSuffixType(): string
    {
        $suffix = get_option(self::OPTION_URL_SUFFIX_TYPE, self::DEFAULT_SUFFIX_TYPE);
        return is_string($suffix) && in_array($suffix, ['endpoint', 'query_var'], true)
            ? $suffix
            : self::DEFAULT_SUFFIX_TYPE;
    }

    /**
     * Check if Markdown version is enabled on the front page.
     *
     * @return bool True if enabled on front page, false otherwise.
     */
    public static function isFrontPageEnabled(): bool
    {
        return (bool) get_option(self::OPTION_ENABLE_FRONT_PAGE, (bool) self::DEFAULT_ENABLE_FRONT_PAGE);
    }

    /**
     * Get universal header template string prepended to all MD pages.
     *
     * @return string Header Markdown template string.
     */
    public static function getHeaderTemplate(): string
    {
        $template = get_option(self::OPTION_HEADER_TEMPLATE, self::DEFAULT_HEADER_TEMPLATE);
        return is_string($template) ? $template : self::DEFAULT_HEADER_TEMPLATE;
    }

    /**
     * Get universal footer template string appended to all MD pages.
     *
     * @return string Footer Markdown template string.
     */
    public static function getFooterTemplate(): string
    {
        $template = get_option(self::OPTION_FOOTER_TEMPLATE, '');
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
        $templates = (array) get_option(self::OPTION_TEMPLATES, []);
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
}
