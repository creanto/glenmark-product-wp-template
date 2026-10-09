<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_elementor_register_category($elements_manager)
{
    $elements_manager->add_category('glenmark', [
        'title' => __('Glenmark', 'gln-pharma-product'),
        'icon' => 'eicon-layout',
    ]);
}
add_action('elementor/elements/categories_registered', 'glenmark_elementor_register_category');

function glenmark_register_gln_elementor_widgets()
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
        'Glenmark_ProductCarousel_Widget',
        'Glenmark_ProductGrid_Widget',
        'Glenmark_ProductFilters_Widget',
        'Glenmark_ProductBox_Widget',
        'Glenmark_PostGrid_Widget',
        'Glenmark_Breadcrumbs_Widget',
        'Glenmark_Benefits_Widget',
        'Glenmark_CTA_Widget',
        'Glenmark_PharmacyGrid_Widget',
        'Glenmark_Rich_Heading_Widget',
        'Glenmark_Shared_Content_Widget',
    ];

    $widgets_manager = \Elementor\Plugin::instance()->widgets_manager;

    foreach ($widgets as $widget_class) {
        if (class_exists($widget_class)) {
            $widgets_manager->register(new $widget_class());
        }
    }
}
add_action('elementor/widgets/register', 'glenmark_register_gln_elementor_widgets');
