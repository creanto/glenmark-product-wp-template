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

function glenmark_shared_content_register_dynamic_tag($dynamic_tags)
{
    $dynamic_tags->register_group('glenmark-shared-content', [
        'title' => __('Glenmark', 'gln-pharma-product'),
    ]);
    $dynamic_tags->register(new Glenmark_Shared_Content_Dynamic_Tag());
}
add_action('elementor/dynamic_tags/register', 'glenmark_shared_content_register_dynamic_tag');