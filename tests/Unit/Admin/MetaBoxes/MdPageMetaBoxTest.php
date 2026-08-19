<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Admin\MetaBoxes;

use IZMDPages\Admin\MetaBoxes\MdPageMetaBox;
use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Admin\Settings\TemplatesSettingsPage;
use IZMDPages\Core\Template\TemplateRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MdPageMetaBox.
 */
class MdPageMetaBoxTest extends TestCase
{
    private MdPageMetaBox $metaBox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metaBox = new MdPageMetaBox();

        global $wp_actions, $wp_filter, $wp_meta_boxes, $wp_post_meta, $wp_options, $wp_current_user_capabilities, $wp_post_revisions_storage;
        $wp_actions = [];
        $wp_filter = [];
        $wp_meta_boxes = [];
        $wp_post_meta = [];
        $wp_options = [];
        $wp_current_user_capabilities = ['edit_post' => true];
        $wp_post_revisions_storage = [];
        $_POST = [];
    }

    protected function tearDown(): void
    {
        if (function_exists('remove_all_filters')) {
            remove_all_filters('iz_md_post_template_123');
            remove_all_filters('iz_md_post_template_500');
        }
        $_POST = [];
        parent::tearDown();
    }

    public function testConstantsAreProperlyDefined(): void
    {
        $this->assertSame('iz_md_page_meta_box', MdPageMetaBox::META_BOX_ID);
        $this->assertSame('iz_md_meta_box_action', MdPageMetaBox::NONCE_ACTION);
        $this->assertSame('iz_md_meta_box_nonce', MdPageMetaBox::NONCE_NAME);
        $this->assertSame('_iz_md_disabled', MdPageMetaBox::META_KEY_DISABLED);
        $this->assertSame('iz_md_disabled', MdPageMetaBox::FIELD_DISABLED);
        $this->assertSame('_iz_md_manual_enabled', MdPageMetaBox::META_KEY_MANUAL_ENABLED);
        $this->assertSame('_iz_md_manual_content', MdPageMetaBox::META_KEY_MANUAL_CONTENT);
        $this->assertSame('iz_md_manual_enabled', MdPageMetaBox::FIELD_MANUAL_ENABLED);
        $this->assertSame('iz_md_manual_content', MdPageMetaBox::FIELD_MANUAL_CONTENT);
    }

    public function testInitRegistersWordPressHooks(): void
    {
        global $wp_actions;

        $this->metaBox->init();

        $this->assertArrayHasKey('add_meta_boxes', $wp_actions);
        $this->assertArrayHasKey('save_post', $wp_actions);
    }

    public function testRegisterMetaBoxRegistersBoxForEnabledPostTypes(): void
    {
        global $wp_meta_boxes, $wp_options;

        $wp_options[SettingsPage::OPTION_KEY] = ['post', 'page'];

        $this->metaBox->registerMetaBox('post');

        $this->assertArrayHasKey(MdPageMetaBox::META_BOX_ID, $wp_meta_boxes);
        $this->assertSame('Markdown Page', $wp_meta_boxes[MdPageMetaBox::META_BOX_ID]['title']);
        $this->assertSame('post', $wp_meta_boxes[MdPageMetaBox::META_BOX_ID]['screen']);
        $this->assertSame([$this->metaBox, 'renderMetaBox'], $wp_meta_boxes[MdPageMetaBox::META_BOX_ID]['callback']);
    }

    public function testRegisterMetaBoxBailsOutForDisabledPostTypes(): void
    {
        global $wp_meta_boxes, $wp_options;

        $wp_options[SettingsPage::OPTION_KEY] = ['post', 'page'];

        $this->metaBox->registerMetaBox('custom_event');

        $this->assertArrayNotHasKey(MdPageMetaBox::META_BOX_ID, $wp_meta_boxes);
    }

    public function testIsPostTemplateOverriddenDetectsHookPresence(): void
    {
        $this->assertFalse(MdPageMetaBox::isPostTemplateOverridden(123));

        add_filter('iz_md_post_template_123', function (string $template): string {
            return $template;
        });

        $this->assertTrue(MdPageMetaBox::isPostTemplateOverridden(123));
    }

    public function testRenderMetaBoxPassesDefaultDataToTemplateRenderer(): void
    {
        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/meta-boxes/md-page-meta-box.php',
                $this->callback(function (array $data): bool {
                    return $data['post']->ID === 10
                        && $data['isDisabled'] === false
                        && $data['isManual'] === false
                        && $data['isTemplateOverridden'] === false
                        && $data['isPublished'] === true
                        && $data['mdUrl'] === 'https://example.com/?p=10/md/'
                        && strpos($data['manualContent'], '# {%post_title%}') !== false;
                })
            );

        $customMetaBox = new MdPageMetaBox($mockRenderer);

        $post = new \WP_Post([
            'ID' => 10,
            'post_type' => 'post',
            'post_status' => 'publish',
        ]);

        $customMetaBox->renderMetaBox($post);
    }

    public function testRenderMetaBoxPassesManualAndDisabledMetaState(): void
    {
        global $wp_post_meta;

        $wp_post_meta[20][MdPageMetaBox::META_KEY_DISABLED] = '1';
        $wp_post_meta[20][MdPageMetaBox::META_KEY_MANUAL_ENABLED] = '1';
        $wp_post_meta[20][MdPageMetaBox::META_KEY_MANUAL_CONTENT] = '# Handcrafted Custom Markdown';

        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/meta-boxes/md-page-meta-box.php',
                $this->callback(function (array $data): bool {
                    return $data['isDisabled'] === true
                        && $data['isManual'] === true
                        && $data['manualContent'] === '# Handcrafted Custom Markdown';
                })
            );

        $customMetaBox = new MdPageMetaBox($mockRenderer);

        $post = new \WP_Post([
            'ID' => 20,
            'post_type' => 'post',
            'post_status' => 'publish',
        ]);

        $customMetaBox->renderMetaBox($post);
    }

    public function testRenderMetaBoxPassesOverriddenTemplateWhenFilterIsAttached(): void
    {
        add_filter('iz_md_post_template_30', function (string $template, \WP_Post $post): string {
            return '### Hooked Template Override for #' . $post->ID;
        }, 10, 2);

        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/meta-boxes/md-page-meta-box.php',
                $this->callback(function (array $data): bool {
                    return $data['isTemplateOverridden'] === true
                        && $data['isManual'] === true
                        && $data['manualContent'] === '### Hooked Template Override for #30';
                })
            );

        $customMetaBox = new MdPageMetaBox($mockRenderer);

        $post = new \WP_Post([
            'ID' => 30,
            'post_type' => 'post',
            'post_status' => 'publish',
        ]);

        $customMetaBox->renderMetaBox($post);
    }

    public function testRenderMetaBoxSetsEmptyMdUrlForDraftPost(): void
    {
        $mockRenderer = $this->createMock(TemplateRenderer::class);
        $mockRenderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/meta-boxes/md-page-meta-box.php',
                $this->callback(function (array $data): bool {
                    return $data['isPublished'] === false
                        && $data['mdUrl'] === '';
                })
            );

        $customMetaBox = new MdPageMetaBox($mockRenderer);

        $post = new \WP_Post([
            'ID' => 40,
            'post_type' => 'post',
            'post_status' => 'draft',
        ]);

        $customMetaBox->renderMetaBox($post);
    }

    public function testSaveMetaBoxDataBailsWhenNonceIsMissingOrInvalid(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 50, 'post_type' => 'post']);

        // Missing nonce
        $_POST = [
            MdPageMetaBox::FIELD_DISABLED => '1',
        ];
        $this->metaBox->saveMetaBoxData(50, $post);
        $this->assertEmpty($wp_post_meta[50] ?? []);

        // Invalid nonce
        $_POST = [
            MdPageMetaBox::NONCE_NAME => 'invalid_nonce_value',
            MdPageMetaBox::FIELD_DISABLED => '1',
        ];
        $this->metaBox->saveMetaBoxData(50, $post);
        $this->assertEmpty($wp_post_meta[50] ?? []);
    }

    public function testSaveMetaBoxDataBailsWhenPostIsRevision(): void
    {
        global $wp_post_meta, $wp_post_revisions_storage;

        $wp_post_revisions_storage[60] = 55; // ID 60 is revision of 55

        $_POST = [
            MdPageMetaBox::NONCE_NAME => 'test_nonce_' . MdPageMetaBox::NONCE_ACTION,
            MdPageMetaBox::FIELD_DISABLED => '1',
        ];

        $post = new \WP_Post(['ID' => 60, 'post_type' => 'revision']);
        $this->metaBox->saveMetaBoxData(60, $post);

        $this->assertEmpty($wp_post_meta[60] ?? []);
    }

    public function testSaveMetaBoxDataBailsWhenUserCannotEditPost(): void
    {
        global $wp_post_meta, $wp_current_user_capabilities;

        $wp_current_user_capabilities['edit_post'] = false;

        $_POST = [
            MdPageMetaBox::NONCE_NAME => 'test_nonce_' . MdPageMetaBox::NONCE_ACTION,
            MdPageMetaBox::FIELD_DISABLED => '1',
        ];

        $post = new \WP_Post(['ID' => 70, 'post_type' => 'post']);
        $this->metaBox->saveMetaBoxData(70, $post);

        $this->assertEmpty($wp_post_meta[70] ?? []);
    }

    public function testSaveMetaBoxDataSavesAndDeletesDisabledMeta(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 80, 'post_type' => 'post']);

        // Checkbox checked
        $_POST = [
            MdPageMetaBox::NONCE_NAME => 'test_nonce_' . MdPageMetaBox::NONCE_ACTION,
            MdPageMetaBox::FIELD_DISABLED => '1',
        ];
        $this->metaBox->saveMetaBoxData(80, $post);
        $this->assertSame('1', $wp_post_meta[80][MdPageMetaBox::META_KEY_DISABLED]);

        // Checkbox unchecked
        $_POST = [
            MdPageMetaBox::NONCE_NAME => 'test_nonce_' . MdPageMetaBox::NONCE_ACTION,
        ];
        $this->metaBox->saveMetaBoxData(80, $post);
        $this->assertArrayNotHasKey(MdPageMetaBox::META_KEY_DISABLED, $wp_post_meta[80] ?? []);
    }

    public function testSaveMetaBoxDataSavesManualContentWithUnslashing(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 90, 'post_type' => 'post']);

        $_POST = [
            MdPageMetaBox::NONCE_NAME => 'test_nonce_' . MdPageMetaBox::NONCE_ACTION,
            MdPageMetaBox::FIELD_MANUAL_ENABLED => '1',
            MdPageMetaBox::FIELD_MANUAL_CONTENT => "# Title with \\'quotes\\'\\\\n\\\\nContent.",
        ];

        $this->metaBox->saveMetaBoxData(90, $post);

        $this->assertSame('1', $wp_post_meta[90][MdPageMetaBox::META_KEY_MANUAL_ENABLED]);
        $this->assertSame("# Title with 'quotes'\\n\\nContent.", $wp_post_meta[90][MdPageMetaBox::META_KEY_MANUAL_CONTENT]);
    }

    public function testSaveMetaBoxDataDeletesManualContentWhenManualModeIsDisabled(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 95, 'post_type' => 'post']);

        $wp_post_meta[95][MdPageMetaBox::META_KEY_MANUAL_ENABLED] = '1';
        $wp_post_meta[95][MdPageMetaBox::META_KEY_MANUAL_CONTENT] = '# Old manual content';

        $_POST = [
            MdPageMetaBox::NONCE_NAME => 'test_nonce_' . MdPageMetaBox::NONCE_ACTION,
            // FIELD_MANUAL_ENABLED is not in $_POST
        ];

        $this->metaBox->saveMetaBoxData(95, $post);

        $this->assertArrayNotHasKey(MdPageMetaBox::META_KEY_MANUAL_ENABLED, $wp_post_meta[95] ?? []);
        $this->assertArrayNotHasKey(MdPageMetaBox::META_KEY_MANUAL_CONTENT, $wp_post_meta[95] ?? []);
    }

    public function testSaveMetaBoxDataDoesNotTouchManualMetaWhenTemplateIsOverriddenByHook(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 100, 'post_type' => 'post']);

        $wp_post_meta[100][MdPageMetaBox::META_KEY_MANUAL_ENABLED] = '1';
        $wp_post_meta[100][MdPageMetaBox::META_KEY_MANUAL_CONTENT] = '# Preserved manual content';

        add_filter('iz_md_post_template_100', function (string $template): string {
            return '# Hooked Template';
        });

        // Submit form attempting to disable manual mode
        $_POST = [
            MdPageMetaBox::NONCE_NAME => 'test_nonce_' . MdPageMetaBox::NONCE_ACTION,
        ];

        $this->metaBox->saveMetaBoxData(100, $post);

        // When post template is overridden by hook, saveMetaBoxData skips manual meta modifications
        $this->assertSame('1', $wp_post_meta[100][MdPageMetaBox::META_KEY_MANUAL_ENABLED]);
        $this->assertSame('# Preserved manual content', $wp_post_meta[100][MdPageMetaBox::META_KEY_MANUAL_CONTENT]);
    }
}
