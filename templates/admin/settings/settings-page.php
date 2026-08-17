<?php

declare(strict_types=1);

/**
 * Admin settings page template.
 *
 * @var array<string, \WP_Post_Type> $postTypes
 * @var array<int, string>          $enabledTypes
 * @var string                       $suffixType
 * @var string                       $settingsGroup
 * @var string                       $optionKey
 * @var string                       $optionSuffixKey
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap">
    <h1><?php echo esc_html__('IZ MD Settings', 'iz-md-pages'); ?></h1>
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
