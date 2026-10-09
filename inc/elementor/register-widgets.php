<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_elementor_register_category($elements_manager)
{
    $elements_manager->add_category('webrev', [
        'title' => __('Glenmark', 'wr-pharma-product'),
        'icon' => 'eicon-layout',
    ]);
}
add_action('elementor/elements/categories_registered', 'webrev_elementor_register_category');

function webrev_register_wr_elementor_widgets()
{
    if (!class_exists('\Elementor\Plugin')) {
        return;
    }

    $widget_dir = __DIR__ . '/widgets';
    $widget_files = [
        $widget_dir . '/ProductCarouselWidget.php',
        $widget_dir . '/ProductGridWidget.php',
        $widget_dir . '/ProductFiltersWidget.php',
        $widget_dir . '/ProductBoxWidget.php',
        $widget_dir . '/PostGridWidget.php',
        $widget_dir . '/BreadcrumbsWidget.php',
        $widget_dir . '/BenefitsWidget.php',
        $widget_dir . '/CTAWidget.php',
        $widget_dir . '/PharmacyGridWidget.php',
        $widget_dir . '/RichHeadingWidget.php',
        $widget_dir . '/SharedContentWidget.php',
    ];

    foreach ($widget_files as $widget_file) {
        if (file_exists($widget_file)) {
            require_once $widget_file;
        }
    }

    $widgets = [
        'Webrev_ProductCarousel_Widget',
        'Webrev_ProductGrid_Widget',
        'Webrev_ProductFilters_Widget',
        'Webrev_ProductBox_Widget',
        'Webrev_PostGrid_Widget',
        'Webrev_Breadcrumbs_Widget',
        'Webrev_Benefits_Widget',
        'Webrev_CTA_Widget',
        'Webrev_PharmacyGrid_Widget',
        'Webrev_Rich_Heading_Widget',
        'Webrev_Shared_Content_Widget',
    ];

    $widgets_manager = \Elementor\Plugin::instance()->widgets_manager;

    foreach ($widgets as $widget_class) {
        if (class_exists($widget_class)) {
            $widgets_manager->register(new $widget_class());
        }
    }
}
add_action('elementor/widgets/register', 'webrev_register_wr_elementor_widgets');
