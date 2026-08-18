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
        public string $thumbnail_url = '';
        public int $thumbnail_id = 0;

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

if (!class_exists('WP_Error')) {
    class WP_Error
    {
        public string $code;
        public string $message;

        public function __construct(string $code = '', string $message = '')
        {
            $this->code = $code;
            $this->message = $message;
        }
    }
}

if (!class_exists('WP_Screen')) {
    class WP_Screen
    {
        public string $id = '';
        public string $post_type = '';

        public function __construct(string $id = '', string $post_type = '')
        {
            $this->id = $id;
            $this->post_type = $post_type;
        }
    }
}

if (!class_exists('WP_Comment')) {
    class WP_Comment
    {
        public int $comment_ID = 0;
        public int $comment_post_ID = 0;
        public string $comment_author = '';
        public string $comment_author_email = '';
        public string $comment_date = '';
        public string $comment_content = '';
        public string $comment_approved = '1';
        public int $comment_parent = 0;

        public function __construct(array $data = [])
        {
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
}

// Global state initialization
global $wp_filter, $wp_actions, $wp_options, $wp_post_meta, $wp_rewrite_endpoints, $wp_redirect_calls, $wp_is_singular, $wp_queried_object, $wp_posts_storage, $wp_terms_storage, $wp_taxonomies_storage, $wp_comments_storage, $wp_query, $wp_enqueued_styles, $wp_enqueued_scripts, $wp_current_screen;

$wp_filter = [];
$wp_actions = [];
$wp_options = [];
$wp_post_meta = [];
$wp_rewrite_endpoints = [];
$wp_redirect_calls = [];
$wp_is_singular = true;
$wp_queried_object = null;
$wp_posts_storage = [];
$wp_terms_storage = [];
$wp_taxonomies_storage = [];
$wp_comments_storage = [];
$wp_query = new WP_Query();
$wp_enqueued_styles = [];
$wp_enqueued_scripts = [];
$wp_current_screen = null;

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

if (!function_exists('remove_all_filters')) {
    function remove_all_filters(string $tag): void
    {
        global $wp_filter;
        unset($wp_filter[$tag]);
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

if (!function_exists('get_the_title')) {
    /**
     * @param mixed $post
     * @return string
     */
    function get_the_title($post = 0): string
    {
        if ($post instanceof \WP_Post) {
            return $post->post_title;
        }
        $p = get_post($post);
        return $p ? $p->post_title : '';
    }
}

if (!function_exists('get_the_excerpt')) {
    /**
     * @param mixed $post
     * @return string
     */
    function get_the_excerpt($post = null): string
    {
        if ($post instanceof \WP_Post) {
            return $post->post_excerpt;
        }
        $p = get_post($post);
        return $p ? $p->post_excerpt : '';
    }
}

if (!function_exists('get_the_date')) {
    function get_the_date(string $format = '', $post = null): string
    {
        $p = $post instanceof \WP_Post ? $post : get_post($post);
        return $p ? $p->post_date : '';
    }
}

if (!function_exists('get_the_time')) {
    function get_the_time(string $format = '', $post = null): string
    {
        $p = $post instanceof \WP_Post ? $post : get_post($post);
        return $p ? $p->post_time : '';
    }
}

if (!function_exists('get_the_modified_date')) {
    function get_the_modified_date(string $format = '', $post = null): string
    {
        $p = $post instanceof \WP_Post ? $post : get_post($post);
        return $p ? $p->post_modified : '';
    }
}

if (!function_exists('get_the_post_thumbnail_url')) {
    /**
     * @param mixed $post
     * @param string $size
     * @return string|false
     */
    function get_the_post_thumbnail_url($post = null, string $size = 'post-thumbnail')
    {
        $p = $post instanceof \WP_Post ? $post : get_post($post);
        return $p && !empty($p->thumbnail_url) ? (string) $p->thumbnail_url : false;
    }
}

if (!function_exists('get_post_thumbnail_id')) {
    /**
     * @param mixed $post
     * @return int
     */
    function get_post_thumbnail_id($post = null): int
    {
        $p = $post instanceof \WP_Post ? $post : get_post($post);
        return $p && isset($p->thumbnail_id) ? (int) $p->thumbnail_id : 0;
    }
}

if (!function_exists('get_author_posts_url')) {
    function get_author_posts_url(int $author_id, string $author_nicename = ''): string
    {
        return 'https://example.com/author/' . $author_id;
    }
}

if (!function_exists('get_the_author_meta')) {
    /**
     * @param string $field
     * @param int|false $user_id
     * @return string
     */
    function get_the_author_meta(string $field = '', $user_id = false): string
    {
        $defaults = [
            'display_name' => 'John Doe',
            'user_email' => 'author@example.com',
            'description' => 'Author biography',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'nickname' => 'johndoe',
        ];
        return $defaults[$field] ?? '';
    }
}

if (!function_exists('get_object_taxonomies')) {
    /**
     * @param mixed $object
     * @param string $output
     * @return array
     */
    function get_object_taxonomies($object, string $output = 'names'): array
    {
        global $wp_taxonomies_storage;
        $postType = is_object($object) ? ($object->post_type ?? 'post') : (string) $object;
        return $wp_taxonomies_storage[$postType] ?? ['category', 'post_tag'];
    }
}

if (!function_exists('taxonomy_exists')) {
    function taxonomy_exists(string $taxonomy): bool
    {
        return in_array($taxonomy, ['category', 'post_tag', 'genre', 'topic'], true);
    }
}

if (!function_exists('get_the_terms')) {
    /**
     * @param mixed $post
     * @param string $taxonomy
     * @return array|\WP_Error|false
     */
    function get_the_terms($post, string $taxonomy)
    {
        global $wp_terms_storage;
        $id = is_object($post) ? (int) ($post->ID ?? 0) : (int) $post;
        return $wp_terms_storage[$id][$taxonomy] ?? false;
    }
}

if (!function_exists('wp_list_pluck')) {
    function wp_list_pluck(array $list, string $field): array
    {
        $result = [];
        foreach ($list as $key => $value) {
            if (is_object($value) && isset($value->{$field})) {
                $result[$key] = $value->{$field};
            } elseif (is_array($value) && isset($value[$field])) {
                $result[$key] = $value[$field];
            }
        }
        return $result;
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing): bool
    {
        return $thing instanceof \WP_Error;
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

if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (!function_exists('sanitize_key')) {
    function sanitize_key(string $key): string
    {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key)) ?: '';
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url(string $path = '', string $plugin = ''): string
    {
        return 'https://example.com/wp-content/plugins/iz-md-pages/' . ltrim($path, '/');
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(string $handle, string $src = '', array $deps = [], $ver = false, string $media = 'all'): void
    {
        global $wp_enqueued_styles;
        $wp_enqueued_styles[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'media' => $media,
        ];
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script(string $handle, string $src = '', array $deps = [], $ver = false, bool $in_footer = false): void
    {
        global $wp_enqueued_scripts;
        $wp_enqueued_scripts[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $in_footer,
        ];
    }
}

if (!function_exists('get_current_screen')) {
    function get_current_screen(): ?\WP_Screen
    {
        global $wp_current_screen;
        return $wp_current_screen;
    }
}

if (!function_exists('get_comments')) {
    /**
     * @param array<string, mixed> $args
     * @return array<int, \WP_Comment>
     */
    function get_comments(array $args = []): array
    {
        global $wp_comments_storage;
        $postId = isset($args['post_id']) ? (int) $args['post_id'] : 0;
        return $wp_comments_storage[$postId] ?? [];
    }
}

if (!function_exists('get_comment_author')) {
    /**
     * @param mixed $comment
     * @return string
     */
    function get_comment_author($comment = null): string
    {
        if ($comment instanceof \WP_Comment) {
            return $comment->comment_author;
        }
        return '';
    }
}

if (!function_exists('get_comment_date')) {
    /**
     * @param string $format
     * @param mixed $comment
     * @return string
     */
    function get_comment_date(string $format = '', $comment = null): string
    {
        if ($comment instanceof \WP_Comment) {
            return $comment->comment_date;
        }
        return '';
    }
}

if (!function_exists('get_comment_text')) {
    /**
     * @param mixed $comment
     * @return string
     */
    function get_comment_text($comment = null): string
    {
        if ($comment instanceof \WP_Comment) {
            return $comment->comment_content;
        }
        return '';
    }
}

if (!function_exists('get_comments_number')) {
    /**
     * @param int|\WP_Post $post
     * @return int
     */
    function get_comments_number($post = 0): int
    {
        global $wp_comments_storage;
        $postId = is_object($post) ? (int) ($post->ID ?? 0) : (int) $post;
        if (isset($wp_comments_storage[$postId])) {
            return count($wp_comments_storage[$postId]);
        }
        return 0;
    }
}
