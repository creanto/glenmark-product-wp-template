<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_get_theme_config()
{
    static $config = null;

    if (null === $config) {
        $parent_path = get_template_directory() . '/config/theme-config.php';
        $config = file_exists($parent_path) ? (array) require $parent_path : [];

        $site_config_path = get_stylesheet_directory() . '/site-specific/config/theme-config.php';

        if (file_exists($site_config_path)) {
            $site_config = require $site_config_path;

            if (is_array($site_config)) {
                $config = array_replace_recursive($config, $site_config);
            }
        }
    }

    return $config;
}

// Reads a dot-notated path, e.g. webrev_config('layout.header_variants.default.label').
function webrev_config($dot_path, $default = null)
{
    $value = webrev_get_theme_config();

    foreach (explode('.', $dot_path) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}
