<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_theme_setup()
{
    load_theme_textdomain('gln-pharma-product', get_stylesheet_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height' => 120,
        'width' => 400,
        'flex-height' => true,
        'flex-width' => true,
    ]);
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);
    add_theme_support('align-wide');

    register_nav_menus([
        'primary' => __('Primary Menu', 'gln-pharma-product'),
        'footer' => __('Footer Menu', 'gln-pharma-product'),
    ]);

    add_image_size('gln-card', 720, 540, true);
    add_image_size('gln-gallery', 640, 640, true);
    add_image_size('gln-post-banner', 1300, 520, true);
    add_image_size('gln-post-banner-large', 2500, 1000, true);
}
add_action('after_setup_theme', 'glenmark_theme_setup');

function glenmark_set_default_post_template($post_id, $post, $update)
{
    if ($update || 'post' !== $post->post_type || wp_is_post_revision($post_id)) {
        return;
    }

    update_post_meta($post_id, '_wp_page_template', 'template-article.php');
}
add_action('wp_after_insert_post', 'glenmark_set_default_post_template', 10, 3);

function glenmark_register_sidebars()
{
    for ($index = 1; $index <= 4; $index++) {
        register_sidebar([
            'name' => sprintf(__('Footer Column %d', 'gln-pharma-product'), $index),
            'id' => 'footer_col_' . $index,
            'before_widget' => '<div class="widget">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ]);
    }
}
add_action('widgets_init', 'glenmark_register_sidebars');

function glenmark_allow_svg_uploads($mimes)
{
    if (!current_user_can('upload_files')) {
        return $mimes;
    }

    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';

    return $mimes;
}
add_filter('upload_mimes', 'glenmark_allow_svg_uploads');

function glenmark_fix_svg_filetype_check($data, $file, $filename, $mimes)
{
    $filetype = wp_check_filetype($filename, $mimes);
    $ext = strtolower((string) ($filetype['ext'] ?? ''));

    if ('' === $ext) {
        $ext = strtolower((string) pathinfo((string) $filename, PATHINFO_EXTENSION));
    }

    if ('svg' === $ext || 'svgz' === $ext) {
        $data['ext'] = $ext;
        $data['type'] = 'image/svg+xml';
        $data['proper_filename'] = $filename;
    }

    return $data;
}
add_filter('wp_check_filetype_and_ext', 'glenmark_fix_svg_filetype_check', 10, 4);