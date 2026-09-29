<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_ProductFilters_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wr-product-filters';
    }

    public function get_title()
    {
        return __('Product Filters', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-filter';
    }

    public function get_categories()
    {
        return ['webrev'];
    }

    protected function render()
    {
        if (!function_exists('webrev_product_filters_shortcode')) {
            return;
        }

        echo webrev_product_filters_shortcode();
    }
}