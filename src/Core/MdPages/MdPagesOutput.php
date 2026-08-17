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
        add_action('wp_head', [$this, 'renderAlternateLink']);
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

        if (is_singular()) {
            $queried = get_queried_object();
            if ($queried instanceof \WP_Post) {
                $post = $queried;
            }
        } elseif (get_option('show_on_front') === 'page') {
            $frontPageId = (int) get_option('page_on_front');
            if ($frontPageId > 0) {
                $post = get_post($frontPageId);
            }
        }

        if (!$post instanceof \WP_Post) {
            return;
        }

        $enabledTypes = (array) get_option(SettingsPage::OPTION_KEY, ['post', 'page']);

        if (!in_array($post->post_type, $enabledTypes, true)) {
            $permalink = (string) get_permalink($post->ID);
            wp_safe_redirect($permalink, 301);
            exit;
        }

        $suffixType = (string) get_option(SettingsPage::OPTION_SUFFIX_KEY, 'endpoint');
        $isQueryVarRequest = isset($_GET['md']);
        $permalink = (string) get_permalink($post->ID);

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

        $this->renderMdPage($post);
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

        $enabledTypes = (array) get_option(SettingsPage::OPTION_KEY, ['post', 'page']);

        if (!in_array($post->post_type, $enabledTypes, true)) {
            return;
        }

        $permalink = (string) get_permalink($post->ID);
        $suffixType = (string) get_option(SettingsPage::OPTION_SUFFIX_KEY, 'endpoint');

        if ($suffixType === 'query_var') {
            $mdUrl = add_query_arg('md', '', $permalink);
        } else {
            $mdUrl = user_trailingslashit(rtrim($permalink, '/') . '/md');
        }

        echo '<link rel="alternate" type="text/markdown" href="' . esc_url($mdUrl) . '" />' . "\n";
    }

    /**
     * Generate and output Markdown representation of a post.
     *
     * @param \WP_Post $post Post object to render.
     */
    public function renderMdPage(\WP_Post $post): void
    {
        header('Content-Type: text/markdown; charset=utf-8');

        $isManual = (bool) get_post_meta($post->ID, MdPageMetaBox::META_KEY_MANUAL_ENABLED, true);

        if ($isManual) {
            $template = (string) get_post_meta($post->ID, MdPageMetaBox::META_KEY_MANUAL_CONTENT, true);
        } else {
            $templates = (array) get_option(TemplatesSettingsPage::OPTION_KEY, []);
            $template = isset($templates[$post->post_type]) && $templates[$post->post_type] !== ''
                ? (string) $templates[$post->post_type]
                : TemplatesSettingsPage::DEFAULT_TEMPLATE;
        }

        echo $this->placeholderRenderer->render($template, $post);
        exit;
    }
}
