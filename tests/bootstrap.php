<?php

declare(strict_types=1);

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Fallback PSR-4 autoloader for tests when vendor/autoload might not have generated autoload-dev
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

// WordPress mock functions for unit tests
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

if (!function_exists('add_filter')) {
    function add_filter(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        global $wp_filter;
        $wp_filter[$tag][] = $callback;
    }
}

if (!function_exists('remove_all_filters')) {
    function remove_all_filters(string $tag): void
    {
        global $wp_filter;
        unset($wp_filter[$tag]);
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
