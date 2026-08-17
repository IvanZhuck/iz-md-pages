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
<div class="wrap">
    <h1><?php echo esc_html__('IZ MD Templates', 'iz-md-pages'); ?></h1>

    <?php $this->render('admin/nav/main-menu.php', ['currentTab' => $currentTab ?? 'templates']); ?>

    <?php settings_errors(); ?>

    <div class="iz-md-placeholders-reference" style="margin-top: 15px; margin-bottom: 20px; background: #fff; border: 1px solid #ccd0d4; padding: 12px 18px; border-radius: 4px;">
        <h3 style="margin-top: 0;"><?php esc_html_e('Available Template Placeholders', 'iz-md-pages'); ?></h3>
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
        <?php endif; ?>
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
