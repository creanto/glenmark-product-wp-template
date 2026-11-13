<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_register_sukl_customizer_settings($wp_customize)
{
    $section_id = 'glenmark_sukl_settings';
    $default_url = 'https://prehledy.sukl.cz/prehled_leciv.html';

    $wp_customize->add_section($section_id, [
        'title' => __('Nastavení SÚKL', 'gln-pharma-product'),
        'priority' => 35,
    ]);

    $settings = [
        'glenmark_sukl_spc_url' => [
            'label' => __('Globální odkaz SPC', 'gln-pharma-product'),
            'default' => $default_url,
        ],
        'glenmark_sukl_pil_url' => [
            'label' => __('Globální odkaz PIL', 'gln-pharma-product'),
            'default' => $default_url,
        ],
        'glenmark_sukl_detail_url' => [
            'label' => __('Odkaz na detail léku', 'gln-pharma-product'),
            'default' => $default_url . '#/detail-reg/',
        ],
    ];

    foreach ($settings as $setting_id => $setting) {
        $wp_customize->add_setting($setting_id, [
            'default' => $setting['default'],
            'sanitize_callback' => 'esc_url_raw',
        ]);

        $wp_customize->add_control($setting_id, [
            'label' => $setting['label'],
            'section' => $section_id,
            'type' => 'url',
        ]);
    }
}
add_action('customize_register', 'glenmark_register_sukl_customizer_settings');