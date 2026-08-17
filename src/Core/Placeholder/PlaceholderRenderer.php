<?php

declare(strict_types=1);

namespace IZMDPages\Core\Placeholder;

use IZMDPages\Core\Converter\HtmlToMarkdownConverter;

/**
 * Handles rendering of template placeholders for Markdown pages.
 */
class PlaceholderRenderer
{
    // Post core placeholders
    public const PLACEHOLDER_POST_TITLE = '{%post_title%}';
    public const PLACEHOLDER_POST_CONTENT = '{%post_content%}';
    public const PLACEHOLDER_POST_EXCERPT = '{%post_excerpt%}';
    public const PLACEHOLDER_POST_ID = '{%post_id%}';
    public const PLACEHOLDER_POST_SLUG = '{%post_slug%}';
    public const PLACEHOLDER_POST_NAME = '{%post_name%}';
    public const PLACEHOLDER_POST_TYPE = '{%post_type%}';
    public const PLACEHOLDER_POST_STATUS = '{%post_status%}';
    public const PLACEHOLDER_POST_DATE = '{%post_date%}';
    public const PLACEHOLDER_POST_DATE_GMT = '{%post_date_gmt%}';
    public const PLACEHOLDER_POST_TIME = '{%post_time%}';
    public const PLACEHOLDER_POST_MODIFIED = '{%post_modified%}';
    public const PLACEHOLDER_POST_MODIFIED_GMT = '{%post_modified_gmt%}';
    public const PLACEHOLDER_POST_PERMALINK = '{%post_permalink%}';
    public const PLACEHOLDER_POST_URL = '{%post_url%}';
    public const PLACEHOLDER_POST_THUMBNAIL_URL = '{%post_thumbnail_url%}';
    public const PLACEHOLDER_POST_FEATURED_IMAGE_URL = '{%post_featured_image_url%}';
    public const PLACEHOLDER_POST_THUMBNAIL = '{%post_thumbnail%}';
    public const PLACEHOLDER_POST_FEATURED_IMAGE = '{%post_featured_image%}';

    // Author placeholders
    public const PLACEHOLDER_AUTHOR_NAME = '{%author_name%}';
    public const PLACEHOLDER_POST_AUTHOR = '{%post_author%}';
    public const PLACEHOLDER_AUTHOR_EMAIL = '{%author_email%}';
    public const PLACEHOLDER_AUTHOR_URL = '{%author_url%}';
    public const PLACEHOLDER_AUTHOR_BIO = '{%author_bio%}';
    public const PLACEHOLDER_AUTHOR_FIRST_NAME = '{%author_first_name%}';
    public const PLACEHOLDER_AUTHOR_LAST_NAME = '{%author_last_name%}';
    public const PLACEHOLDER_AUTHOR_NICKNAME = '{%author_nickname%}';

    // Taxonomy placeholders
    public const PLACEHOLDER_CATEGORIES = '{%categories%}';
    public const PLACEHOLDER_TAGS = '{%tags%}';
    public const PLACEHOLDER_TAXONOMY = '{%taxonomy:<taxonomy_name>%}';

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

        $result = strtr($template, $placeholders);

