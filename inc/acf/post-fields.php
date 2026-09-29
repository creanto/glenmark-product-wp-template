<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_register_wr_post_acf_fields()
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_wr_post_fields',
        'title' => 'Příspěvek',
        'fields' => [
            [
                'key' => 'field_post_short_title',
                'label' => 'Zkrácený název',
                'name' => 'short_title',
                'type' => 'text',
                'instructions' => 'Kratší název pro zobrazení ve výpisech (grid). Na stránce příspěvku se zobrazuje plný název.',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'post',
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
add_action('init', 'webrev_register_wr_post_acf_fields', 20);

function webrev_register_wr_category_acf_fields()
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_wr_category_fields',
        'title' => 'Kategorie příspěvků',
        'fields' => [
            [
                'key' => 'field_category_main_page',
                'label' => 'Hlavní stránka',
                'name' => 'category_main_page',
                'type' => 'page_link',
                'post_type' => ['page'],
                'allow_null' => 1,
                'return_format' => 'url',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'taxonomy',
                    'operator' => '==',
                    'value' => 'category',
                ],
            ],
        ],
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
    ]);
}
add_action('init', 'webrev_register_wr_category_acf_fields', 20);
