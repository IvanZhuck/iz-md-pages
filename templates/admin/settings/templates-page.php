<?php

declare(strict_types=1);

/**
 * Admin templates settings page template.
 *
 * @var string                                         $currentTab            Current active tab identifier.
 * @var string                                         $settingsGroup         Settings group name.
 * @var string                                         $optionKey             Templates option key.
 * @var string                                         $optionHeaderTemplateKey Header template option key.
 * @var string                                         $optionFooterTemplateKey Footer template option key.
 * @var array<string, \WP_Post_Type>                   $postTypes             Target public post types.
 * @var array<string, string>                          $templates             Configured templates map.
 * @var string                                         $headerTemplate        Universal header template.
 * @var string                                         $footerTemplate        Universal footer template.
 * @var string                                         $defaultTemplate       Default Markdown template string.
 * @var array<int, string>                             $supportedPlaceholders Supported placeholder tokens.
 * @var array<string, array<string, string>>           $groupedPlaceholders   Grouped placeholder definitions.
 */

use IZMDPages\Admin\Settings\TemplatesSettingsPage;

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap iz-md-settings-wrap iz-md-templates-wrap">
    <h1><?php echo esc_html__('IZ MD Templates', 'iz-md-pages'); ?></h1>

    <?php $this->render('admin/nav/main-menu.php', ['currentTab' => $currentTab ?? 'templates']); ?>

    <?php settings_errors(); ?>

    <?php $this->render('admin/info/placeholders-reference.php', ['groupedPlaceholders' => $groupedPlaceholders]); ?>
    <?php $this->render('admin/info/markdown-reference.php'); ?>

    <form method="post" action="options.php">
        <?php settings_fields($settingsGroup); ?>

        <h2><?php echo esc_html__('Post Type Templates', 'iz-md-pages'); ?></h2>
        <p class="description">
            <?php echo esc_html__('Configure individual Markdown layout templates for each enabled post type.', 'iz-md-pages'); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <?php foreach ($postTypes as $postType) : ?>
                    <?php
                    $isOverridden = TemplatesSettingsPage::isTemplateOverridden($postType->name);
                    $templateValue = $isOverridden
                        ? TemplatesSettingsPage::getTemplateForPostType($postType->name)
                        : (array_key_exists($postType->name, $templates) ? $templates[$postType->name] : $defaultTemplate);
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
                                <?php disabled($isOverridden, true); ?>
                            ><?php echo esc_textarea($templateValue); ?></textarea>
                            <?php if ($isOverridden) : ?>
                                <p class="description" style="color: #d63638; margin-top: 6px;">
                                    <?php esc_html_e('This template is overridden via filter hook and cannot be edited here.', 'iz-md-pages'); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr style="margin: 30px 0 20px;" />

        <h2><?php echo esc_html__('Universal Header & Footer Templates', 'iz-md-pages'); ?></h2>
        <p class="description">
            <?php echo esc_html__('These templates are automatically prepended and appended to all Markdown pages across your site, regardless of post type, manual edits, or hooks.', 'iz-md-pages'); ?>
        </p>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="<?php echo esc_attr($optionHeaderTemplateKey); ?>">
                            <strong><?php echo esc_html__('Header Template (Prepend)', 'iz-md-pages'); ?></strong>
                        </label>
                    </th>
                    <td>
                        <textarea
                            name="<?php echo esc_attr($optionHeaderTemplateKey); ?>"
                            id="<?php echo esc_attr($optionHeaderTemplateKey); ?>"
                            rows="6"
                            class="large-text code"
                            placeholder="<?php echo esc_attr__('# Enter header markdown (optional)...', 'iz-md-pages'); ?>"
                        ><?php echo esc_textarea($headerTemplate); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('Universal Markdown markup output at the very beginning of every MD page. Supports template placeholders.', 'iz-md-pages'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo esc_attr($optionFooterTemplateKey); ?>">
                            <strong><?php echo esc_html__('Footer Template (Append)', 'iz-md-pages'); ?></strong>
                        </label>
                    </th>
                    <td>
                        <textarea
                            name="<?php echo esc_attr($optionFooterTemplateKey); ?>"
                            id="<?php echo esc_attr($optionFooterTemplateKey); ?>"
                            rows="6"
                            class="large-text code"
                            placeholder="<?php echo esc_attr__('# Enter footer markdown (optional)...', 'iz-md-pages'); ?>"
                        ><?php echo esc_textarea($footerTemplate); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('Universal Markdown markup output at the very end of every MD page. Supports template placeholders.', 'iz-md-pages'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
