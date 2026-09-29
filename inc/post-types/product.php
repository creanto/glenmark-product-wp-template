<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_register_wr_product_post_type()
{
    $labels = [
        'name' => __('Produkty', 'wr-pharma-product'),
        'singular_name' => __('Produkt', 'wr-pharma-product'),
        'menu_name' => __('Produkty', 'wr-pharma-product'),
        'name_admin_bar' => __('Produkt', 'wr-pharma-product'),
        'add_new' => __('Přidat produkt', 'wr-pharma-product'),
        'add_new_item' => __('Přidat nový produkt', 'wr-pharma-product'),
        'edit_item' => __('Upravit produkt', 'wr-pharma-product'),
        'new_item' => __('Nový produkt', 'wr-pharma-product'),
        'view_item' => __('Zobrazit produkt', 'wr-pharma-product'),
        'view_items' => __('Zobrazit produkty', 'wr-pharma-product'),
        'search_items' => __('Hledat produkty', 'wr-pharma-product'),
        'not_found' => __('Nebyly nalezeny žádné produkty.', 'wr-pharma-product'),
        'not_found_in_trash' => __('V koši nebyly nalezeny žádné produkty.', 'wr-pharma-product'),
        'all_items' => __('Všechny produkty', 'wr-pharma-product'),
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
        'taxonomies' => ['wr_product_category'],
    ];

    register_post_type('wr_product', $args);
}
add_action('init', 'webrev_register_wr_product_post_type', 0);

/**
 * Native drag & drop ordering for wr_product in the admin list table.
 * Uses the standard WordPress menu_order column (also editable via the
 * "Order" field added by the 'page-attributes' support above).
 */
function webrev_wr_product_default_admin_order($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ('wr_product' !== $query->get('post_type')) {
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
add_action('pre_get_posts', 'webrev_wr_product_default_admin_order');

function webrev_wr_product_admin_columns($columns)
{
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        if ('title' === $key) {
            $new_columns['wr_product_order'] = __('Pořadí', 'wr-pharma-product');
        }
    }

    return $new_columns;
}
add_filter('manage_wr_product_posts_columns', 'webrev_wr_product_admin_columns');

function webrev_wr_product_admin_column_content($column, $post_id)
{
    if ('wr_product_order' === $column) {
        echo '<span class="wr-product-order-handle" data-post-id="' . esc_attr((string) $post_id) . '">'
            . esc_html((string) get_post_field('menu_order', $post_id)) . '</span>';
    }
}
add_action('manage_wr_product_posts_custom_column', 'webrev_wr_product_admin_column_content', 10, 2);

function webrev_wr_product_sortable_columns($columns)
{
    $columns['wr_product_order'] = 'menu_order';

    return $columns;
}
add_filter('manage_edit-wr_product_sortable_columns', 'webrev_wr_product_sortable_columns');

function webrev_wr_product_enqueue_admin_order_script($hook)
{
    global $post_type;

    if ('edit.php' !== $hook || 'wr_product' !== $post_type) {
        return;
    }

    wp_enqueue_script(
        'wr-product-admin-order',
        get_template_directory_uri() . '/assets/js/admin-product-order.js',
        ['jquery', 'jquery-ui-sortable'],
        wp_get_theme()->get('Version'),
        true
    );

    wp_localize_script('wr-product-admin-order', 'wrProductOrder', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wr_product_reorder'),
    ]);
}
add_action('admin_enqueue_scripts', 'webrev_wr_product_enqueue_admin_order_script');

function webrev_wr_product_handle_reorder_ajax()
{
    check_ajax_referer('wr_product_reorder', 'nonce');

    if (!current_user_can('edit_others_posts')) {
        wp_send_json_error(['message' => __('Nedostatečná oprávnění.', 'wr-pharma-product')], 403);
    }

    $post_ids = isset($_POST['post_ids']) ? (array) $_POST['post_ids'] : [];
    $post_ids = array_map('absint', $post_ids);

    foreach ($post_ids as $index => $post_id) {
        if ('wr_product' !== get_post_type($post_id)) {
            continue;
        }

        wp_update_post([
            'ID' => $post_id,
            'menu_order' => $index,
        ]);
    }

    wp_send_json_success();
}
add_action('wp_ajax_wr_product_reorder', 'webrev_wr_product_handle_reorder_ajax');

function webrev_register_wr_product_taxonomy()
{
    $category_labels = [
        'name' => __('Kategorie produktů', 'wr-pharma-product'),
        'singular_name' => __('Kategorie produktu', 'wr-pharma-product'),
        'search_items' => __('Hledat kategorie', 'wr-pharma-product'),
        'all_items' => __('Všechny kategorie', 'wr-pharma-product'),
        'edit_item' => __('Upravit kategorii', 'wr-pharma-product'),
        'update_item' => __('Aktualizovat kategorii', 'wr-pharma-product'),
        'add_new_item' => __('Přidat novou kategorii', 'wr-pharma-product'),
        'new_item_name' => __('Název nové kategorie', 'wr-pharma-product'),
        'menu_name' => __('Kategorie', 'wr-pharma-product'),
    ];

    register_taxonomy('wr_product_category', ['wr_product'], [
        'labels' => $category_labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'produkty/kategorie'],
    ]);
}
add_action('init', 'webrev_register_wr_product_taxonomy', 0);
