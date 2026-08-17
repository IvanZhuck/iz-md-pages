<?php

declare(strict_types=1);

/**
 * Admin templates settings page template.
 *
 * @var string                                         $currentTab            Current active tab identifier.
 * @var string                                         $settingsGroup         Settings group name.
 * @var string                                         $optionKey             Templates option key.
 * @var array<string, \WP_Post_Type>                   $postTypes             Target public post types.
 * @var array<string, string>                          $templates             Configured templates map.
 * @var string                                         $defaultTemplate       Default Markdown template string.
 * @var array<int, string>                             $supportedPlaceholders Supported placeholder tokens.
 * @var array<string, array<string, string>>           $groupedPlaceholders   Grouped placeholder definitions.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap iz-md-settings-wrap iz-md-templates-wrap">
    <h1><?php echo esc_html__('IZ MD Templates', 'iz-md-pages'); ?></h1>

    <?php $this->render('admin/nav/main-menu.php', ['currentTab' => $currentTab ?? 'templates']); ?>

    <?php settings_errors(); ?>

    <div class="iz-md-placeholders-reference" id="iz-md-placeholders-reference">
        <div class="iz-md-placeholders-header" role="button" tabindex="0" aria-expanded="true">
            <h3><?php esc_html_e('Available Template Placeholders', 'iz-md-pages'); ?></h3>
            <button type="button" class="iz-md-placeholders-toggle" aria-label="<?php esc_attr_e('Toggle placeholders reference', 'iz-md-pages'); ?>">
                <span class="iz-md-placeholders-toggle-text" data-text-expand="<?php esc_attr_e('Expand', 'iz-md-pages'); ?>" data-text-collapse="<?php esc_attr_e('Collapse', 'iz-md-pages'); ?>"><?php esc_html_e('Collapse', 'iz-md-pages'); ?></span>
                <span class="dashicons dashicons-arrow-up" aria-hidden="true"></span>
            </button>
        </div>
        <div class="iz-md-placeholders-body">
            <p class="description">
                <?php esc_html_e('You can use the following placeholders inside your Markdown templates. They will be automatically replaced with the corresponding post data when rendering.', 'iz-md-pages'); ?>
            </p>

            <?php if (!empty($groupedPlaceholders)) : ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 10px;">
                    <?php foreach ($groupedPlaceholders as $groupTitle => $placeholders) : ?>
                        <div>
                            <strong><?php echo esc_html($groupTitle); ?></strong>
                            <ul style="margin: 5px 0; padding-left: 18px; list-style-type: disc;">
                                <?php foreach ($placeholders as $tag => $description) : ?>
                                    <li style="margin-bottom: 4px;">
                                        <code><?php echo esc_html($tag); ?></code> &mdash; <span style="color: #666;"><?php echo esc_html($description); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 15px; padding-top: 12px; border-top: 1px solid #e0e0e0; font-size: 13px; color: #444;">
                    <p style="margin: 0 0 6px 0;"><strong><?php esc_html_e('Taxonomy Custom Separators:', 'iz-md-pages'); ?></strong></p>
                    <ul style="margin: 0; padding-left: 18px; list-style-type: disc; color: #666;">
                        <li style="margin-bottom: 4px;">
                            <?php esc_html_e('By default, taxonomy terms are separated by a comma and space (e.g. ', 'iz-md-pages'); ?><code>{%categories%}</code> &rarr; <em>Term 1, Term 2</em>).
                        </li>
                        <li style="margin-bottom: 4px;">
                            <?php esc_html_e('You can specify a custom separator after a colon, for example: ', 'iz-md-pages'); ?>
                            <code>{%categories: | %}</code>, <code>{%tags: / %}</code>, <code>{%taxonomy:product_cat: &bull; %}</code>.
                        </li>
                        <li style="margin-bottom: 2px;">
                            <?php esc_html_e('Control characters like newline (', 'iz-md-pages'); ?><code>\\n</code><?php esc_html_e(') and tab (', 'iz-md-pages'); ?><code>\\t</code><?php esc_html_e(') are supported for Markdown lists or multiline output, for example: ', 'iz-md-pages'); ?>
                            <code>{%categories:\\n* %}</code> <?php esc_html_e('or', 'iz-md-pages'); ?> <code>{%tags:\\n\\t%}</code>.
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields($settingsGroup); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <?php foreach ($postTypes as $postType) : ?>
                    <?php
                    $templateValue = array_key_exists($postType->name, $templates)
                        ? $templates[$postType->name]
                        : $defaultTemplate;
                    ?>
                    <tr>
                        <th scope="row">
                            <label for="iz_md_template_<?php echo esc_attr($postType->name); ?>">
                                <strong><?php echo esc_html($postType->label); ?></strong>
                                <br />
                                <code>(<?php echo esc_html($postType->name); ?>)</code>
                            </label>
                        </th>
                        <td>
                            <textarea
                                name="<?php echo esc_attr($optionKey); ?>[<?php echo esc_attr($postType->name); ?>]"
                                id="iz_md_template_<?php echo esc_attr($postType->name); ?>"
                                rows="8"
                                class="large-text code"
                                placeholder="<?php echo esc_attr($defaultTemplate); ?>"
                            ><?php echo esc_textarea($templateValue); ?></textarea>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
