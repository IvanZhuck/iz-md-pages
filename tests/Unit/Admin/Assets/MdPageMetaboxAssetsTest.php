<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Admin\Assets;

use IZMDPages\Admin\Assets\MdPageMetaboxAssets;
use IZMDPages\Admin\Settings\SettingsPage;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MdPageMetaboxAssets.
 */
class MdPageMetaboxAssetsTest extends TestCase
{
    private MdPageMetaboxAssets $assets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assets = new MdPageMetaboxAssets();

        global $wp_actions, $wp_enqueued_styles, $wp_enqueued_scripts, $wp_current_screen, $wp_options;
        $wp_actions = [];
        $wp_enqueued_styles = [];
        $wp_enqueued_scripts = [];
        $wp_current_screen = null;
        $wp_options = [];
    }

    public function testInitRegistersAdminEnqueueScriptsHook(): void
    {
        global $wp_actions;

        $this->assets->init();

        $this->assertArrayHasKey('admin_enqueue_scripts', $wp_actions);
    }

    public function testEnqueueMetaBoxAssetsEnqueuesStylesAndScriptsOnMetaBoxScreen(): void
    {
        global $wp_enqueued_styles, $wp_enqueued_scripts, $wp_current_screen;

        $wp_current_screen = new \WP_Screen('post', 'post');

        $this->assets->enqueueMetaBoxAssets('post.php');

        $this->assertArrayHasKey('iz-md-md-page-meta-box', $wp_enqueued_styles);
        $this->assertArrayHasKey('iz-md-md-page-meta-box', $wp_enqueued_scripts);
        $this->assertStringContainsString('assets/build/css/md-page-meta-box.css', $wp_enqueued_styles['iz-md-md-page-meta-box']['src']);
        $this->assertStringContainsString('assets/build/js/md-page-meta-box.bundle.js', $wp_enqueued_scripts['iz-md-md-page-meta-box']['src']);
    }

    public function testEnqueueMetaBoxAssetsDoesNothingOnNonMetaBoxScreen(): void
    {
        global $wp_enqueued_styles, $wp_enqueued_scripts, $wp_current_screen;

        // Disabled post type
        $wp_current_screen = new \WP_Screen('custom_type', 'custom_type');
        $this->assets->enqueueMetaBoxAssets('post.php');

        $this->assertArrayNotHasKey('iz-md-md-page-meta-box', $wp_enqueued_styles);
        $this->assertArrayNotHasKey('iz-md-md-page-meta-box', $wp_enqueued_scripts);

        // Non-post hook
        $wp_current_screen = new \WP_Screen('post', 'post');
        $this->assets->enqueueMetaBoxAssets('options-general.php');

        $this->assertArrayNotHasKey('iz-md-md-page-meta-box', $wp_enqueued_styles);
        $this->assertArrayNotHasKey('iz-md-md-page-meta-box', $wp_enqueued_scripts);
    }
}
