<?php

declare(strict_types=1);

namespace IZMDPages\Admin\Settings;

use IZMDPages\Core\Placeholder\PlaceholderRenderer;

/**
 * Handles administration documentation and reference page for IZ MD Pages.
 */
class DocumentationSettingsPage extends Settings
{
    /**
     * Page slug for the documentation settings page.
     */
    public const PAGE_SLUG = 'iz-md-docs';

    /**
     * Register settings via WordPress Settings API.
     */
    public function registerSettings(): void
    {
        // Documentation page has no form settings to register
    }

    /**
     * Add sub-menu item under IZ MD Pages menu.
     */
    public function addSettingsPage(): void
    {
        add_submenu_page(
            self::PARENT_SLUG,
            __('IZ MD Documentation', 'iz-md-pages'),
            __('Documentation', 'iz-md-pages'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Render administrative documentation page HTML template.
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $data = [
            'currentTab' => 'docs',
            'supportedPlaceholders' => PlaceholderRenderer::getSupportedPlaceholders(),
            'groupedPlaceholders' => PlaceholderRenderer::getGroupedPlaceholders(),
        ];

        $this->templateRenderer->render('admin/settings/docs-page.php', $data);
    }
}
