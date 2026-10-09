<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_shared_content_render_key($key, $format = 'auto', $field = '', $linked = false)
{
    $item = webrev_shared_content_get_item(sanitize_text_field((string) $key));
    if (!$item) {
        return '';
    }

    if ('' !== $field) {
        return webrev_shared_content_render_field($item, $field, $linked);
    }

    return webrev_shared_content_render_item($item, $format);
}

function webrev_shared_content_render_item($item, $format = 'auto')
{
    $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : [];
    $type = isset($item['type']) ? (string) $item['type'] : 'content';

    if ('auto' === $format) {
        $format = $type;
        if ('document' === $format) {
            $format = 'content';
        }
    }

    if ('address' === $format) {
        return webrev_shared_content_render_address($data);
    }

    if ('contact' === $format || 'footer' === $format) {
        return webrev_shared_content_render_contact($data);
    }

    if ('link' === $format) {
        return webrev_shared_content_render_link($data);
    }

    if ('content' === $format || 'document' === $format || 'auto' === $format) {
        return webrev_shared_content_render_html($data['content_html'] ?? '');
    }

    return '';
}

function webrev_shared_content_render_field($item, $field, $linked = false)
{
    $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : [];
    if (!array_key_exists($field, $data)) {
        return '';
    }

    $value = (string) $data[$field];
    if ('content_html' === $field) {
        return webrev_shared_content_render_html($value);
    }

    if ('website_url' === $field) {
        $url = esc_url($value);
        if ('' === $url) {
            return '';
        }
        return $linked ? '<a href="' . esc_url($url) . '">' . esc_html($data['link_label'] ?? $url) . '</a>' : esc_html($url);
    }

    if ($linked && 'phone' === $field) {
        return '<a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $value)) . '">' . esc_html($value) . '</a>';
    }

    if ($linked && in_array($field, ['email_primary', 'email_secondary'], true)) {
        return '<a href="mailto:' . esc_attr(sanitize_email($value)) . '">' . esc_html($value) . '</a>';
    }

    return esc_html($value);
}

function webrev_shared_content_render_html($html)
{
    return wp_kses_post(webrev_shared_content_replace_tokens((string) $html));
}

function webrev_shared_content_render_address($data)
{
    $lines = [];
    foreach (['building', 'street'] as $field) {
        if (!empty($data[$field])) {
            $lines[] = esc_html((string) $data[$field]);
        }
    }

    $city_line = trim(implode(' ', array_filter([
        isset($data['postal_code']) ? (string) $data['postal_code'] : '',
        isset($data['city']) ? (string) $data['city'] : '',
    ])));
    if ('' !== $city_line) {
        $lines[] = esc_html($city_line);
    }
    if (!empty($data['country'])) {
        $lines[] = esc_html((string) $data['country']);
    }

    return empty($lines) ? '' : '<address class="wr-shared-content-address">' . implode("<br>\n", $lines) . '</address>';
}

function webrev_shared_content_render_contact($data)
{
    $parts = ['<div class="wr-shared-content-contact">'];
    if (!empty($data['company_name'])) {
        $parts[] = '<p class="wr-shared-content-company">';
        if (!empty($data['website_url'])) {
            $parts[] = '<a href="' . esc_url($data['website_url']) . '">' . esc_html((string) $data['company_name']) . '</a>';
        } else {
            $parts[] = esc_html((string) $data['company_name']);
        }
        $parts[] = '</p>';
    }

    $address = webrev_shared_content_render_address($data);
    if ('' !== $address) {
        $parts[] = $address;
    }
    if (!empty($data['phone'])) {
        $parts[] = '<p class="wr-shared-content-phone">' . webrev_shared_content_render_field(['data' => $data], 'phone', true) . '</p>';
    }
    foreach (['email_primary', 'email_secondary'] as $field) {
        if (!empty($data[$field])) {
            $parts[] = '<p class="wr-shared-content-email">' . webrev_shared_content_render_field(['data' => $data], $field, true) . '</p>';
        }
    }
    $parts[] = '</div>';

    return implode("\n", $parts);
}

function webrev_shared_content_render_link($data)
{
    if (empty($data['website_url'])) {
        return '';
    }

    $label = !empty($data['link_label']) ? (string) $data['link_label'] : (string) $data['website_url'];

    return '<a href="' . esc_url($data['website_url']) . '">' . esc_html($label) . '</a>';
}

function webrev_shared_content_shortcode($attributes)
{
    $attributes = shortcode_atts([
        'key' => '',
        'field' => '',
        'format' => 'auto',
        'link' => '0',
    ], $attributes, 'wr_shared_content');

    if ('' === (string) $attributes['key']) {
        return '';
    }

    return webrev_shared_content_render_key(
        $attributes['key'],
        sanitize_key($attributes['format']),
        sanitize_key($attributes['field']),
        '1' === (string) $attributes['link']
    );
}
add_shortcode('wr_shared_content', 'webrev_shared_content_shortcode');

function webrev_shared_contact_shortcode($attributes)
{
    $attributes = shortcode_atts([
        'key' => '',
        'format' => 'contact',
    ], $attributes, 'wr_shared_contact');

    if ('' === (string) $attributes['key']) {
        return '';
    }

    return webrev_shared_content_render_key($attributes['key'], sanitize_key($attributes['format']));
}
add_shortcode('wr_shared_contact', 'webrev_shared_contact_shortcode');