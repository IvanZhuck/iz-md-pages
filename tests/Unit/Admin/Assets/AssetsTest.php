<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Admin\Assets;

use IZMDPages\Admin\Assets\Assets;
use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Admin\Settings\TemplatesSettingsPage;
use PHPUnit\Framework\TestCase;

/**
 * Concrete testable implementation of abstract Assets class.
 */
class TestableAssets extends Assets
{
    public function testGetPluginFilePath(): string
    {
        return $this->getPluginFilePath();
    }

    public function testGetBuildDirPath(): string
    {
        return $this->getBuildDirPath();
    }

    public function testEnqueueStyle(string $name, array $deps = []): void
    {
        $this->enqueueStyle($name, $deps);
    }

    public function testEnqueueScript(string $name, array $deps = [], bool $inFooter = true): void
    {
        $this->enqueueScript($name, $deps, $inFooter);
    }

    public function testIsPluginPage(string $hook = ''): bool
    {
        return $this->isPluginPage($hook);
    }
}

/**
 * Unit tests for abstract Assets class.
 */
class AssetsTest extends TestCase
{
    private TestableAssets $assets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assets = new TestableAssets();

        global $wp_enqueued_styles, $wp_enqueued_scripts, $wp_current_screen;
        $wp_enqueued_styles = [];
        $wp_enqueued_scripts = [];
        $wp_current_screen = null;
        unset($_GET['page']);
    }

    public function testGetPluginFilePathReturnsPathToPluginMainFile(): void
    {
        $path = $this->assets->testGetPluginFilePath();

        $this->assertStringEndsWith('iz-md-pages.php', $path);
        $this->assertFileExists($path);
    }

    public function testGetBuildDirPathReturnsAssetsBuild(): void
    {
        $this->assertSame('assets/build', $this->assets->testGetBuildDirPath());
    }

    public function testEnqueueStyleEnqueuesStyleWithCorrectHandleAndUrl(): void
    {
        global $wp_enqueued_styles;

        $this->assets->testEnqueueStyle('settings');

        $this->assertArrayHasKey('iz-md-settings', $wp_enqueued_styles);
        $this->assertStringContainsString('assets/build/css/settings.css', $wp_enqueued_styles['iz-md-settings']['src']);
    }

    public function testEnqueueScriptEnqueuesScriptWithCorrectHandleUrlAndFooterOption(): void
    {
        global $wp_enqueued_scripts;

        $this->assets->testEnqueueScript('settings', ['jquery'], true);

        $this->assertArrayHasKey('iz-md-settings', $wp_enqueued_scripts);
        $this->assertStringContainsString('assets/build/js/settings.bundle.js', $wp_enqueued_scripts['iz-md-settings']['src']);
        $this->assertContains('jquery', $wp_enqueued_scripts['iz-md-settings']['deps']);
        $this->assertTrue($wp_enqueued_scripts['iz-md-settings']['in_footer']);
    }

    public function testIsPluginPageReturnsTrueWhenGetParameterMatchesPluginPages(): void
    {
        $_GET['page'] = SettingsPage::PAGE_SLUG;
        $this->assertTrue($this->assets->testIsPluginPage());

        $_GET['page'] = TemplatesSettingsPage::PAGE_SLUG;
        $this->assertTrue($this->assets->testIsPluginPage());

        $_GET['page'] = 'other_page';
        $this->assertFalse($this->assets->testIsPluginPage());
    }

    public function testIsPluginPageReturnsTrueWhenScreenIdMatchesPluginSlug(): void
    {
        global $wp_current_screen;

        $wp_current_screen = new \WP_Screen('toplevel_page_' . SettingsPage::PAGE_SLUG);
        $this->assertTrue($this->assets->testIsPluginPage());

        $wp_current_screen = new \WP_Screen('iz-md-pages_page_' . TemplatesSettingsPage::PAGE_SLUG);
        $this->assertTrue($this->assets->testIsPluginPage());

        $wp_current_screen = new \WP_Screen('edit-post');
        $this->assertFalse($this->assets->testIsPluginPage());
    }

    public function testIsPluginPageReturnsTrueWhenHookSuffixMatchesPluginSlug(): void
    {
        $this->assertTrue($this->assets->testIsPluginPage('toplevel_page_' . SettingsPage::PAGE_SLUG));
        $this->assertTrue($this->assets->testIsPluginPage('iz-md-pages_page_' . TemplatesSettingsPage::PAGE_SLUG));
        $this->assertFalse($this->assets->testIsPluginPage('index.php'));
    }
}
