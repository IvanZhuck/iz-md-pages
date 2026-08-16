<?php

declare(strict_types=1);

namespace IZMDPages\Core\MdPages;

use IZMDPages\Admin\Settings\SettingsPage;
use IZMDPages\Core\Converter\HtmlToMarkdownConverter;

/**
 * Handles Markdown page output and URL routing.
 */
class MdPagesOutput
{
    /**
     * Initialize WordPress hooks for Markdown output.
     */
    public function init(): void
    {
        add_action('init', [$this, 'addRewriteEndpoints']);
        add_filter('query_vars', [$this, 'addQueryVars']);
        add_action('template_redirect', [$this, 'handleTemplateRedirect']);
    }

    /**
     * Register /md rewrite endpoint for posts and pages.
     */
    public function addRewriteEndpoints(): void
    {
        add_rewrite_endpoint('md', EP_PERMALINK | EP_PAGES);
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

        if (!is_singular() || !isset($wp_query->query_vars['md'])) {
            return;
        }

        $post = get_queried_object();
        if (!$post || !$post instanceof \WP_Post) {
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
     * Generate and output Markdown representation of a post.
     *
     * @param \WP_Post $post Post object to render.
     */
    public function renderMdPage(\WP_Post $post): void
    {
        header('Content-Type: text/markdown; charset=utf-8');

        $title = (string) get_the_title($post);
        $htmlContent = (string) apply_filters('the_content', $post->post_content);

        $converter = new HtmlToMarkdownConverter();
        $mdContent = $converter->convert($htmlContent);

        echo '# ' . esc_html($title) . "\n\n";
        echo $mdContent;
        exit;
    }
}
