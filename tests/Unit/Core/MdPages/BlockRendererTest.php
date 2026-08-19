<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Core\MdPages;

use IZMDPages\Core\Converter\HtmlToMarkdownConverter;
use IZMDPages\Core\MdPages\BlockRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for BlockRenderer.
 */
class BlockRendererTest extends TestCase
{
    private BlockRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new BlockRenderer();

        if (function_exists('remove_all_filters')) {
            remove_all_filters('the_content');
            remove_all_filters('iz_md_placeholder_render_post_content');
            remove_all_filters('iz_md_render_block');
            remove_all_filters('iz_md_render_block_core/quote');
            remove_all_filters('iz_md_render_block_core_quote');
            remove_all_filters('iz_md_render_block_core/heading');
            remove_all_filters('iz_md_render_block_core_heading');
            remove_all_filters('iz_md_render_block_custom/banner');
            remove_all_filters('iz_md_render_block_custom_banner');
            remove_all_filters('iz_md_block_html');
            remove_all_filters('iz_md_render_blocks_content');
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('remove_all_filters')) {
            remove_all_filters('the_content');
            remove_all_filters('iz_md_placeholder_render_post_content');
            remove_all_filters('iz_md_render_block');
            remove_all_filters('iz_md_render_block_core/quote');
            remove_all_filters('iz_md_render_block_core_quote');
            remove_all_filters('iz_md_render_block_core/heading');
            remove_all_filters('iz_md_render_block_core_heading');
            remove_all_filters('iz_md_render_block_custom/banner');
            remove_all_filters('iz_md_render_block_custom_banner');
            remove_all_filters('iz_md_block_html');
            remove_all_filters('iz_md_render_blocks_content');
        }
        parent::tearDown();
    }

    public function testRenderReturnsEmptyStringForEmptyContent(): void
    {
        $post = new \WP_Post(['ID' => 1, 'post_content' => '   ']);
        $this->assertSame('', $this->renderer->render($post));
    }

    public function testRenderRendersClassicContentWhenNoBlocksPresent(): void
    {
        $post = new \WP_Post([
            'ID' => 2,
            'post_content' => '<h2>Classic Title</h2><p>This is classic HTML content with <strong>bold</strong> text.</p>',
        ]);

        $result = $this->renderer->render($post);

        $this->assertStringContainsString('## Classic Title', $result);
        $this->assertStringContainsString('This is classic HTML content with **bold** text.', $result);
    }

    public function testRenderClassicContentAppliesFilters(): void
    {
        add_filter('the_content', function (string $content): string {
            return $content . '<p>Appended by the_content</p>';
        });

        add_filter('iz_md_placeholder_render_post_content', function (string $content, \WP_Post $post): string {
            return $content . '<p>Appended by iz_md_placeholder (' . $post->post_title . ')</p>';
        }, 10, 2);

        $post = new \WP_Post([
            'ID' => 3,
            'post_title' => 'My Post',
            'post_content' => '<p>Initial classic content.</p>',
        ]);

        $result = $this->renderer->render($post);

        $this->assertStringContainsString('Initial classic content.', $result);
        $this->assertStringContainsString('Appended by the_content', $result);
        $this->assertStringContainsString('Appended by iz_md_placeholder (My Post)', $result);
    }

    public function testRenderRendersMultipleBlocksJoinedByDoubleNewlines(): void
    {
        $blockContent = "<!-- wp:core/heading {\"level\":2} -->\n<h2>Section Header</h2>\n<!-- /wp:core/heading -->\n\n<!-- wp:core/paragraph -->\n<p>First paragraph with <a href=\"https://example.com\">link</a>.</p>\n<!-- /wp:core/paragraph -->\n\n<!-- wp:core/paragraph -->\n<p>Second paragraph.</p>\n<!-- /wp:core/paragraph -->";

        $post = new \WP_Post([
            'ID' => 4,
            'post_content' => $blockContent,
        ]);

        $result = $this->renderer->render($post);

        $this->assertStringContainsString('## Section Header', $result);
        $this->assertStringContainsString('First paragraph with [link](https://example.com).', $result);
        $this->assertStringContainsString('Second paragraph.', $result);
    }

    public function testRenderBlockAppliesGenericIzMdRenderBlockFilter(): void
    {
        add_filter('iz_md_render_block', function ($override, array $block, \WP_Post $post) {
            if (($block['blockName'] ?? '') === 'core/quote') {
                return "> Overridden Quote Block for post {$post->ID}";
            }
            return $override;
        }, 10, 3);

        $blockContent = "<!-- wp:core/paragraph -->\n<p>Regular text.</p>\n<!-- /wp:core/paragraph -->\n\n<!-- wp:core/quote -->\n<blockquote><p>Quote text</p></blockquote>\n<!-- /wp:core/quote -->";

        $post = new \WP_Post([
            'ID' => 5,
            'post_content' => $blockContent,
        ]);

        $result = $this->renderer->render($post);

        $this->assertStringContainsString('Regular text.', $result);
        $this->assertStringContainsString('> Overridden Quote Block for post 5', $result);
    }

    public function testRenderBlockAppliesBlockSpecificHookWithExactName(): void
    {
        add_filter('iz_md_render_block_core/quote', function ($override, array $block, \WP_Post $post): string {
            return ">>> Custom Block-Specific Quote for {$post->post_title}";
        }, 10, 3);

        $blockContent = "<!-- wp:core/quote -->\n<blockquote><p>Original quote</p></blockquote>\n<!-- /wp:core/quote -->";

        $post = new \WP_Post([
            'ID' => 6,
            'post_title' => 'Test Post',
            'post_content' => $blockContent,
        ]);

        $result = $this->renderer->render($post);

        $this->assertSame('>>> Custom Block-Specific Quote for Test Post', $result);
    }

    public function testRenderBlockAppliesBlockSpecificHookWithSanitizedName(): void
    {
        add_filter('iz_md_render_block_custom_banner', function ($override, array $block, \WP_Post $post): string {
            return "[!BANNER] Custom Banner Markdown";
        }, 10, 3);

        $blockContent = "<!-- wp:custom/banner -->\n<div class=\"banner\">HTML Banner</div>\n<!-- /wp:custom/banner -->";

        $post = new \WP_Post([
            'ID' => 7,
            'post_content' => $blockContent,
        ]);

        $result = $this->renderer->render($post);

        $this->assertSame('[!BANNER] Custom Banner Markdown', $result);
    }

    public function testRenderBlockAppliesIzMdBlockHtmlFilter(): void
    {
        add_filter('iz_md_block_html', function (string $html, array $block, \WP_Post $post): string {
            return str_replace('Original', 'Filtered HTML', $html);
        }, 10, 3);

        $blockContent = "<!-- wp:core/paragraph -->\n<p>Original paragraph text.</p>\n<!-- /wp:core/paragraph -->";

        $post = new \WP_Post([
            'ID' => 8,
            'post_content' => $blockContent,
        ]);

        $result = $this->renderer->render($post);

        $this->assertStringContainsString('Filtered HTML paragraph text.', $result);
    }

    public function testRenderBlocksAppliesIzMdRenderBlocksContentFilter(): void
    {
        add_filter('iz_md_render_blocks_content', function (string $content, array $blocks, \WP_Post $post): string {
            return "# Table of Contents\n\n" . $content . "\n\n---\n*End of Blocks (count: " . count($blocks) . ")*";
        }, 10, 3);

        $blockContent = "<!-- wp:core/paragraph -->\n<p>Paragraph one.</p>\n<!-- /wp:core/paragraph -->\n\n<!-- wp:core/paragraph -->\n<p>Paragraph two.</p>\n<!-- /wp:core/paragraph -->";

        $post = new \WP_Post([
            'ID' => 9,
            'post_content' => $blockContent,
        ]);

        $result = $this->renderer->render($post);

        $this->assertStringStartsWith("# Table of Contents\n\n", $result);
        $this->assertStringContainsString('Paragraph one.', $result);
        $this->assertStringContainsString('Paragraph two.', $result);
        $this->assertStringEndsWith('*End of Blocks (count: 2)*', $result);
    }

    public function testRenderHandlesEmptyBlocksGracefully(): void
    {
        $blockContent = "<!-- wp:core/paragraph -->\n<p></p>\n<!-- /wp:core/paragraph -->\n\n<!-- wp:core/paragraph -->\n<p>Valid text</p>\n<!-- /wp:core/paragraph -->";

        $post = new \WP_Post([
            'ID' => 10,
            'post_content' => $blockContent,
        ]);

        $result = $this->renderer->render($post);

        $this->assertSame('Valid text', $result);
    }

    public function testConstructorAcceptsCustomConverter(): void
    {
        $mockConverter = $this->createMock(HtmlToMarkdownConverter::class);
        $mockConverter->expects($this->once())
            ->method('convert')
            ->willReturn('Mocked Markdown Output');

        $renderer = new BlockRenderer($mockConverter);

        $post = new \WP_Post([
            'ID' => 11,
            'post_content' => '<p>Some content</p>',
        ]);

        $result = $renderer->render($post);

        $this->assertSame('Mocked Markdown Output', $result);
    }
}
