<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Settings;

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
     * Settings group name for WordPress Settings API.
     */
    public const SETTINGS_GROUP = 'iz_md_settings_group';

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
     * Add sub-menu item under WordPress "Settings" menu.
     */
    public function addSettingsPage(): void
    {
        add_options_page(
            __('IZ MD Settings', 'iz-md-pages'),
            __('IZ MD Settings', 'iz-md-pages'),
            'manage_options',
            'iz-md-settings',
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Render administrative settings page HTML.
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $postTypes = $this->getTargetPostTypes();
        $enabledTypes = (array) get_option(self::OPTION_KEY, ['post', 'page']);
        $suffixType = (string) get_option(self::OPTION_SUFFIX_KEY, 'endpoint');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('IZ MD Settings', 'iz-md-pages'); ?></h1>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields(self::SETTINGS_GROUP);
                ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('URL Format for MD Pages', 'iz-md-pages'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php esc_html_e('URL Format for MD Pages', 'iz-md-pages'); ?></span>
                                    </legend>
                                    <label for="iz_md_suffix_endpoint" style="display: block; margin-bottom: 8px;">
                                        <input
                                            type="radio"
                                            name="<?php echo esc_attr(self::OPTION_SUFFIX_KEY); ?>"
                                            id="iz_md_suffix_endpoint"
                                            value="endpoint"
                                            <?php checked($suffixType, 'endpoint'); ?>
                                        />
                                        <strong><?php esc_html_e('Permalink Suffix (/md)', 'iz-md-pages'); ?></strong>
                                        <code>(e.g., /page-name/md)</code>
                                    </label>
                                    <label for="iz_md_suffix_query_var" style="display: block; margin-bottom: 8px;">
                                        <input
                                            type="radio"
                                            name="<?php echo esc_attr(self::OPTION_SUFFIX_KEY); ?>"
                                            id="iz_md_suffix_query_var"
                                            value="query_var"
                                            <?php checked($suffixType, 'query_var'); ?>
                                        />
                                        <strong><?php esc_html_e('GET Parameter (/?md)', 'iz-md-pages'); ?></strong>
                                        <code>(e.g., /page-name/?md)</code>
                                    </label>
                                </fieldset>
                                <p class="description">
                                    <?php esc_html_e('Select your preferred URL format for serving Markdown content.', 'iz-md-pages'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Post Types for MD Pages', 'iz-md-pages'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php esc_html_e('Post Types for MD Pages', 'iz-md-pages'); ?></span>
                                    </legend>
                                    <?php foreach ($postTypes as $postType) : ?>
                                        <label for="iz_md_pt_<?php echo esc_attr($postType->name); ?>" style="display: block; margin-bottom: 8px;">
                                            <input
                                                type="checkbox"
                                                name="<?php echo esc_attr(self::OPTION_KEY); ?>[]"
                                                id="iz_md_pt_<?php echo esc_attr($postType->name); ?>"
                                                value="<?php echo esc_attr($postType->name); ?>"
                                                <?php checked(in_array($postType->name, $enabledTypes, true)); ?>
                                            />
                                            <strong><?php echo esc_html($postType->label); ?></strong>
                                            <code>(<?php echo esc_html($postType->name); ?>)</code>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                                <p class="description">
                                    <?php esc_html_e('Select post types for which Markdown page generation should be enabled.', 'iz-md-pages'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
