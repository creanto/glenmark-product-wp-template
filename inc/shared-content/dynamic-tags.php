<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Shared_Content_Dynamic_Tag extends \Elementor\Core\DynamicTags\Tag
{
    public function get_name()
    {
        return 'glenmark-shared-content-field';
    }

    public function get_title()
    {
        return __('Glenmark: krátký sdílený údaj', 'gln-pharma-product');
    }

    public function get_group()
    {
        return 'glenmark-shared-content';
    }

    public function get_categories()
    {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
        ];
    }

    protected function register_controls()
    {
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $items = glenmark_shared_content_get_items($is_editor);
        $options = ['' => __('Vyberte krátký údaj', 'gln-pharma-product')];

        foreach ($items as $key => $item) {
            if ('contact' !== ($item['type'] ?? '') && 'contact' !== ($item['data']['content_type'] ?? '')) {
                continue;
            }
            $options[$key] = (string) ($item['title'] ?? $key) . ' (' . $key . ')';
        }

        $this->add_control('item_key', [
            'label' => __('Krátký údaj', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $options,
            'label_block' => true,
        ]);
        $this->add_control('field', [
            'label' => __('Hodnota', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'company_name' => __('Název společnosti', 'gln-pharma-product'),
                'building' => __('Budova', 'gln-pharma-product'),
                'street' => __('Ulice a číslo', 'gln-pharma-product'),
                'postal_code' => __('PSČ', 'gln-pharma-product'),
                'city' => __('Město', 'gln-pharma-product'),
                'country' => __('Země', 'gln-pharma-product'),
                'phone' => __('Telefon', 'gln-pharma-product'),
                'email_primary' => __('Hlavní e-mail', 'gln-pharma-product'),
                'email_secondary' => __('Další e-mail', 'gln-pharma-product'),
                'link_label' => __('Text odkazu', 'gln-pharma-product'),
                'website_url' => __('URL', 'gln-pharma-product'),
            ],
        ]);

    }

    public function get_content(array $options = [])
    {
        $settings = $this->get_settings_for_display();
        $key = isset($settings['item_key']) ? sanitize_text_field((string) $settings['item_key']) : '';
        $field = isset($settings['field']) ? sanitize_key((string) $settings['field']) : '';
        if ('' === $key || '' === $field) {
            return '';
        }

        $item = glenmark_shared_content_get_item($key);
        $value = $item['data'][$field] ?? '';
        if (!is_scalar($value)) {
            return '';
        }

        return 'website_url' === $field
            ? esc_url_raw((string) $value)
            : sanitize_text_field((string) $value);
    }
}

class Glenmark_Sukl_Dynamic_Tag extends \Elementor\Core\DynamicTags\Tag
{
    public function get_name()
    {
        return 'glenmark-sukl-url';
    }

    public function get_title()
    {
        return __('Glenmark: odkaz na SÚKL', 'gln-pharma-product');
    }

    public function get_group()
    {
        return 'glenmark-shared-content';
    }

    public function get_categories()
    {
        return [
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
        ];
    }

    protected function register_controls()
    {
        $products = get_posts([
            'post_type' => 'gln_product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'no_found_rows' => true,
        ]);
        $product_options = [
            '' => __('Hlavní produkt', 'gln-pharma-product'),
        ];
        $variant_count = 0;

        foreach ($products as $product) {
            $product_options[$product->ID] = $product->post_title;
            $variant_count = max(
                $variant_count,
                count(glenmark_get_product_document_variants($product->ID))
            );
        }

        $variant_options = [
            '' => __('Hlavní varianta nebo první', 'gln-pharma-product'),
        ];
        for ($index = 1; $index < $variant_count; $index++) {
            $variant_options[(string) $index] = sprintf(
                /* translators: %d: package variant position */
                __('Varianta balení %d', 'gln-pharma-product'),
                $index + 1
            );
        }

        $this->add_control('product_id', [
            'label' => __('Produkt', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $product_options,
            'label_block' => true,
        ]);
        $this->add_control('variant_index', [
            'label' => __('Varianta balení', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $variant_options,
        ]);
    }

    public function get_content(array $options = [])
    {
        $settings = $this->get_settings_for_display();
        $product_id = isset($settings['product_id']) ? absint($settings['product_id']) : 0;

        if (
            !$product_id
            || 'gln_product' !== get_post_type($product_id)
            || 'publish' !== get_post_status($product_id)
        ) {
            $main_products = get_posts([
                'post_type' => 'gln_product',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
                'meta_query' => [
                    [
                        'key' => 'product_main_product',
                        'value' => '1',
                        'compare' => '=',
                    ],
                ],
                'no_found_rows' => true,
            ]);
            $product_id = !empty($main_products) ? (int) $main_products[0]->ID : 0;

            if (!$product_id && is_singular('gln_product')) {
                $context_product_id = get_queried_object_id();
                if ('publish' === get_post_status($context_product_id)) {
                    $product_id = (int) $context_product_id;
                }
            }
        }

        if (!$product_id) {
            return '';
        }

        $variants = glenmark_get_product_document_variants($product_id);
        $variant_index = isset($settings['variant_index']) ? absint($settings['variant_index']) : 0;
        $variant = $variants[$variant_index] ?? ($variants[0] ?? []);
        $sukl_code = isset($variant['sukl_code'])
            ? str_pad(sanitize_text_field((string) $variant['sukl_code']), 7, '0', STR_PAD_LEFT)
            : '';

        if ('' === $sukl_code) {
            return '';
        }

        $base_url = get_theme_mod('glenmark_sukl_detail_url', 'https://prehledy.sukl.cz/prehled_leciv.html#/detail-reg/');
        $url = untrailingslashit((string) $base_url) . '/' . rawurlencode($sukl_code);

        return esc_url_raw($url);
    }
}

function glenmark_shared_content_register_dynamic_tag($dynamic_tags)
{
    $dynamic_tags->register_group('glenmark-shared-content', [
        'title' => __('Glenmark data', 'gln-pharma-product'),
    ]);
    $dynamic_tags->register(new Glenmark_Shared_Content_Dynamic_Tag());
    $dynamic_tags->register(new Glenmark_Sukl_Dynamic_Tag());
}
add_action('elementor/dynamic_tags/register', 'glenmark_shared_content_register_dynamic_tag');