<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_scalable_canvas_admin_notice()
{
    if (!current_user_can('activate_plugins') || did_action('elementor/loaded')) {
        return;
    }

    echo '<div class="notice notice-warning"><p>' . esc_html__('Glenmark Hero requires an active Elementor installation with Container support.', 'wr-pharma-product') . '</p></div>';
}
add_action('admin_notices', 'webrev_scalable_canvas_admin_notice');

function webrev_scalable_canvas_load_element()
{
    if (!class_exists('\Elementor\Includes\Elements\Container', false)) {
        add_action('admin_notices', 'webrev_scalable_canvas_admin_notice');
        return;
    }

    require_once __DIR__ . '/elements/ScalableCanvasElement.php';
}

function webrev_scalable_canvas_register_element($elements_manager)
{
    if (!is_object($elements_manager) || !method_exists($elements_manager, 'register_element_type') || !class_exists('Webrev_Scalable_Canvas_Element')) {
        return;
    }

    if (method_exists($elements_manager, 'get_element_types') && $elements_manager->get_element_types('wr-hero')) {
        return;
    }

    $elements_manager->register_element_type(new Webrev_Scalable_Canvas_Element());
}

if (did_action('elementor/loaded')) {
    add_action('elementor/elements/elements_registered', 'webrev_scalable_canvas_load_element', 5);
    add_action('elementor/elements/elements_registered', 'webrev_scalable_canvas_register_element', 10);
} else {
    add_action('elementor/loaded', function () {
        add_action('elementor/elements/elements_registered', 'webrev_scalable_canvas_load_element', 5);
        add_action('elementor/elements/elements_registered', 'webrev_scalable_canvas_register_element', 10);
    });
}

function webrev_scalable_canvas_enqueue_assets()
{
    if (!did_action('elementor/loaded')) {
        return;
    }

    $css_path = webrev_get_theme_file_path('assets/css/components/scalable-canvas.css');
    $css_uri = webrev_get_theme_file_uri('assets/css/components/scalable-canvas.css');
    $js_path = webrev_get_theme_file_path('assets/js/scalable-canvas.js');
    $js_uri = webrev_get_theme_file_uri('assets/js/scalable-canvas.js');

    if ($css_uri && file_exists($css_path)) {
        wp_enqueue_style('webrev-scalable-canvas', $css_uri, [], (string) filemtime($css_path));
    }

    if ($js_uri && file_exists($js_path)) {
        wp_enqueue_script('webrev-scalable-canvas', $js_uri, ['jquery'], (string) filemtime($js_path), true);
    }
}
add_action('elementor/frontend/after_enqueue_styles', 'webrev_scalable_canvas_enqueue_assets');
add_action('elementor/editor/after_enqueue_styles', 'webrev_scalable_canvas_enqueue_assets');
add_action('elementor/frontend/after_enqueue_scripts', 'webrev_scalable_canvas_enqueue_assets');
add_action('elementor/editor/after_enqueue_scripts', 'webrev_scalable_canvas_enqueue_assets');
