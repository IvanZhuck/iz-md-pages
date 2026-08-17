<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Settings;

use IZMDPages\Core\Template\TemplateRenderer;

/**
 * Handles administration templates settings page for IZ MD Pages.
 */
class TemplatesSettingsPage
{
    /**
     * Page slug for the templates settings page.
     */
    public const PAGE_SLUG = 'iz-md-templates';

    /**
     * Template renderer instance.
     */
    private TemplateRenderer $templateRenderer;

    /**
     * TemplatesSettingsPage constructor.
     *
     * @param TemplateRenderer|null $templateRenderer Template renderer instance.
     */
    public function __construct(?TemplateRenderer $templateRenderer = null)
    {
        $this->templateRenderer = $templateRenderer ?? new TemplateRenderer();
    }

    /**
     * Register WordPress hooks.
     */
    public function init(): void
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
    }

    /**
     * Add sub-menu item under IZ MD Pages menu.
     */
    public function addSettingsPage(): void
    {
        add_submenu_page(
            SettingsPage::PAGE_SLUG,
            __('IZ MD Templates', 'iz-md-pages'),
            __('Templates', 'iz-md-pages'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Render administrative templates settings page HTML template.
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $data = [
            'currentTab' => 'templates',
        ];

        $this->templateRenderer->render('admin/settings/templates-page.php', $data);
    }
}
