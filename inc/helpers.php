<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_get_theme_file_path($relative_path)
{
    $relative_path = ltrim((string) $relative_path, '/');
    $stylesheet_path = trailingslashit(get_stylesheet_directory()) . $relative_path;

    if (file_exists($stylesheet_path)) {
        return $stylesheet_path;
    }

    $template_path = trailingslashit(get_template_directory()) . $relative_path;

    return file_exists($template_path) ? $template_path : '';
}

function glenmark_get_theme_file_uri($relative_path)
{
    $relative_path = ltrim((string) $relative_path, '/');
    $stylesheet_path = trailingslashit(get_stylesheet_directory()) . $relative_path;

    if (file_exists($stylesheet_path)) {
        return trailingslashit(get_stylesheet_directory_uri()) . $relative_path;
    }

    $template_path = trailingslashit(get_template_directory()) . $relative_path;

    return file_exists($template_path) ? trailingslashit(get_template_directory_uri()) . $relative_path : '';
}

function glenmark_attachment_is_svg($attachment_id)
{
    $attachment_id = (int) $attachment_id;

    if ($attachment_id <= 0) {
        return false;
    }

    $mime_type = strtolower((string) get_post_mime_type($attachment_id));

    if (false !== strpos($mime_type, 'svg')) {
        return true;
    }

    $attached_file = get_attached_file($attachment_id);

    if (is_string($attached_file) && '' !== $attached_file) {
        $filetype = wp_check_filetype($attached_file);
        $ext = strtolower((string) ($filetype['ext'] ?? ''));

        if ('svg' === $ext || 'svgz' === $ext) {
            return true;
        }

        $path_ext = strtolower((string) pathinfo($attached_file, PATHINFO_EXTENSION));

        if ('svg' === $path_ext || 'svgz' === $path_ext) {
            return true;
        }
    }

    $attachment_url = wp_get_attachment_url($attachment_id);

    if (is_string($attachment_url) && '' !== $attachment_url) {
        $url_path = wp_parse_url($attachment_url, PHP_URL_PATH);
        $url_ext = strtolower((string) pathinfo((string) $url_path, PATHINFO_EXTENSION));

        if ('svg' === $url_ext || 'svgz' === $url_ext) {
            return true;
        }
    }

    return false;
}

function glenmark_scope_inline_svg_markup($svg_markup, $unique_key)
{
    $prefix = 'glnsvg' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $unique_key) . '-';

    // Illustrator exports reuse generic ids (SVGID_1_...) and classes (.st0...) across files;
    // when several logos are inlined on the same page these collide, so make them unique.
    if (preg_match_all('/\bid=(["\'])([^"\']+)\1/i', $svg_markup, $id_matches)) {
        foreach (array_unique($id_matches[2]) as $id) {
            if ('' === trim($id)) {
                continue;
            }

            $new_id = $prefix . $id;
            $quoted_id = preg_quote($id, '/');

            $svg_markup = preg_replace('/\bid=(["\'])' . $quoted_id . '\1/', 'id="' . $new_id . '"', $svg_markup);
            $svg_markup = preg_replace('/url\((["\']?)#' . $quoted_id . '\1\)/', 'url(#' . $new_id . ')', $svg_markup);
            $svg_markup = preg_replace('/((?:xlink:)?href)=(["\'])#' . $quoted_id . '\2/', '$1="#' . $new_id . '"', $svg_markup);
        }
    }

    if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $svg_markup, $style_matches)) {
        foreach ($style_matches[1] as $style_content) {
            if (!preg_match_all('/\.([a-zA-Z0-9_-]+)\s*\{/', $style_content, $class_matches)) {
                continue;
            }

            foreach (array_unique($class_matches[1]) as $class_name_in_style) {
                $new_class = $prefix . $class_name_in_style;
                $quoted_class = preg_quote($class_name_in_style, '/');

                $svg_markup = preg_replace('/\.' . $quoted_class . '(?=[\s,{.:])/', '.' . $new_class, $svg_markup);
                $svg_markup = preg_replace_callback(
                    '/\bclass=(["\'])([^"\']*)\1/',
                    function ($m) use ($class_name_in_style, $new_class) {
                        $classes = preg_split('/\s+/', trim($m[2]));
                        $classes = array_map(function ($c) use ($class_name_in_style, $new_class) {
                            return $c === $class_name_in_style ? $new_class : $c;
                        }, $classes);

                        return 'class="' . implode(' ', $classes) . '"';
                    },
                    $svg_markup
                );
            }
        }
    }

    return $svg_markup;
}

