<?php

declare(strict_types=1);

namespace IZMDPages\Admin\MetaBoxes;

use IZMDPages\Admin\Settings\SettingsPage;
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
     * Render the meta box content via template renderer.
     *
     * @param \WP_Post $post Current post object.
     * @param array<string, mixed> $args Additional meta box arguments.
     */
    public function renderMetaBox(\WP_Post $post, array $args = []): void
    {
        $data = [
            'post' => $post,
            'nonceAction' => self::NONCE_ACTION,
            'nonceName' => self::NONCE_NAME,
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
        if (
            !isset($_POST[self::NONCE_NAME])
            || !is_string($_POST[self::NONCE_NAME])
            || !wp_verify_nonce($_POST[self::NONCE_NAME], self::NONCE_ACTION)
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Logic for saving custom meta fields will be placed here.
    }
}
