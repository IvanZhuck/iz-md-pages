<?php

declare(strict_types=1);

/**
 * Admin placeholders reference info block template.
 *
 * @var array<string, array<string, string>> $groupedPlaceholders Grouped placeholder definitions.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="iz-md-info-block is-collapsed" id="iz-md-placeholders-reference" data-default-state="collapsed">
    <div class="iz-md-info-block-header" role="button" tabindex="0" aria-expanded="false">
        <h3>
            <span class="dashicons dashicons-shortcode" aria-hidden="true"></span>
            <?php esc_html_e('Available Template Placeholders', 'iz-md-pages'); ?>
        </h3>
        <button type="button" class="iz-md-info-block-toggle" aria-label="<?php esc_attr_e('Toggle placeholders reference', 'iz-md-pages'); ?>">
            <span class="iz-md-info-block-toggle-text" data-text-expand="<?php esc_attr_e('Expand', 'iz-md-pages'); ?>" data-text-collapse="<?php esc_attr_e('Collapse', 'iz-md-pages'); ?>"><?php esc_html_e('Expand', 'iz-md-pages'); ?></span>
            <span class="dashicons dashicons-arrow-up" aria-hidden="true"></span>
        </button>
    </div>
    <div class="iz-md-info-block-body iz-md-docs">
        <p class="description">
            <?php esc_html_e('You can use the following placeholders inside your Markdown templates. They will be automatically replaced with the corresponding post data when rendering.', 'iz-md-pages'); ?>
        </p>

        <?php if (!empty($groupedPlaceholders)) : ?>
            <div class="iz-md-docs-grid">
                <?php foreach ($groupedPlaceholders as $izMdGroupTitle => $izMdPlaceholders) : ?>
                    <div>
                        <strong><?php echo esc_html($izMdGroupTitle); ?></strong>
                        <ul>
                            <?php foreach ($izMdPlaceholders as $izMdTag => $izMdDescription) : ?>
                                <li>
                                    <code><?php echo esc_html($izMdTag); ?></code> &mdash; <span class="iz-md-docs-desc"><?php echo esc_html($izMdDescription); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="iz-md-docs-notes">
                <p><strong><?php esc_html_e('Taxonomy Custom Separators:', 'iz-md-pages'); ?></strong></p>
                <ul>
                    <li>
                        <?php esc_html_e('By default, taxonomy terms are separated by a comma and space (e.g. ', 'iz-md-pages'); ?><code>{%categories%}</code> &rarr; <em>Term 1, Term 2</em>).
                    </li>
                    <li>
                        <?php esc_html_e('You can specify a custom separator after a colon, for example: ', 'iz-md-pages'); ?>
                        <code>{%categories: | %}</code>, <code>{%tags: / %}</code>, <code>{%taxonomy:product_cat: &bull; %}</code>.
                    </li>
                    <li>
                        <?php esc_html_e('Control characters like newline (', 'iz-md-pages'); ?><code>\\n</code><?php esc_html_e(') and tab (', 'iz-md-pages'); ?><code>\\t</code><?php esc_html_e(') are supported for Markdown lists or multiline output, for example: ', 'iz-md-pages'); ?>
                        <code>{%categories:\\n* %}</code> <?php esc_html_e('or', 'iz-md-pages'); ?> <code>{%tags:\\n\t%}</code>.
                    </li>
                </ul>

                <p><strong><?php esc_html_e('Leading Separator (Prefix Before First Item):', 'iz-md-pages'); ?></strong></p>
                <ul>
                    <li>
                        <?php esc_html_e('To show the separator before the first element (e.g. for complete Markdown lists), append ', 'iz-md-pages'); ?>
                        <code>:before</code> (<?php esc_html_e('or', 'iz-md-pages'); ?> <code>:leading</code> / <code>:prefix</code>):<br>
                        <code>{%categories:\\n* :before%}</code>, <code>{%tags: #:before%}</code>, <code>{%taxonomy:genre:\\n- :leading%}</code>, <code>{%meta:features:\\n* :prefix%}</code>.
                    </li>
                </ul>

                <p><strong><?php esc_html_e('Taxonomy Links to Archive Pages:', 'iz-md-pages'); ?></strong></p>
                <ul>
                    <li>
                        <?php esc_html_e('To output taxonomy terms formatted as Markdown links to their archive pages (e.g. [Term](url)), append ', 'iz-md-pages'); ?>
                        <code>:links</code>):<br>
                        <code>{%categories:links%}</code>, <code>{%tags: | :links%}</code>, <code>{%taxonomy:genre:\\n* :before:links%}</code>.
                    </li>
                </ul>

                <p><strong><?php esc_html_e('Custom Fields (Post Meta):', 'iz-md-pages'); ?></strong></p>
                <ul>
                    <li>
                        <?php esc_html_e('You can output any custom post field using ', 'iz-md-pages'); ?>
                        <code>{%meta:meta_key%}</code> (<?php esc_html_e('aliases:', 'iz-md-pages'); ?> <code>{%post_meta:key%}</code>, <code>{%custom_field:key%}</code>).
                    </li>
                    <li>
                        <?php esc_html_e('If the meta value is an array or object, it is formatted recursively with an optional separator:', 'iz-md-pages'); ?>
                        <code>{%meta:my_list_key:, %}</code>, <code>{%meta:specs:\\n- %}</code>, <code>{%meta:specs:\\n- :before%}</code>.
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
