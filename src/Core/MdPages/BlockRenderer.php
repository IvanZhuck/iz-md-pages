<?php

declare(strict_types=1);

namespace IZMDPages\Core\MdPages;

use IZMDPages\Core\Converter\HtmlToMarkdownConverter;

/**
 * Handles rendering and custom hook overrides of Gutenberg editor blocks for Markdown pages.
 */
class BlockRenderer
{
    /**
     * HTML to Markdown converter instance.
     */
    private HtmlToMarkdownConverter $converter;

    /**
     * BlockRenderer constructor.
     *
     * @param HtmlToMarkdownConverter|null $converter HTML to Markdown converter.
     */
    public function __construct(?HtmlToMarkdownConverter $converter = null)
    {
        $this->converter = $converter ?? new HtmlToMarkdownConverter();
    }

    /**
     * Render post content blocks to Markdown with hook overrides.
     *
     * @param \WP_Post $post Current post object.
     * @return string Rendered Markdown content.
     */
    public function render(\WP_Post $post): string
    {
        $rawContent = (string) $post->post_content;

        if (trim($rawContent) === '') {
            return '';
        }

        if (!function_exists('has_blocks') || !has_blocks($rawContent)) {
            return $this->renderClassicContent($rawContent, $post);
        }

        $blocks = function_exists('parse_blocks') ? parse_blocks($rawContent) : [];

        if (empty($blocks)) {
            return $this->renderClassicContent($rawContent, $post);
        }

        return $this->renderBlocks($blocks, $post);
    }

    /**
     * Render an array of parsed blocks into Markdown.
     *
     * @param array<int, array<string, mixed>> $blocks Array of parsed block structures.
     * @param \WP_Post                         $post   Current post object.
     * @return string Rendered Markdown string.
     */
    public function renderBlocks(array $blocks, \WP_Post $post): string
    {
        $renderedBlocks = [];

        foreach ($blocks as $block) {
            $rendered = $this->renderBlock($block, $post);
            if (trim($rendered) !== '') {
                $renderedBlocks[] = trim($rendered);
            }
        }

        $content = implode("\n\n", $renderedBlocks);

        /**
         * Filter the fully assembled Markdown content from blocks.
         *
         * @param string                           $content Rendered Markdown content.
         * @param array<int, array<string, mixed>> $blocks  Original array of parsed blocks.
         * @param \WP_Post                         $post    Current post object.
         */
        return (string) apply_filters('iz_md_render_blocks_content', $content, $blocks, $post);
    }

    /**
     * Render a single parsed block, checking for custom override hooks first.
     *
     * @param array<string, mixed> $block Parsed block data.
     * @param \WP_Post              $post  Current post object.
     * @return string Rendered block in Markdown.
     */
    public function renderBlock(array $block, \WP_Post $post): string
    {
        $blockName = isset($block['blockName']) && is_string($block['blockName']) ? $block['blockName'] : '';

        /**
         * Filter to override any block rendering on the Markdown page.
         *
         * Return a non-null string (Markdown) to override the default block rendering.
         *
         * @param string|null          $override Custom Markdown string or null for default rendering.
         * @param array<string, mixed> $block    Parsed block structure (attrs, innerBlocks, innerHTML, etc.).
         * @param \WP_Post             $post     Current post object.
         */
        $override = apply_filters('iz_md_render_block', null, $block, $post);

        if ($override !== null) {
            return (string) $override;
        }

        if ($blockName !== '') {
            /**
             * Filter to override a specific block by its exact block name (e.g. 'core/paragraph', 'acf/gallery').
             *
             * @param string|null          $override Custom Markdown string or null.
             * @param array<string, mixed> $block    Parsed block structure.
             * @param \WP_Post             $post     Current post object.
             */
            $override = apply_filters("iz_md_render_block_{$blockName}", null, $block, $post);

            if ($override !== null) {
                return (string) $override;
            }

            // Also support sanitized hook name (e.g. 'iz_md_render_block_core_paragraph' for 'core/paragraph')
            $sanitizedName = str_replace(['/', '-'], '_', $blockName);
            if ($sanitizedName !== $blockName) {
                /**
                 * Filter to override a specific block using a sanitized hook identifier.
                 *
                 * @param string|null          $override Custom Markdown string or null.
                 * @param array<string, mixed> $block    Parsed block structure.
                 * @param \WP_Post             $post     Current post object.
                 */
                $override = apply_filters("iz_md_render_block_{$sanitizedName}", null, $block, $post);

                if ($override !== null) {
                    return (string) $override;
                }
            }
        }

        // Default block rendering
        return $this->renderDefaultBlock($block, $post);
    }

    /**
     * Default rendering of a block using standard WordPress render_block and HTML to Markdown converter.
     *
     * @param array<string, mixed> $block Parsed block structure.
     * @param \WP_Post              $post  Current post object.
     * @return string Converted Markdown output.
     */
    private function renderDefaultBlock(array $block, \WP_Post $post): string
    {
        if (function_exists('render_block')) {
            $html = (string) render_block($block);
        } else {
            $html = isset($block['innerHTML']) && is_string($block['innerHTML']) ? $block['innerHTML'] : '';
        }

        if (trim($html) === '') {
            return '';
        }

        /**
         * Filter the HTML content of a block before Markdown conversion.
         *
         * @param string               $html  Rendered block HTML.
         * @param array<string, mixed> $block Parsed block structure.
         * @param \WP_Post             $post  Current post object.
         */
        $html = (string) apply_filters('iz_md_block_html', $html, $block, $post);

        return $this->converter->convert($html);
    }

    /**
     * Render classic (non-block) content.
     *
     * @param string   $rawContent Raw post content.
     * @param \WP_Post $post       Current post object.
     * @return string Converted Markdown output.
     */
    private function renderClassicContent(string $rawContent, \WP_Post $post): string
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Intentionally applying WordPress core 'the_content' filter to render classic post content.
        $htmlContent = (string) apply_filters('the_content', $rawContent);
        $htmlContent = (string) apply_filters('iz_md_placeholder_render_post_content', $htmlContent, $post);

        return $this->converter->convert($htmlContent);
    }
}
