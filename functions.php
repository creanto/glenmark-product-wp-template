<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_prepare_schema_text_value($value)
{
    $text = wp_strip_all_tags((string) $value);
    $text = preg_replace('/\s+/u', ' ', $text);

    return trim((string) $text);
}

function webrev_get_wr_product_schema_additional_properties($product_id)
{
    $properties = [];

    $packaging_size = webrev_prepare_schema_text_value(
        get_field('product_schema_packaging_size', $product_id) ?: get_field('product_packaging', $product_id) ?: ''
    );

    if ('' !== $packaging_size) {
        $properties[] = [
            '@type' => 'PropertyValue',
            'name' => 'Velikost balení',
            'value' => $packaging_size,
        ];
    }

    $dosage_form = webrev_prepare_schema_text_value(
        get_field('product_schema_dosage_form', $product_id) ?: get_field('product_dosage', $product_id) ?: ''
    );

    if ('' !== $dosage_form) {
        $properties[] = [
            '@type' => 'PropertyValue',
            'name' => 'Léková forma',
            'value' => $dosage_form,
        ];
    }

    $active_ingredients = webrev_prepare_schema_text_value(get_field('product_active_ingredients', $product_id) ?: '');
    if ('' !== $active_ingredients) {
        $properties[] = [
            '@type' => 'PropertyValue',
            'name' => 'Účinné látky',
            'value' => $active_ingredients,
        ];
    }

    $contained_ingredients = webrev_prepare_schema_text_value(get_field('product_contained_ingredients', $product_id) ?: '');
    if ('' !== $contained_ingredients) {
        $properties[] = [
            '@type' => 'PropertyValue',
            'name' => 'Obsažené látky',
            'value' => $contained_ingredients,
        ];
    }

    return $properties;
}

function webrev_get_wr_product_schema_data($product_id)
{
    if (empty($product_id) || 'wr_product' !== get_post_type($product_id)) {
        return [];
    }

    $product_name = webrev_prepare_schema_text_value(get_field('product_name', $product_id) ?: get_the_title($product_id));
    $product_subtitle = webrev_prepare_schema_text_value(get_field('product_subtitle', $product_id) ?: '');
    $product_full_name = webrev_prepare_schema_text_value(get_field('product_full_name', $product_id) ?: trim($product_name . ' ' . $product_subtitle));
    $product_name_final = '' !== $product_full_name ? $product_full_name : $product_name;

    $description = get_field('product_short_description', $product_id) ?: get_field('product_description', $product_id) ?: get_the_excerpt($product_id);
    $description_clean = webrev_prepare_schema_text_value($description ?: get_post_field('post_content', $product_id));

    $image_id = get_post_thumbnail_id($product_id) ?: get_field('product_packshot', $product_id) ?: 0;
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';

    $permalink = get_permalink($product_id);
    $canonical_url = $permalink ? untrailingslashit((string) $permalink) : '';
    $product_url = $canonical_url ?: home_url('/');

    $category_terms = wp_get_post_terms($product_id, 'wr_product_category', ['fields' => 'names']);
    $categories = [];

    if (!is_wp_error($category_terms) && is_array($category_terms)) {
        foreach ($category_terms as $category_name) {
            $clean_category = webrev_prepare_schema_text_value($category_name);

            if ('' !== $clean_category) {
                $categories[] = $clean_category;
            }
        }
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        '@id' => $product_url . '#product',
        'name' => $product_name_final,
        'url' => $product_url,
    ];

    if ('' !== $description_clean) {
        $schema['description'] = $description_clean;
    }

    if ('' !== $image_url) {
        $schema['image'] = $image_url;
    }

    if (!empty($categories)) {
        $schema['category'] = 1 === count($categories) ? $categories[0] : array_values(array_unique($categories));
    }

    $brand = webrev_prepare_schema_text_value(get_field('product_schema_brand', $product_id) ?: '');
    if ('' !== $brand) {
        $schema['brand'] = [
            '@type' => 'Brand',
            'name' => $brand,
        ];
    }

    $manufacturer = webrev_prepare_schema_text_value(get_field('product_schema_manufacturer', $product_id) ?: '');
    if ('' !== $manufacturer) {
        $schema['manufacturer'] = [
            '@type' => 'Organization',
            'name' => $manufacturer,
        ];
    }

    $sku = webrev_prepare_schema_text_value(get_field('product_schema_sku', $product_id) ?: '');
    if ('' !== $sku) {
        $schema['sku'] = $sku;
    }

    $gtin13 = webrev_prepare_schema_text_value(get_field('product_schema_gtin13', $product_id) ?: '');
    if ('' !== $gtin13) {
        $schema['gtin13'] = $gtin13;
    }

    $additional_properties = webrev_get_wr_product_schema_additional_properties($product_id);
    if (!empty($additional_properties)) {
        $schema['additionalProperty'] = $additional_properties;
    }

    return $schema;
}

function webrev_get_wr_product_schema_for_current_context()
{
    if (is_singular('wr_product')) {
        return webrev_get_product_feed_product(get_queried_object_id());
    }

    if (is_front_page()) {
        $homepage_products = get_posts([
            'post_type' => 'wr_product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'product_main_product',
                    'value' => '1',
                    'compare' => '=',
                ],
            ],
        ]);

        if (!empty($homepage_products)) {
            return webrev_get_product_feed_product($homepage_products[0]->ID);
        }
    }

    return [];
}

function webrev_render_wr_product_schema()
{
    if (is_admin() || wp_doing_ajax() || is_feed()) {
        return;
    }

    $schema = webrev_get_wr_product_schema_for_current_context();
    if (empty($schema)) {
        return;
    }

    $schema = ['@context' => 'https://schema.org'] + $schema;

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}

add_action('wp_head', 'webrev_render_wr_product_schema', 20);

require_once __DIR__ . '/inc/bootstrap.php';