function glenmark_get_inline_svg_logo_html($attachment_id, $class_name = '', $alt_text = '')
{
    $attachment_id = (int) $attachment_id;

    if ($attachment_id <= 0) {
        return '';
    }

    $svg_path = get_attached_file($attachment_id);

    $svg_markup = '';

    if (is_string($svg_path) && '' !== $svg_path && is_readable($svg_path)) {
        $svg_file_content = file_get_contents($svg_path);

        if (is_string($svg_file_content)) {
            $svg_markup = $svg_file_content;
        }
    }

    if ('' === trim($svg_markup)) {
        $svg_url = wp_get_attachment_url($attachment_id);

        if (is_string($svg_url) && '' !== $svg_url && function_exists('wp_remote_get')) {
            $response = wp_remote_get($svg_url, [
                'timeout' => 5,
            ]);

            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);

                if (is_string($body)) {
                    $svg_markup = $body;
                }
            }
        }
    }

    if (!is_string($svg_markup) || '' === trim($svg_markup)) {
        return '';
    }

    // Some exported logos are UTF-16; convert to UTF-8 so string checks and regex work.
    if (0 === strpos($svg_markup, "\xFF\xFE") || 0 === strpos($svg_markup, "\xFE\xFF")) {
        if (function_exists('mb_convert_encoding')) {
            $svg_markup = (string) mb_convert_encoding($svg_markup, 'UTF-8', 'UTF-16');
        } elseif (function_exists('iconv')) {
            $converted = @iconv('UTF-16', 'UTF-8//IGNORE', $svg_markup);

            if (is_string($converted)) {
                $svg_markup = $converted;
            }
        }
    } elseif (preg_match('/<\?xml[^>]*encoding=["\']([^"\']+)["\']/i', $svg_markup, $encoding_match)) {
        $source_encoding = strtoupper(trim((string) $encoding_match[1]));

        if ('UTF-8' !== $source_encoding) {
            if (function_exists('mb_convert_encoding')) {
                $svg_markup = (string) mb_convert_encoding($svg_markup, 'UTF-8', $source_encoding);
            } elseif (function_exists('iconv')) {
                $converted = @iconv($source_encoding, 'UTF-8//IGNORE', $svg_markup);

                if (is_string($converted)) {
                    $svg_markup = $converted;
                }
            }
        }
    }

    $class_name = trim((string) $class_name);
    $svg_markup = preg_replace('/<\?xml.*?\?>/i', '', $svg_markup);
    $svg_markup = preg_replace('/<!DOCTYPE.*?>/i', '', $svg_markup);
    $svg_markup = trim((string) $svg_markup);

    if ('' === $svg_markup || false === stripos($svg_markup, '<svg')) {
        return '';
    }

    $svg_markup = glenmark_scope_inline_svg_markup($svg_markup, $attachment_id);

    $classes = trim('custom-logo ' . $class_name);
    $aria_label = trim((string) $alt_text);

    if ('' === $aria_label) {
        $aria_label = get_bloginfo('name');
    }

    if (preg_match('/<svg\b[^>]*\bclass=("|\')(.*?)\1/i', $svg_markup, $matches)) {
        $existing = trim((string) $matches[2]);
        $combined = trim($existing . ' ' . $classes);
        $svg_markup = preg_replace(
            '/(<svg\b[^>]*\bclass=("|\'))(.*?)(\2)/i',
            '$1' . esc_attr($combined) . '$4',
            $svg_markup,
            1
        );
    } else {
        $svg_markup = preg_replace('/<svg\b/i', '<svg class="' . esc_attr($classes) . '"', $svg_markup, 1);
    }

    if (false === stripos($svg_markup, 'aria-label=')) {
        $svg_markup = preg_replace('/<svg\b/i', '<svg role="img" aria-label="' . esc_attr($aria_label) . '"', $svg_markup, 1);
    }

    if (false === stripos($svg_markup, 'focusable=')) {
        $svg_markup = preg_replace('/<svg\b/i', '<svg focusable="false"', $svg_markup, 1);
    }

    // Keep inline SVG behavior, but strip known unsafe patterns.
    $svg_markup = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $svg_markup);
    $svg_markup = preg_replace('/<foreignObject\b[^>]*>.*?<\/foreignObject>/is', '', $svg_markup);
    $svg_markup = preg_replace('/\son[a-z0-9_-]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $svg_markup);
    $svg_markup = preg_replace('/\s(xlink:href|href)\s*=\s*("|\')\s*javascript:[^\2]*\2/i', '', $svg_markup);

    return is_string($svg_markup) ? trim($svg_markup) : '';
}

