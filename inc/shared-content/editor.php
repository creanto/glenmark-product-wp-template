<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_shared_content_register_editor_block()
{
    $script_path = get_template_directory() . '/inc/shared-content/assets/js/shared-content-editor.js';
    $script_uri = get_template_directory_uri() . '/inc/shared-content/assets/js/shared-content-editor.js';
    $version = file_exists($script_path) ? (string) filemtime($script_path) : wp_get_theme()->get('Version');

    wp_register_script(
        'glenmark-shared-content-editor',
        $script_uri,
        ['wp-api-fetch', 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-element', 'wp-plugins'],
        $version,
        true
    );

    register_block_type('glenmark/shared-content-snapshot', [
        'api_version' => 2,
        'editor_script' => 'glenmark-shared-content-editor',
        'attributes' => [
            'sourceKey' => ['type' => 'string'],
            'sourceVersion' => ['type' => 'string'],
            'sourceTitle' => ['type' => 'string'],
        ],
        'supports' => ['html' => false],
    ]);
}
add_action('init', 'glenmark_shared_content_register_editor_block', 20);

function glenmark_shared_content_prepare_editor_items($force_refresh = false)
{
    $items = array_values(glenmark_shared_content_get_items($force_refresh));

    foreach ($items as &$item) {
        $item['snapshot_html'] = glenmark_shared_content_render_item($item);
    }
    unset($item);

    return $items;
}

function glenmark_shared_content_enqueue_editor_assets()
{
    wp_localize_script('glenmark-shared-content-editor', 'glenmarkSharedContent', [
        'items' => glenmark_shared_content_prepare_editor_items(),
        'refreshUrl' => rest_url('gln-pharma-product/v1/shared-content-items'),
        'nonce' => wp_create_nonce('wp_rest'),
        'labels' => [
            'panelTitle' => __('Sdílený obsah Glenmark', 'gln-pharma-product'),
            'selectItem' => __('Vyberte obsah', 'gln-pharma-product'),
            'insert' => __('Vložit / aktualizovat kopii', 'gln-pharma-product'),
            'refresh' => __('Načíst aktuální nabídku', 'gln-pharma-product'),
            'current' => __('Aktuální', 'gln-pharma-product'),
            'outdated' => __('Nová verze', 'gln-pharma-product'),
            'noItems' => __('Není dostupný žádný publikovaný obsah. Zkontrolujte URL API v nastavení webu.', 'gln-pharma-product'),
            'confirmReplace' => __('Tato stránka už obsahuje kopii této položky. Nahradit ji aktuální verzí? Vaše úpravy uvnitř nahrazeného bloku budou přepsány.', 'gln-pharma-product'),
            'refreshFailed' => __('Aktuální obsah se nepodařilo načíst. Zobrazené kopie zůstaly beze změny.', 'gln-pharma-product'),
        ],
    ]);
}
add_action('enqueue_block_editor_assets', 'glenmark_shared_content_enqueue_editor_assets');

function glenmark_shared_content_register_editor_route()
{
    register_rest_route('gln-pharma-product/v1', '/shared-content-items', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'glenmark_shared_content_editor_route',
        'permission_callback' => static function () {
            return current_user_can('edit_posts');
        },
    ]);
}
add_action('rest_api_init', 'glenmark_shared_content_register_editor_route');

function glenmark_shared_content_editor_route()
{
    if ('' === glenmark_shared_content_api_endpoint()) {
        return new WP_Error(
            'glenmark_shared_content_not_configured',
            __('Není nastavena URL centrálního REST API.', 'gln-pharma-product'),
            ['status' => 503]
        );
    }

    return rest_ensure_response([
        'items' => glenmark_shared_content_prepare_editor_items(true),
    ]);
}