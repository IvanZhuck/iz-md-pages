<?php

declare(strict_types=1);

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Fallback PSR-4 autoloader for tests
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'IZMDPages\\Tests\\' => dirname(__DIR__) . '/tests/',
        'IZMDPages\\' => dirname(__DIR__) . '/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Constants
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}
if (!defined('EP_PERMALINK')) {
    define('EP_PERMALINK', 1);
}
if (!defined('EP_PAGES')) {
    define('EP_PAGES', 4096);
}
if (!defined('EP_ROOT')) {
    define('EP_ROOT', 8192);
}

// WordPress Stub Classes
if (!class_exists('WP_Post')) {
    class WP_Post
    {
        public int $ID = 0;
        public string $post_title = '';
        public string $post_content = '';
        public string $post_excerpt = '';
        public string $post_type = 'post';
        public string $post_status = 'publish';
        public int $post_author = 1;
        public string $post_name = '';
        public string $post_date = '2026-01-01 12:00:00';
        public string $post_date_gmt = '2026-01-01 12:00:00';
        public string $post_time = '12:00:00';
        public string $post_modified = '2026-01-02 12:00:00';
        public string $post_modified_gmt = '2026-01-02 12:00:00';

        public function __construct(array $data = [])
        {
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
}

if (!class_exists('WP_Query')) {
    class WP_Query
    {
        public array $query_vars = [];

        public function __construct(array $query_vars = [])
        {
            $this->query_vars = $query_vars;
        }
    }
}

// Global state initialization
global $wp_filter, $wp_actions, $wp_options, $wp_post_meta, $wp_rewrite_endpoints, $wp_redirect_calls, $wp_is_singular, $wp_queried_object, $wp_posts_storage, $wp_query;

$wp_filter = [];
$wp_actions = [];
$wp_options = [];
$wp_post_meta = [];
$wp_rewrite_endpoints = [];
$wp_redirect_calls = [];
$wp_is_singular = true;
$wp_queried_object = null;
$wp_posts_storage = [];
$wp_query = new WP_Query();

// WordPress Mock Functions
if (!function_exists('add_action')) {
    function add_action(string $tag, $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        global $wp_actions;
        $wp_actions[$tag][] = $callback;
    }
}

if (!function_exists('do_action')) {
    function do_action(string $tag, ...$args): void
    {
        global $wp_actions;
        if (isset($wp_actions[$tag]) && is_array($wp_actions[$tag])) {
            foreach ($wp_actions[$tag] as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $tag, $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        global $wp_filter;
        $wp_filter[$tag][] = $callback;
    }
}

if (!function_exists('apply_filters')) {
    /**
     * @param string $tag
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    function apply_filters(string $tag, $value, ...$args)
    {
        global $wp_filter;
        if (isset($wp_filter[$tag]) && is_array($wp_filter[$tag])) {
            foreach ($wp_filter[$tag] as $callback) {
                $value = call_user_func($callback, $value, ...$args);
            }
        }
        return $value;
    }
}

if (!function_exists('get_option')) {
    /**
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    function get_option(string $option, $default = false)
    {
        global $wp_options;
        return array_key_exists($option, $wp_options) ? $wp_options[$option] : $default;
    }
}

if (!function_exists('update_option')) {
    /**
     * @param string $option
     * @param mixed $value
     * @param mixed $autoload
     * @return bool
     */
    function update_option(string $option, $value, $autoload = null): bool
    {
        global $wp_options;
        $wp_options[$option] = $value;
        return true;
    }
}

if (!function_exists('get_post')) {
    /**
     * @param mixed $post
     * @return \WP_Post|null
     */
    function get_post($post = null): ?\WP_Post
    {
        global $wp_posts_storage;
        if ($post instanceof \WP_Post) {
            return $post;
        }
        $id = (int) $post;
        return $wp_posts_storage[$id] ?? null;
    }
}

if (!function_exists('get_permalink')) {
    /**
     * @param mixed $post
     * @return string
     */
    function get_permalink($post = 0): string
    {
        $id = is_object($post) ? (int) ($post->ID ?? 0) : (int) $post;
        return 'https://example.com/?p=' . $id;
    }
}

if (!function_exists('get_post_meta')) {
    /**
     * @param int $post_id
     * @param string $key
     * @param bool $single
     * @return mixed
     */
    function get_post_meta(int $post_id, string $key = '', bool $single = false)
    {
        global $wp_post_meta;
        if (!isset($wp_post_meta[$post_id])) {
            return $single ? '' : [];
        }
        if ($key === '') {
            return $wp_post_meta[$post_id];
        }
        if (!isset($wp_post_meta[$post_id][$key])) {
            return $single ? '' : [];
        }
        return $single ? $wp_post_meta[$post_id][$key] : [$wp_post_meta[$post_id][$key]];
    }
}

if (!function_exists('add_rewrite_endpoint')) {
    function add_rewrite_endpoint(string $name, int $places): void
    {
        global $wp_rewrite_endpoints;
        $wp_rewrite_endpoints[$name] = $places;
    }
}

if (!function_exists('wp_safe_redirect')) {
    function wp_safe_redirect(string $location, int $status = 302): bool
    {
        global $wp_redirect_calls;
        $wp_redirect_calls[] = ['location' => $location, 'status' => $status];
        return true;
    }
}

if (!function_exists('user_trailingslashit')) {
    function user_trailingslashit(string $url): string
    {
        return rtrim($url, '/') . '/';
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg(...$args): string
    {
        if (count($args) === 2) {
            $key = $args[0];
            $url = $args[1];
            return $url . (strpos($url, '?') !== false ? '&' : '?') . $key;
        }
        if (count($args) === 3) {
            $key = $args[0];
            $val = $args[1];
            $url = $args[2];
            return $url . (strpos($url, '?') !== false ? '&' : '?') . $key . '=' . $val;
        }
        return '';
    }
}

if (!function_exists('is_singular')) {
    function is_singular($post_types = ''): bool
    {
        global $wp_is_singular;
        return $wp_is_singular ?? true;
    }
}

if (!function_exists('get_queried_object')) {
    function get_queried_object()
    {
        global $wp_queried_object;
        return $wp_queried_object ?? null;
    }
}

if (!function_exists('esc_url')) {
    function esc_url(string $url): string
    {
        return $url;
    }
}

if (!function_exists('home_url')) {
    function home_url(string $path = ''): string
    {
        $base = 'https://example.com';
        return $path !== '' ? rtrim($base, '/') . '/' . ltrim($path, '/') : $base;
    }
}

if (!function_exists('wp_parse_url')) {
    /**
     * @param string $url
     * @param int $component
     * @return mixed
     */
    function wp_parse_url(string $url, int $component = -1)
    {
        return parse_url($url, $component);
    }
}
