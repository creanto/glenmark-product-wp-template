<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_register_gln_pharmacy_acf_fields()
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_gln_pharmacy_fields',
        'title' => 'Lékárna',
        'fields' => [
            [
                'key' => 'field_pharmacy_logo',
                'label' => 'Logo lékárny',
                'name' => 'pharmacy_logo',
                'type' => 'image',
                'return_format' => 'id',
                'preview_size' => 'medium',
            ],
            [
                'key' => 'field_pharmacy_url',
                'label' => 'URL lékárny',
                'name' => 'pharmacy_url',
                'type' => 'url',
                'placeholder' => 'https://www.example.com',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'gln_pharmacy',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
    ]);
}
add_action('init', 'glenmark_register_gln_pharmacy_acf_fields', 20);
