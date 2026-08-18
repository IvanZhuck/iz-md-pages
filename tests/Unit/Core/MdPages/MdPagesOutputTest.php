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

        global $wp_actions, $wp_filter, $wp_rewrite_endpoints, $wp_redirect_calls, $wp_options, $wp_queried_object, $wp_is_singular, $wp_query;
        $wp_actions = [];
        $wp_filter = [];
        $wp_rewrite_endpoints = [];
        $wp_redirect_calls = [];
        $wp_options = [];
        $wp_queried_object = null;
        $wp_is_singular = true;
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
        global $wp_query, $wp_queried_object, $wp_is_singular, $wp_redirect_calls;

        $wp_query = new \WP_Query(['md' => '']);
        $wp_is_singular = false;
        $wp_queried_object = null;

        $this->output->handleTemplateRedirect();

        $this->assertEmpty($wp_redirect_calls);
    }
}
