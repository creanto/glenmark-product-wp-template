<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_shared_content_render_key($key, $format = 'auto', $field = '', $linked = false)
{
    $item = glenmark_shared_content_get_item(sanitize_text_field((string) $key));
    if (!$item) {
        return '';
    }

    if ('' !== $field) {
        return glenmark_shared_content_render_field($item, $field, $linked);
    }

    return glenmark_shared_content_render_item($item, $format);
}

function glenmark_shared_content_render_item($item, $format = 'auto')
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
        return glenmark_shared_content_render_address($data);
    }

    if ('contact' === $format || 'footer' === $format) {
        return glenmark_shared_content_render_contact($data);
    }

    if ('link' === $format) {
        return glenmark_shared_content_render_link($data);
    }

    if ('content' === $format || 'document' === $format || 'auto' === $format) {
        return glenmark_shared_content_render_html($data['content_html'] ?? '');
    }

    return '';
}

function glenmark_shared_content_render_field($item, $field, $linked = false)
{
    $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : [];
    if (!array_key_exists($field, $data)) {
        return '';
    }

    $value = (string) $data[$field];
    if ('content_html' === $field) {
        return glenmark_shared_content_render_html($value);
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

function glenmark_shared_content_render_html($html)
{
    return wp_kses_post(glenmark_shared_content_replace_tokens((string) $html));
}

function glenmark_shared_content_render_cookies_list()
{
    if (!shortcode_exists('wpconsent_cookie_policy')) {
        return '';
    }

    return do_shortcode('[wpconsent_cookie_policy]');
}

function glenmark_shared_content_render_cookies_preferences_button()
{
    if (!shortcode_exists('wpconsent_preferences_button')) {
        return '';
    }

    // WPConsent otevírá předvolby delegovaným handlerem na třídu
    // .wpconsent-open-preferences; odkaz používáme proto, že element
    // <button> by neprošel sanitací wp_kses_post.
    return '<a href="#" class="wpconsent-open-preferences">' . esc_html__('Nastavení cookies', 'gln-pharma-product') . '</a>';
}

function glenmark_cookies_preferences_shortcode($attributes)
{
    $attributes = shortcode_atts([
        'text' => __('Nastavení cookies', 'gln-pharma-product'),
        'class' => '',
    ], $attributes, 'gln_cookies_preferences');

    if (!shortcode_exists('wpconsent_preferences_button')) {
        return '';
    }

    $classes = ['wpconsent-open-preferences'];
    foreach (preg_split('/\s+/', (string) $attributes['class']) as $extra_class) {
        $extra_class = sanitize_html_class($extra_class);
        if ('' !== $extra_class) {
            $classes[] = $extra_class;
        }
    }

    return '<a href="#" class="' . esc_attr(implode(' ', $classes)) . '">'
        . esc_html((string) $attributes['text'])
        . '</a>';
}
add_shortcode('gln_cookies_preferences', 'glenmark_cookies_preferences_shortcode');

function glenmark_shared_content_sanitize_heading_tag($tag)
{
    $tag = strtolower((string) $tag);

    return in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) ? $tag : 'h1';
}

function glenmark_shared_content_render_template_item($item, $args = [])
{
    $args = wp_parse_args($args, [
        'show_heading' => false,
        'heading_tag' => 'h1',
        'show_version' => true,
        'show_date' => true,
    ]);

    $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : [];
    $content = glenmark_shared_content_render_html($data['content_html'] ?? '');

    if ($args['show_heading']) {
        $heading = !empty($data['full_title']) && is_scalar($data['full_title'])
            ? (string) $data['full_title']
            : (string) ($item['title'] ?? '');
        $heading = trim($heading);
        if ('' !== $heading) {
            $tag = glenmark_shared_content_sanitize_heading_tag($args['heading_tag']);
            $content = '<' . $tag . ' class="gln-shared-template__title">' . esc_html($heading) . '</' . $tag . '>' . $content;
        }
    }

    $meta_fields = [];
    if ($args['show_version']) {
        $meta_fields['document_version'] = __('Verze', 'gln-pharma-product');
    }
    if ($args['show_date']) {
        $meta_fields['document_date'] = __('Datum', 'gln-pharma-product');
    }
    $meta_fields['approval_number'] = __('Schvalovací číslo', 'gln-pharma-product');

    $metadata = [];

    foreach ($meta_fields as $field => $label) {
        if (empty($data[$field]) || !is_scalar($data[$field])) {
            continue;
        }

        $value = sanitize_text_field((string) $data[$field]);
        if ('document_date' === $field) {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, wp_timezone());
            if ($date) {
                $value = $date->format('j. n. Y');
            }
        }
        $metadata[] = '<span><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</span>';
    }

    if (empty($metadata)) {
        return $content;
    }

    return $content . '<footer class="gln-shared-template__meta"><small>' . implode(' ', $metadata) . '</small></footer>';
}

