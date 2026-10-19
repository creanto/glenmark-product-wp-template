<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_shared_content_live_endpoint($key, $format = 'auto', $field = '', $linked = false)
{
    $url = rest_url('gln-pharma-product/v1/shared-content/' . rawurlencode((string) $key));

    return add_query_arg([
        'format' => sanitize_key((string) $format),
        'field' => sanitize_key((string) $field),
        'link' => $linked ? '1' : '0',
    ], $url);
}

function glenmark_shared_content_enqueue_live_script()
{
    $script_path = get_template_directory() . '/assets/js/shared-content-live.js';
    $script_url = get_template_directory_uri() . '/assets/js/shared-content-live.js';
    $version = file_exists($script_path) ? (string) filemtime($script_path) : wp_get_theme()->get('Version');

    wp_enqueue_script('glenmark-shared-content-live', $script_url, [], $version, true);
}

function glenmark_shared_content_render_live($key, $format = 'auto', $field = '', $linked = false, $class_name = 'gln-shared-content')
{
    $key = glenmark_shared_content_sanitize_key($key);
    if ('' === $key) {
        return '';
    }

    $item = glenmark_shared_content_get_item($key);
    if (!$item) {
        return '';
    }

    if ('template' === $format) {
        $content = glenmark_shared_content_render_template_item($item);
    } elseif ('' !== $field) {
        $content = glenmark_shared_content_render_field($item, $field, $linked);
    } else {
        $content = glenmark_shared_content_render_item($item, $format);
    }

    if ('' === $content) {
        return '';
    }

    glenmark_shared_content_enqueue_live_script();
    $class_name = sanitize_html_class($class_name);
    $endpoint = glenmark_shared_content_live_endpoint($key, $format, $field, $linked);
    $tag = '' !== $field ? 'span' : 'div';

    return '<' . $tag . ' class="gln-shared-content-live ' . esc_attr($class_name) . '" data-shared-content-refresh="'
        . esc_url($endpoint) . '">' . $content . '</' . $tag . '>';
}

function glenmark_shared_content_register_live_route()
{
    register_rest_route('gln-pharma-product/v1', '/shared-content/(?P<key>[a-z0-9][a-z0-9._-]*)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'glenmark_shared_content_live_route',
        'permission_callback' => '__return_true',
        'args' => [
            'key' => [
                'required' => true,
                'sanitize_callback' => 'glenmark_shared_content_sanitize_key',
            ],
        ],
    ]);
}
add_action('rest_api_init', 'glenmark_shared_content_register_live_route');

function glenmark_shared_content_live_route(WP_REST_Request $request)
{
    $key = glenmark_shared_content_sanitize_key($request->get_param('key'));
    $item = glenmark_shared_content_get_item($key);
    if (!$item) {
        return new WP_Error(
            'glenmark_shared_content_not_found',
            __('Sdílený obsah nebyl nalezen.', 'gln-pharma-product'),
            ['status' => 404]
        );
    }

    $format = sanitize_key((string) $request->get_param('format'));
    $field = sanitize_key((string) $request->get_param('field'));
    $linked = '1' === (string) $request->get_param('link');

    if ('template' === $format) {
        $content = glenmark_shared_content_render_template_item($item);
    } elseif ('' !== $field) {
        $content = glenmark_shared_content_render_field($item, $field, $linked);
    } else {
        $content = glenmark_shared_content_render_item($item, $format);
    }

    return new WP_REST_Response(
        ['content' => $content],
        200,
        ['Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0']
    );
}

function glenmark_shared_content_sanitize_key($key)
{
    return preg_replace('/[^a-z0-9._-]/', '', strtolower((string) $key));
}