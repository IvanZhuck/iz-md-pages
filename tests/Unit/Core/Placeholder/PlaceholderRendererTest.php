<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Core\Placeholder;

use IZMDPages\Core\Placeholder\PlaceholderRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PlaceholderRenderer.
 */
class PlaceholderRendererTest extends TestCase
{
    private PlaceholderRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new PlaceholderRenderer();

        global $wp_terms_storage, $wp_taxonomies_storage, $wp_post_meta, $wp_comments_storage;
        $wp_terms_storage = [];
        $wp_taxonomies_storage = [];
        $wp_post_meta = [];
        $wp_comments_storage = [];

        if (function_exists('remove_all_filters')) {
            remove_all_filters('iz_md_pages_placeholders');
            remove_all_filters('iz_md_render_post_field');
            remove_all_filters('iz_md_render_author_field');
            remove_all_filters('iz_md_placeholder_render_post_content');
            remove_all_filters('iz_md_placeholder_render_post_excerpt');
            remove_all_filters('iz_md_placeholder_taxonomy_terms');
            remove_all_filters('iz_md_placeholder_comments');
            remove_all_filters('the_content');
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('remove_all_filters')) {
            remove_all_filters('iz_md_pages_placeholders');
            remove_all_filters('iz_md_render_post_field');
            remove_all_filters('iz_md_render_author_field');
            remove_all_filters('iz_md_placeholder_render_post_content');
            remove_all_filters('iz_md_placeholder_render_post_excerpt');
            remove_all_filters('iz_md_placeholder_taxonomy_terms');
            remove_all_filters('iz_md_placeholder_comments');
            remove_all_filters('the_content');
        }
        parent::tearDown();
    }

    public function testRenderReturnsEmptyStringForEmptyTemplate(): void
    {
        $post = new \WP_Post(['ID' => 1, 'post_title' => 'Test']);
        $this->assertSame('', $this->renderer->render('', $post));
    }

    public function testRenderReplacesAllCorePostPlaceholders(): void
    {
        $post = new \WP_Post([
            'ID' => 101,
            'post_title' => 'Sample Article Title',
            'post_content' => '<p>Paragraph with <strong>bold</strong> text.</p>',
            'post_excerpt' => 'A brief article summary.',
            'post_name' => 'sample-article-title',
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_date' => '2026-06-01 10:00:00',
            'post_date_gmt' => '2026-06-01 07:00:00',
            'post_time' => '10:00:00',
            'post_modified' => '2026-06-02 12:00:00',
            'post_modified_gmt' => '2026-06-02 09:00:00',
        ]);

        $post->thumbnail_url = 'https://example.com/images/thumb.png';
        $post->thumbnail_id = 202;

        global $wp_post_meta;
        $wp_post_meta[202]['_wp_attachment_image_alt'] = 'Thumbnail Alt Text';

        $template = "# {%post_title%}\nID: {%post_id%}\nSlug: {%post_slug%}\nName: {%post_name%}\nType: {%post_type%}\nStatus: {%post_status%}\nDate: {%post_date%}\nDate GMT: {%post_date_gmt%}\nTime: {%post_time%}\nModified: {%post_modified%}\nModified GMT: {%post_modified_gmt%}\nPermalink: {%post_permalink%}\nURL: {%post_url%}\nThumb URL: {%post_thumbnail_url%}\nFeatured URL: {%post_featured_image_url%}\nThumb: {%post_thumbnail%}\nFeatured: {%post_featured_image%}\n\n{%post_content%}\n\n{%post_excerpt%}";

        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('# Sample Article Title', $result);
        $this->assertStringContainsString('ID: 101', $result);
        $this->assertStringContainsString('Slug: sample-article-title', $result);
        $this->assertStringContainsString('Name: sample-article-title', $result);
        $this->assertStringContainsString('Type: post', $result);
        $this->assertStringContainsString('Status: publish', $result);
        $this->assertStringContainsString('Date: 2026-06-01 10:00:00', $result);
        $this->assertStringContainsString('Date GMT: 2026-06-01 07:00:00', $result);
        $this->assertStringContainsString('Time: 10:00:00', $result);
        $this->assertStringContainsString('Modified: 2026-06-02 12:00:00', $result);
        $this->assertStringContainsString('Modified GMT: 2026-06-02 09:00:00', $result);
        $this->assertStringContainsString('Permalink: https://example.com/?p=101', $result);
        $this->assertStringContainsString('URL: https://example.com/?p=101', $result);
        $this->assertStringContainsString('Thumb URL: https://example.com/images/thumb.png', $result);
        $this->assertStringContainsString('Featured URL: https://example.com/images/thumb.png', $result);
        $this->assertStringContainsString('![Thumbnail Alt Text](https://example.com/images/thumb.png)', $result);
        $this->assertStringContainsString('Paragraph with **bold** text.', $result);
        $this->assertStringContainsString('A brief article summary.', $result);
    }

    public function testRenderReplacesAllAuthorPlaceholders(): void
    {
        $post = new \WP_Post([
            'ID' => 55,
            'post_author' => 3,
        ]);

        $template = "Name: {%author_name%}\nAuthor: {%post_author%}\nEmail: {%author_email%}\nURL: {%author_url%}\nBio: {%author_bio%}\nFirst: {%author_first_name%}\nLast: {%author_last_name%}\nNick: {%author_nickname%}";

        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Name: John Doe', $result);
        $this->assertStringContainsString('Author: John Doe', $result);
        $this->assertStringContainsString('Email: author@example.com', $result);
        $this->assertStringContainsString('URL: https://example.com/author/3', $result);
        $this->assertStringContainsString('Bio: Author biography', $result);
        $this->assertStringContainsString('First: John', $result);
        $this->assertStringContainsString('Last: Doe', $result);
        $this->assertStringContainsString('Nick: johndoe', $result);
    }

    public function testRenderReplacesTaxonomiesWithDefaultAndCustomSeparators(): void
    {
        global $wp_terms_storage;

        $post = new \WP_Post(['ID' => 77, 'post_type' => 'post']);

        $wp_terms_storage[77]['category'] = [
            (object) ['name' => 'Backend'],
            (object) ['name' => 'Architecture'],
        ];
        $wp_terms_storage[77]['post_tag'] = [
            (object) ['name' => 'php8'],
            (object) ['name' => 'clean-code'],
        ];
        $wp_terms_storage[77]['topic'] = [
            (object) ['name' => 'Design Patterns'],
            (object) ['name' => 'SOLID'],
        ];

        $template = "Cat Default: {%categories%}\nCat Slashes: {%categories: / %}\nTags Default: {%tags%}\nTags Pipes: {%tags: | %}\nTopic Default: {%taxonomy:topic%}\nTopic Newline: {%taxonomy:topic:\n%}";

        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Cat Default: Backend, Architecture', $result);
        $this->assertStringContainsString('Cat Slashes: Backend / Architecture', $result);
        $this->assertStringContainsString('Tags Default: php8, clean-code', $result);
        $this->assertStringContainsString('Tags Pipes: php8 | clean-code', $result);
        $this->assertStringContainsString('Topic Default: Design Patterns, SOLID', $result);
        $this->assertStringContainsString("Topic Newline: Design Patterns\nSOLID", $result);
    }

    public function testRenderHandlesNonExistentOrEmptyTaxonomy(): void
    {
        $post = new \WP_Post(['ID' => 88, 'post_type' => 'post']);

        $template = "Non-existent: [{%taxonomy:unknown_tax%}]\nEmpty terms: [{%tags%}]";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Non-existent: []', $result);
        $this->assertStringContainsString('Empty terms: []', $result);
    }

    public function testGetPlaceholderReplacementsIncludesObjectTaxonomies(): void
    {
        global $wp_taxonomies_storage, $wp_terms_storage;

        $post = new \WP_Post(['ID' => 99, 'post_type' => 'custom_product']);
        $wp_taxonomies_storage['custom_product'] = ['genre'];
        $wp_terms_storage[99]['genre'] = [
            (object) ['name' => 'Fantasy'],
        ];

        $replacements = $this->renderer->getPlaceholderReplacements($post);

        $this->assertArrayHasKey('{%taxonomy:genre%}', $replacements);
        $this->assertSame('Fantasy', $replacements['{%taxonomy:genre%}']);
    }

    public function testGetGroupedPlaceholdersAndGetSupportedPlaceholders(): void
    {
        $grouped = PlaceholderRenderer::getGroupedPlaceholders();

        $this->assertArrayHasKey('Post', $grouped);
        $this->assertArrayHasKey('Author', $grouped);
        $this->assertArrayHasKey('Taxonomies', $grouped);

        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_POST_TITLE, $grouped['Post']);
        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_POST_CONTENT, $grouped['Post']);
        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_AUTHOR_NAME, $grouped['Author']);
        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_CATEGORIES, $grouped['Taxonomies']);

        $supported = PlaceholderRenderer::getSupportedPlaceholders();
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_POST_TITLE, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_POST_PERMALINK, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_AUTHOR_EMAIL, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_TAGS, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_COMMENTS, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_COMMENTS_COUNT, $supported);
    }

    public function testRenderReplacesCommentsAndCommentsCountWhenCommentsExist(): void
    {
        global $wp_comments_storage;

        $post = new \WP_Post(['ID' => 200, 'post_title' => 'Article with Comments']);

        $wp_comments_storage[200] = [
            new \WP_Comment([
                'comment_ID' => 1,
                'comment_post_ID' => 200,
                'comment_author' => 'Alice',
                'comment_date' => '2026-06-01 10:00:00',
                'comment_content' => '<p>First comment with <strong>bold</strong> text.</p>',
            ]),
            new \WP_Comment([
                'comment_ID' => 2,
                'comment_post_ID' => 200,
                'comment_author' => 'Bob',
                'comment_date' => '2026-06-02 11:30:00',
                'comment_content' => '<p>Second comment with a <a href="https://example.com">link</a>.</p>',
            ]),
        ];

        $template = "# Comments ({%comments_count%})\n\n{%comments%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('# Comments (2)', $result);
        $this->assertStringContainsString('### **Alice** *(2026-06-01 10:00:00)*', $result);
        $this->assertStringContainsString('First comment with **bold** text.', $result);
        $this->assertStringContainsString('---', $result);
        $this->assertStringContainsString('### **Bob** *(2026-06-02 11:30:00)*', $result);
        $this->assertStringContainsString('Second comment with a [link](https://example.com).', $result);
    }

    public function testRenderHandlesNoCommentsGracefully(): void
    {
        $post = new \WP_Post(['ID' => 201, 'post_title' => 'Article without Comments']);

        $template = "Count: [{%comments_count%}]\nComments: [{%comments%}]";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Count: [0]', $result);
        $this->assertStringContainsString('Comments: []', $result);
    }

    public function testRenderHandlesAnonymousCommentAuthor(): void
    {
        global $wp_comments_storage;

        $post = new \WP_Post(['ID' => 202, 'post_title' => 'Article with Anonymous Comment']);

        $wp_comments_storage[202] = [
            new \WP_Comment([
                'comment_ID' => 3,
                'comment_post_ID' => 202,
                'comment_author' => '',
                'comment_date' => '2026-06-03 14:00:00',
                'comment_content' => '<p>Anonymous feedback.</p>',
            ]),
        ];

        $template = "{%comments%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('### **Anonymous** *(2026-06-03 14:00:00)*', $result);
        $this->assertStringContainsString('Anonymous feedback.', $result);
    }

    public function testFilterAllowsOverridingCommentsOutput(): void
    {
        global $wp_comments_storage;

        add_filter('iz_md_placeholder_comments', function (string $result, array $comments, \WP_Post $post): string {
            return 'Custom Comments for ' . $post->post_title . ' (count: ' . count($comments) . ')';
        }, 10, 3);

        $post = new \WP_Post(['ID' => 203, 'post_title' => 'Overridden Comments Post']);
        $wp_comments_storage[203] = [
            new \WP_Comment(['comment_ID' => 4, 'comment_author' => 'Charlie']),
        ];

        $template = "{%comments%}";
        $result = $this->renderer->render($template, $post);

        $this->assertSame('Custom Comments for Overridden Comments Post (count: 1)', $result);
    }

    public function testFiltersAllowOverridingValues(): void
    {
        add_filter('iz_md_render_post_field', function ($value, string $field) {
            if ($field === 'post_title') {
                return 'Overridden Title';
            }
            return $value;
        }, 10, 2);

        add_filter('iz_md_render_author_field', function ($value, string $field) {
            if ($field === 'display_name') {
                return 'Overridden Author';
            }
            return $value;
        }, 10, 2);

        add_filter('iz_md_placeholder_render_post_content', function () {
            return '<p>Overridden HTML Content</p>';
        });

        add_filter('iz_md_placeholder_taxonomy_terms', function ($termNames) {
            return array_map('strtoupper', $termNames);
        });

        global $wp_terms_storage;
        $post = new \WP_Post(['ID' => 123, 'post_title' => 'Original', 'post_content' => 'Original Content']);
        $wp_terms_storage[123]['category'] = [(object) ['name' => 'wordpress']];

        $template = "{%post_title%} by {%author_name%}: {%post_content%}\nCategory: {%categories%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Overridden Title by Overridden Author', $result);
        $this->assertStringContainsString('Overridden HTML Content', $result);
        $this->assertStringContainsString('Category: WORDPRESS', $result);
    }
}
