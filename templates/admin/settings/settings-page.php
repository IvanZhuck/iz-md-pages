<?php

declare(strict_types=1);

/**
 * Admin settings page template.
 *
 * @var array<string, \WP_Post_Type> $postTypes
 * @var array<int, string>          $enabledTypes
 * @var string                       $suffixType
 * @var bool                         $frontPageEnabled
 * @var bool                         $isStaticFrontPage
 * @var string                       $readingSettingsUrl
 * @var string                       $settingsGroup
 * @var string                       $optionKey
 * @var string                       $optionSuffixKey
 * @var string                       $optionFrontPageKey
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap iz-md-settings-wrap">
    <h1><?php echo esc_html__('IZ MD Settings', 'iz-md-pages'); ?></h1>

    <?php $this->render('admin/nav/main-menu.php', ['currentTab' => $currentTab ?? 'general']); ?>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php settings_fields($settingsGroup); ?>

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
                                    name="<?php echo esc_attr($optionSuffixKey); ?>"
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
                                    name="<?php echo esc_attr($optionSuffixKey); ?>"
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
                    <th scope="row"><?php esc_html_e('Front Page MD Version', 'iz-md-pages'); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php esc_html_e('Front Page MD Version', 'iz-md-pages'); ?></span>
                            </legend>
                            <label for="iz_md_enable_front_page" style="display: block; margin-bottom: 8px;">
                                <input
                                    type="checkbox"
                                    name="<?php echo esc_attr($optionFrontPageKey); ?>"
                                    id="iz_md_enable_front_page"
                                    value="1"
                                    <?php checked($frontPageEnabled); ?>
                                />
                                <strong><?php esc_html_e('Enable Markdown version for the front page', 'iz-md-pages'); ?></strong>
                            </label>
                        </fieldset>
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %s: URL to WordPress reading settings page */
                                esc_html__('The Markdown version of the front page only works with a static front page. See WordPress settings: %s.', 'iz-md-pages'),
                                '<a href="' . esc_url($readingSettingsUrl) . '">' . esc_html__('Settings &rarr; Reading', 'iz-md-pages') . '</a>'
                            );
                            ?>
                        </p>
                        <?php if (!$isStaticFrontPage) : ?>
                            <p class="description" style="color: #d63638; margin-top: 4px;">
                                <?php esc_html_e('Note: Your homepage is currently set to display your latest posts. To use a Markdown front page, set "Your homepage displays" to "A static page" in Reading Settings.', 'iz-md-pages'); ?>
                            </p>
                        <?php endif; ?>
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
                                        name="<?php echo esc_attr($optionKey); ?>[]"
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
