<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Admin\Settings;

use IZMDPages\Admin\Settings\TemplatesSettingsPage;
use IZMDPages\Core\Template\TemplateRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TemplatesSettingsPage.
 */
class TemplatesSettingsPageTest extends TestCase
{
    private TemplatesSettingsPage $templatesPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->templatesPage = new TemplatesSettingsPage();

        global $wp_actions, $wp_registered_settings, $wp_submenu_pages, $wp_options, $wp_current_user_capabilities;
        $wp_actions = [];
        $wp_registered_settings = [];
        $wp_submenu_pages = [];
        $wp_options = [];
        $wp_current_user_capabilities = ['manage_options' => true];
    }

    protected function tearDown(): void
    {
        if (function_exists('remove_all_filters')) {
            remove_all_filters('iz_md_post_type_template_post');
            remove_all_filters('iz_md_post_type_template_page');
        }
        parent::tearDown();
    }

    public function testConstantsAreProperlyDefined(): void
    {
        $this->assertSame('iz_md_templates', TemplatesSettingsPage::OPTION_KEY);
        $this->assertSame('iz_md_header_template', TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY);
        $this->assertSame('iz_md_footer_template', TemplatesSettingsPage::OPTION_FOOTER_TEMPLATE_KEY);
        $this->assertSame('iz_md_templates_group', TemplatesSettingsPage::SETTINGS_GROUP);
        $this->assertSame('iz-md-templates', TemplatesSettingsPage::PAGE_SLUG);
        $this->assertSame("# {%post_title%}\n\n{%post_content%}", TemplatesSettingsPage::DEFAULT_TEMPLATE);
    }

    public function testGetHeaderAndFooterTemplate(): void
    {
        global $wp_options;

        $this->assertSame(TemplatesSettingsPage::DEFAULT_HEADER_TEMPLATE, TemplatesSettingsPage::getHeaderTemplate());
        $this->assertSame('', TemplatesSettingsPage::getFooterTemplate());

        $wp_options[TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY] = '<!-- HEADER -->';
        $wp_options[TemplatesSettingsPage::OPTION_FOOTER_TEMPLATE_KEY] = '<!-- FOOTER -->';

        $this->assertSame('<!-- HEADER -->', TemplatesSettingsPage::getHeaderTemplate());
        $this->assertSame('<!-- FOOTER -->', TemplatesSettingsPage::getFooterTemplate());
    }

    public function testGetTemplateForPostTypeReturnsDefaultWhenUnset(): void
    {
        $this->assertSame(TemplatesSettingsPage::DEFAULT_TEMPLATE, TemplatesSettingsPage::getTemplateForPostType('post'));
    }

    public function testGetTemplateForPostTypeReturnsConfiguredTemplate(): void
    {
        global $wp_options;

        $wp_options[TemplatesSettingsPage::OPTION_KEY] = [
            'post' => "## Post: {%post_title%}\n\n{%post_content%}",
        ];

        $this->assertSame("## Post: {%post_title%}\n\n{%post_content%}", TemplatesSettingsPage::getTemplateForPostType('post'));
    }

    public function testGetTemplateForPostTypeAppliesFilterHook(): void
    {
        add_filter('iz_md_post_type_template_post', function (string $template, string $postType): string {
            return '### Hook Override for ' . $postType;
        }, 10, 2);

        $this->assertSame('### Hook Override for post', TemplatesSettingsPage::getTemplateForPostType('post'));
    }

    public function testIsTemplateOverriddenDetectsHook(): void
    {
        $this->assertFalse(TemplatesSettingsPage::isTemplateOverridden('page'));

        add_filter('iz_md_post_type_template_page', function (string $template): string {
            return $template;
        });

        $this->assertTrue(TemplatesSettingsPage::isTemplateOverridden('page'));
    }

    public function testRegisterSettingsRegistersAllOptionKeys(): void
    {
        global $wp_registered_settings;

        $this->templatesPage->registerSettings();

        $this->assertArrayHasKey(TemplatesSettingsPage::SETTINGS_GROUP, $wp_registered_settings);
        $group = $wp_registered_settings[TemplatesSettingsPage::SETTINGS_GROUP];

        $this->assertArrayHasKey(TemplatesSettingsPage::OPTION_KEY, $group);
        $this->assertSame('array', $group[TemplatesSettingsPage::OPTION_KEY]['type']);
        $this->assertSame([], $group[TemplatesSettingsPage::OPTION_KEY]['default']);

        $this->assertArrayHasKey(TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY, $group);
        $this->assertSame('string', $group[TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY]['type']);

        $this->assertArrayHasKey(TemplatesSettingsPage::OPTION_FOOTER_TEMPLATE_KEY, $group);
        $this->assertSame('string', $group[TemplatesSettingsPage::OPTION_FOOTER_TEMPLATE_KEY]['type']);
    }

    public function testSanitizeHeaderFooterTemplateUnslashesString(): void
    {
        $this->assertSame("Title with 'quotes'", $this->templatesPage->sanitizeHeaderFooterTemplate("Title with \\'quotes\\'"));
        $this->assertSame('', $this->templatesPage->sanitizeHeaderFooterTemplate(null));
        $this->assertSame('', $this->templatesPage->sanitizeHeaderFooterTemplate(['array']));
    }

    public function testSanitizeTemplatesCleansInputAndPreservesOverriddenTemplates(): void
    {
        global $wp_options;

        $wp_options[TemplatesSettingsPage::OPTION_KEY] = [
            'page' => '# Existing Page Template',
        ];

        add_filter('iz_md_post_type_template_page', function (string $template): string {
            return '# Hook';
        });

        $input = [
            'post' => "## Post with \\'quotes\\'",
            'page' => 'Attempted override from form',
            'invalid_type' => 'Should be ignored',
        ];

        $sanitized = $this->templatesPage->sanitizeTemplates($input);

        $this->assertSame("## Post with 'quotes'", $sanitized['post']);
        // For overridden type 'page', it preserves existing saved template, ignoring input
        $this->assertSame('# Existing Page Template', $sanitized['page']);
        $this->assertArrayNotHasKey('invalid_type', $sanitized);

        $this->assertSame([], $this->templatesPage->sanitizeTemplates('not_an_array'));
    }

    public function testAddSettingsPageRegistersSubmenu(): void
    {
        global $wp_submenu_pages;

        $this->templatesPage->addSettingsPage();

        $this->assertArrayHasKey(TemplatesSettingsPage::PARENT_SLUG, $wp_submenu_pages);
        $this->assertArrayHasKey(TemplatesSettingsPage::PAGE_SLUG, $wp_submenu_pages[TemplatesSettingsPage::PARENT_SLUG]);
        $menuItem = $wp_submenu_pages[TemplatesSettingsPage::PARENT_SLUG][TemplatesSettingsPage::PAGE_SLUG];

        $this->assertSame('IZ MD Templates', $menuItem['page_title']);
        $this->assertSame('Templates', $menuItem['menu_title']);
        $this->assertSame('manage_options', $menuItem['capability']);
    }

    public function testRenderSettingsPagePassesExpectedDataToTemplate(): void
    {
        global $wp_options;

        $wp_options[TemplatesSettingsPage::OPTION_KEY] = ['post' => '# Post'];
        $wp_options[TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY] = 'HEADER';
        $wp_options[TemplatesSettingsPage::OPTION_FOOTER_TEMPLATE_KEY] = 'FOOTER';

        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/settings/templates-page.php',
                $this->callback(function (array $data): bool {
                    return $data['currentTab'] === 'templates'
                        && $data['settingsGroup'] === TemplatesSettingsPage::SETTINGS_GROUP
                        && $data['headerTemplate'] === 'HEADER'
                        && $data['footerTemplate'] === 'FOOTER'
                        && $data['defaultTemplate'] === TemplatesSettingsPage::DEFAULT_TEMPLATE
                        && is_array($data['supportedPlaceholders'])
                        && is_array($data['groupedPlaceholders']);
                })
            );

        $customTemplatesPage = new TemplatesSettingsPage($mockRenderer);
        $customTemplatesPage->renderSettingsPage();
    }

    public function testRenderSettingsPageBailsWhenUserLacksManageOptionsCapability(): void
    {
        global $wp_current_user_capabilities;

        $wp_current_user_capabilities['manage_options'] = false;

        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->never())
            ->method('render');

        $customTemplatesPage = new TemplatesSettingsPage($mockRenderer);
        $customTemplatesPage->renderSettingsPage();
    }
}
