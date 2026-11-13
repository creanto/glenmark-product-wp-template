<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_register_pharmacy_customizer_settings($wp_customize)
{
    $pharmacies = (array) glenmark_config('pharmacies', []);

    if (empty($pharmacies)) {
        return;
    }

    $wp_customize->add_section('glenmark_pharmacies', [
        'title' => __('Lékárny', 'gln-pharma-product'),
        'description' => __('Vyberte lékárny, které se mají nabízet a zobrazovat na webu.', 'gln-pharma-product'),
        'priority' => 34,
    ]);

    foreach ($pharmacies as $key => $pharmacy) {
        if (!is_array($pharmacy) || empty($pharmacy['label']) || sanitize_key($key) !== (string) $key) {
            continue;
        }

        $setting_id = 'glenmark_pharmacy_' . $key . '_active';

        $wp_customize->add_setting($setting_id, [
            'default' => !empty($pharmacy['active']),
            'sanitize_callback' => 'wp_validate_boolean',
        ]);

        $wp_customize->add_control($setting_id, [
            'label' => (string) $pharmacy['label'],
            'section' => 'glenmark_pharmacies',
            'type' => 'checkbox',
        ]);
    }
}
add_action('customize_register', 'glenmark_register_pharmacy_customizer_settings');