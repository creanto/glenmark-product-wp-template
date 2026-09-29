<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_register_product_feed_route()
{
    register_rest_route('wr-pharma-product/v1', '/product-feed', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'webrev_get_product_feed_response',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'webrev_register_product_feed_route');

function webrev_render_product_feed_discovery_link()
{
    echo '<link rel="alternate" type="application/ld+json" href="'
        . esc_url(rest_url('wr-pharma-product/v1/product-feed'))
        . '" title="Produktový katalog">' . "\n";
}
add_action('wp_head', 'webrev_render_product_feed_discovery_link', 1);

function webrev_get_product_feed_response()
{
    $products = get_posts([
        'post_type' => 'wr_product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
        'no_found_rows' => true,
        'suppress_filters' => false,
    ]);

    $graph = [];

    foreach ($products as $product) {
        $schema = webrev_get_product_feed_product($product->ID);

        if (!empty($schema)) {
            $graph[] = $schema;
        }
    }

    return new WP_REST_Response([
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    ], 200, [
        'Cache-Control' => 'public, max-age=3600',
        'Content-Type' => 'application/ld+json; charset=UTF-8',
    ]);
}

function webrev_get_product_feed_product($product_id)
{
    $product = webrev_get_wr_product_schema_data($product_id);

    if (empty($product)) {
        return [];
    }

    unset($product['@context']);

    $properties = isset($product['additionalProperty']) ? $product['additionalProperty'] : [];
    $field_properties = [
        'product_subtitle_more' => 'Dodatečný podtitul',
        'product_indication' => 'Indikace',
        'product_claim' => 'Claim',
        'product_main_benefits' => 'Hlavní benefity',
        'product_description' => 'Popis',
        'product_composition' => 'Složení',
        'product_packaging' => 'Balení',
        'product_dosage' => 'Dávkování',
        'product_legal_notice' => 'Upozornění',
        'product_sale_end' => 'Ukončení prodeje',
    ];

    foreach ($field_properties as $field_name => $label) {
        webrev_product_feed_add_property($properties, $label, get_field($field_name, $product_id));
    }

    webrev_product_feed_add_property(
        $properties,
        'V prodeji',
        get_field('product_for_sale', $product_id) ? 'Ano' : 'Ne'
    );
    webrev_product_feed_add_property(
        $properties,
        'VPOIS',
        get_field('product_vpois', $product_id) ? 'Ano' : 'Ne'
    );

    $spc_url = esc_url_raw((string) get_field('product_spc', $product_id));
    if ('' !== $spc_url) {
        webrev_product_feed_add_property($properties, 'SPC / příbalová informace', $spc_url);
    }

    $product_website_url = esc_url_raw((string) get_field('product_website_url', $product_id));
    if ('' !== $product_website_url) {
        $product['sameAs'] = [$product_website_url];
    }

    $image_urls = webrev_get_product_feed_image_urls($product_id);
    if (!empty($image_urls)) {
        $product['image'] = 1 === count($image_urls) ? $image_urls[0] : $image_urls;
    }

    $pharmacies = webrev_get_product_feed_pharmacies($product_id);
    foreach ($pharmacies as $pharmacy_name => $url) {
        webrev_product_feed_add_property($properties, 'Koupit u ' . $pharmacy_name, $url);
    }

    $registered_packages = get_field('product_registered_packages', $product_id);
    if (is_array($registered_packages)) {
        foreach ($registered_packages as $package) {
            $package_parts = [];

            foreach (['strength', 'quantity', 'sukl_code'] as $key) {
                if (!empty($package[$key])) {
                    $package_parts[] = webrev_prepare_schema_text_value($package[$key]);
                }
            }

            if (!empty($package_parts)) {
                $properties[] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Registrované balení',
                    'value' => implode(', ', $package_parts),
                ];
            }

            foreach (['spc' => 'SPC', 'pil' => 'PIL'] as $key => $label) {
                $url = isset($package[$key]) ? esc_url_raw((string) $package[$key]) : '';
                if ('' !== $url) {
                    webrev_product_feed_add_property($properties, $label . ' balení', $url);
                }
            }
        }
    }

    $registered_packages_url = esc_url_raw((string) get_field('product_registered_packages_url', $product_id));
    if ('' !== $registered_packages_url) {
        webrev_product_feed_add_property($properties, 'Registrovaná balení', $registered_packages_url);
    }

    $icon_rows = get_field('product_icons', $product_id);
    if (is_array($icon_rows)) {
        foreach ($icon_rows as $icon) {
            $label = isset($icon['label']) ? webrev_prepare_schema_text_value($icon['label']) : '';
            $description = isset($icon['description']) ? webrev_prepare_schema_text_value($icon['description']) : '';
            $value = trim($label . ('' !== $description ? ': ' . $description : ''));

            webrev_product_feed_add_property($properties, 'Vlastnost produktu', $value);
        }
    }

    if (!empty($properties)) {
        $product['additionalProperty'] = array_values($properties);
    }

    $variants = webrev_get_product_feed_variants($product_id);
    if (!empty($variants)) {
        $product['hasVariant'] = $variants;
    }

    return $product;
}

