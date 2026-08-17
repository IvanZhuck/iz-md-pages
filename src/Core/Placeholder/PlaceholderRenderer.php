<?php

declare(strict_types=1);

namespace IZMDPages\Core\Placeholder;

use IZMDPages\Core\Converter\HtmlToMarkdownConverter;

/**
 * Handles rendering of template placeholders for Markdown pages.
 */
class PlaceholderRenderer
{
    /**
     * Placeholder token for post title.
     */
    public const PLACEHOLDER_POST_TITLE = '{%post_title%}';

    /**
     * Placeholder token for post content.
     */
    public const PLACEHOLDER_POST_CONTENT = '{%post_content%}';

    /**
     * HTML to Markdown converter instance.
     */
    private HtmlToMarkdownConverter $converter;

    /**
     * PlaceholderRenderer constructor.
     *
     * @param HtmlToMarkdownConverter|null $converter HTML to Markdown converter.
     */
    public function __construct(?HtmlToMarkdownConverter $converter = null)
    {
        $this->converter = $converter ?? new HtmlToMarkdownConverter();
    }

    /**
     * Render placeholders in a template string for a specific post.
     *
     * @param string   $template Template content containing placeholders.
     * @param \WP_Post $post     Post object to extract entity data from.
     * @return string Rendered template content with replaced placeholders.
     */
    public function render(string $template, \WP_Post $post): string
    {
        if ($template === '') {
            return '';
        }

        $placeholders = $this->getPlaceholderReplacements($post);

        /**
         * Filter to add or modify placeholder replacements before substitution.
         *
         * @param array<string, string> $placeholders Associative array of [placeholder => value].
         * @param \WP_Post              $post         Current post object.
         * @param string                $template     Original template string.
         */
        $placeholders = apply_filters('iz_md_pages_placeholders', $placeholders, $post, $template);

        return strtr($template, $placeholders);
    }

    /**
     * Retrieve all available placeholders and their replaced values for a post.
     *
     * @param \WP_Post $post Current post object.
     * @return array<string, string> Map of placeholder tokens to their string values.
     */
    public function getPlaceholderReplacements(\WP_Post $post): array
    {
        return [
            self::PLACEHOLDER_POST_TITLE => $this->renderPostTitle($post),
            self::PLACEHOLDER_POST_CONTENT => $this->renderPostContent($post),
        ];
    }

    /**
     * Retrieve list of supported placeholder tags.
     *
     * @return array<int, string> List of placeholder tags.
     */
    public static function getSupportedPlaceholders(): array
    {
        return [
            self::PLACEHOLDER_POST_TITLE,
            self::PLACEHOLDER_POST_CONTENT,
        ];
    }

    /**
     * Render post title placeholder value.
     *
     * @param \WP_Post $post Current post object.
     * @return string Post title.
     */
    protected function renderPostTitle(\WP_Post $post): string
    {
        $title = get_the_title($post);
        $title = (string) apply_filters('iz_md_placeholder_render_post_title', $title);
        return trim((string) $title);
    }

    /**
     * Render post content placeholder value as Markdown.
     *
     * @param \WP_Post $post Current post object.
     * @return string Post content converted to Markdown.
     */
    protected function renderPostContent(\WP_Post $post): string
    {
        $htmlContent = (string) apply_filters('the_content', $post->post_content);
        $htmlContent = (string) apply_filters('iz_md_placeholder_render_post_content', $htmlContent);
        return $this->converter->convert($htmlContent);
    }
}
