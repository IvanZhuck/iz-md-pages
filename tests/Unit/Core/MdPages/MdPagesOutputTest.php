<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Core\MdPages;

use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Core\MdPages\MdPagesOutput;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MdPagesOutput.
 */
class MdPagesOutputTest extends TestCase
{
    private MdPagesOutput $output;

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = new MdPagesOutput();

        global $wp_actions, $wp_filter, $wp_rewrite_endpoints, $wp_redirect_calls, $wp_options, $wp_queried_object, $wp_is_singular, $wp_is_front_page, $wp_is_home, $wp_query;
        $wp_actions = [];
        $wp_filter = [];
        $wp_rewrite_endpoints = [];
        $wp_redirect_calls = [];
        $wp_options = [];
        $wp_queried_object = null;
        $wp_is_singular = true;
        $wp_is_front_page = false;
        $wp_is_home = false;
        $wp_query = new \WP_Query();
    }

    public function testInitRegistersAllRequiredHooks(): void
    {
        global $wp_actions, $wp_filter;

        $this->output->init();

        $this->assertArrayHasKey('init', $wp_actions);
        $this->assertArrayHasKey('template_redirect', $wp_actions);
        $this->assertArrayHasKey('wp_head', $wp_actions);
        $this->assertArrayHasKey('query_vars', $wp_filter);
    }

    public function testAddRewriteEndpointsRegistersMdEndpoint(): void
    {
        global $wp_rewrite_endpoints;

        $this->output->addRewriteEndpoints();

        $this->assertArrayHasKey('md', $wp_rewrite_endpoints);
        $this->assertSame(EP_PERMALINK | EP_PAGES | EP_ROOT, $wp_rewrite_endpoints['md']);
    }

    public function testAddQueryVarsAppendsMdVar(): void
    {
        $existingVars = ['p', 'post_type', 'name'];
        $result = $this->output->addQueryVars($existingVars);

        $this->assertContains('md', $result);
        $this->assertContains('p', $result);
        $this->assertContains('post_type', $result);
        $this->assertContains('name', $result);
    }

    public function testRenderAlternateLinkRendersForSingularPostWithEndpointSuffix(): void
    {
        global $wp_options, $wp_queried_object, $wp_is_singular;

        $post = new \WP_Post(['ID' => 10, 'post_type' => 'post']);
        $wp_queried_object = $post;
        $wp_is_singular = true;
        $wp_options[SettingsPage::OPTION_KEY] = ['post', 'page'];
        $wp_options[SettingsPage::OPTION_SUFFIX_KEY] = 'endpoint';

        ob_start();
        $this->output->renderAlternateLink();
        $output = ob_get_clean();

        $this->assertStringContainsString('<link rel="alternate" type="text/markdown"', $output);
        $this->assertStringContainsString('https://example.com/?p=10/md/', $output);
    }

    public function testRenderAlternateLinkRendersForSingularPostWithQueryVarSuffix(): void
    {
        global $wp_options, $wp_queried_object, $wp_is_singular;

        $post = new \WP_Post(['ID' => 25, 'post_type' => 'page']);
        $wp_queried_object = $post;
        $wp_is_singular = true;
        $wp_options[SettingsPage::OPTION_KEY] = ['page'];
        $wp_options[SettingsPage::OPTION_SUFFIX_KEY] = 'query_var';

        ob_start();
        $this->output->renderAlternateLink();
        $output = ob_get_clean();

        $this->assertStringContainsString('<link rel="alternate" type="text/markdown"', $output);
        $this->assertStringContainsString('https://example.com/?p=25&md', $output);
    }

    public function testRenderAlternateLinkDoesNothingForDisabledPostType(): void
    {
        global $wp_options, $wp_queried_object, $wp_is_singular;

        $post = new \WP_Post(['ID' => 30, 'post_type' => 'custom_type']);
        $wp_queried_object = $post;
        $wp_is_singular = true;
        $wp_options[SettingsPage::OPTION_KEY] = ['post', 'page'];

        ob_start();
        $this->output->renderAlternateLink();
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testRenderAlternateLinkDoesNothingForNonSingularScreens(): void
    {
        global $wp_is_singular;
        $wp_is_singular = false;

        ob_start();
        $this->output->renderAlternateLink();
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testRenderAlternateLinkDoesNothingWhenQueriedObjectIsNotPost(): void
    {
        global $wp_queried_object, $wp_is_singular;
        $wp_queried_object = null;
        $wp_is_singular = true;

        ob_start();
        $this->output->renderAlternateLink();
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testHandleTemplateRedirectBailsOutWhenMdQueryVarIsNotSet(): void
    {
        global $wp_query, $wp_redirect_calls;

        $wp_query = new \WP_Query([]);

        $this->output->handleTemplateRedirect();

        $this->assertEmpty($wp_redirect_calls);
    }

    public function testHandleTemplateRedirectBailsOutWhenNoPostCanBeResolved(): void
    {
        global $wp_query, $wp_queried_object, $wp_is_singular, $wp_is_front_page, $wp_is_home, $wp_redirect_calls;

        $wp_query = new \WP_Query(['md' => '']);
        $wp_is_singular = false;
        $wp_is_front_page = false;
        $wp_is_home = false;
        $wp_queried_object = null;

        $this->output->handleTemplateRedirect();

        $this->assertEmpty($wp_redirect_calls);
    }

    public function testRenderAlternateLinkDoesNotRenderWhenFrontPageIsDisabled(): void
    {
        global $wp_options, $wp_queried_object, $wp_is_singular, $wp_is_front_page;

        $post = new \WP_Post(['ID' => 15, 'post_type' => 'page']);
        $wp_queried_object = $post;
        $wp_is_singular = true;
        $wp_is_front_page = true;
        $wp_options['show_on_front'] = 'page';
        $wp_options['page_on_front'] = 15;
        $wp_options[SettingsPage::OPTION_KEY] = ['page'];
        $wp_options[SettingsPage::OPTION_FRONT_PAGE_KEY] = 0;

        ob_start();
        $this->output->renderAlternateLink();
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testGetMdUrlReturnsEndpointAndQueryVarUrls(): void
    {
        global $wp_options;

        $post = new \WP_Post(['ID' => 50, 'post_type' => 'post']);

        $wp_options[SettingsPage::OPTION_SUFFIX_KEY] = 'endpoint';
        $this->assertSame('https://example.com/?p=50/md/', MdPagesOutput::getMdUrl($post));

        $wp_options[SettingsPage::OPTION_SUFFIX_KEY] = 'query_var';
        $this->assertSame('https://example.com/?p=50&md', MdPagesOutput::getMdUrl($post));
    }

    public function testRenderContentUsesDefaultTemplateWhenNoSpecificTemplateConfigured(): void
    {
        $post = new \WP_Post([
            'ID' => 10,
            'post_title' => 'Hello World',
            'post_content' => '<p>Welcome to WordPress.</p>',
            'post_type' => 'post',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('# Hello World', $content);
        $this->assertStringContainsString('Welcome to WordPress.', $content);
    }

    public function testRenderContentUsesPostTypeTemplateFromOptions(): void
    {
        global $wp_options;

        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_KEY] = [
            'book' => "## Book: {%post_title%}\n\nSummary:\n{%post_content%}",
        ];

        $post = new \WP_Post([
            'ID' => 20,
            'post_title' => 'The Great Novel',
            'post_content' => '<p>A masterpiece story.</p>',
            'post_type' => 'book',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('## Book: The Great Novel', $content);
        $this->assertStringContainsString("Summary:\nA masterpiece story.", $content);
    }

    public function testRenderContentAppliesPostTypeTemplateFilterHook(): void
    {
        add_filter('iz_md_post_type_template_event', function (string $template, string $postType): string {
            return "### Event: {%post_title%}\n\nDetails:\n{%post_content%}";
        }, 10, 2);

        $post = new \WP_Post([
            'ID' => 30,
            'post_title' => 'Tech Conference 2026',
            'post_content' => '<p>Join us online.</p>',
            'post_type' => 'event',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('### Event: Tech Conference 2026', $content);
        $this->assertStringContainsString("Details:\nJoin us online.", $content);
    }

    public function testRenderContentAppliesPostIdTemplateFilterHook(): void
    {
        add_filter('iz_md_post_template_42', function (string $template, \WP_Post $post): string {
            return "# Custom Single Title ({%post_id%})\n\n{%post_content%}";
        }, 10, 2);

        $post = new \WP_Post([
            'ID' => 42,
            'post_title' => 'Original Post Title',
            'post_content' => '<p>Article body.</p>',
            'post_type' => 'post',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('# Custom Single Title (42)', $content);
        $this->assertStringContainsString('Article body.', $content);
    }

    public function testRenderContentUsesManualContentWhenManualModeIsEnabled(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post([
            'ID' => 55,
            'post_title' => 'Manual Post',
            'post_content' => '<p>Default editor content.</p>',
            'post_type' => 'post',
        ]);

        $wp_post_meta[55][\IZMDPages\Admin\MetaBoxes\MdPageMetaBox::META_KEY_MANUAL_ENABLED] = '1';
        $wp_post_meta[55][\IZMDPages\Admin\MetaBoxes\MdPageMetaBox::META_KEY_MANUAL_CONTENT] = "# Handcrafted Markdown\n\nCustom manual text here.";

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('# Handcrafted Markdown', $content);
        $this->assertStringContainsString('Custom manual text here.', $content);
        $this->assertStringNotContainsString('Default editor content.', $content);
    }

    public function testRenderContentPrefersPostIdTemplateFilterOverManualContent(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post([
            'ID' => 60,
            'post_title' => 'Hooked Post',
            'post_type' => 'post',
        ]);

        $wp_post_meta[60][\IZMDPages\Admin\MetaBoxes\MdPageMetaBox::META_KEY_MANUAL_ENABLED] = '1';
        $wp_post_meta[60][\IZMDPages\Admin\MetaBoxes\MdPageMetaBox::META_KEY_MANUAL_CONTENT] = "# Manual Content to Ignore";

        add_filter('iz_md_post_template_60', function (string $template, \WP_Post $post): string {
            return "# Hook Overridden Content ({%post_id%})";
        }, 10, 2);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('# Hook Overridden Content (60)', $content);
        $this->assertStringNotContainsString('Manual Content to Ignore', $content);
    }

    public function testRenderContentAppliesPostIdPageContentFilterHook(): void
    {
        add_filter('iz_md_page_content_77', function (string $content, \WP_Post $post): string {
            return $content . "\n\n*Special Post Note for #77*";
        }, 10, 2);

        $post = new \WP_Post([
            'ID' => 77,
            'post_title' => 'Filtered Post',
            'post_content' => '<p>Main content.</p>',
            'post_type' => 'post',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('*Special Post Note for #77*', $content);
    }

    public function testRenderContentAppliesPostTypePageContentFilterHook(): void
    {
        add_filter('iz_md_page_content_docs', function (string $content, \WP_Post $post): string {
            return "--- DOCS HEADER ---\n\n" . $content;
        }, 10, 2);

        $post = new \WP_Post([
            'ID' => 88,
            'post_title' => 'Documentation Guide',
            'post_content' => '<p>How to use docs.</p>',
            'post_type' => 'docs',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('--- DOCS HEADER ---', $content);
    }

    public function testRenderContentAppliesGlobalPageContentFilterHook(): void
    {
        add_filter('iz_md_page_content', function (string $content, \WP_Post $post): string {
            return $content . "\n\n<!-- Global Generator Stamp -->";
        }, 10, 2);

        $post = new \WP_Post([
            'ID' => 99,
            'post_title' => 'Any Post',
            'post_content' => '<p>Content.</p>',
            'post_type' => 'post',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('<!-- Global Generator Stamp -->', $content);
    }

    public function testRenderContentAppliesContentFiltersInOrderOfSpecificity(): void
    {
        $executionOrder = [];

        add_filter('iz_md_page_content_100', function (string $content) use (&$executionOrder): string {
            $executionOrder[] = 'post_id';
            return $content . "\n[1. Post ID]";
        });

        add_filter('iz_md_page_content_post', function (string $content) use (&$executionOrder): string {
            $executionOrder[] = 'post_type';
            return $content . "\n[2. Post Type]";
        });

        add_filter('iz_md_page_content', function (string $content) use (&$executionOrder): string {
            $executionOrder[] = 'global';
            return $content . "\n[3. Global]";
        });

        $post = new \WP_Post([
            'ID' => 100,
            'post_title' => 'Order Test',
            'post_content' => '<p>Body.</p>',
            'post_type' => 'post',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertSame(['post_id', 'post_type', 'global'], $executionOrder);
        $this->assertStringContainsString("[1. Post ID]\n[2. Post Type]\n[3. Global]", $content);
    }

    public function testRenderContentPrependsUniversalHeaderTemplate(): void
    {
        global $wp_options;

        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY] = "> AI Notice: This document is auto-generated for {%post_title%}.";

        $post = new \WP_Post([
            'ID' => 110,
            'post_title' => 'Sample Article',
            'post_content' => '<p>Article text.</p>',
            'post_type' => 'post',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringStartsWith('> AI Notice: This document is auto-generated for Sample Article.', $content);
        $this->assertStringContainsString('# Sample Article', $content);
    }

    public function testRenderContentAppendsUniversalFooterTemplate(): void
    {
        global $wp_options;

        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_FOOTER_TEMPLATE_KEY] = "---\n*Published by author #1 for {%post_title%}*";

        $post = new \WP_Post([
            'ID' => 120,
            'post_title' => 'Footer Test Article',
            'post_content' => '<p>Main content here.</p>',
            'post_type' => 'post',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('# Footer Test Article', $content);
        $this->assertStringEndsWith('*Published by author #1 for Footer Test Article*', trim($content));
    }

    public function testRenderContentCombinesHeaderBodyAndFooter(): void
    {
        global $wp_options;

        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY] = "<!-- HEADER: {%post_title%} -->";
        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_FOOTER_TEMPLATE_KEY] = "<!-- FOOTER: {%post_id%} -->";

        $post = new \WP_Post([
            'ID' => 130,
            'post_title' => 'Full Combo Post',
            'post_content' => '<p>Middle section.</p>',
            'post_type' => 'post',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringStartsWith('<!-- HEADER: Full Combo Post -->', $content);
        $this->assertStringContainsString('# Full Combo Post', $content);
        $this->assertStringContainsString('Middle section.', $content);
        $this->assertStringEndsWith('<!-- FOOTER: 130 -->', trim($content));
    }

    public function testRenderContentDoesNotPassHeaderAndFooterIntoContentFilterHooks(): void
    {
        global $wp_options;

        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY] = "HEADER_STAMP";
        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_FOOTER_TEMPLATE_KEY] = "FOOTER_STAMP";

        $passedToFilter = '';
        add_filter('iz_md_page_content', function (string $content) use (&$passedToFilter): string {
            $passedToFilter = $content;
            return $content;
        });

        $post = new \WP_Post([
            'ID' => 140,
            'post_title' => 'Filter Isolation Post',
            'post_content' => '<p>Original body.</p>',
            'post_type' => 'post',
        ]);

        $this->output->renderContent($post);

        // Filter should only have received the post content, not the header or footer
        $this->assertStringNotContainsString('HEADER_STAMP', $passedToFilter);
        $this->assertStringNotContainsString('FOOTER_STAMP', $passedToFilter);
    }

    public function testRenderAlternateLinkDoesNotRenderWhenPostIsDisabledViaMeta(): void
    {
        global $wp_options, $wp_queried_object, $wp_is_singular, $wp_post_meta;

        $post = new \WP_Post(['ID' => 150, 'post_type' => 'post']);
        $wp_queried_object = $post;
        $wp_is_singular = true;
        $wp_options[SettingsPage::OPTION_KEY] = ['post'];
        $wp_post_meta[150][\IZMDPages\Admin\MetaBoxes\MdPageMetaBox::META_KEY_DISABLED] = '1';

        ob_start();
        $this->output->renderAlternateLink();
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testConstructorAcceptsCustomPlaceholderRenderer(): void
    {
        global $wp_options;
        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY] = '';

        $mockRenderer = $this->createMock(\IZMDPages\Core\Placeholder\PlaceholderRenderer::class);
        $mockRenderer->expects($this->once())
            ->method('render')
            ->willReturn('# Mocked Rendered Content');

        $customOutput = new MdPagesOutput($mockRenderer);

        $post = new \WP_Post([
            'ID' => 160,
            'post_title' => 'Mock Post',
            'post_content' => '<p>Some content</p>',
            'post_type' => 'post',
        ]);

        $result = $customOutput->renderContent($post);
        $this->assertSame('# Mocked Rendered Content', $result);
    }

    public function testRenderContentHandlesEmptyOrWhitespaceHeaderAndFooterTemplates(): void
    {
        global $wp_options;

        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_HEADER_TEMPLATE_KEY] = "   \n\n   ";
        $wp_options[\IZMDPages\Admin\Settings\TemplatesSettingsPage::OPTION_FOOTER_TEMPLATE_KEY] = "   ";

        $post = new \WP_Post([
            'ID' => 170,
            'post_title' => 'Clean Post',
            'post_content' => '<p>Clean body.</p>',
            'post_type' => 'post',
        ]);

        $content = $this->output->renderContent($post);

        $this->assertStringContainsString('# Clean Post', $content);
        $this->assertStringContainsString('Clean body.', $content);
    }
}
