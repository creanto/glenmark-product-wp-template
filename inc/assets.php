<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_enqueue_theme_assets()
{
    $theme = wp_get_theme();
    $version = $theme->get('Version') ?: '1.0.0';
    $theme_css_path = webrev_get_theme_file_path('assets/css/theme.css');
    $theme_css_uri = webrev_get_theme_file_uri('assets/css/theme.css');

    if ('open sans' === strtolower((string) webrev_config('typography.font_family', ''))) {
        wp_enqueue_style(
            'webrev-open-sans',
            'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap',
            [],
            null
        );
    }

    if (!empty($theme_css_path)) {
        $file_mtime = filemtime($theme_css_path);

        if (false !== $file_mtime) {
            $version = (string) $file_mtime;
        }
    }

    if (!empty($theme_css_uri)) {
        wp_enqueue_style('webrev-theme', $theme_css_uri, [], $version);
    }

    $slick_css_uri = webrev_get_theme_file_uri('assets/slick/slick.css');
    if (!empty($slick_css_uri)) {
        wp_enqueue_style('webrev-slick', $slick_css_uri, [], $version);
    }

    $slick_theme_css_uri = webrev_get_theme_file_uri('assets/slick/slick-theme.css');
    if (!empty($slick_theme_css_uri)) {
        wp_enqueue_style('webrev-slick-theme', $slick_theme_css_uri, ['webrev-slick'], $version);
    }

    $magnific_css_uri = webrev_get_theme_file_uri('assets/magnific/magnific-popup.css');
    if (!empty($magnific_css_uri)) {
        wp_enqueue_style('webrev-magnific', $magnific_css_uri, [], $version);
    }

    $header_height = (int) webrev_get_setting('header_height', 88);
    $header_shrink_height = (int) webrev_get_setting('header_shrink_height', 68);
    $inline_css = ':root{--wr-header-height:' . esc_attr((string) $header_height) . 'px;--wr-header-height-shrunk:' . esc_attr((string) $header_shrink_height) . 'px;}';

    $colors = webrev_config('colors', []);
    $color_vars = '';

    foreach (['blue' => 'primary', 'blue-dark' => 'secondary', 'red' => 'accent', 'white' => 'white', 'bg' => 'bg'] as $css_var => $config_key) {
        if (!empty($colors[$config_key])) {
            $color_vars .= '--' . $css_var . ':' . esc_attr($colors[$config_key]) . ';';
        }
    }

    if ('' !== $color_vars) {
        $inline_css .= ':root{' . $color_vars . '}';
    }

    wp_add_inline_style('webrev-theme', $inline_css);

    $theme_js_path = webrev_get_theme_file_path('assets/js/theme.js');
    $theme_js_uri = webrev_get_theme_file_uri('assets/js/theme.js');
    $theme_js_version = $version;

    if (!empty($theme_js_path)) {
        $theme_js_mtime = filemtime($theme_js_path);

        if (false !== $theme_js_mtime) {
            $theme_js_version = (string) $theme_js_mtime;
        }
    }

    $slick_js_uri = webrev_get_theme_file_uri('assets/slick/slick.min.js');
    if (!empty($slick_js_uri)) {
        wp_enqueue_script('webrev-slick', $slick_js_uri, ['jquery'], $version, true);
    }

    $magnific_js_uri = webrev_get_theme_file_uri('assets/magnific/jquery.magnific-popup.min.js');
    if (!empty($magnific_js_uri)) {
        wp_enqueue_script('webrev-magnific', $magnific_js_uri, ['jquery'], $version, true);
    }

    if (!empty($theme_js_uri)) {
        wp_enqueue_script('webrev-theme', $theme_js_uri, ['jquery', 'webrev-slick', 'webrev-magnific'], $theme_js_version, true);
    }

    $nbsp_fix_path = webrev_get_theme_file_path('assets/js/nbsp-fix.js');
    $nbsp_fix_uri = webrev_get_theme_file_uri('assets/js/nbsp-fix.js');
    $nbsp_fix_version = $version;

    if (!empty($nbsp_fix_path)) {
        $nbsp_fix_mtime = filemtime($nbsp_fix_path);

        if (false !== $nbsp_fix_mtime) {
            $nbsp_fix_version = (string) $nbsp_fix_mtime;
        }
    }

    if (!empty($nbsp_fix_uri)) {
        wp_enqueue_script('webrev-nbsp-fix', $nbsp_fix_uri, [], $nbsp_fix_version, true);
    }
}
add_action('wp_enqueue_scripts', 'webrev_enqueue_theme_assets');