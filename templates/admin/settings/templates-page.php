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

    <?php $this->render('admin/info/placeholders-reference.php', ['groupedPlaceholders' => $groupedPlaceholders]); ?>

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