        // Dynamic taxonomy replacement: {%taxonomy:<tax_name>%}, {%tax_<tax_name>%}, {%taxonomy_<tax_name>%}
        return (string) preg_replace_callback(
            '/\{%(?:taxonomy:|tax_|taxonomy_)([a-zA-Z0-9_\-]+)%\}/',
            function (array $matches) use ($post): string {
                $taxonomy = $matches[1];
                return $this->renderTaxonomyTerms($post, $taxonomy);
            },
            $result
        );
    }

    /**
     * Retrieve all available predefined placeholders and their replaced values for a post.
     *
     * @param \WP_Post $post Current post object.
     * @return array<string, string> Map of placeholder tokens to their string values.
     */
    public function getPlaceholderReplacements(\WP_Post $post): array
    {
        $replacements = [
            // Post core fields
            self::PLACEHOLDER_POST_TITLE => $this->renderPostField('post_title', $post),
            self::PLACEHOLDER_POST_CONTENT => $this->renderPostContent($post),
            self::PLACEHOLDER_POST_EXCERPT => $this->renderPostExcerpt($post),
            self::PLACEHOLDER_POST_ID => $this->renderPostField('ID', $post),
            self::PLACEHOLDER_POST_SLUG => $this->renderPostField('post_name', $post),
            self::PLACEHOLDER_POST_NAME => $this->renderPostField('post_name', $post),
            self::PLACEHOLDER_POST_TYPE => $this->renderPostField('post_type', $post),
            self::PLACEHOLDER_POST_STATUS => $this->renderPostField('post_status', $post),
            self::PLACEHOLDER_POST_DATE => $this->renderPostField('post_date', $post),
            self::PLACEHOLDER_POST_DATE_GMT => $this->renderPostField('post_date_gmt', $post),
            self::PLACEHOLDER_POST_TIME => $this->renderPostField('post_time', $post),
            self::PLACEHOLDER_POST_MODIFIED => $this->renderPostField('post_modified', $post),
            self::PLACEHOLDER_POST_MODIFIED_GMT => $this->renderPostField('post_modified_gmt', $post),
            self::PLACEHOLDER_POST_PERMALINK => $this->renderPostField('permalink', $post),
            self::PLACEHOLDER_POST_URL => $this->renderPostField('permalink', $post),
            self::PLACEHOLDER_POST_THUMBNAIL_URL => $this->renderPostField('thumbnail_url', $post),
            self::PLACEHOLDER_POST_FEATURED_IMAGE_URL => $this->renderPostField('thumbnail_url', $post),
            self::PLACEHOLDER_POST_THUMBNAIL => $this->renderFeaturedImageMarkdown($post),
            self::PLACEHOLDER_POST_FEATURED_IMAGE => $this->renderFeaturedImageMarkdown($post),

            // Author fields
            self::PLACEHOLDER_AUTHOR_NAME => $this->renderAuthorField('display_name', $post),
            self::PLACEHOLDER_POST_AUTHOR => $this->renderAuthorField('display_name', $post),
            self::PLACEHOLDER_AUTHOR_EMAIL => $this->renderAuthorField('user_email', $post),
            self::PLACEHOLDER_AUTHOR_URL => $this->renderAuthorField('author_url', $post),
            self::PLACEHOLDER_AUTHOR_BIO => $this->renderAuthorField('description', $post),
            self::PLACEHOLDER_AUTHOR_FIRST_NAME => $this->renderAuthorField('first_name', $post),
            self::PLACEHOLDER_AUTHOR_LAST_NAME => $this->renderAuthorField('last_name', $post),
            self::PLACEHOLDER_AUTHOR_NICKNAME => $this->renderAuthorField('nickname', $post),

            // Standard taxonomy shortcuts
            self::PLACEHOLDER_CATEGORIES => $this->renderTaxonomyTerms($post, 'category'),
            self::PLACEHOLDER_TAGS => $this->renderTaxonomyTerms($post, 'post_tag'),
        ];

        // Add placeholders for all taxonomies attached to this post's post type
        $taxonomies = get_object_taxonomies($post->post_type, 'names');
        foreach ($taxonomies as $taxonomyName) {
            $termsString = $this->renderTaxonomyTerms($post, $taxonomyName);
            $replacements['{%taxonomy:' . $taxonomyName . '%}'] = $termsString;
        }

        return $replacements;
    }

    /**
     * Render a post field value and pass it through an extensible filter hook.
     *
     * @param string   $fieldName Post field name (e.g. 'post_title', 'post_name', 'ID', 'permalink').
     * @param \WP_Post $post      Current post object.
     * @return string Rendered post field value.
     */
    public function renderPostField(string $fieldName, \WP_Post $post): string
    {
        switch ($fieldName) {
            case 'post_title':
                $value = trim((string) get_the_title($post));
                break;

            case 'post_date':
                $value = (string) get_the_date('', $post);
                break;

            case 'post_time':
                $value = (string) get_the_time('', $post);
                break;

            case 'post_modified':
                $value = (string) get_the_modified_date('', $post);
                break;

            case 'permalink':
                $value = (string) get_permalink($post->ID);
                break;

            case 'thumbnail_url':
                $url = get_the_post_thumbnail_url($post, 'full');
                $value = is_string($url) ? $url : '';
                break;

            default:
                $value = isset($post->{$fieldName}) && is_scalar($post->{$fieldName})
                    ? (string) $post->{$fieldName}
                    : '';
                break;
        }

        /**
         * Filter to override a rendered post field value.
         *
         * @param string   $value     Rendered field value.
         * @param string   $fieldName Post field identifier.
         * @param \WP_Post $post      Current post object.
         */
        return (string) apply_filters('iz_md_render_post_field', $value, $fieldName, $post);
    }

    /**
     * Render an author field value and pass it through an extensible filter hook.
     *
     * @param string   $fieldName Author field name (e.g. 'display_name', 'user_email', 'author_url').
     * @param \WP_Post $post      Current post object.
     * @return string Rendered author field value.
     */
    public function renderAuthorField(string $fieldName, \WP_Post $post): string
    {
        $authorId = (int) $post->post_author;

        if ($fieldName === 'author_url') {
            $value = (string) get_author_posts_url($authorId);
        } else {
            $value = (string) get_the_author_meta($fieldName, $authorId);
        }

        /**
         * Filter to override a rendered author field value.
         *
         * @param string   $value     Rendered author field value.
         * @param string   $fieldName Author field identifier.
         * @param \WP_Post $post      Current post object.
         */
        return (string) apply_filters('iz_md_render_author_field', $value, $fieldName, $post);
    }

    /**
     * Retrieve list of supported placeholder tags grouped by category.
     *
     * @return array<string, array<string, string>> Grouped map of placeholder tags and descriptions.
     */
    public static function getGroupedPlaceholders(): array
    {
        return [
            'Post' => [
                self::PLACEHOLDER_POST_TITLE => __('Post title', 'iz-md-pages'),
                self::PLACEHOLDER_POST_CONTENT => __('Post content converted to Markdown', 'iz-md-pages'),
                self::PLACEHOLDER_POST_EXCERPT => __('Post excerpt or short description', 'iz-md-pages'),
                self::PLACEHOLDER_POST_ID => __('Post ID', 'iz-md-pages'),
                self::PLACEHOLDER_POST_SLUG => __('Post slug / URL slug', 'iz-md-pages'),
                self::PLACEHOLDER_POST_TYPE => __('Post type name', 'iz-md-pages'),
                self::PLACEHOLDER_POST_STATUS => __('Post publication status', 'iz-md-pages'),
                self::PLACEHOLDER_POST_DATE => __('Post published date', 'iz-md-pages'),
                self::PLACEHOLDER_POST_TIME => __('Post published time', 'iz-md-pages'),
                self::PLACEHOLDER_POST_MODIFIED => __('Post last modified date', 'iz-md-pages'),
                self::PLACEHOLDER_POST_PERMALINK => __('Post permalink URL', 'iz-md-pages'),
                self::PLACEHOLDER_POST_FEATURED_IMAGE_URL => __('Featured image URL', 'iz-md-pages'),
                self::PLACEHOLDER_POST_FEATURED_IMAGE => __('Featured image Markdown tag', 'iz-md-pages'),
            ],
            'Author' => [
                self::PLACEHOLDER_AUTHOR_NAME => __('Author display name', 'iz-md-pages'),
                self::PLACEHOLDER_AUTHOR_EMAIL => __('Author email address', 'iz-md-pages'),
                self::PLACEHOLDER_AUTHOR_URL => __('Author posts archive URL', 'iz-md-pages'),
                self::PLACEHOLDER_AUTHOR_BIO => __('Author biography / description', 'iz-md-pages'),
                self::PLACEHOLDER_AUTHOR_FIRST_NAME => __('Author first name', 'iz-md-pages'),
                self::PLACEHOLDER_AUTHOR_LAST_NAME => __('Author last name', 'iz-md-pages'),
            ],
            'Taxonomies' => [
                self::PLACEHOLDER_CATEGORIES => __('Post categories (comma-separated)', 'iz-md-pages'),
                self::PLACEHOLDER_TAGS => __('Post tags (comma-separated)', 'iz-md-pages'),
                self::PLACEHOLDER_TAXONOMY => __('Terms of any taxonomy (comma-separated), e.g. {%taxonomy:product_cat%}', 'iz-md-pages'),
            ],
        ];
    }

    /**
     * Retrieve list of supported placeholder tags.
     *
     * @return array<int, string> List of placeholder tags.
     */
    public static function getSupportedPlaceholders(): array
    {
        $tags = [];
        foreach (self::getGroupedPlaceholders() as $group) {
            foreach (array_keys($group) as $tag) {
                $tags[] = (string) $tag;
            }
        }
        return $tags;
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
        $htmlContent = (string) apply_filters('iz_md_placeholder_render_post_content', $htmlContent, $post);
        return $this->converter->convert($htmlContent);
    }

    /**
     * Render post excerpt placeholder value as Markdown/plain text.
     *
     * @param \WP_Post $post Current post object.
     * @return string Post excerpt converted to Markdown.
     */
    protected function renderPostExcerpt(\WP_Post $post): string
    {
        $excerpt = (string) get_the_excerpt($post);
        $excerpt = (string) apply_filters('iz_md_placeholder_render_post_excerpt', $excerpt, $post);

        if (empty(trim($excerpt))) {
            return '';
        }

        return $this->converter->convert($excerpt);
    }

    /**
     * Render featured image Markdown syntax.
     *
     * @param \WP_Post    $post Current post object.
     * @param string|null $url  Optional featured image URL.
     * @return string Markdown image snippet or empty string.
     */
    protected function renderFeaturedImageMarkdown(\WP_Post $post, ?string $url = null): string
    {
        $imageUrl = $url ?? $this->renderPostField('thumbnail_url', $post);

        if ($imageUrl === '') {
            return '';
        }

        $thumbnailId = get_post_thumbnail_id($post);
        $alt = $thumbnailId ? (string) get_post_meta($thumbnailId, '_wp_attachment_image_alt', true) : '';
        if ($alt === '') {
            $alt = $this->renderPostField('post_title', $post);
        }

        return '![' . $alt . '](' . esc_url($imageUrl) . ')';
    }

    /**
     * Render comma-separated terms for a given taxonomy and post.
     *
     * @param \WP_Post $post         Current post object.
     * @param string   $taxonomyName Taxonomy slug.
     * @return string Comma-separated list of term names.
     */
    public function renderTaxonomyTerms(\WP_Post $post, string $taxonomyName): string
    {
        if (!taxonomy_exists($taxonomyName)) {
            return '';
        }

        $terms = get_the_terms($post->ID, $taxonomyName);

        if (is_wp_error($terms) || !is_array($terms) || empty($terms)) {
            return '';
        }

        $termNames = wp_list_pluck($terms, 'name');

        /**
         * Filter rendered terms list for a taxonomy placeholder.
         *
         * @param array<int, string> $termNames    Array of term names.
         * @param \WP_Post           $post         Current post object.
         * @param string             $taxonomyName Taxonomy slug.
         */
        $termNames = apply_filters('iz_md_placeholder_taxonomy_terms', $termNames, $post, $taxonomyName);

        return implode(', ', $termNames);
    }
}
