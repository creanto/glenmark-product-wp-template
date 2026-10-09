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

    acf_add_local_field_group([
        'key' => 'group_gln_product_basic_information',
        'title' => 'Produkt - Základní informace',
        'fields' => [
            [
                'key' => 'field_product_full_name',
                'label' => 'Celý název',
                'name' => 'product_full_name',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_name',
                'label' => 'Název',
                'name' => 'product_name',
                'type' => 'text',
                'wrapper' => ['width' => '25'],
            ],
            [
                'key' => 'field_product_subtitle',
                'label' => 'Podtitul',
                'name' => 'product_subtitle',
                'type' => 'text',
                'wrapper' => ['width' => '25'],
            ],
            [
                'key' => 'field_product_subtitle_more',
                'label' => 'Dodatečný podtitul',
                'name' => 'product_subtitle_more',
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
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_indication',
                'label' => 'Indikace',
                'name' => 'product_indication',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_claim',
                'label' => 'Claim',
                'name' => 'product_claim',
                'type' => 'text',
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
                'key' => 'field_product_short_description',
                'label' => 'Krátký popis',
                'name' => 'product_short_description',
                'type' => 'wysiwyg',
                'rows' => 3,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_description',
                'label' => 'Popis',
                'name' => 'product_description',
                'type' => 'wysiwyg',
                'media_upload' => 1,
                'wrapper' => ['width' => '100'],
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
                'key' => 'field_product_composition',
                'label' => 'Složení',
                'name' => 'product_composition',
                'type' => 'wysiwyg',
                'rows' => 4,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_packaging',
                'label' => 'Balení',
                'name' => 'product_packaging',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_dosage',
                'label' => 'Dávkování',
                'name' => 'product_dosage',
                'type' => 'textarea',
                'rows' => 3,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_website_url',
                'label' => 'Domovská stránka',
                'name' => 'product_website_url',
                'type' => 'url',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_vpois',
                'label' => 'VPOIS',
                'name' => 'product_vpois',
                'type' => 'true_false',
                'default_value' => 1,
                'ui' => 1,
                'wrapper' => [
                    'width' => '25',
                ],
            ],
            [
                'key' => 'field_product_for_sale',
                'label' => 'V prodeji',
                'name' => 'product_for_sale',
                'type' => 'true_false',
                'default_value' => 1,
                'ui' => 1,
                'wrapper' => [
                    'width' => '25',
                ],
            ],
            [
                'key' => 'field_product_sale_end',
                'label' => 'Ukončení prodeje',
                'name' => 'product_sale_end',
                'type' => 'text',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_spc',
                'label' => 'SPC (příbalová informace)',
                'name' => 'product_spc',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_legal_notice',
                'label' => 'Upozornění',
                'name' => 'product_legal_notice',
                'type' => 'wysiwyg',
                'media_upload' => 0,
                'wrapper' => [
                    'width' => '100',
                ],
            ],
        ],
        'location' => $location,
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            /* 'the_content', */
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
                'key' => 'field_product_main_product',
                'label' => 'Hlavní produkt',
                'name' => 'product_main_product',
                'type' => 'true_false',
                'message' => 'Použít tento produkt jako hlavní produkt pro Schema.org na homepage.',
                'default_value' => 0,
                'ui' => 1,
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_schema_brand',
                'label' => 'Značka / brand',
                'name' => 'product_schema_brand',
                'type' => 'text',
                'placeholder' => 'Glenmark',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_product_schema_manufacturer',
                'label' => 'Výrobce / manufacturer',
                'name' => 'product_schema_manufacturer',
                'type' => 'text',
                'placeholder' => 'Glenmark Pharmaceuticals',
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
        ],
        'location' => $location,
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            'the_content',
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
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            'the_content',
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
        'menu_order' => 2,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            'the_content',
            'discussion',
            'comments',
            'format',
        ],
    ]);

    acf_add_local_field_group([
        'key' => 'group_gln_product_pharmacies',
        'title' => 'Produkt - Lékárny',
        'fields' => [
            [
                'key' => 'field_product_pharmacy_benu',
                'label' => 'Benu',
                'name' => 'product_pharmacy_benu',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_pharmacy_drmax',
                'label' => 'Dr. Max',
                'name' => 'product_pharmacy_drmax',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_pharmacy_lekarna_cz',
                'label' => 'lekarna.cz',
                'name' => 'product_pharmacy_lekarna_cz',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_pharmacy_magistra',
                'label' => 'Magistra',
                'name' => 'product_pharmacy_magistra',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_pharmacy_mojelekarna',
                'label' => 'Moje lékárna',
                'name' => 'product_pharmacy_mojelekarna',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_pharmacy_euclekarna',
                'label' => 'Euc lékárna',
                'name' => 'product_pharmacy_euclekarna',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_pharmacy_alza',
                'label' => 'Alza',
                'name' => 'product_pharmacy_alza',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_pharmacy_alphega',
                'label' => 'Alphega',
                'name' => 'product_pharmacy_alphega',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_pharmacy_pilulka',
                'label' => 'Pilulka',
                'name' => 'product_pharmacy_pilulka',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_product_pharmacy_ave',
                'label' => 'Lékárna Ave',
                'name' => 'product_pharmacy_ave',
                'type' => 'url',
                'placeholder' => 'https://',
                'wrapper' => [
                    'width' => '50',
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
            'the_content',
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
                            'width' => '33.33',
                        ],
                    ],
                    [
                        'key' => 'field_product_package_variant_count',
                        'label' => 'Počet',
                        'name' => 'variant_count',
                        'type' => 'text',
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
                            'width' => '33.33',
                        ],
                    ],
                    [
                        'key' => 'field_product_package_variant_pharmacies',
                        'label' => 'Odkazy na lékárny',
                        'name' => 'variant_pharmacies',
                        'type' => 'group',
                        'sub_fields' => [
                            [
                                'key' => 'field_product_package_variant_pharmacy_benu',
                                'label' => 'Benu',
                                'name' => 'variant_pharmacy_benu',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                            [
                                'key' => 'field_product_package_variant_pharmacy_drmax',
                                'label' => 'Dr. Max',
                                'name' => 'variant_pharmacy_drmax',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                            [
                                'key' => 'field_product_package_variant_pharmacy_lekarna_cz',
                                'label' => 'lekarna.cz',
                                'name' => 'variant_pharmacy_lekarna_cz',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                            [
                                'key' => 'field_product_package_variant_pharmacy_magistra',
                                'label' => 'Magistra',
                                'name' => 'variant_pharmacy_magistra',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                            [
                                'key' => 'field_product_package_variant_pharmacy_mojelekarna',
                                'label' => 'Moje lékárna',
                                'name' => 'variant_pharmacy_mojelekarna',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                            [
                                'key' => 'field_product_package_variant_pharmacy_euclekarna',
                                'label' => 'Euc lékárna',
                                'name' => 'variant_pharmacy_euclekarna',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                            [
                                'key' => 'field_product_package_variant_pharmacy_alza',
                                'label' => 'Alza',
                                'name' => 'variant_pharmacy_alza',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                            [
                                'key' => 'field_product_package_variant_pharmacy_alphega',
                                'label' => 'Alphega',
                                'name' => 'variant_pharmacy_alphega',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                            [
                                'key' => 'field_product_package_variant_pharmacy_pilulka',
                                'label' => 'Pilulka',
                                'name' => 'variant_pharmacy_pilulka',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                            [
                                'key' => 'field_product_package_variant_pharmacy_ave',
                                'label' => 'Lékárna Ave',
                                'name' => 'variant_pharmacy_ave',
                                'type' => 'url',
                                'placeholder' => 'https://',
                                'wrapper' => [
                                    'width' => '33.33',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'location' => $location,
        'menu_order' => 5,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => [
            'the_content',
            'discussion',
            'comments',
            'format',
        ],
    ]);

    acf_add_local_field_group([
        'key' => 'group_gln_product_registered_packages',
        'title' => 'Produkt - Registrovaná balení',
        'fields' => [
            [
                'key' => 'field_product_registered_packages',
                'label' => 'Balení',
                'name' => 'product_registered_packages',
                'type' => 'repeater',
                'instructions' => 'Pokud je vyplněn kód SÚKL, odkazy na SPC a PIL lze vytvořit podle něj. Jinak vyplňte přímo odkazy.',
                'layout' => 'table',
                'button_label' => 'Přidat balení',
                'sub_fields' => [
                    [
                        'key' => 'field_product_registered_package_strength',
                        'label' => 'Síla',
                        'name' => 'strength',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'field_product_registered_package_quantity',
                        'label' => 'Množství',
                        'name' => 'quantity',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'field_product_registered_package_sukl_code',
                        'label' => 'Kód SÚKL',
                        'name' => 'sukl_code',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'field_product_registered_package_spc',
                        'label' => 'SPC',
                        'name' => 'spc',
                        'type' => 'url',
                    ],
                    [
                        'key' => 'field_product_registered_package_pil',
                        'label' => 'PIL',
                        'name' => 'pil',
                        'type' => 'url',
                    ],
                ],
            ],
            [
                'key' => 'field_product_registered_packages_url',
                'label' => 'Odkaz na seznam registrovaných balení',
                'name' => 'product_registered_packages_url',
                'type' => 'url',
            ],
        ],
        'location' => $location,
        'menu_order' => 6,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
    ]);
}
add_action('init', 'glenmark_register_gln_product_acf_fields', 20);
