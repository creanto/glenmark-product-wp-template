<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_shared_content_register_customizer_settings($wp_customize)
{
    $wp_customize->add_section('webrev_shared_content', [
        'title' => __('Sdílený obsah Glenmark', 'wr-pharma-product'),
        'description' => __('Napojení na centrální Glenmark Content Hub a údaje konkrétního webu pro právní šablony.', 'wr-pharma-product'),
        'priority' => 33,
    ]);

    $settings = [
        'webrev_shared_content_api_url' => [
            'label' => __('REST API URL', 'wr-pharma-product'),
            'type' => 'url',
            'default' => '',
            'sanitize_callback' => 'webrev_shared_content_sanitize_api_url',
            'description' => __('Základní URL API včetně /wp-json/glenmark-content/v1, bez koncového /items.', 'wr-pharma-product'),
        ],
        'webrev_shared_content_site_name' => [
            'label' => __('Název tohoto webu', 'wr-pharma-product'),
            'type' => 'text',
            'default' => get_bloginfo('name'),
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'webrev_shared_content_site_url' => [
            'label' => __('URL tohoto webu', 'wr-pharma-product'),
            'type' => 'url',
            'default' => home_url('/'),
            'sanitize_callback' => 'esc_url_raw',
        ],
        'webrev_shared_content_controller_name' => [
            'label' => __('Správce osobních údajů', 'wr-pharma-product'),
            'type' => 'text',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'webrev_shared_content_controller_address' => [
            'label' => __('Adresa správce osobních údajů', 'wr-pharma-product'),
            'type' => 'textarea',
            'default' => '',
            'sanitize_callback' => 'sanitize_textarea_field',
        ],
    ];

    foreach ($settings as $setting_id => $setting) {
        $wp_customize->add_setting($setting_id, [
            'default' => $setting['default'],
            'sanitize_callback' => $setting['sanitize_callback'],
        ]);
        $wp_customize->add_control($setting_id, [
            'label' => $setting['label'],
            'section' => 'webrev_shared_content',
            'type' => $setting['type'],
            'description' => isset($setting['description']) ? $setting['description'] : '',
        ]);
    }
}
add_action('customize_register', 'webrev_shared_content_register_customizer_settings');

function webrev_shared_content_sanitize_api_url($value)
{
    $value = untrailingslashit(esc_url_raw(trim((string) $value), ['http', 'https']));

    if ('' === $value || false === wp_http_validate_url($value)) {
        return '';
    }

    return $value;
}

function webrev_shared_content_api_url()
{
    $url = trim((string) get_theme_mod('webrev_shared_content_api_url', ''));

    return untrailingslashit((string) apply_filters('webrev/shared_content/api_url', $url));
}

function webrev_shared_content_api_endpoint()
{
    $base_url = webrev_shared_content_api_url();

    return '' === $base_url ? '' : trailingslashit($base_url) . 'items';
}

function webrev_shared_content_cache_key($suffix = 'items')
{
    return 'webrev_sc_' . md5(webrev_shared_content_api_endpoint()) . '_' . $suffix;
}

function webrev_shared_content_get_items($force_refresh = false)
{
    $endpoint = webrev_shared_content_api_endpoint();
    if ('' === $endpoint) {
        return [];
    }

    $cache_key = webrev_shared_content_cache_key();
    $stale_items = get_transient(webrev_shared_content_cache_key('stale'));
    $cached_items = get_transient($cache_key);

    if (!$force_refresh && is_array($cached_items)) {
        return $cached_items;
    }

    $headers = ['Accept' => 'application/json'];
    $etag = get_transient(webrev_shared_content_cache_key('etag'));
    if (is_string($etag) && '' !== $etag) {
        $headers['If-None-Match'] = $etag;
    }

    $response = wp_remote_get($endpoint, [
        'timeout' => 5,
        'redirection' => 2,
        'headers' => $headers,
    ]);

    if (is_wp_error($response)) {
        return is_array($stale_items) ? $stale_items : [];
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    if (304 === $status && is_array($stale_items)) {
        set_transient($cache_key, $stale_items, 5 * MINUTE_IN_SECONDS);
        return $stale_items;
    }

    if (200 !== $status) {
        return is_array($stale_items) ? $stale_items : [];
    }

    $payload = json_decode((string) wp_remote_retrieve_body($response), true);
    if (!is_array($payload) || !isset($payload['items']) || !is_array($payload['items'])) {
        return is_array($stale_items) ? $stale_items : [];
    }

    $items = [];
    foreach ($payload['items'] as $item) {
        if (!is_array($item) || empty($item['key']) || !is_array($item['data'] ?? null)) {
            continue;
        }

        $key = (string) $item['key'];
        if (!preg_match('/^[a-z0-9][a-z0-9._-]*$/', $key)) {
            continue;
        }

        $items[$key] = $item;
    }

    $response_etag = wp_remote_retrieve_header($response, 'etag');
    if (is_string($response_etag) && '' !== $response_etag) {
        set_transient(webrev_shared_content_cache_key('etag'), $response_etag, WEEK_IN_SECONDS);
    }

    set_transient($cache_key, $items, 5 * MINUTE_IN_SECONDS);
    set_transient(webrev_shared_content_cache_key('stale'), $items, WEEK_IN_SECONDS);

    return $items;
}

function webrev_shared_content_get_item($key, $force_refresh = false)
{
    $items = webrev_shared_content_get_items($force_refresh);

    return isset($items[$key]) ? $items[$key] : null;
}

function webrev_shared_content_get_site_tokens()
{
    $tokens = [
        'site_name' => (string) get_theme_mod('webrev_shared_content_site_name', get_bloginfo('name')),
        'site_url' => (string) get_theme_mod('webrev_shared_content_site_url', home_url('/')),
        'data_controller_name' => (string) get_theme_mod('webrev_shared_content_controller_name', ''),
        'data_controller_address' => (string) get_theme_mod('webrev_shared_content_controller_address', ''),
    ];

    return apply_filters('webrev/shared_content/site_tokens', $tokens);
}

function webrev_shared_content_replace_tokens($html)
{
    $tokens = webrev_shared_content_get_site_tokens();
    $replacements = [];

    foreach ($tokens as $name => $value) {
        $replacements['{{' . $name . '}}'] = 'site_url' === $name
            ? esc_url((string) $value)
            : esc_html((string) $value);
    }

    return strtr((string) $html, $replacements);
}