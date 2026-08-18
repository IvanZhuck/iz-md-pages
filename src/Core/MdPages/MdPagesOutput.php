<?php

declare(strict_types=1);

namespace IZMDPages\Core\MdPages;

use IZMDPages\Admin\MetaBoxes\MdPageMetaBox;
use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Admin\Settings\TemplatesSettingsPage;
use IZMDPages\Core\Placeholder\PlaceholderRenderer;

/**
 * Handles Markdown page output and URL routing.
 */
class MdPagesOutput
{
    /**
     * Placeholder renderer instance.
     */
    private PlaceholderRenderer $placeholderRenderer;

    /**
     * MdPagesOutput constructor.
     *
     * @param PlaceholderRenderer|null $placeholderRenderer Placeholder renderer instance.
     */
    public function __construct(?PlaceholderRenderer $placeholderRenderer = null)
    {
        $this->placeholderRenderer = $placeholderRenderer ?? new PlaceholderRenderer();
    }

    /**
     * Initialize WordPress hooks for Markdown output.
     */
    public function init(): void
    {
        add_action('init', [$this, 'addRewriteEndpoints']);
        add_filter('query_vars', [$this, 'addQueryVars']);
        add_action('template_redirect', [$this, 'handleTemplateRedirect']);
        add_action('wp_head', [$this, 'renderAlternateLink'], 2);
    }

    /**
     * Register /md rewrite endpoint for posts, pages, and static front page.
     */
    public function addRewriteEndpoints(): void
    {
        add_rewrite_endpoint('md', EP_PERMALINK | EP_PAGES | EP_ROOT);
    }

    /**
     * Add "md" query variable to WordPress public query vars.
     *
     * @param array<int, string> $vars List of public query variables.
     * @return array<int, string> Updated list of query variables.
     */
    public function addQueryVars(array $vars): array
    {
        $vars[] = 'md';
        return $vars;
    }

    /**
     * Handle template redirect for Markdown requests.
     */
    public function handleTemplateRedirect(): void
    {
        global $wp_query;

        if (!isset($wp_query->query_vars['md'])) {
            return;
        }

        $post = null;
        $queried = get_queried_object();

        if (is_singular() && $queried instanceof \WP_Post) {
            $post = $queried;
        } elseif ($queried instanceof \WP_Post) {
            $blogHomePageId = (int) get_option('page_for_posts');
            if ($queried->ID === $blogHomePageId){
                $post = get_post($blogHomePageId);
            }
        } elseif (get_option('show_on_front') === 'page') {
            $frontPageId = (int) get_option('page_on_front');
            if ($frontPageId > 0) {
                $post = get_post($frontPageId);
            }
        }

        if (!$post instanceof \WP_Post) {
            if (is_front_page() || is_home()) {
                wp_safe_redirect(home_url('/'), 301);
                exit;
            }
            return;
        }

        $this->maybeRedirect($post);
        $this->renderMdPage($post);
    }

    /**
     * Redirect to the configured canonical Markdown URL format if needed.
     *
     * @param \WP_Post $post Post object.
     */
    private function maybeRedirect(\WP_Post $post): void
    {
        $enabledTypes = (array) get_option(SettingsPage::OPTION_KEY, ['post', 'page']);
        $permalink = (string) get_permalink($post->ID);
        $isDisabled = (bool) get_post_meta($post->ID, MdPageMetaBox::META_KEY_DISABLED, true);
        $isFrontPage = ($post->ID === (int) get_option('page_on_front') && get_option('show_on_front') === 'page');
        $isFrontPageEnabled = (bool) get_option(SettingsPage::OPTION_FRONT_PAGE_KEY, true);

        if (($isFrontPage && !$isFrontPageEnabled) || !in_array($post->post_type, $enabledTypes, true) || $isDisabled) {
            wp_safe_redirect($permalink, 301);
            exit;
        }

        $suffixType = (string) get_option(SettingsPage::OPTION_SUFFIX_KEY, 'endpoint');
        $isQueryVarRequest = isset($_GET['md']);

        if ($suffixType === 'endpoint' && $isQueryVarRequest) {
            $targetUrl = user_trailingslashit(rtrim($permalink, '/') . '/md');
            wp_safe_redirect($targetUrl, 301);
            exit;
        }

        if ($suffixType === 'query_var' && !$isQueryVarRequest) {
            $targetUrl = add_query_arg('md', '', $permalink);
            wp_safe_redirect($targetUrl, 301);
            exit;
        }
    }

