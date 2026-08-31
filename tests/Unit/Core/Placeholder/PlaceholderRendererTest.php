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
            remove_all_filters('iz_md_render_post_meta');
            remove_all_filters('iz_md_render_custom_placeholder');
            remove_all_filters('iz_md_render_custom_placeholder_reading_time');
            remove_all_filters('iz_md_render_custom_placeholder_badge');
            remove_all_filters('iz_md_grouped_placeholders');
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
            remove_all_filters('iz_md_render_post_meta');
            remove_all_filters('iz_md_render_custom_placeholder');
            remove_all_filters('iz_md_render_custom_placeholder_reading_time');
            remove_all_filters('iz_md_render_custom_placeholder_badge');
            remove_all_filters('iz_md_grouped_placeholders');
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
            'post_modified_time' => '12:00:00',
        ]);

        $post->thumbnail_url = 'https://example.com/images/thumb.png';
        $post->thumbnail_id = 202;

        global $wp_post_meta;
        $wp_post_meta[202]['_wp_attachment_image_alt'] = 'Thumbnail Alt Text';

        $template = implode("\n", [
            'Title: {%post_title%}',
            'ID: {%post_id%}',
            'Slug: {%post_slug%}',
            'Name: {%post_name%}',
            'Type: {%post_type%}',
            'Status: {%post_status%}',
            'Date: {%post_date%}',
            'Time: {%post_time%}',
            'DateTime: {%post_date_time%}',
            'DateTime GMT: {%post_date_time_gmt%}',
            'DateTime GMT ISO: {%post_date_time_gmt_iso%}',
            'Modified Date: {%post_modified_date%}',
            'Modified Time: {%post_modified_time%}',
            'Modified DateTime: {%post_modified_date_time%}',
            'Modified DateTime GMT: {%post_modified_date_time_gmt%}',
            'Modified DateTime GMT ISO: {%post_modified_date_time_gmt_iso%}',
            'Modified GMT ISO: {%post_modified_gmt_iso%}',
            'Permalink: {%post_permalink%}',
            'URL: {%post_url%}',
            'Thumb URL: {%post_thumbnail_url%}',
            'Featured URL: {%post_featured_image_url%}',
            'Thumb: {%post_thumbnail%}',
            'Featured: {%post_featured_image%}',
            'Content: {%post_content%}',
            'Excerpt: {%post_excerpt%}',
        ]);

        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Title: Sample Article Title', $result);
        $this->assertStringContainsString('ID: 101', $result);
        $this->assertStringContainsString('Slug: sample-article-title', $result);
        $this->assertStringContainsString('Name: sample-article-title', $result);
        $this->assertStringContainsString('Type: post', $result);
        $this->assertStringContainsString('Status: publish', $result);
        $this->assertStringContainsString('Date: 2026-06-01 10:00:00', $result);
        $this->assertStringContainsString('Time: 10:00:00', $result);
        $this->assertStringContainsString('DateTime: 2026-06-01 10:00:00', $result);
        $this->assertStringContainsString('DateTime GMT: 2026-06-01 07:00:00', $result);
        $this->assertStringContainsString('DateTime GMT ISO: 2026-06-01T07:00:00+00:00', $result);
        $this->assertStringContainsString('Modified Date: 2026-06-02 12:00:00', $result);
        $this->assertStringContainsString('Modified Time: 12:00:00', $result);
        $this->assertStringContainsString('Modified DateTime: 2026-06-02 12:00:00', $result);
        $this->assertStringContainsString('Modified DateTime GMT: 2026-06-02 09:00:00', $result);
        $this->assertStringContainsString('Modified DateTime GMT ISO: 2026-06-02T09:00:00+00:00', $result);
        $this->assertStringContainsString('Permalink: https://example.com/?p=101', $result);
        $this->assertStringContainsString('URL: https://example.com/?p=101', $result);
        $this->assertStringContainsString('Thumb URL: https://example.com/images/thumb.png', $result);
        $this->assertStringContainsString('Featured URL: https://example.com/images/thumb.png', $result);
        $this->assertStringContainsString('Thumb: ![Thumbnail Alt Text](https://example.com/images/thumb.png)', $result);
        $this->assertStringContainsString('Featured: ![Thumbnail Alt Text](https://example.com/images/thumb.png)', $result);
        $this->assertStringContainsString('Content: Paragraph with **bold** text.', $result);
        $this->assertStringContainsString('Excerpt: A brief article summary.', $result);
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

        $template = "Cat Default: {%categories%}\nCat Slashes: {%categories: / %}\nTags Default: {%tags%}\nTags Pipes: {%tags: | %}\nTopic Default: {%taxonomy:topic%}\nTopic Newline: {%taxonomy:topic:\\n%}";

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
        $this->assertArrayHasKey('Comments', $grouped);
        $this->assertArrayHasKey('Custom Fields', $grouped);

        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_POST_TITLE, $grouped['Post']);
        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_POST_CONTENT, $grouped['Post']);
        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_AUTHOR_NAME, $grouped['Author']);
        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_CATEGORIES, $grouped['Taxonomies']);
        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_COMMENTS, $grouped['Comments']);
        $this->assertArrayHasKey(PlaceholderRenderer::PLACEHOLDER_META, $grouped['Custom Fields']);

        $supported = PlaceholderRenderer::getSupportedPlaceholders();
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_POST_TITLE, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_POST_PERMALINK, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_AUTHOR_EMAIL, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_TAGS, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_COMMENTS, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_COMMENTS_COUNT, $supported);
        $this->assertContains(PlaceholderRenderer::PLACEHOLDER_META, $supported);
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

    public function testRenderReplacesScalarPostMeta(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 301, 'post_title' => 'Product Post']);
        $wp_post_meta[301] = [
            'price' => '$99.99',
            '_sku' => 'PROD-001',
            'subtitle' => 'The Best Product Ever',
            'rating' => 5,
        ];

        $template = "Price: {%meta:price%}\nSKU: {%post_meta:_sku%}\nSubtitle: {%custom_field:subtitle%}\nRating: {%cf:rating%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Price: $99.99', $result);
        $this->assertStringContainsString('SKU: PROD-001', $result);
        $this->assertStringContainsString('Subtitle: The Best Product Ever', $result);
        $this->assertStringContainsString('Rating: 5', $result);
    }

    public function testRenderReplacesArrayPostMetaWithDefaultAndCustomSeparators(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 302, 'post_title' => 'Product with Features']);
        $wp_post_meta[302] = [
            'features' => ['Fast', 'Reliable', 'Secure'],
        ];

        $template = "Default: {%meta:features%}\nPipes: {%meta:features: | %}\nNewline: {%meta:features:\\n* %}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Default: Fast, Reliable, Secure', $result);
        $this->assertStringContainsString('Pipes: Fast | Reliable | Secure', $result);
        $this->assertStringContainsString("Newline: Fast\n* Reliable\n* Secure", $result);
    }

    public function testRenderReplacesAssociativeAndNestedPostMetaRecursively(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 303, 'post_title' => 'Complex Meta Post']);
        $wp_post_meta[303] = [
            'dimensions' => [
                'width' => '100px',
                'height' => '200px',
            ],
            'specs' => [
                'cpu' => 'M3 Max',
                'nested' => [
                    'ram' => '64GB',
                    'storage' => '1TB',
                ],
            ],
        ];

        $template = "Dimensions: {%meta:dimensions:, %}\nSpecs: {%meta:specs: | %}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Dimensions: width: 100px, height: 200px', $result);
        $this->assertStringContainsString('Specs: cpu: M3 Max | nested: ram: 64GB | storage: 1TB', $result);
    }

    public function testRenderPostMetaWithLeadingSeparatorModifier(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 304, 'post_title' => 'Post with Lists']);
        $wp_post_meta[304] = [
            'bullets' => ['Item 1', 'Item 2', 'Item 3'],
        ];

        $template = "List Before:\n{%meta:bullets:\\n* :before%}\n\nList Leading:\n{%meta:bullets:\\n- :leading%}\n\nList Prefix:\n{%meta:bullets:\\n> :prefix%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString("List Before:\n\n* Item 1\n* Item 2\n* Item 3", $result);
        $this->assertStringContainsString("List Leading:\n\n- Item 1\n- Item 2\n- Item 3", $result);
        $this->assertStringContainsString("List Prefix:\n\n> Item 1\n> Item 2\n> Item 3", $result);
    }

    public function testRenderCategoriesAndTagsWithLeadingSeparator(): void
    {
        global $wp_terms_storage;

        $post = new \WP_Post(['ID' => 305, 'post_type' => 'post']);
        $wp_terms_storage[305]['category'] = [
            (object) ['name' => 'News'],
            (object) ['name' => 'Updates'],
        ];
        $wp_terms_storage[305]['post_tag'] = [
            (object) ['name' => 'release'],
            (object) ['name' => 'v2'],
        ];

        $template = "Categories List:\n{%categories:\\n* :before%}\n\nTags Hash:\n{%tags: #:leading%}\n\nTags Hash:\n{%tags: -:prefix%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString("Categories List:\n\n* News\n* Updates", $result);
        $this->assertStringContainsString("Tags Hash:\n #release #v2", $result);
        $this->assertStringContainsString("Tags Hash:\n -release -v2", $result);
    }

    public function testRenderHandlesNonExistentOrEmptyPostMeta(): void
    {
        $post = new \WP_Post(['ID' => 306, 'post_title' => 'Empty Meta Post']);

        $template = "Empty: [{%meta:non_existent%}]\nEmpty Leading: [{%meta:non_existent:\\n* :before%}]";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Empty: []', $result);
        $this->assertStringContainsString('Empty Leading: []', $result);
    }

    public function testFilterAllowsOverridingPostMetaOutput(): void
    {
        global $wp_post_meta;

        add_filter('iz_md_render_post_meta', function ($value, string $metaKey, \WP_Post $post) {
            if ($metaKey === 'custom_field') {
                return 'Overridden Meta (' . $post->post_title . ')';
            }
            return $value;
        }, 10, 3);

        $post = new \WP_Post(['ID' => 307, 'post_title' => 'Filter Meta Post']);
        $wp_post_meta[307]['custom_field'] = 'Original Value';

        $template = "{%meta:custom_field%}";
        $result = $this->renderer->render($template, $post);

        $this->assertSame('Overridden Meta (Filter Meta Post)', $result);
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

    public function testRenderEvaluatesGeneralCustomPlaceholderHook(): void
    {
        add_filter('iz_md_render_custom_placeholder', function ($replacement, string $tag, array $args, \WP_Post $post, string $template) {
            if ($tag === 'site_domain') {
                return 'example.org';
            }
            if ($tag === 'qr_code') {
                $size = $args[0] ?? '150x150';
                return "![QR Code](https://api.qrserver.com/v1/?size={$size}&data=" . urlencode($post->post_title) . ")";
            }
            return $replacement;
        }, 10, 5);

        $post = new \WP_Post(['ID' => 401, 'post_title' => 'Custom Hook Article']);

        $template = "Domain: {%site_domain%}\nQR: {%qr_code:300x300%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Domain: example.org', $result);
        $this->assertStringContainsString('QR: ![QR Code](https://api.qrserver.com/v1/?size=300x300&data=Custom+Hook+Article)', $result);
    }

    public function testRenderEvaluatesTagSpecificCustomPlaceholderHook(): void
    {
        add_filter('iz_md_render_custom_placeholder_reading_time', function ($replacement, array $args, \WP_Post $post) {
            $speed = isset($args[0]) ? (int) $args[0] : 200;
            return "5 min read (at {$speed} wpm)";
        }, 10, 3);

        add_filter('iz_md_render_custom_placeholder_badge', function ($replacement, array $args) {
            $type = $args[0] ?? 'info';
            $label = $args[1] ?? 'Notice';
            return "[!badge type={$type} label=\"{$label}\"]";
        }, 10, 2);

        $post = new \WP_Post(['ID' => 402, 'post_title' => 'Tag Hook Article']);

        $template = "Time: {%reading_time:250%}\nBadge: {%badge:warning:Deprecated%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Time: 5 min read (at 250 wpm)', $result);
        $this->assertStringContainsString('Badge: [!badge type=warning label="Deprecated"]', $result);
    }

    public function testRenderPreservesUnhandledCustomPlaceholders(): void
    {
        $post = new \WP_Post(['ID' => 403, 'post_title' => 'Unhandled Post']);

        $template = "Unknown: {%unknown_placeholder%}\nUnknown with args: {%another_tag:arg1:arg2%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Unknown: {%unknown_placeholder%}', $result);
        $this->assertStringContainsString('Unknown with args: {%another_tag:arg1:arg2%}', $result);
    }

    public function testFilterAllowsModifyingGroupedPlaceholders(): void
    {
        add_filter('iz_md_grouped_placeholders', function (array $groups): array {
            $groups['Custom Addon'] = [
                '{%custom_addon_tag%}' => 'Custom addon description',
            ];
            return $groups;
        });

        $grouped = PlaceholderRenderer::getGroupedPlaceholders();
        $this->assertArrayHasKey('Custom Addon', $grouped);
        $this->assertArrayHasKey('{%custom_addon_tag%}', $grouped['Custom Addon']);

        $supported = PlaceholderRenderer::getSupportedPlaceholders();
        $this->assertContains('{%custom_addon_tag%}', $supported);
    }

    public function testRenderFeaturedImageFallsBackToPostTitleWhenAltMetaIsEmpty(): void
    {
        $post = new \WP_Post([
            'ID' => 501,
            'post_title' => 'Article Fallback Alt Title',
        ]);
        $post->thumbnail_url = 'https://example.com/images/no-alt.png';
        $post->thumbnail_id = 601;

        $template = "Image: {%post_featured_image%}\nThumb: {%post_thumbnail%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Image: ![Article Fallback Alt Title](https://example.com/images/no-alt.png)', $result);
        $this->assertStringContainsString('Thumb: ![Article Fallback Alt Title](https://example.com/images/no-alt.png)', $result);
    }

    public function testRenderFeaturedImageReturnsEmptyStringWhenNoThumbnail(): void
    {
        $post = new \WP_Post([
            'ID' => 502,
            'post_title' => 'Post without thumbnail',
        ]);

        $template = "Image: [{%post_featured_image%}]\nThumb: [{%post_thumbnail%}]\nURL: [{%post_thumbnail_url%}]";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Image: []', $result);
        $this->assertStringContainsString('Thumb: []', $result);
        $this->assertStringContainsString('URL: []', $result);
    }

    public function testRenderPostExcerptAppliesFilterAndHandlesEmptyExcerpt(): void
    {
        $postWithoutExcerpt = new \WP_Post([
            'ID' => 503,
            'post_title' => 'Empty Excerpt Post',
            'post_excerpt' => '',
        ]);

        $this->assertSame('Excerpt: []', $this->renderer->render('Excerpt: [{%post_excerpt%}]', $postWithoutExcerpt));

        add_filter('iz_md_placeholder_render_post_excerpt', function (string $excerpt, \WP_Post $post): string {
            return '<p>Custom filtered excerpt for ' . $post->post_title . '</p>';
        }, 10, 2);

        $postWithFilter = new \WP_Post([
            'ID' => 504,
            'post_title' => 'Filtered Excerpt Post',
            'post_excerpt' => 'Raw excerpt',
        ]);

        $result = $this->renderer->render('Excerpt: {%post_excerpt%}', $postWithFilter);
        $this->assertStringContainsString('Excerpt: Custom filtered excerpt for Filtered Excerpt Post', $result);
    }

    public function testFilterAllowsModifyingPredefinedPlaceholdersMap(): void
    {
        add_filter('iz_md_pages_placeholders', function (array $placeholders, \WP_Post $post, string $template): array {
            $placeholders['{%custom_static_token%}'] = 'Injected Static Value';
            $placeholders['{%post_title%}'] = strtoupper($placeholders['{%post_title%}']);
            return $placeholders;
        }, 10, 3);

        $post = new \WP_Post([
            'ID' => 505,
            'post_title' => 'Sample uppercase title',
        ]);

        $template = "Title: {%post_title%}\nCustom: {%custom_static_token%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Title: SAMPLE UPPERCASE TITLE', $result);
        $this->assertStringContainsString('Custom: Injected Static Value', $result);
    }

    public function testRenderCategoriesAndTagsWithDirectLeadingModifierWithoutCustomSeparator(): void
    {
        global $wp_terms_storage, $wp_post_meta;

        $post = new \WP_Post(['ID' => 506, 'post_type' => 'post']);
        $wp_terms_storage[506]['category'] = [
            (object) ['name' => 'Tech'],
            (object) ['name' => 'AI'],
        ];
        $wp_terms_storage[506]['post_tag'] = [
            (object) ['name' => 'php'],
            (object) ['name' => 'testing'],
        ];
        $wp_post_meta[506] = [
            'features' => ['Fast', 'Reliable'],
        ];

        $template = "Cat: [{%categories:leading%}]\nTags: [{%tags:before%}]\nMeta: [{%meta:features:prefix%}]";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Cat: [, Tech, AI]', $result);
        $this->assertStringContainsString('Tags: [, php, testing]', $result);
        $this->assertStringContainsString('Meta: [, Fast, Reliable]', $result);
    }

    public function testRenderDynamicPlaceholderAliases(): void
    {
        global $wp_terms_storage;

        $post = new \WP_Post(['ID' => 507, 'post_type' => 'post']);
        $wp_terms_storage[507]['category'] = [
            (object) ['name' => 'General'],
        ];
        $wp_terms_storage[507]['post_tag'] = [
            (object) ['name' => 'news'],
        ];
        $wp_terms_storage[507]['series'] = [
            (object) ['name' => 'Season 1'],
        ];

        $template = "Cat: {%category%}\nTag1: {%tag%}\nTag2: {%post_tags%}\nTag3: {%post_tag%}\nTax1: {%tax_series%}\nTax2: {%taxonomy_series%}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Cat: General', $result);
        $this->assertStringContainsString('Tag1: news', $result);
        $this->assertStringContainsString('Tag2: news', $result);
        $this->assertStringContainsString('Tag3: news', $result);
        $this->assertStringContainsString('Tax1: Season 1', $result);
        $this->assertStringContainsString('Tax2: Season 1', $result);
    }

    public function testRenderPostMetaHandlesObjects(): void
    {
        global $wp_post_meta;

        $post = new \WP_Post(['ID' => 508, 'post_title' => 'Object Meta Post']);
        $wp_post_meta[508]['device_info'] = (object) [
            'brand' => 'Apple',
            'model' => 'MacBook Pro',
        ];

        $template = "Device: {%meta:device_info: | %}";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString('Device: brand: Apple | model: MacBook Pro', $result);
    }

    public function testRenderPostMetaStopsRecursionAtMaxDepth(): void
    {
        global $wp_post_meta;

        // Build deeply nested array (> 10 levels)
        $deep = 'Deep Value';
        for ($i = 0; $i < 15; $i++) {
            $deep = ['level_' . $i => $deep];
        }

        $post = new \WP_Post(['ID' => 509, 'post_title' => 'Deep Meta Post']);
        $wp_post_meta[509]['deep_data'] = $deep;

        $template = "Deep: {%meta:deep_data%}";
        $result = $this->renderer->render($template, $post);

        // Should render without error or stack overflow
        $this->assertIsString($result);
        $this->assertStringContainsString('Deep:', $result);
    }

    public function testParseSeparatorDecodesEscapeSequences(): void
    {
        global $wp_terms_storage;

        $post = new \WP_Post(['ID' => 510, 'post_type' => 'post']);
        $wp_terms_storage[510]['category'] = [
            (object) ['name' => 'First'],
            (object) ['name' => 'Second'],
        ];

        $template = "Tabs: [{%categories:\\t%}]";
        $result = $this->renderer->render($template, $post);

        $this->assertStringContainsString("Tabs: [First\tSecond]", $result);
    }

    public function testConstructorAcceptsCustomConverterAndBlockRenderer(): void
    {
        $mockConverter = $this->createMock(\IZMDPages\Core\Converter\HtmlToMarkdownConverter::class);
        $mockBlockRenderer = $this->createMock(\IZMDPages\Core\MdPages\BlockRenderer::class);

        $mockBlockRenderer->expects($this->once())
            ->method('render')
            ->willReturn('Custom Mocked Block Content');

        $renderer = new PlaceholderRenderer($mockConverter, $mockBlockRenderer);

        $post = new \WP_Post([
            'ID' => 511,
            'post_title' => 'DI Test Post',
            'post_content' => '<p>Some content</p>',
        ]);

        $result = $renderer->render('Content: {%post_content%}', $post);

        $this->assertSame('Content: Custom Mocked Block Content', $result);
    }
}
