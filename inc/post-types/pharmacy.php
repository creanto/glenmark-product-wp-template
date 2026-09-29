<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_register_wr_pharmacy_post_type()
{
    $labels = [
        'name' => __('Lékárny', 'wr-pharma-product'),
        'singular_name' => __('Lékárna', 'wr-pharma-product'),
        'menu_name' => __('Lékárny', 'wr-pharma-product'),
        'name_admin_bar' => __('Lékárna', 'wr-pharma-product'),
        'add_new' => __('Přidat lékárnu', 'wr-pharma-product'),
        'add_new_item' => __('Přidat novou lékárnu', 'wr-pharma-product'),
        'edit_item' => __('Upravit lékárnu', 'wr-pharma-product'),
        'new_item' => __('Nová lékárna', 'wr-pharma-product'),
        'view_item' => __('Zobrazit lékárnu', 'wr-pharma-product'),
        'view_items' => __('Zobrazit lékárny', 'wr-pharma-product'),
        'search_items' => __('Hledat lékárny', 'wr-pharma-product'),
        'not_found' => __('Nebyly nalezeny žádné lékárny.', 'wr-pharma-product'),
        'not_found_in_trash' => __('V koši nebyly nalezeny žádné lékárny.', 'wr-pharma-product'),
        'all_items' => __('Všechny lékárny', 'wr-pharma-product'),
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'lekarny'],
        'menu_icon' => 'dashicons-store',
        'supports' => ['title', 'thumbnail', 'revisions', 'custom-fields'],
        'show_in_rest' => true,
        'menu_position' => 21,
    ];

    register_post_type('wr_pharmacy', $args);
}
add_action('init', 'webrev_register_wr_pharmacy_post_type', 0);
