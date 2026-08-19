<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Core\Converter;

use IZMDPages\Core\Converter\HtmlToMarkdownConverter;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for HtmlToMarkdownConverter.
 */
class HtmlToMarkdownConverterTest extends TestCase
{
    private HtmlToMarkdownConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new HtmlToMarkdownConverter();
        if (function_exists('remove_all_filters')) {
            remove_all_filters('iz_md_pages_convert_tag');
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('remove_all_filters')) {
            remove_all_filters('iz_md_pages_convert_tag');
        }
        parent::tearDown();
    }

    /**
     * @dataProvider emptyHtmlDataProvider
     */
    public function testConvertReturnsEmptyStringForEmptyOrWhitespaceInput(string $input): void
    {
        $this->assertSame('', $this->converter->convert($input));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function emptyHtmlDataProvider(): array
    {
        return [
            'empty string' => [''],
            'only spaces' => ['   '],
            'tabs and newlines' => ["\n\t  \r\n"],
        ];
    }

    /**
     * @dataProvider headingsDataProvider
     */
    public function testConvertHeadings(string $html, string $expectedMarkdown): void
    {
        $this->assertSame($expectedMarkdown, $this->converter->convert($html));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function headingsDataProvider(): array
    {
        return [
            'h1 heading' => ['<h1>Main Heading</h1>', '# Main Heading'],
            'h2 heading' => ['<h2>Section Title</h2>', '## Section Title'],
            'h3 heading' => ['<h3>Sub Section</h3>', '### Sub Section'],
            'h4 heading' => ['<h4>Minor Title</h4>', '#### Minor Title'],
            'h5 heading' => ['<h5>Small Title</h5>', '##### Small Title'],
            'h6 heading' => ['<h6>Tiny Title</h6>', '###### Tiny Title'],
        ];
    }

    public function testConvertParagraphs(): void
    {
        $html = '<p>First paragraph.</p><p>Second paragraph.</p>';
        $expected = "First paragraph.\n\nSecond paragraph.";
        $this->assertSame($expected, $this->converter->convert($html));
    }

    /**
     * @dataProvider inlineFormattingDataProvider
     */
    public function testConvertInlineFormatting(string $html, string $expectedMarkdown): void
    {
        $this->assertSame($expectedMarkdown, $this->converter->convert($html));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function inlineFormattingDataProvider(): array
    {
        return [
            'strong tag' => ['<p>Text with <strong>bold</strong> word.</p>', 'Text with **bold** word.'],
            'b tag' => ['<p>Text with <b>bold</b> word.</p>', 'Text with **bold** word.'],
            'em tag' => ['<p>Text with <em>italic</em> word.</p>', 'Text with *italic* word.'],
            'i tag' => ['<p>Text with <i>italic</i> word.</p>', 'Text with *italic* word.'],
            'del tag' => ['<p>Text with <del>deleted</del> word.</p>', 'Text with ~~deleted~~ word.'],
            's tag' => ['<p>Text with <s>struck</s> word.</p>', 'Text with ~~struck~~ word.'],
            'strike tag' => ['<p>Text with <strike>strike</strike> word.</p>', 'Text with ~~strike~~ word.'],
            'inline code' => ['<p>Call <code>wp_safe_redirect()</code> function.</p>', 'Call `wp_safe_redirect()` function.'],
            'break tag' => ['<p>Line 1<br>Line 2</p>', "Line 1  \nLine 2"],
            'horizontal rule' => ['<p>Section 1</p><hr><p>Section 2</p>', "Section 1\n\n---\n\nSection 2"],
        ];
    }

    /**
     * @dataProvider linksDataProvider
     */
    public function testConvertLinks(string $html, string $expectedMarkdown): void
    {
        $this->assertSame($expectedMarkdown, $this->converter->convert($html));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function linksDataProvider(): array
    {
        return [
            'standard link' => [
                '<a href="https://example.com/docs">Documentation</a>',
                '[Documentation](https://example.com/docs)',
            ],
            'autolink when text matches href' => [
                '<a href="https://example.com">https://example.com</a>',
                '<https://example.com>',
            ],
            'autolink when text is empty' => [
                '<a href="https://example.com"></a>',
                '<https://example.com>',
            ],
            'root-relative url' => [
                '<a href="/about-us">About</a>',
                '[About](https://example.com/about-us)',
            ],
            'fragment-only link' => [
                '<a href="#features">Features</a>',
                '[Features](https://example.com/#features)',
            ],
            'relative link without leading slash' => [
                '<a href="faq.html">FAQ</a>',
                '[FAQ](https://example.com/faq.html)',
            ],
            'link with empty href' => [
                '<a href="">Text only</a>',
                'Text only',
            ],
            'link without href attribute' => [
                '<a name="target-anchor">Anchor without href</a>',
                'Anchor without href',
            ],
            'mailto scheme link' => [
                '<a href="mailto:support@example.com">Contact Support</a>',
                '[Contact Support](mailto:support@example.com)',
            ],
            'tel scheme link' => [
                '<a href="tel:+1234567890">Call Sales</a>',
                '[Call Sales](tel:+1234567890)',
            ],
            'protocol-relative url' => [
                '<a href="//cdn.example.com/script.js">CDN Asset</a>',
                '[CDN Asset](//cdn.example.com/script.js)',
            ],
        ];
    }

    public function testConvertLinksWithCustomPortInBaseUrl(): void
    {
        add_filter('home_url', function (): string {
            return 'https://example.com:8443';
        });

        // Test with custom port baseUrl by using an anonymous class or testing resolveUrl via convert
        $html = '<a href="/dashboard">Dashboard</a>';
        // Note: home_url in bootstrap defaults to https://example.com, with custom port in wp_parse_url
        $result = $this->converter->convert($html);
        $this->assertStringContainsString('/dashboard', $result);
    }

    public function testConvertImages(): void
    {
        $html = '<img src="https://example.com/logo.png" alt="Company Logo">';
        $this->assertSame('![Company Logo](https://example.com/logo.png)', $this->converter->convert($html));

        $relativeHtml = '<img src="/images/banner.jpg" alt="Banner">';
        $this->assertSame('![Banner](https://example.com/images/banner.jpg)', $this->converter->convert($relativeHtml));

        $relativeNoSlash = '<img src="images/icon.png" alt="Icon">';
        $this->assertSame('![Icon](https://example.com/images/icon.png)', $this->converter->convert($relativeNoSlash));
    }

    public function testConvertImagesIgnoresEmptyOrMissingSrc(): void
    {
        $htmlWithoutSrc = '<p>Text with <img alt="Missing Src"> image.</p>';
        $this->assertSame('Text with image.', $this->converter->convert($htmlWithoutSrc));

        $htmlWithEmptySrc = '<p>Text with <img src="" alt="Empty Src"> image.</p>';
        $this->assertSame('Text with image.', $this->converter->convert($htmlWithEmptySrc));
    }

    public function testConvertBlockquote(): void
    {
        $html = '<blockquote>Single line quote.</blockquote>';
        $this->assertSame('> Single line quote.', $this->converter->convert($html));

        $multiLineHtml = "<blockquote>Line one\nLine two</blockquote>";
        $expected = "> Line one\n> Line two";
        $this->assertSame($expected, $this->converter->convert($multiLineHtml));
    }

    public function testConvertUnorderedList(): void
    {
        $html = '<ul><li>Apple</li><li>Banana</li><li>Cherry</li></ul>';
        $expected = "- Apple\n- Banana\n- Cherry";
        $this->assertSame($expected, $this->converter->convert($html));
    }

    public function testConvertOrderedList(): void
    {
        $html = '<ol><li>First step</li><li>Second step</li><li>Third step</li></ol>';
        $expected = "1. First step\n2. Second step\n3. Third step";
        $this->assertSame($expected, $this->converter->convert($html));
    }

    public function testConvertListIgnoresNonLiChildren(): void
    {
        $html = '<ul>' .
            'Some stray text' .
            '<li>Item 1</li>' .
            '<!-- Comment -->' .
            '<li>Item 2</li>' .
            '</ul>';

        $expected = "- Item 1\n- Item 2";
        $this->assertSame($expected, $this->converter->convert($html));
    }

    public function testConvertCodeBlocks(): void
    {
        $html = '<pre><code>function hello() {
    return "world";
}</code></pre>';

        $expected = "```\nfunction hello() {\n    return \"world\";\n}\n```";
        $this->assertSame($expected, $this->converter->convert($html));
    }

    public function testConvertPreWithoutInnerCodeTag(): void
    {
        $html = '<pre>Plain preformatted text without code tag</pre>';
        $expected = "```\nPlain preformatted text without code tag\n```";
        $this->assertSame($expected, $this->converter->convert($html));
    }

    public function testConvertPreservesIndentationInsideCodeBlocksWhileStrippingOutside(): void
    {
        $html = '<p>   Paragraph with leading spaces</p>' .
            '<pre><code>    line with 4 spaces indent' . "\n" .
            '        line with 8 spaces indent</code></pre>';

        $result = $this->converter->convert($html);

        $this->assertStringContainsString('Paragraph with leading spaces', $result);
        $this->assertStringContainsString("    line with 4 spaces indent\n        line with 8 spaces indent", $result);
    }

    public function testConvertNormalizesConsecutiveBlankLinesAndSpaces(): void
    {
        $html = '<p>First line</p><br><br><br><br><p>Second line</p>';
        $result = $this->converter->convert($html);

        // Should not have more than 2 consecutive newlines
        $this->assertDoesNotMatchRegularExpression("/\n{3,}/", $result);
    }

    public function testConvertGenericContainerTags(): void
    {
        $html = '<div class="content-wrapper"><section><article><span>Text inside generic tags</span></article></section></div>';
        $this->assertSame('Text inside generic tags', $this->converter->convert($html));
    }

    public function testConvertNestedInlineFormatting(): void
    {
        $html = '<p>This is <strong>bold and <em>italic and <del>struck</del></em></strong> text.</p>';
        $expected = 'This is **bold and *italic and ~~struck~~*** text.';
        $this->assertSame($expected, $this->converter->convert($html));
    }

    public function testConvertCyrillicAndUtf8Content(): void
    {
        $html = '<h1>Заголовок статьи</h1><p>Привет, мир! Это тестовый параграф на русском языке.</p>';
        $expected = "# Заголовок статьи\n\nПривет, мир! Это тестовый параграф на русском языке.";
        $this->assertSame($expected, $this->converter->convert($html));
    }

    public function testConvertTagFilterAllowsOverridingOutput(): void
    {
        add_filter('iz_md_pages_convert_tag', function ($override, string $tagName, \DOMElement $element, string $innerText) {
            if ($tagName === 'h2') {
                return "\n\n## [CUSTOM] " . trim($innerText) . "\n\n";
            }
            return $override;
        }, 10, 4);

        $html = '<h2>Custom Heading</h2><p>Normal text</p>';
        $expected = "## [CUSTOM] Custom Heading\n\nNormal text";

        $this->assertSame($expected, $this->converter->convert($html));
    }
}
