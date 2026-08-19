<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Admin\Assets;

use IZMDPages\Admin\Assets\SettingsAssets;
use IZMDPages\Admin\Settings\SettingsPage;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SettingsAssets.
 */
class SettingsAssetsTest extends TestCase
{
    private SettingsAssets $assets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assets = new SettingsAssets();

        global $wp_actions, $wp_enqueued_styles, $wp_enqueued_scripts, $wp_current_screen, $wp_options;
        $wp_actions = [];
        $wp_enqueued_styles = [];
        $wp_enqueued_scripts = [];
        $wp_current_screen = null;
        $wp_options = [];
        unset($_GET['page']);
    }

    public function testInitRegistersAdminEnqueueScriptsHook(): void
    {
        global $wp_actions;

        $this->assets->init();

        $this->assertArrayHasKey('admin_enqueue_scripts', $wp_actions);
    }

    public function testEnqueueSettingsAssetsEnqueuesStylesAndScriptsOnPluginPage(): void
    {
        global $wp_enqueued_styles, $wp_enqueued_scripts;

        $_GET['page'] = SettingsPage::PAGE_SLUG;

        $this->assets->enqueueSettingsAssets('toplevel_page_' . SettingsPage::PAGE_SLUG);

        $this->assertArrayHasKey('iz-md-settings', $wp_enqueued_styles);
        $this->assertArrayHasKey('iz-md-settings', $wp_enqueued_scripts);
        $this->assertStringContainsString('assets/build/css/settings.css', $wp_enqueued_styles['iz-md-settings']['src']);
        $this->assertStringContainsString('assets/build/js/settings.bundle.js', $wp_enqueued_scripts['iz-md-settings']['src']);
    }

    public function testEnqueueSettingsAssetsDoesNothingOnNonPluginPage(): void
    {
        global $wp_enqueued_styles, $wp_enqueued_scripts;

        $this->assets->enqueueSettingsAssets('index.php');

        $this->assertArrayNotHasKey('iz-md-settings', $wp_enqueued_styles);
        $this->assertArrayNotHasKey('iz-md-settings', $wp_enqueued_scripts);
    }
}
