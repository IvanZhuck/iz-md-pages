<?php

declare(strict_types=1);

namespace IZMDPages\Admin\MetaBoxes;

use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Admin\Settings\TemplatesSettingsPage;
use IZMDPages\Core\MdPages\MdPagesOutput;
use IZMDPages\Core\Template\TemplateRenderer;

/**
 * Handles the Markdown Page meta box on post edit screens.
 */
class MdPageMetaBox
{
    /**
     * Unique identifier for the meta box.
     */
    public const META_BOX_ID = 'iz_md_page_meta_box';

    /**
     * Nonce action for verifying meta box data.
     */
    public const NONCE_ACTION = 'iz_md_meta_box_action';

    /**
     * Nonce name in the request payload.
     */
    public const NONCE_NAME = 'iz_md_meta_box_nonce';

    /**
     * Post meta key for disabling the Markdown page.
     */
    public const META_KEY_DISABLED = '_iz_md_disabled';

    /**
     * Form field name for disable checkbox.
     */
    public const FIELD_DISABLED = 'iz_md_disabled';

    /**
     * Post meta key for enabling manual Markdown content.
     */
    public const META_KEY_MANUAL_ENABLED = '_iz_md_manual_enabled';

    /**
     * Post meta key for manual Markdown content.
     */
    public const META_KEY_MANUAL_CONTENT = '_iz_md_manual_content';

    /**
     * Form field name for manual mode checkbox.
     */
    public const FIELD_MANUAL_ENABLED = 'iz_md_manual_enabled';

    /**
     * Form field name for manual content textarea.
     */
    public const FIELD_MANUAL_CONTENT = 'iz_md_manual_content';

    /**
     * Template renderer instance.
     */
    private TemplateRenderer $templateRenderer;

    /**
     * MdPageMetaBox constructor.
     *
     * @param TemplateRenderer|null $templateRenderer Template renderer instance.
     */
    public function __construct(?TemplateRenderer $templateRenderer = null)
    {
        $this->templateRenderer = $templateRenderer ?? new TemplateRenderer();
    }

    /**
     * Initialize WordPress hooks for the meta box.
     */
    public function init(): void
    {
        add_action('add_meta_boxes', [$this, 'registerMetaBox']);
        add_action('save_post', [$this, 'saveMetaBoxData'], 10, 2);
    }

    /**
     * Register meta box for enabled post types.
     *
     * @param string $postType Current screen post type.
     */
    public function registerMetaBox(string $postType): void
    {
        $enabledTypes = (array) get_option(SettingsPage::OPTION_KEY, ['post', 'page']);

        if (!in_array($postType, $enabledTypes, true)) {
            return;
        }

        add_meta_box(
            self::META_BOX_ID,
            __('Markdown Page', 'iz-md-pages'),
            [$this, 'renderMetaBox'],
            $postType,
            'normal',
            'default'
        );
    }

    /**
     * Check if a template for a specific post is overridden via filter hook.
     *
     * @param int $postId Post ID.
     * @return bool True if overridden via hook, false otherwise.
     */
    public static function isPostTemplateOverridden(int $postId): bool
    {
        return has_filter("iz_md_post_template_{$postId}") !== false;
    }

    /**
     * Render the meta box content via template renderer.
     *
     * @param \WP_Post $post Current post object.
     * @param array<string, mixed> $args Additional meta box arguments.
     */
    public function renderMetaBox(\WP_Post $post, array $args = []): void
    {
        $isDisabled = (bool) get_post_meta($post->ID, self::META_KEY_DISABLED, true);
        $isTemplateOverridden = self::isPostTemplateOverridden($post->ID);
        $defaultTemplate = TemplatesSettingsPage::getTemplateForPostType($post->post_type);

        if ($isTemplateOverridden) {
            $isManual = true;
            $manualContent = (string) apply_filters("iz_md_post_template_{$post->ID}", $defaultTemplate, $post);
        } else {
            $isManual = (bool) get_post_meta($post->ID, self::META_KEY_MANUAL_ENABLED, true);
            $manualContent = (string) get_post_meta($post->ID, self::META_KEY_MANUAL_CONTENT, true);

            if (!$isManual) {
                $manualContent = $defaultTemplate;
            }
        }

        $isPublished = $post->post_status === 'publish';
        $mdUrl = $isPublished ? MdPagesOutput::getMdUrl($post) : '';

        $data = [
            'post' => $post,
            'nonceAction' => self::NONCE_ACTION,
            'nonceName' => self::NONCE_NAME,
            'fieldDisabled' => self::FIELD_DISABLED,
            'fieldManualEnabled' => self::FIELD_MANUAL_ENABLED,
            'fieldManualContent' => self::FIELD_MANUAL_CONTENT,
            'isDisabled' => $isDisabled,
            'isManual' => $isManual,
            'isTemplateOverridden' => $isTemplateOverridden,
            'manualContent' => $manualContent,
            'defaultTemplate' => $defaultTemplate,
            'isPublished' => $isPublished,
            'mdUrl' => $mdUrl,
        ];

        $this->templateRenderer->render('admin/meta-boxes/md-page-meta-box.php', $data);
    }

    /**
     * Save meta box data when the post is updated.
     *
     * @param int $postId Current post ID.
     * @param \WP_Post $post Current post object.
     */
    public function saveMetaBoxData(int $postId, \WP_Post $post): void
    {
        $nonce = isset($_POST[self::NONCE_NAME]) && is_string($_POST[self::NONCE_NAME])
            ? sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME]))
            : '';

        if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($postId)) {
            return;
        }

        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        if (isset($_POST[self::FIELD_DISABLED])) {
            update_post_meta($postId, self::META_KEY_DISABLED, '1');
        } else {
            delete_post_meta($postId, self::META_KEY_DISABLED);
        }

        if (!self::isPostTemplateOverridden($postId)) {
            if (isset($_POST[self::FIELD_MANUAL_ENABLED])) {
                update_post_meta($postId, self::META_KEY_MANUAL_ENABLED, '1');
                if (isset($_POST[self::FIELD_MANUAL_CONTENT]) && is_string($_POST[self::FIELD_MANUAL_CONTENT])) {
                    update_post_meta($postId, self::META_KEY_MANUAL_CONTENT, wp_kses_post(wp_unslash($_POST[self::FIELD_MANUAL_CONTENT])));
                }
            } else {
                delete_post_meta($postId, self::META_KEY_MANUAL_ENABLED);
                delete_post_meta($postId, self::META_KEY_MANUAL_CONTENT);
            }
        }
    }
}
