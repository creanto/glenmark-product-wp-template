<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_register_gln_pharmacy_post_type()
{
    $labels = [
        'name' => __('Lékárny', 'gln-pharma-product'),
        'singular_name' => __('Lékárna', 'gln-pharma-product'),
        'menu_name' => __('Lékárny', 'gln-pharma-product'),
        'name_admin_bar' => __('Lékárna', 'gln-pharma-product'),
        'add_new' => __('Přidat lékárnu', 'gln-pharma-product'),
        'add_new_item' => __('Přidat novou lékárnu', 'gln-pharma-product'),
        'edit_item' => __('Upravit lékárnu', 'gln-pharma-product'),
        'new_item' => __('Nová lékárna', 'gln-pharma-product'),
        'view_item' => __('Zobrazit lékárnu', 'gln-pharma-product'),
        'view_items' => __('Zobrazit lékárny', 'gln-pharma-product'),
        'search_items' => __('Hledat lékárny', 'gln-pharma-product'),
        'not_found' => __('Nebyly nalezeny žádné lékárny.', 'gln-pharma-product'),
        'not_found_in_trash' => __('V koši nebyly nalezeny žádné lékárny.', 'gln-pharma-product'),
        'all_items' => __('Všechny lékárny', 'gln-pharma-product'),
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

    register_post_type('gln_pharmacy', $args);
}
add_action('init', 'glenmark_register_gln_pharmacy_post_type', 0);
