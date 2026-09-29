<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_register_wr_pharmacy_acf_fields()
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_wr_pharmacy_fields',
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
                    'value' => 'wr_pharmacy',
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
add_action('init', 'webrev_register_wr_pharmacy_acf_fields', 20);