    /**
     * Get the Markdown URL for a specific post.
     *
     * @param \WP_Post $post Post object.
     * @return string Markdown URL.
     */
    public static function getMdUrl(\WP_Post $post): string
    {
        $permalink = (string) get_permalink($post->ID);
        $suffixType = (string) get_option(SettingsPage::OPTION_SUFFIX_KEY, 'endpoint');

        if ($suffixType === 'query_var') {
            return add_query_arg('md', '', $permalink);
        }

        return user_trailingslashit(rtrim($permalink, '/') . '/md');
    }

    /**
     * Output a <link rel="alternate"> tag pointing to the Markdown
     * version of the current page when available.
     */
    public function renderAlternateLink(): void
    {
        if (!is_singular()) {
            return;
        }

        $post = get_queried_object();

        if (!$post instanceof \WP_Post) {
            return;
        }

        $isFrontPage = ($post->ID === (int) get_option('page_on_front') && get_option('show_on_front') === 'page');
        $isFrontPageEnabled = (bool) get_option(SettingsPage::OPTION_FRONT_PAGE_KEY, true);

        if ($isFrontPage && !$isFrontPageEnabled) {
            return;
        }

        $isDisabled = (bool) get_post_meta($post->ID, MdPageMetaBox::META_KEY_DISABLED, true);

        if ($isDisabled) {
            return;
        }

        $enabledTypes = (array) get_option(SettingsPage::OPTION_KEY, ['post', 'page']);

        if (!in_array($post->post_type, $enabledTypes, true)) {
            return;
        }

        $mdUrl = self::getMdUrl($post);

        echo '<link rel="alternate" type="text/markdown" href="' . esc_url($mdUrl) . '" />' . "\n";
    }

    /**
     * Generate and output Markdown representation of a post.
     *
     * @param \WP_Post $post Post object to render.
     */
    private function renderMdPage(\WP_Post $post): void
    {
        header('Content-Type: text/markdown; charset=utf-8');

        $isManual = (bool) get_post_meta($post->ID, MdPageMetaBox::META_KEY_MANUAL_ENABLED, true);

        if ($isManual) {
            $template = (string) get_post_meta($post->ID, MdPageMetaBox::META_KEY_MANUAL_CONTENT, true);
        } else {
            $template = TemplatesSettingsPage::getTemplateForPostType($post->post_type);
        }

        /**
         * Filter Markdown template for a specific post by its ID.
         *
         * @param string   $template Markdown template string.
         * @param \WP_Post $post     Post object.
         */
        $template = (string) apply_filters("iz_md_post_template_{$post->ID}", $template, $post);

        $content = $this->placeholderRenderer->render($template, $post);

        /**
         * Filter assembled Markdown page content for a specific post by its ID.
         *
         * @param string   $content Rendered Markdown page content.
         * @param \WP_Post $post    Post object.
         */
        $content = (string) apply_filters("iz_md_page_content_{$post->ID}", $content, $post);

        /**
         * Filter assembled Markdown page content for a specific post type.
         *
         * @param string   $content Rendered Markdown page content.
         * @param \WP_Post $post    Post object.
         */
        $content = (string) apply_filters("iz_md_page_content_{$post->post_type}", $content, $post);

        /**
         * Filter assembled Markdown page content for any post type.
         *
         * @param string   $content Rendered Markdown page content.
         * @param \WP_Post $post    Post object.
         */
        $content = (string) apply_filters('iz_md_page_content', $content, $post);

        echo $content;
        exit;
    }
}