function webrev_product_feed_add_property(&$properties, $name, $value)
{
    $value = webrev_prepare_schema_text_value($value);

    if ('' === $value) {
        return;
    }

    $properties[] = [
        '@type' => 'PropertyValue',
        'name' => $name,
        'value' => $value,
    ];
}

function webrev_get_product_feed_image_urls($product_id)
{
    $image_ids = array_filter([
        get_post_thumbnail_id($product_id),
        get_field('product_packshot', $product_id),
    ]);
    $gallery = get_field('product_gallery', $product_id);

    if (is_array($gallery)) {
        $image_ids = array_merge($image_ids, $gallery);
    }

    $image_urls = [];
    foreach (array_unique($image_ids) as $image_id) {
        $image_url = webrev_get_product_feed_image_url($image_id);
        if ('' !== $image_url) {
            $image_urls[] = $image_url;
        }
    }

    return array_values(array_unique($image_urls));
}

function webrev_get_product_feed_image_url($image)
{
    if (is_array($image)) {
        $image = isset($image['ID']) ? $image['ID'] : (isset($image['id']) ? $image['id'] : '');
    }

    if (is_numeric($image)) {
        $image = wp_get_attachment_image_url((int) $image, 'large');
    }

    return is_string($image) ? esc_url_raw($image) : '';
}

function webrev_get_product_feed_pharmacies($product_id)
{
    $pharmacy_fields = [
        'product_pharmacy_benu' => 'Benu',
        'product_pharmacy_drmax' => 'Dr. Max',
        'product_pharmacy_lekarna_cz' => 'lekarna.cz',
        'product_pharmacy_magistra' => 'Magistra',
        'product_pharmacy_mojelekarna' => 'Moje lékárna',
        'product_pharmacy_euclekarna' => 'Euc lékárna',
        'product_pharmacy_alza' => 'Alza',
        'product_pharmacy_alphega' => 'Alphega',
        'product_pharmacy_pilulka' => 'Pilulka',
        'product_pharmacy_ave' => 'Lékárna Ave',
    ];

    $pharmacies = [];
    foreach ($pharmacy_fields as $field_name => $label) {
        $url = esc_url_raw((string) get_field($field_name, $product_id));
        if ('' !== $url) {
            $pharmacies[$label] = $url;
        }
    }

    return $pharmacies;
}

function webrev_get_product_feed_variants($product_id)
{
    $rows = get_field('product_package_variants', $product_id);
    if (!is_array($rows)) {
        return [];
    }

    $variants = [];
    $pharmacy_fields = [
        'variant_pharmacy_benu' => 'Benu',
        'variant_pharmacy_drmax' => 'Dr. Max',
        'variant_pharmacy_lekarna_cz' => 'lekarna.cz',
        'variant_pharmacy_magistra' => 'Magistra',
        'variant_pharmacy_mojelekarna' => 'Moje lékárna',
        'variant_pharmacy_euclekarna' => 'Euc lékárna',
        'variant_pharmacy_alza' => 'Alza',
        'variant_pharmacy_alphega' => 'Alphega',
        'variant_pharmacy_pilulka' => 'Pilulka',
        'variant_pharmacy_ave' => 'Lékárna Ave',
    ];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $name = webrev_prepare_schema_text_value(
            trim((isset($row['variant_name']) ? $row['variant_name'] : '') . ' '
                . (isset($row['variant_count']) ? $row['variant_count'] : ''))
        );
        if ('' === $name) {
            continue;
        }

        $variant = [
            '@type' => 'Product',
            'name' => $name,
        ];

        $image_url = webrev_get_product_feed_image_url(isset($row['variant_image']) ? $row['variant_image'] : '');
        if ('' !== $image_url) {
            $variant['image'] = $image_url;
        }

        $variant_properties = [];
        $variant_pharmacies = isset($row['variant_pharmacies']) && is_array($row['variant_pharmacies'])
            ? $row['variant_pharmacies']
            : [];

        foreach ($pharmacy_fields as $field_name => $label) {
            $url = isset($variant_pharmacies[$field_name])
                ? esc_url_raw((string) $variant_pharmacies[$field_name])
                : '';
            if ('' !== $url) {
                webrev_product_feed_add_property($variant_properties, 'Koupit u ' . $label, $url);
            }
        }

        if (!empty($variant_properties)) {
            $variant['additionalProperty'] = $variant_properties;
        }

        $variants[] = $variant;
    }

    return $variants;
}