<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit;

use IZMDPages\Admin\MetaBoxes\MdPageMetaBox;
use IZMDPages\Core\Settings\CoreSettings;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for global functions in inc/functions.php.
 */
class FunctionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $wp_query, $wp_options, $wp_queried_object, $wp_posts_storage, $wp_post_meta;
        $wp_query = new \WP_Query([]);
        $wp_options = [];
        $wp_queried_object = null;
        $wp_posts_storage = [];
        $wp_post_meta = [];
    }

    public function testFunctionsAreDefined(): void
    {
        $this->assertTrue(function_exists('iz_md_is_md_page'));
        $this->assertTrue(function_exists('iz_md_get_md_url'));
    }

    public function testIsMdPageReturnsTrueWhenQueryVarIsSet(): void
    {
        global $wp_query;

        $wp_query->query_vars['md'] = '';
        $this->assertTrue(iz_md_is_md_page());

        $wp_query->query_vars['md'] = '1';
        $this->assertTrue(iz_md_is_md_page());
    }

    public function testIsMdPageReturnsFalseWhenQueryVarIsNotSet(): void
    {
        global $wp_query;

        $wp_query->query_vars = [];
        $this->assertFalse(iz_md_is_md_page());
    }

    public function testGetMdUrlReturnsEmptyWhenNoPostCanBeResolved(): void
    {
        global $wp_queried_object;

        $wp_queried_object = null;
        $this->assertSame('', iz_md_get_md_url());
    }

    public function testGetMdUrlAcceptsPostObjectExplicitly(): void
    {
        global $wp_options;

        $post = new \WP_Post(['ID' => 25, 'post_type' => 'page']);
        $wp_options[CoreSettings::OPTION_ENABLED_POST_TYPES] = ['page'];
        $wp_options[CoreSettings::OPTION_URL_SUFFIX_TYPE] = 'query_var';

        $this->assertSame('https://example.com/?p=25&md=', iz_md_get_md_url($post));
    }

    public function testGetMdUrlAcceptsPostIdExplicitly(): void
    {
        global $wp_posts_storage, $wp_options;

        $post = new \WP_Post(['ID' => 30, 'post_type' => 'post', 'post_name' => 'my-article']);
        $wp_posts_storage[30] = $post;
        $wp_options['permalink_structure'] = '/%postname%/';
        $wp_options[CoreSettings::OPTION_ENABLED_POST_TYPES] = ['post'];
        $wp_options[CoreSettings::OPTION_URL_SUFFIX_TYPE] = 'endpoint';

        $this->assertSame('https://example.com/my-article/md/', iz_md_get_md_url(30));
    }

    public function testGetMdUrlResolvesStaticFrontPageWhenQueriedObjectIsNull(): void
    {
        global $wp_queried_object, $wp_options, $wp_posts_storage;

        $frontPage = new \WP_Post(['ID' => 50, 'post_type' => 'page', 'post_name' => 'home-page']);
        $wp_posts_storage[50] = $frontPage;
        $wp_queried_object = null;

        $wp_options['permalink_structure'] = '/%postname%/';
        $wp_options['show_on_front'] = 'page';
        $wp_options['page_on_front'] = 50;
        $wp_options[CoreSettings::OPTION_ENABLED_POST_TYPES] = ['page'];
        $wp_options[CoreSettings::OPTION_ENABLE_FRONT_PAGE] = 1;

        $this->assertSame('https://example.com/md/', iz_md_get_md_url());
    }

    public function testGetMdUrlReturnsEmptyWhenFrontPageDisabled(): void
    {
        global $wp_queried_object, $wp_options;

        $post = new \WP_Post(['ID' => 50, 'post_type' => 'page']);
        $wp_queried_object = $post;

        $wp_options['show_on_front'] = 'page';
        $wp_options['page_on_front'] = 50;
        $wp_options[CoreSettings::OPTION_ENABLED_POST_TYPES] = ['page'];
        $wp_options[CoreSettings::OPTION_ENABLE_FRONT_PAGE] = 0;

        $this->assertSame('', iz_md_get_md_url($post));
    }

    public function testGetMdUrlReturnsEmptyWhenPostTypeNotEnabled(): void
    {
        global $wp_options;

        $post = new \WP_Post(['ID' => 60, 'post_type' => 'custom_product']);
        $wp_options[CoreSettings::OPTION_ENABLED_POST_TYPES] = ['post', 'page'];

        $this->assertSame('', iz_md_get_md_url($post));
    }

    public function testGetMdUrlReturnsEmptyWhenPostIsDisabledViaMeta(): void
    {
        global $wp_options, $wp_post_meta;

        $post = new \WP_Post(['ID' => 70, 'post_type' => 'post']);
        $wp_options[CoreSettings::OPTION_ENABLED_POST_TYPES] = ['post'];
        $wp_post_meta[70][MdPageMetaBox::META_KEY_DISABLED] = '1';

        $this->assertSame('', iz_md_get_md_url($post));
    }
}
