<?php

declare(strict_types=1);

if (!function_exists('iz_md_is_md_page')) {
    /**
     * Determine whether the current request is for a Markdown version of a page.
     *
     * @return bool True if current request is a valid Markdown page, false otherwise.
     */
    function iz_md_is_md_page(): bool
    {
        global $wp_query;

        return isset($wp_query->query_vars['md']);
    }
}

if (!function_exists('iz_md_get_md_url')) {
    /**
     * Get the Markdown URL for the current page or a specified post.
     *
     * @param int|\WP_Post|null $post Optional. Post ID or WP_Post object. Defaults to current queried post.
     * @return string Markdown URL if available and enabled, empty string otherwise.
     */
    function iz_md_get_md_url($post = null): string
    {
        if ($post === null) {
            $queried = function_exists('get_queried_object') ? get_queried_object() : null;
            if ($queried instanceof \WP_Post) {
                $post = $queried;
            } elseif (get_option('show_on_front') === 'page') {
                $frontPageId = (int) get_option('page_on_front');
                if ($frontPageId > 0) {
                    $post = get_post($frontPageId);
                }
            }
        } elseif (!$post instanceof \WP_Post) {
            $post = get_post($post);
        }

        if (!$post instanceof \WP_Post) {
            return '';
        }

        $isFrontPage = ($post->ID === (int) get_option('page_on_front') && get_option('show_on_front') === 'page');
        if ($isFrontPage && !\IZMDPages\Core\Settings\CoreSettings::isFrontPageEnabled()) {
            return '';
        }

        $isDisabled = (bool) get_post_meta($post->ID, \IZMDPages\Admin\MetaBoxes\MdPageMetaBox::META_KEY_DISABLED, true);
        if ($isDisabled) {
            return '';
        }

        if (!\IZMDPages\Core\Settings\CoreSettings::isPostTypeEnabled($post->post_type)) {
            return '';
        }

        return \IZMDPages\Core\MdPages\MdPagesOutput::getMdUrl($post);
    }
}
