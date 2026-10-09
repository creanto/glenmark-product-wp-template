<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_ProductFilters_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'gln-product-filters';
    }

    public function get_title()
    {
        return __('Product Filters', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-filter';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    protected function render()
    {
        if (!function_exists('glenmark_product_filters_shortcode')) {
            return;
        }

        echo glenmark_product_filters_shortcode();
    }
}