<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_register_gln_product_acf_fields()
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $location = [
        [
            [
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'gln_product',
            ],
        ],
    ];

    $variant_pharmacy_fields = [];

    foreach (glenmark_get_pharmacy_sources() as $source) {
        $variant_pharmacy_fields[] = [
            'key' => 'field_product_package_variant_pharmacy_' . $source['key'],
            'label' => $source['label'],
            'name' => $source['variant_field'],
            'type' => 'url',
            'placeholder' => 'https://',
            'wrapper' => ['width' => '33.33'],
        ];
    }

    acf_add_local_field_group([
        'key' => 'group_gln_product_basic_information',
        'title' => 'Produkt - Základní informace',
        'fields' => [
            [
                'key' => 'field_product_full_name',
                'label' => 'Celý název',
                'name' => 'product_full_name',
                'type' => 'text',
                'wrapper' => ['width' => '33.33'],
            ],
            [
                'key' => 'field_product_name',
                'label' => 'Zkrácený název',
                'name' => 'product_name',
                'type' => 'text',
                'wrapper' => ['width' => '33.33'],
            ],
            [
                'key' => 'field_product_main_product',
                'label' => 'Hlavní produkt',
                'name' => 'product_main_product',
                'type' => 'true_false',
                'message' => 'Označit produkt jako hlavní pro výběry a výpisy na webu.',
                'default_value' => 0,
                'ui' => 1,
                'wrapper' => ['width' => '33.33'],
            ],
            [
                'key' => 'field_product_subtitle',
                'label' => 'Podtitul',
                'name' => 'product_subtitle',
                'type' => 'text',
                'wrapper' => ['width' => '25'],
            ],
            [
                'key' => 'field_product_claim',
                'label' => 'Claim',
                'name' => 'product_claim',
                'type' => 'text',
                'wrapper' => ['width' => '25'],
            ],
            [
                'key' => 'field_product_indication',
                'label' => 'Indikace',
                'name' => 'product_indication',
                'type' => 'text',
                'wrapper' => ['width' => '25'],
            ],
            [
                'key' => 'field_product_category',
                'label' => 'Kategorie produktu',
                'name' => 'product_category',
                'type' => 'taxonomy',
                'taxonomy' => 'gln_product_category',
                'field_type' => 'select',
                'add_term' => 1,
                'save_terms' => 1,
                'load_terms' => 1,
                'return_format' => 'object',
                'wrapper' => ['width' => '25'],
            ],
            [
                'key' => 'field_product_active_ingredients',
                'label' => 'Účinné látky',
                'name' => 'product_active_ingredients',
                'type' => 'textarea',
                'rows' => 3,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_contained_ingredients',
                'label' => 'Obsažené látky',
                'name' => 'product_contained_ingredients',
                'type' => 'textarea',
                'rows' => 3,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_short_description',
                'label' => 'Krátký popis',
                'name' => 'product_short_description',
                'type' => 'wysiwyg',
                'rows' => 3,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_main_benefits',
                'label' => 'Hlavní benefity',
                'name' => 'product_main_benefits',
                'type' => 'wysiwyg',
                'media_upload' => 0,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_composition',
                'label' => 'Kompletní složení',
                'name' => 'product_composition',
                'type' => 'wysiwyg',
                'rows' => 4,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_dosage',
                'label' => 'Dávkování',
                'name' => 'product_dosage',
                'type' => 'wysiwyg',
                'media_upload' => 0,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_legal_notice',
                'label' => 'Upozornění',
                'name' => 'product_legal_notice',
                'type' => 'wysiwyg',
                'media_upload' => 0,
                'wrapper' => [
                    'width' => '50',
                ],
            ]
        ],
        'location' => $location,
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            'discussion',
            'comments',
            'format',
        ],
    ]);

    acf_add_local_field_group([
        'key' => 'group_gln_product_schema',
        'title' => 'Produkt - Product schema',
        'fields' => [
            [
                'key' => 'field_product_schema_brand',
                'label' => 'Značka / brand',
                'name' => 'product_schema_brand',
                'type' => 'text',
                'placeholder' => '',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_schema_manufacturer',
                'label' => 'Výrobce / manufacturer',
                'name' => 'product_schema_manufacturer',
                'type' => 'text',
                'placeholder' => '',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_schema_sku',
                'label' => 'SKU',
                'name' => 'product_schema_sku',
                'type' => 'text',
                'placeholder' => 'SK-12345',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_schema_gtin13',
                'label' => 'GTIN-13',
                'name' => 'product_schema_gtin13',
                'type' => 'text',
                'placeholder' => '8591234567890',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_schema_packaging_size',
                'label' => 'Velikost balení',
                'name' => 'product_schema_packaging_size',
                'type' => 'text',
                'placeholder' => '30 tablet',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_schema_dosage_form',
                'label' => 'Léková forma',
                'name' => 'product_schema_dosage_form',
                'type' => 'text',
                'placeholder' => 'tableta',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_schema_sources',
                'label' => 'Kde údaje získat',
                'type' => 'message',
                'message' => '<p>Údaje o produktu (značka, výrobce, SKU a GTIN-13) lze dohledat v následujících databázích a katalozích:</p><ul><li><a href="https://prehledy.sukl.cz/prehled_leciv.html#/" target="_blank" rel="noopener noreferrer">SÚKL – Databáze léčivých přípravků</a><br>Oficiální informace o léčivých přípravcích, výrobcích, registracích a kódech SÚKL.</li><li><a href="https://www.gs1.org/services/verified-by-gs1" target="_blank" rel="noopener noreferrer">GS1 – Verified by GS1</a><br>Mezinárodní databáze pro ověřování GTIN (EAN).</li></ul>',
                'new_lines' => '',
            ],
        ],
        'location' => $location,
        'menu_order' => 2,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            'discussion',
            'comments',
            'format',
        ],
    ]);

    acf_add_local_field_group([
        'key' => 'group_gln_product_detailed_information',
        'title' => 'Produkt - obrázky',
        'fields' => [

            [
                'key' => 'field_product_packshot',
                'label' => 'Foto do slideru',
                'name' => 'product_packshot',
                'type' => 'image',
                'return_format' => 'id',
                'instructions' => 'Foto by mělo mít stejný poměr stran jako fotografie ostatních produktů.',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_gallery',
                'label' => 'Galerie',
                'name' => 'product_gallery',
                'type' => 'gallery',
                'return_format' => 'id',
                'preview_size' => 'medium',
                'insert' => 'append',
                'library' => 'all',
            ]

        ],
        'location' => $location,
        'menu_order' => 3,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            'discussion',
            'comments',
            'format',
        ],
    ]);

    acf_add_local_field_group([
        'key' => 'group_gln_product_icons',
        'title' => 'Produkt - Ikony',
        'fields' => [
            [
                'key' => 'field_product_icons',
                'label' => 'Ikony',
                'name' => 'product_icons',
                'type' => 'repeater',
                'layout' => 'row',
                'button_label' => 'Přidat ikonu',
                'min' => 0,
                'max' => 4,
                'sub_fields' => [
                    [
                        'key' => 'field_product_icon_image',
                        'label' => 'Obrázek',
                        'name' => 'image',
                        'type' => 'image',
                        'return_format' => 'id',
                        'wrapper' => [
                            'width' => '50',
                        ],
                    ],
                    [
                        'key' => 'field_product_icon_label',
                        'label' => 'Text',
                        'name' => 'label',
                        'type' => 'text',
                        'wrapper' => [
                            'width' => '50',
                        ],
                    ],
                    [
                        'key' => 'field_product_icon_description',
                        'label' => 'Popis',
                        'name' => 'description',
                        'type' => 'textarea',
                        'rows' => 3,
                    ],
                ],
            ],
        ],
        'location' => $location,
        'menu_order' => 4,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            'discussion',
            'comments',
            'format',
        ],
    ]);

    acf_add_local_field_group([
        'key' => 'group_gln_product_package_variants',
        'title' => 'Produkt - Produktové varianty (balení)',
        'fields' => [
            [
                'key' => 'field_product_package_variants',
                'label' => 'Varianty balení',
                'name' => 'product_package_variants',
                'type' => 'repeater',
                'layout' => 'block',
                'button_label' => 'Přidat variantu balení',
                'sub_fields' => [
                    [
                        'key' => 'field_product_package_variant_name',
                        'label' => 'Název balení',
                        'name' => 'variant_name',
                        'type' => 'text',
                        'wrapper' => [
                            'width' => '25',
                        ],
                    ],
                    [
                        'key' => 'field_product_registered_package_strength',
                        'label' => 'Síla',
                        'name' => 'strength',
                        'type' => 'text',
                        'wrapper' => [
                            'width' => '25',
                        ],
                    ],
                    [
                        'key' => 'field_product_package_variant_count',
                        'label' => 'Počet',
                        'name' => 'variant_count',
                        'type' => 'text',
                        'wrapper' => [
                            'width' => '25',
                        ],
                    ],
                    [
                        'key' => 'field_product_package_variant_is_primary',
                        'label' => 'Hlavní varianta',
                        'name' => 'variant_is_primary',
                        'type' => 'true_false',
                        'instructions' => 'Zobrazit jako první.',
                        'ui' => 1,
                        'wrapper' => [
                            'width' => '25',
                        ],
                    ],
                    [
                        'key' => 'field_product_registered_package_sukl_code',
                        'label' => 'Kód SÚKL',
                        'name' => 'sukl_code',
                        'type' => 'text',
                        'wrapper' => [
                            'width' => '33.33',
                        ],
                    ],
                    [
                        'key' => 'field_product_registered_package_spc',
                        'label' => 'SPC',
                        'name' => 'spc',
                        'type' => 'url',
                        'wrapper' => [
                            'width' => '33.33',
                        ],
                    ],
                    [
                        'key' => 'field_product_registered_package_pil',
                        'label' => 'PIL',
                        'name' => 'pil',
                        'type' => 'url',
                        'wrapper' => [
                            'width' => '33.33',
                        ],
                    ],
                    [
                        'key' => 'field_product_package_variant_image',
                        'label' => 'Náhledový obrázek',
                        'name' => 'variant_image',
                        'type' => 'image',
                        'return_format' => 'id',
                        'preview_size' => 'medium',
                        'wrapper' => [
                            'width' => '100',
                        ],
                    ],
                    [
                        'key' => 'field_product_package_variant_pharmacies',
                        'label' => 'Odkazy na lékárny',
                        'name' => 'variant_pharmacies',
                        'type' => 'group',
                        'sub_fields' => $variant_pharmacy_fields,
                        'wrapper' => [
                            'width' => '100',
                        ],
                    ],
                ],
            ],
        ],
        'location' => $location,
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            'discussion',
            'comments',
            'format',
        ],
    ]);
}
add_action('init', 'glenmark_register_gln_product_acf_fields', 20);
