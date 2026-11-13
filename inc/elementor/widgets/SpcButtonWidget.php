<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Spc_Button_Widget extends \Elementor\Widget_Button
{
    public function get_name()
    {
        return 'gln-spc-button';
    }

    public function get_title()
    {
        return __('Odkaz na SPC', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-button';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    public function get_keywords()
    {
        return ['glenmark', 'spc', 'dokument', 'produkt', 'tlačítko'];
    }

    protected function register_controls()
    {
        parent::register_controls();
        $this->remove_control('link');
        $this->update_control('text', [
            'default' => __('Příbalová informace', 'gln-pharma-product'),
            'placeholder' => __('Příbalová informace', 'gln-pharma-product'),
        ]);

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

        $this->start_controls_section('section_spc_product', [
            'label' => __('Produkt a balení', 'gln-pharma-product'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('spc_product_id', [
            'label' => __('Produkt', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $product_options,
            'label_block' => true,
        ]);

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
        $this->add_control('spc_variant_index', [
            'label' => __('Varianta balení', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $variant_options,
        ]);
        $this->end_controls_section();
    }

    public function get_settings_for_display($setting_key = null)
    {
        $settings = parent::get_settings_for_display($setting_key);
        if (null !== $setting_key && 'link' !== $setting_key) {
            return $settings;
        }

        $link = [
            'url' => $this->get_spc_url(),
            'is_external' => 'on',
            'nofollow' => '',
        ];

        if ('link' === $setting_key) {
            return $link;
        }

        $settings['link'] = $link;
        return $settings;
    }

    private function get_spc_url()
    {
        $settings = parent::get_settings_for_display();
        $product_id = isset($settings['spc_product_id']) ? absint($settings['spc_product_id']) : 0;

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

        $variants = $product_id ? glenmark_get_product_document_variants($product_id) : [];
        $variant_index = isset($settings['spc_variant_index']) ? absint($settings['spc_variant_index']) : 0;
        $variant = $variants[$variant_index] ?? ($variants[0] ?? []);
        $url = isset($variant['spc']) ? esc_url_raw((string) $variant['spc']) : '';

        if ('' !== $url) {
            return $url;
        }

        $sukl_code = isset($variant['sukl_code'])
            ? sanitize_text_field((string) $variant['sukl_code'])
            : '';
        if ('' === $sukl_code) {
            return '';
        }

        $sukl_code = str_pad($sukl_code, 7, '0', STR_PAD_LEFT);
        $base_url = get_theme_mod(
            'glenmark_sukl_detail_url',
            'https://prehledy.sukl.cz/prehled_leciv.html#/detail-reg/'
        );

        return esc_url_raw(untrailingslashit((string) $base_url) . '/' . rawurlencode($sukl_code));
    }

    protected function content_template()
    {
        ?>
        <# if (''===settings.text && ''===settings.selected_icon.value) { return; } const
            optimizedMarkup=elementorCommon.config.experimentalFeatures.e_optimized_markup;
            view.addRenderAttribute('wrapper', 'class' , 'elementor-button-wrapper' ); view.addRenderAttribute('button', 'class'
            , 'elementor-button' ); view.addRenderAttribute('button', 'role' , 'button' ); if (settings.button_css_id) {
            view.addRenderAttribute('button', 'id' , settings.button_css_id); } if (settings.size) {
            view.addRenderAttribute('button', 'class' , 'elementor-size-' + settings.size); } else {
            view.addRenderAttribute('button', 'class' , 'elementor-size-sm' ); } if (settings.hover_animation) {
            view.addRenderAttribute('button', 'class' , 'elementor-animation-' + settings.hover_animation); }
            view.addRenderAttribute('icon', 'class' , 'elementor-button-icon' ); view.addRenderAttribute('text', 'class'
            , 'elementor-button-text' ); view.addInlineEditingAttributes('text', 'none' ); const
            iconHTML=elementor.helpers.renderIcon(view, settings.selected_icon, { 'aria-hidden' : true }, 'i' , 'object' );
            const migrated=elementor.helpers.isIconMigrated(settings, 'selected_icon' ); #>
            <# if (!optimizedMarkup) { #>
                <div {{{ view.getRenderAttributeString('wrapper') }}}>
                    <# } #>
                        <a {{{ view.getRenderAttributeString('button') }}}>
                            <span class="elementor-button-content-wrapper">
                                <# if (settings.icon || settings.selected_icon) { #>
                                    <span {{{ view.getRenderAttributeString('icon') }}}>
                                        <# if ((migrated || !settings.icon) && iconHTML.rendered) { #>
                                            {{{ iconHTML.value }}}
                                            <# } else { #>
                                                <i class="{{ settings.icon }}" aria-hidden="true"></i>
                                                <# } #>
                                    </span>
                                    <# } #>
                                        <# if (settings.text) { #>
                                            <span {{{ view.getRenderAttributeString('text') }}}>{{{ settings.text }}}</span>
                                            <# } #>
                            </span>
                        </a>
                        <# if (!optimizedMarkup) { #>
                </div>
                <# } #>
                    <?php
    }
}