function glenmark_get_logo_image_html($attachment_id, $class_name = '')
{
    $attachment_id = (int) $attachment_id;

    if ($attachment_id <= 0) {
        return '';
    }

    $class_name = trim((string) $class_name);

    if (glenmark_attachment_is_svg($attachment_id)) {

        $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));

        if ('' === $alt) {
            $alt = get_bloginfo('name');
        }

        $inline_svg = glenmark_get_inline_svg_logo_html($attachment_id, $class_name, $alt);

        if ('' !== $inline_svg) {
            return $inline_svg;
        }

        $src = wp_get_attachment_url($attachment_id);

        if (is_string($src) && '' !== $src) {
            $classes = trim('custom-logo ' . $class_name);

            return sprintf(
                '<img src="%1$s" class="%2$s" alt="%3$s" loading="eager" decoding="async">',
                esc_url($src),
                esc_attr($classes),
                esc_attr($alt)
            );
        }

        return '';
    }

    return wp_get_attachment_image($attachment_id, 'full', false, [
        'class' => trim('custom-logo ' . $class_name),
        'loading' => 'eager',
    ]);
}

function glenmark_get_logo_attachment_ids()
{
    $ids = [
        (int) glenmark_get_logo_default_id(),
        (int) glenmark_get_logo_transparent_id(),
        (int) get_theme_mod('custom_logo'),
    ];

    $ids = array_map('absint', $ids);
    $ids = array_values(array_filter($ids));

    return array_values(array_unique($ids));
}

function glenmark_maybe_inline_svg_attachment_image($html, $attachment_id, $size, $icon, $attr)
{
    if (is_admin()) {
        return $html;
    }

    $attachment_id = (int) $attachment_id;

    if ($attachment_id <= 0 || !glenmark_attachment_is_svg($attachment_id)) {
        return $html;
    }

    if (!in_array($attachment_id, glenmark_get_logo_attachment_ids(), true)) {
        return $html;
    }

    $class_name = '';
    $alt = '';

    if (is_array($attr)) {
        $class_name = isset($attr['class']) ? trim((string) $attr['class']) : '';
        $alt = isset($attr['alt']) ? trim((string) $attr['alt']) : '';
    }

    if ('' === $alt) {
        $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
    }

    if ('' === $alt) {
        $alt = get_bloginfo('name');
    }

    $inline_svg = glenmark_get_inline_svg_logo_html($attachment_id, $class_name, $alt);

    return '' !== $inline_svg ? $inline_svg : $html;
}
add_filter('wp_get_attachment_image', 'glenmark_maybe_inline_svg_attachment_image', 10, 5);

function glenmark_get_template_part($slug, $name = null, $args = [])
{
    get_template_part($slug, $name, $args);
}

function glenmark_primary_menu_fallback()
{
    echo '<ul class="nav-menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Uvod', 'gln-pharma-product') . '</a></li>';
    echo '</ul>';
}

function glenmark_is_built_with_elementor($post_id = null)
{
    $post_id = $post_id ? (int) $post_id : get_the_ID();

    if ($post_id <= 0) {
        return false;
    }

    if (did_action('elementor/loaded') && class_exists('Elementor\\Plugin')) {
        $document = \Elementor\Plugin::$instance->documents->get($post_id);

        if ($document && method_exists($document, 'is_built_with_elementor')) {
            return (bool) $document->is_built_with_elementor();
        }
    }

    if (!empty(get_post_meta($post_id, '_elementor_edit_mode', true))) {
        return true;
    }

    $elementor_data = get_post_meta($post_id, '_elementor_data', true);

    if (is_string($elementor_data)) {
        $trimmed = trim($elementor_data);
        return '' !== $trimmed && '[]' !== $trimmed;
    }

    return !empty($elementor_data);
}

function glenmark_get_favicon_custom_url()
{
    $favicon_id = (int) glenmark_get_setting('favicon', 0);

    if ($favicon_id <= 0) {
        return '';
    }

    $url = wp_get_attachment_url($favicon_id);

    return is_string($url) ? $url : '';
}

function glenmark_locate_icon($filename)
{
    return glenmark_get_theme_file_uri('assets/icon/' . ltrim((string) $filename, '/'));
}

function glenmark_print_icon_tags()
{
    $favicon_96 = glenmark_locate_icon('favicon-96x96.png');
    $favicon_svg = glenmark_locate_icon('favicon.svg');
    $favicon_ico = glenmark_locate_icon('favicon.ico');
    $apple_touch = glenmark_locate_icon('apple-touch-icon.png');
    $manifest = glenmark_locate_icon('site.webmanifest');

    if (!empty($favicon_96)) {
        echo '<link rel="icon" type="image/png" href="' . esc_url($favicon_96) . '" sizes="96x96" />' . "\n";
    }

    if (!empty($favicon_svg)) {
        echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($favicon_svg) . '" />' . "\n";
    }

    if (!empty($favicon_ico)) {
        echo '<link rel="shortcut icon" href="' . esc_url($favicon_ico) . '" />' . "\n";
    }

    if (!empty($apple_touch)) {
        echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url($apple_touch) . '" />' . "\n";
    }

    echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";

    if (!empty($manifest)) {
        echo '<link rel="manifest" href="' . esc_url($manifest) . '" />' . "\n";
    }
}
add_action('wp_head', 'glenmark_print_icon_tags', 1);