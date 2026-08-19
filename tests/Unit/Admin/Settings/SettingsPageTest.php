<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Admin\Settings;

use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Core\Template\TemplateRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SettingsPage.
 */
class SettingsPageTest extends TestCase
{
    private SettingsPage $settingsPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingsPage = new SettingsPage();

        global $wp_actions, $wp_registered_settings, $wp_menu_pages, $wp_submenu_pages, $wp_options, $wp_current_user_capabilities;
        $wp_actions = [];
        $wp_registered_settings = [];
        $wp_menu_pages = [];
        $wp_submenu_pages = [];
        $wp_options = [];
        $wp_current_user_capabilities = ['manage_options' => true];
    }

    public function testConstantsAreProperlyDefined(): void
    {
        $this->assertSame('iz_md_enabled_post_types', SettingsPage::OPTION_KEY);
        $this->assertSame('iz_md_url_suffix_type', SettingsPage::OPTION_SUFFIX_KEY);
        $this->assertSame('iz_md_enable_front_page', SettingsPage::OPTION_FRONT_PAGE_KEY);
        $this->assertSame('iz-md-settings', SettingsPage::PAGE_SLUG);
        $this->assertSame('iz_md_settings_group', SettingsPage::SETTINGS_GROUP);
        $this->assertSame('iz-md-settings', SettingsPage::PARENT_SLUG);
        $this->assertSame('dashicons-media-document', SettingsPage::MENU_ICON);
    }

    public function testInitRegistersWordPressHooksAndFlushTriggers(): void
    {
        global $wp_actions;

        $this->settingsPage->init();

        $this->assertArrayHasKey('admin_menu', $wp_actions);
        $this->assertArrayHasKey('admin_init', $wp_actions);
        $this->assertArrayHasKey('update_option_' . SettingsPage::OPTION_KEY, $wp_actions);
        $this->assertArrayHasKey('update_option_' . SettingsPage::OPTION_SUFFIX_KEY, $wp_actions);
        $this->assertArrayHasKey('update_option_' . SettingsPage::OPTION_FRONT_PAGE_KEY, $wp_actions);
    }

    public function testRegisterSettingsRegistersAllOptionKeys(): void
    {
        global $wp_registered_settings;

        $this->settingsPage->registerSettings();

        $this->assertArrayHasKey(SettingsPage::SETTINGS_GROUP, $wp_registered_settings);
        $group = $wp_registered_settings[SettingsPage::SETTINGS_GROUP];

        $this->assertArrayHasKey(SettingsPage::OPTION_KEY, $group);
        $this->assertSame('array', $group[SettingsPage::OPTION_KEY]['type']);
        $this->assertSame(['post', 'page'], $group[SettingsPage::OPTION_KEY]['default']);

        $this->assertArrayHasKey(SettingsPage::OPTION_SUFFIX_KEY, $group);
        $this->assertSame('string', $group[SettingsPage::OPTION_SUFFIX_KEY]['type']);
        $this->assertSame('endpoint', $group[SettingsPage::OPTION_SUFFIX_KEY]['default']);

        $this->assertArrayHasKey(SettingsPage::OPTION_FRONT_PAGE_KEY, $group);
        $this->assertSame('boolean', $group[SettingsPage::OPTION_FRONT_PAGE_KEY]['type']);
        $this->assertSame(1, $group[SettingsPage::OPTION_FRONT_PAGE_KEY]['default']);
    }

    public function testSanitizeFrontPageOption(): void
    {
        $this->assertSame(1, $this->settingsPage->sanitizeFrontPageOption('1'));
        $this->assertSame(1, $this->settingsPage->sanitizeFrontPageOption(true));
        $this->assertSame(1, $this->settingsPage->sanitizeFrontPageOption(1));
        $this->assertSame(0, $this->settingsPage->sanitizeFrontPageOption('0'));
        $this->assertSame(0, $this->settingsPage->sanitizeFrontPageOption(0));
        $this->assertSame(0, $this->settingsPage->sanitizeFrontPageOption(null));
        $this->assertSame(0, $this->settingsPage->sanitizeFrontPageOption(''));
    }

    public function testSanitizeUrlSuffixType(): void
    {
        $this->assertSame('endpoint', $this->settingsPage->sanitizeUrlSuffixType('endpoint'));
        $this->assertSame('query_var', $this->settingsPage->sanitizeUrlSuffixType('query_var'));
        $this->assertSame('endpoint', $this->settingsPage->sanitizeUrlSuffixType('invalid_value'));
        $this->assertSame('endpoint', $this->settingsPage->sanitizeUrlSuffixType(['array']));
        $this->assertSame('endpoint', $this->settingsPage->sanitizeUrlSuffixType(null));
    }

    public function testSanitizeEnabledPostTypes(): void
    {
        // Valid post types in bootstrap mock: 'post', 'page'
        $input = ['post', 'page', 'invalid_cpt', 'attachment', 123];
        $sanitized = $this->settingsPage->sanitizeEnabledPostTypes($input);

        $this->assertSame(['post', 'page'], $sanitized);
        $this->assertSame([], $this->settingsPage->sanitizeEnabledPostTypes('not_an_array'));
    }

    public function testAddSettingsPageRegistersTopLevelMenuAndSubmenu(): void
    {
        global $wp_menu_pages, $wp_submenu_pages;

        $this->settingsPage->addSettingsPage();

        $this->assertArrayHasKey(SettingsPage::PAGE_SLUG, $wp_menu_pages);
        $this->assertSame('IZ MD Settings', $wp_menu_pages[SettingsPage::PAGE_SLUG]['page_title']);
        $this->assertSame('IZ MD Pages', $wp_menu_pages[SettingsPage::PAGE_SLUG]['menu_title']);
        $this->assertSame('manage_options', $wp_menu_pages[SettingsPage::PAGE_SLUG]['capability']);
        $this->assertSame(SettingsPage::MENU_ICON, $wp_menu_pages[SettingsPage::PAGE_SLUG]['icon_url']);

        $this->assertArrayHasKey(SettingsPage::PAGE_SLUG, $wp_submenu_pages);
        $this->assertArrayHasKey(SettingsPage::PAGE_SLUG, $wp_submenu_pages[SettingsPage::PAGE_SLUG]);
        $this->assertSame('General', $wp_submenu_pages[SettingsPage::PAGE_SLUG][SettingsPage::PAGE_SLUG]['menu_title']);
    }

    public function testRenderSettingsPagePassesExpectedDataToTemplate(): void
    {
        global $wp_options;

        $wp_options['show_on_front'] = 'page';
        $wp_options['page_on_front'] = 42;
        $wp_options[SettingsPage::OPTION_KEY] = ['post'];
        $wp_options[SettingsPage::OPTION_SUFFIX_KEY] = 'query_var';
        $wp_options[SettingsPage::OPTION_FRONT_PAGE_KEY] = 0;

        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/settings/settings-page.php',
                $this->callback(function (array $data): bool {
                    return $data['currentTab'] === 'general'
                        && $data['enabledTypes'] === ['post']
                        && $data['suffixType'] === 'query_var'
                        && $data['frontPageEnabled'] === false
                        && $data['isStaticFrontPage'] === true
                        && $data['settingsGroup'] === SettingsPage::SETTINGS_GROUP
                        && $data['optionKey'] === SettingsPage::OPTION_KEY
                        && $data['optionSuffixKey'] === SettingsPage::OPTION_SUFFIX_KEY
                        && $data['optionFrontPageKey'] === SettingsPage::OPTION_FRONT_PAGE_KEY;
                })
            );

        $customSettingsPage = new SettingsPage($mockRenderer);
        $customSettingsPage->renderSettingsPage();
    }

    public function testRenderSettingsPageBailsWhenUserLacksManageOptionsCapability(): void
    {
        global $wp_current_user_capabilities;

        $wp_current_user_capabilities['manage_options'] = false;

        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->never())
            ->method('render');

        $customSettingsPage = new SettingsPage($mockRenderer);
        $customSettingsPage->renderSettingsPage();
    }
}