function glenmark_shared_content_render_address($data)
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

    return empty($lines) ? '' : '<address class="gln-shared-content-address">' . implode("<br>\n", $lines) . '</address>';
}

function glenmark_shared_content_render_contact($data)
{
    $parts = ['<div class="gln-shared-content-contact">'];
    if (!empty($data['company_name'])) {
        $parts[] = '<p class="gln-shared-content-company">';
        if (!empty($data['website_url'])) {
            $parts[] = '<a href="' . esc_url($data['website_url']) . '">' . esc_html((string) $data['company_name']) . '</a>';
        } else {
            $parts[] = esc_html((string) $data['company_name']);
        }
        $parts[] = '</p>';
    }

    $address = glenmark_shared_content_render_address($data);
    if ('' !== $address) {
        $parts[] = $address;
    }
    if (!empty($data['phone'])) {
        $parts[] = '<p class="gln-shared-content-phone">' . glenmark_shared_content_render_field(['data' => $data], 'phone', true) . '</p>';
    }
    foreach (['email_primary', 'email_secondary'] as $field) {
        if (!empty($data[$field])) {
            $parts[] = '<p class="gln-shared-content-email">' . glenmark_shared_content_render_field(['data' => $data], $field, true) . '</p>';
        }
    }
    $parts[] = '</div>';

    return implode("\n", $parts);
}

function glenmark_shared_content_render_link($data)
{
    if (empty($data['website_url'])) {
        return '';
    }

    $label = !empty($data['link_label']) ? (string) $data['link_label'] : (string) $data['website_url'];

    return '<a href="' . esc_url($data['website_url']) . '">' . esc_html($label) . '</a>';
}

function glenmark_shared_content_shortcode($attributes)
{
    $attributes = shortcode_atts([
        'key' => '',
        'field' => '',
        'format' => 'auto',
        'link' => '0',
        'heading' => '0',
        'heading_tag' => 'h1',
        'version' => '1',
        'date' => '1',
    ], $attributes, 'gln_shared_content');

    if ('' === (string) $attributes['key']) {
        return '';
    }

    return glenmark_shared_content_render_live(
        $attributes['key'],
        sanitize_key($attributes['format']),
        sanitize_key($attributes['field']),
        '1' === (string) $attributes['link'],
        'gln-shared-content',
        [
            'show_heading' => '1' === (string) $attributes['heading'],
            'heading_tag' => glenmark_shared_content_sanitize_heading_tag($attributes['heading_tag']),
            'show_version' => '1' === (string) $attributes['version'],
            'show_date' => '1' === (string) $attributes['date'],
        ]
    );
}
add_shortcode('gln_shared_content', 'glenmark_shared_content_shortcode');

function glenmark_shared_contact_shortcode($attributes)
{
    $attributes = shortcode_atts([
        'key' => '',
        'format' => 'contact',
    ], $attributes, 'gln_shared_contact');

    if ('' === (string) $attributes['key']) {
        return '';
    }

    return glenmark_shared_content_render_live($attributes['key'], sanitize_key($attributes['format']));
}
add_shortcode('gln_shared_contact', 'glenmark_shared_contact_shortcode');