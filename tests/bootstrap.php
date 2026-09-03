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
        public string $post_modified_time = '';
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

if (!class_exists('WP_Post_Type')) {
    class WP_Post_Type
    {
        public string $name = '';
        public string $label = '';
        /** @var object */
        public $labels;
        public bool $public = true;

        /**
         * @param string $name
         * @param array<string, mixed> $args
         */
        public function __construct(string $name, array $args = [])
        {
            $this->name = $name;
            $this->label = $args['label'] ?? ucfirst($name);
            $this->labels = is_object($args['labels'] ?? null)
                ? $args['labels']
                : (object) ($args['labels'] ?? ['singular_name' => $this->label, 'name' => $this->label]);
            $this->public = $args['public'] ?? true;
            foreach ($args as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
}

// Global state initialization
global $wp_filter, $wp_actions, $wp_options, $wp_post_meta, $wp_rewrite_endpoints, $wp_redirect_calls, $wp_is_singular, $wp_is_front_page, $wp_is_home, $wp_queried_object, $wp_posts_storage, $wp_terms_storage, $wp_taxonomies_storage, $wp_comments_storage, $wp_query, $wp_enqueued_styles, $wp_enqueued_scripts, $wp_current_screen, $wp_meta_boxes, $wp_post_revisions_storage, $wp_current_user_capabilities, $wp_menu_pages, $wp_submenu_pages, $wp_registered_settings, $wp_rewrite_rules_flushed, $wp_post_types_storage;

$wp_filter = [];
$wp_actions = [];
$wp_options = [];
$wp_post_meta = [];
$wp_rewrite_endpoints = [];
$wp_redirect_calls = [];
$wp_is_singular = true;
$wp_is_front_page = false;
$wp_is_home = false;
$wp_queried_object = null;
$wp_posts_storage = [];
$wp_terms_storage = [];
$wp_taxonomies_storage = [];
$wp_comments_storage = [];
$wp_query = new WP_Query();
$wp_enqueued_styles = [];
$wp_enqueued_scripts = [];
$wp_current_screen = null;
$wp_meta_boxes = [];
$wp_post_revisions_storage = [];
$wp_current_user_capabilities = [];
$wp_menu_pages = [];
$wp_submenu_pages = [];
$wp_registered_settings = [];
$wp_rewrite_rules_flushed = false;
$wp_post_types_storage = [];

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

if (!function_exists('has_filter')) {
    /**
     * @param string $tag
     * @param mixed $callback
     * @return bool|int
     */
    function has_filter(string $tag, $callback = false)
    {
        global $wp_filter;

        if (!isset($wp_filter[$tag]) || empty($wp_filter[$tag])) {
            return false;
        }

        if ($callback === false) {
            return true;
        }

        return in_array($callback, $wp_filter[$tag], true);
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

if (!function_exists('get_post_types')) {
    /**
     * @param array<string, mixed> $args
     * @param string $output 'names' or 'objects'
     * @param string $operator 'and' or 'or' or 'not'
     * @return array<string, string>|array<string, \WP_Post_Type>
     */
    function get_post_types(array $args = [], string $output = 'names', string $operator = 'and'): array
    {
        global $wp_post_types_storage;

        if (!is_array($wp_post_types_storage) || empty($wp_post_types_storage)) {
            $wp_post_types_storage = [
                'post' => new \WP_Post_Type('post', [
                    'label' => 'Posts',
                    'public' => true,
                    'labels' => (object) ['singular_name' => 'Post', 'name' => 'Posts'],
                ]),
                'page' => new \WP_Post_Type('page', [
                    'label' => 'Pages',
                    'public' => true,
                    'labels' => (object) ['singular_name' => 'Page', 'name' => 'Pages'],
                ]),
                'attachment' => new \WP_Post_Type('attachment', [
                    'label' => 'Media',
                    'public' => true,
                    'labels' => (object) ['singular_name' => 'Media', 'name' => 'Media'],
                ]),
            ];
        }

        $filtered = [];
        foreach ($wp_post_types_storage as $name => $postType) {
            $match = true;
            if (!empty($args)) {
                foreach ($args as $key => $value) {
                    $prop = $postType->{$key} ?? null;
                    if ($operator === 'and' && $prop !== $value) {
                        $match = false;
                        break;
                    }
                    if ($operator === 'not' && $prop === $value) {
                        $match = false;
                        break;
                    }
                }
            }
            if ($match) {
                $filtered[$name] = $output === 'objects' ? $postType : $name;
            }
        }

        return $filtered;
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

if (!function_exists('get_the_modified_time')) {
    function get_the_modified_time(string $format = '', $post = null): string
    {
        $p = $post instanceof \WP_Post ? $post : get_post($post);
        return $p ? ($p->post_modified_time ?? $p->post_time ?? '') : '';
    }
}

if (!function_exists('mysql2date')) {
    /**
     * @param string $format
     * @param string $date
     * @param bool   $translate
     * @return string|int|false
     */
    function mysql2date(string $format, string $date, bool $translate = true)
    {
        if (empty($date)) {
            return false;
        }

        if ($format === 'G') {
            return strtotime($date . ' UTC');
        }

        if ($format === 'U') {
            return strtotime($date);
        }

        $datetime = date_create($date);
        if (!$datetime) {
            return false;
        }

        return $datetime->format($format);
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
        global $wp_taxonomies_storage, $wp_terms_storage;

        if (in_array($taxonomy, ['category', 'post_tag', 'genre', 'topic', 'series'], true)) {
            return true;
        }

        if (isset($wp_taxonomies_storage) && is_array($wp_taxonomies_storage)) {
            foreach ($wp_taxonomies_storage as $taxList) {
                if (is_array($taxList) && in_array($taxonomy, $taxList, true)) {
                    return true;
                }
            }
        }

        if (isset($wp_terms_storage) && is_array($wp_terms_storage)) {
            foreach ($wp_terms_storage as $postTerms) {
                if (is_array($postTerms) && isset($postTerms[$taxonomy])) {
                    return true;
                }
            }
        }

        return false;
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

if (!function_exists('get_term_link')) {
    /**
     * @param object|int|string $term
     * @param string            $taxonomy
     * @return string|\WP_Error
     */
    function get_term_link($term, string $taxonomy = '')
    {
        $slug = is_object($term) ? ($term->slug ?? sanitize_title((string) ($term->name ?? 'term'))) : (string) $term;
        $tax = is_object($term) ? ($term->taxonomy ?? $taxonomy) : $taxonomy;
        $tax = !empty($tax) ? $tax : 'category';
        return 'https://example.com/' . $tax . '/' . $slug . '/';
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

if (!function_exists('update_post_meta')) {
    /**
     * @param int $post_id
     * @param string $meta_key
     * @param mixed $meta_value
     * @param mixed $prev_value
     * @return bool
     */
    function update_post_meta(int $post_id, string $meta_key, $meta_value, $prev_value = ''): bool
    {
        global $wp_post_meta;
        if (!isset($wp_post_meta[$post_id])) {
            $wp_post_meta[$post_id] = [];
        }
        $wp_post_meta[$post_id][$meta_key] = $meta_value;
        return true;
    }
}

if (!function_exists('delete_post_meta')) {
    /**
     * @param int $post_id
     * @param string $meta_key
     * @param mixed $meta_value
     * @return bool
     */
    function delete_post_meta(int $post_id, string $meta_key, $meta_value = ''): bool
    {
        global $wp_post_meta;
        if (isset($wp_post_meta[$post_id][$meta_key])) {
            unset($wp_post_meta[$post_id][$meta_key]);
            return true;
        }
        return false;
    }
}

if (!function_exists('add_meta_box')) {
    /**
     * @param string $id
     * @param string $title
     * @param callable $callback
     * @param mixed $screen
     * @param string $context
     * @param string $priority
     * @param mixed $callback_args
     * @return void
     */
    function add_meta_box(string $id, string $title, $callback, $screen = null, string $context = 'advanced', string $priority = 'default', $callback_args = null): void
    {
        global $wp_meta_boxes;
        if (!is_array($wp_meta_boxes)) {
            $wp_meta_boxes = [];
        }
        $wp_meta_boxes[$id] = [
            'id' => $id,
            'title' => $title,
            'callback' => $callback,
            'screen' => $screen,
            'context' => $context,
            'priority' => $priority,
            'callback_args' => $callback_args,
        ];
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1): string
    {
        return 'test_nonce_' . (string) $action;
    }
}

if (!function_exists('wp_verify_nonce')) {
    /**
     * @param mixed $nonce
     * @param mixed $action
     * @return bool
     */
    function wp_verify_nonce($nonce, $action = -1): bool
    {
        if (!is_string($nonce) || $nonce === '') {
            return false;
        }
        return $nonce === 'test_nonce_' . (string) $action || $nonce === 'valid_nonce';
    }
}

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, string $name = '_wpnonce', bool $referer = true, bool $echo = true): string
    {
        $nonce = wp_create_nonce($action);
        $field = '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($nonce) . '" />';
        if ($echo) {
            echo $field;
        }
        return $field;
    }
}

if (!function_exists('wp_is_post_revision')) {
    /**
     * @param mixed $post
     * @return mixed
     */
    function wp_is_post_revision($post)
    {
        global $wp_post_revisions_storage;
        $postId = is_object($post) && isset($post->ID) ? (int) $post->ID : (int) $post;
        return $wp_post_revisions_storage[$postId] ?? false;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can(string $capability, ...$args): bool
    {
        global $wp_current_user_capabilities;
        if (isset($wp_current_user_capabilities[$capability])) {
            return (bool) $wp_current_user_capabilities[$capability];
        }
        return true;
    }
}

if (!function_exists('wp_unslash')) {
    /**
     * @param mixed $value
     * @return mixed
     */
    function wp_unslash($value)
    {
        return is_string($value) ? stripslashes($value) : $value;
    }
}

if (!function_exists('checked')) {
    function checked($checked, $current = true, bool $echo = true): string
    {
        $result = ((string) $checked === (string) $current) ? " checked='checked'" : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

if (!function_exists('add_menu_page')) {
    function add_menu_page(string $page_title, string $menu_title, string $capability, string $menu_slug, $callback = '', string $icon_url = '', $position = null): string
    {
        global $wp_menu_pages;
        if (!is_array($wp_menu_pages)) {
            $wp_menu_pages = [];
        }
        $wp_menu_pages[$menu_slug] = [
            'page_title' => $page_title,
            'menu_title' => $menu_title,
            'capability' => $capability,
            'menu_slug' => $menu_slug,
            'callback' => $callback,
            'icon_url' => $icon_url,
            'position' => $position,
        ];
        return $menu_slug;
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page(string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, $callback = '', $position = null): string
    {
        global $wp_submenu_pages;
        if (!is_array($wp_submenu_pages)) {
            $wp_submenu_pages = [];
        }
        if (!isset($wp_submenu_pages[$parent_slug])) {
            $wp_submenu_pages[$parent_slug] = [];
        }
        $wp_submenu_pages[$parent_slug][$menu_slug] = [
            'parent_slug' => $parent_slug,
            'page_title' => $page_title,
            'menu_title' => $menu_title,
            'capability' => $capability,
            'menu_slug' => $menu_slug,
            'callback' => $callback,
            'position' => $position,
        ];
        return $menu_slug;
    }
}

if (!function_exists('register_setting')) {
    function register_setting(string $option_group, string $option_name, array $args = []): void
    {
        global $wp_registered_settings;
        if (!is_array($wp_registered_settings)) {
            $wp_registered_settings = [];
        }
        if (!isset($wp_registered_settings[$option_group])) {
            $wp_registered_settings[$option_group] = [];
        }
        $wp_registered_settings[$option_group][$option_name] = $args;
    }
}

if (!function_exists('admin_url')) {
    function admin_url(string $path = '', string $scheme = 'admin'): string
    {
        $base = 'https://example.com/wp-admin/';
        return $path !== '' ? $base . ltrim($path, '/') : $base;
    }
}

if (!function_exists('flush_rewrite_rules')) {
    function flush_rewrite_rules(bool $hard = true): void
    {
        global $wp_rewrite_rules_flushed;
        $wp_rewrite_rules_flushed = true;
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

if (!function_exists('get_query_var')) {
    /**
     * @param string $var
     * @param mixed  $default
     * @return mixed
     */
    function get_query_var(string $var, $default = '')
    {
        global $wp_query;
        if (isset($wp_query->query_vars[$var])) {
            return $wp_query->query_vars[$var];
        }
        return $default;
    }
}

if (!function_exists('is_singular')) {
    function is_singular($post_types = ''): bool
    {
        global $wp_is_singular;
        return $wp_is_singular ?? true;
    }
}

if (!function_exists('is_front_page')) {
    function is_front_page(): bool
    {
        global $wp_is_front_page;
        return $wp_is_front_page ?? false;
    }
}

if (!function_exists('is_home')) {
    function is_home(): bool
    {
        global $wp_is_home;
        return $wp_is_home ?? false;
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

if (!function_exists('esc_html')) {
    /**
     * @param mixed $text
     * @return string
     */
    function esc_html($text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html_e')) {
    /**
     * @param mixed $text
     * @param string $domain
     * @return void
     */
    function esc_html_e($text, string $domain = 'default'): void
    {
        echo esc_html($text);
    }
}

if (!function_exists('esc_html__')) {
    /**
     * @param mixed $text
     * @param string $domain
     * @return string
     */
    function esc_html__($text, string $domain = 'default'): string
    {
        return esc_html($text);
    }
}

if (!function_exists('esc_attr')) {
    /**
     * @param mixed $text
     * @return string
     */
    function esc_attr($text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr_e')) {
    /**
     * @param mixed $text
     * @param string $domain
     * @return void
     */
    function esc_attr_e($text, string $domain = 'default'): void
    {
        echo esc_attr($text);
    }
}

if (!function_exists('esc_attr__')) {
    /**
     * @param mixed $text
     * @param string $domain
     * @return string
     */
    function esc_attr__($text, string $domain = 'default'): string
    {
        return esc_attr($text);
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

if (!function_exists('sanitize_text_field')) {
    /**
     * @param mixed $str
     * @return string
     */
    function sanitize_text_field($str): string
    {
        $filtered = is_scalar($str) ? (string) $str : '';
        $filtered = preg_replace('/[\r\n\t ]+/', ' ', strip_tags($filtered));
        return trim($filtered ?? '');
    }
}

if (!function_exists('sanitize_textarea_field')) {
    /**
     * @param mixed $str
     * @return string
     */
    function sanitize_textarea_field($str): string
    {
        $filtered = is_scalar($str) ? (string) $str : '';
        return trim(strip_tags($filtered));
    }
}

if (!function_exists('wp_kses_post')) {
    /**
     * @param mixed $data
     * @return string
     */
    function wp_kses_post($data): string
    {
        return is_scalar($data) ? (string) $data : '';
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url(string $path = '', string $plugin = ''): string
    {
        return 'https://example.com/wp-content/plugins/iz-md-pages/' . ltrim($path, '/');
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename(string $file): string
    {
        $file = str_replace('\\', '/', $file);
        $pluginDir = str_replace('\\', '/', dirname(__DIR__));

        if (strpos($file, $pluginDir) === 0) {
            $relative = ltrim(substr($file, strlen($pluginDir)), '/');
            return basename(dirname($file)) . '/' . basename($file);
        }

        return basename(dirname($file)) . '/' . basename($file);
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

if (!function_exists('has_blocks')) {
    /**
     * @param string|\WP_Post|null $post
     * @return bool
     */
    function has_blocks($post = null): bool
    {
        if ($post instanceof \WP_Post) {
            $content = (string) $post->post_content;
        } else {
            $content = (string) $post;
        }
        return strpos($content, '<!-- wp:') !== false;
    }
}

if (!function_exists('parse_blocks')) {
    /**
     * @param string $content
     * @return array<int, array<string, mixed>>
     */
    function parse_blocks(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        $blocks = [];
        $pattern = '/<!--\s+wp:([a-z0-9_\/-]+)(\s+(\{[^>]*\}))?\s+-->(.*?)<!--\s+\/wp:\1\s+-->/s';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $blockName = $match[1];
                $attrs = !empty($match[3]) ? json_decode($match[3], true) : [];
                $innerHTML = $match[4];

                $blocks[] = [
                    'blockName' => $blockName,
                    'attrs' => is_array($attrs) ? $attrs : [],
                    'innerBlocks' => [],
                    'innerHTML' => $innerHTML,
                    'innerContent' => [$innerHTML],
                ];
            }
        }

        $voidPattern = '/<!--\s+wp:([a-z0-9_\/-]+)(\s+(\{[^>]*\}))?\s+\/-->/s';
        if (preg_match_all($voidPattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $blocks[] = [
                    'blockName' => $match[1],
                    'attrs' => !empty($match[3]) ? (json_decode($match[3], true) ?: []) : [],
                    'innerBlocks' => [],
                    'innerHTML' => '',
                    'innerContent' => [],
                ];
            }
        }

        if (empty($blocks) && strpos($content, '<!-- wp:') !== false) {
            $blocks[] = [
                'blockName' => 'core/freeform',
                'attrs' => [],
                'innerBlocks' => [],
                'innerHTML' => $content,
                'innerContent' => [$content],
            ];
        }

        return $blocks;
    }
}

if (!function_exists('render_block')) {
    /**
     * @param array<string, mixed> $block
     * @return string
     */
    function render_block(array $block): string
    {
        return isset($block['innerHTML']) && is_string($block['innerHTML']) ? $block['innerHTML'] : '';
    }
}

// Load plugin global helper functions
require_once dirname(__DIR__) . '/inc/functions.php';
