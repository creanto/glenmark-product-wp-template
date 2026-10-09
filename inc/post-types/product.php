<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_register_gln_product_post_type()
{
    $labels = [
        'name' => __('Produkty', 'gln-pharma-product'),
        'singular_name' => __('Produkt', 'gln-pharma-product'),
        'menu_name' => __('Produkty', 'gln-pharma-product'),
        'name_admin_bar' => __('Produkt', 'gln-pharma-product'),
        'add_new' => __('Přidat produkt', 'gln-pharma-product'),
        'add_new_item' => __('Přidat nový produkt', 'gln-pharma-product'),
        'edit_item' => __('Upravit produkt', 'gln-pharma-product'),
        'new_item' => __('Nový produkt', 'gln-pharma-product'),
        'view_item' => __('Zobrazit produkt', 'gln-pharma-product'),
        'view_items' => __('Zobrazit produkty', 'gln-pharma-product'),
        'search_items' => __('Hledat produkty', 'gln-pharma-product'),
        'not_found' => __('Nebyly nalezeny žádné produkty.', 'gln-pharma-product'),
        'not_found_in_trash' => __('V koši nebyly nalezeny žádné produkty.', 'gln-pharma-product'),
        'all_items' => __('Všechny produkty', 'gln-pharma-product'),
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => false,
        'rewrite' => ['slug' => 'produkty'],
        'menu_icon' => 'dashicons-cart',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'page-attributes'],
        'show_in_rest' => true,
        'menu_position' => 20,
        'taxonomies' => ['gln_product_category'],
    ];

    register_post_type('gln_product', $args);
}
add_action('init', 'glenmark_register_gln_product_post_type', 0);

/**
 * Native drag & drop ordering for gln_product in the admin list table.
 * Uses the standard WordPress menu_order column (also editable via the
 * "Order" field added by the 'page-attributes' support above).
 */
function glenmark_gln_product_default_admin_order($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ('gln_product' !== $query->get('post_type')) {
        return;
    }

    global $pagenow;

    if ('edit.php' !== $pagenow) {
        return;
    }

    if (!$query->get('orderby')) {
        $query->set('orderby', 'menu_order title');
        $query->set('order', 'ASC');
    }
}
add_action('pre_get_posts', 'glenmark_gln_product_default_admin_order');

function glenmark_gln_product_admin_columns($columns)
{
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        if ('title' === $key) {
            $new_columns['gln_product_order'] = __('Pořadí', 'gln-pharma-product');
        }
    }

    return $new_columns;
}
add_filter('manage_gln_product_posts_columns', 'glenmark_gln_product_admin_columns');

function glenmark_gln_product_admin_column_content($column, $post_id)
{
    if ('gln_product_order' === $column) {
        echo '<span class="gln-product-order-handle" data-post-id="' . esc_attr((string) $post_id) . '">'
            . esc_html((string) get_post_field('menu_order', $post_id)) . '</span>';
    }
}
add_action('manage_gln_product_posts_custom_column', 'glenmark_gln_product_admin_column_content', 10, 2);

function glenmark_gln_product_sortable_columns($columns)
{
    $columns['gln_product_order'] = 'menu_order';

    return $columns;
}
add_filter('manage_edit-gln_product_sortable_columns', 'glenmark_gln_product_sortable_columns');

function glenmark_gln_product_enqueue_admin_order_script($hook)
{
    global $post_type;

    if ('edit.php' !== $hook || 'gln_product' !== $post_type) {
        return;
    }

    wp_enqueue_script(
        'gln-product-admin-order',
        get_template_directory_uri() . '/assets/js/admin-product-order.js',
        ['jquery', 'jquery-ui-sortable'],
        wp_get_theme()->get('Version'),
        true
    );

    wp_localize_script('gln-product-admin-order', 'glnProductOrder', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gln_product_reorder'),
    ]);
}
add_action('admin_enqueue_scripts', 'glenmark_gln_product_enqueue_admin_order_script');

function glenmark_gln_product_handle_reorder_ajax()
{
    check_ajax_referer('gln_product_reorder', 'nonce');

    if (!current_user_can('edit_others_posts')) {
        wp_send_json_error(['message' => __('Nedostatečná oprávnění.', 'gln-pharma-product')], 403);
    }

    $post_ids = isset($_POST['post_ids']) ? (array) $_POST['post_ids'] : [];
    $post_ids = array_map('absint', $post_ids);

    foreach ($post_ids as $index => $post_id) {
        if ('gln_product' !== get_post_type($post_id)) {
            continue;
        }

        wp_update_post([
            'ID' => $post_id,
            'menu_order' => $index,
        ]);
    }

    wp_send_json_success();
}
add_action('wp_ajax_gln_product_reorder', 'glenmark_gln_product_handle_reorder_ajax');

function glenmark_register_gln_product_taxonomy()
{
    $category_labels = [
        'name' => __('Kategorie produktů', 'gln-pharma-product'),
        'singular_name' => __('Kategorie produktu', 'gln-pharma-product'),
        'search_items' => __('Hledat kategorie', 'gln-pharma-product'),
        'all_items' => __('Všechny kategorie', 'gln-pharma-product'),
        'edit_item' => __('Upravit kategorii', 'gln-pharma-product'),
        'update_item' => __('Aktualizovat kategorii', 'gln-pharma-product'),
        'add_new_item' => __('Přidat novou kategorii', 'gln-pharma-product'),
        'new_item_name' => __('Název nové kategorie', 'gln-pharma-product'),
        'menu_name' => __('Kategorie', 'gln-pharma-product'),
    ];

    register_taxonomy('gln_product_category', ['gln_product'], [
        'labels' => $category_labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'produkty/kategorie'],
    ]);
}
add_action('init', 'glenmark_register_gln_product_taxonomy', 0);
