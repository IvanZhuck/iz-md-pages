<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Admin\Settings;

use IZMDPages\Admin\Settings\DocumentationSettingsPage;
use IZMDPages\Core\Template\TemplateRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for DocumentationSettingsPage.
 */
class DocumentationSettingsPageTest extends TestCase
{
    private DocumentationSettingsPage $docsPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->docsPage = new DocumentationSettingsPage();

        global $wp_actions, $wp_submenu_pages, $wp_current_user_capabilities;
        $wp_actions = [];
        $wp_submenu_pages = [];
        $wp_current_user_capabilities = ['manage_options' => true];
    }

    public function testConstantsAreProperlyDefined(): void
    {
        $this->assertSame('iz-md-docs', DocumentationSettingsPage::PAGE_SLUG);
    }

    public function testInitRegistersWordPressHooks(): void
    {
        global $wp_actions;

        $this->docsPage->init();

        $this->assertArrayHasKey('admin_menu', $wp_actions);
        $this->assertArrayHasKey('admin_init', $wp_actions);
    }

    public function testRegisterSettingsDoesNothing(): void
    {
        // Should execute without errors or side effects
        $this->docsPage->registerSettings();
        $this->assertTrue(true);
    }

    public function testAddSettingsPageRegistersSubmenu(): void
    {
        global $wp_submenu_pages;

        $this->docsPage->addSettingsPage();

        $this->assertArrayHasKey(DocumentationSettingsPage::PARENT_SLUG, $wp_submenu_pages);
        $this->assertArrayHasKey(DocumentationSettingsPage::PAGE_SLUG, $wp_submenu_pages[DocumentationSettingsPage::PARENT_SLUG]);
        $menuItem = $wp_submenu_pages[DocumentationSettingsPage::PARENT_SLUG][DocumentationSettingsPage::PAGE_SLUG];

        $this->assertSame('IZ MD Documentation', $menuItem['page_title']);
        $this->assertSame('Documentation', $menuItem['menu_title']);
        $this->assertSame('manage_options', $menuItem['capability']);
        $this->assertSame([$this->docsPage, 'renderSettingsPage'], $menuItem['callback']);
    }

    public function testRenderSettingsPagePassesExpectedDataToTemplate(): void
    {
        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/settings/docs-page.php',
                $this->callback(function (array $data): bool {
                    return $data['currentTab'] === 'docs'
                        && is_array($data['supportedPlaceholders'])
                        && is_array($data['groupedPlaceholders']);
                })
            );

        $customDocsPage = new DocumentationSettingsPage($mockRenderer);
        $customDocsPage->renderSettingsPage();
    }

    public function testRenderSettingsPageBailsWhenUserLacksManageOptionsCapability(): void
    {
        global $wp_current_user_capabilities;

        $wp_current_user_capabilities['manage_options'] = false;

        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->never())
            ->method('render');

        $customDocsPage = new DocumentationSettingsPage($mockRenderer);
        $customDocsPage->renderSettingsPage();
    }
}
