<?php

// add_filter('elementor_admin_notice_should_display', '__return_false');
// add_filter('elementor_dashboard_notice_display', '__return_false');

function glenmark_enable_elementor_for_gln_products()
{
    add_post_type_support('gln_product', 'elementor');

    $supported_post_types = get_option('elementor_cpt_support', []);
    $supported_post_types = is_array($supported_post_types) ? $supported_post_types : [];

    if (!in_array('gln_product', $supported_post_types, true)) {
        $supported_post_types[] = 'gln_product';
        update_option('elementor_cpt_support', array_values($supported_post_types));
    }
}
add_action('init', 'glenmark_enable_elementor_for_gln_products', 20);

function glenmark_force_product_single_template($template)
{
    if (!is_singular('gln_product')) {
        return $template;
    }

    $product_template = locate_template('single-gln_product.php');

    return $product_template ?: $template;
}
add_filter('template_include', 'glenmark_force_product_single_template', 99);

add_filter('custom_menu_order', function () {
    global $submenu;

    if (isset($submenu['elementor']) && is_array($submenu['elementor'])) {
        $submenu['elementor'] = array_filter($submenu['elementor'], function ($item) {
            return !in_array('elementor-one-upgrade', $item, true);
        });
    }

    return true;
}, 999);

function glenmark_add_tinymce_toolbar_buttons($buttons)
{
    $extra_buttons = ['formatselect', 'fontsizeselect', 'fontsize', 'forecolor', 'backcolor'];

    foreach ($extra_buttons as $button) {
        if (!in_array($button, $buttons, true)) {
            $buttons[] = $button;
        }
    }

    return $buttons;
}

function glenmark_normalize_tinymce_toolbar($toolbar, $extra_buttons)
{
    if (!is_string($toolbar)) {
        return implode(',', $extra_buttons);
    }

    $items = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $toolbar))));

    foreach ($extra_buttons as $button) {
        if (!in_array($button, $items, true)) {
            $items[] = $button;
        }
    }

    return implode(',', $items);
}

function glenmark_elementor_extend_tinymce_toolbar($settings)
{
    if (!is_array($settings)) {
        return $settings;
    }

    $extra_buttons = ['formatselect', 'fontsizeselect', 'fontsize', 'forecolor', 'backcolor'];
    $groups = ['tinymce', 'tinyMCE', 'initial_settings', 'initial_document'];

    foreach ($groups as $group) {
        if (!isset($settings[$group]) || !is_array($settings[$group])) {
            continue;
        }

        $group_settings = $settings[$group];
        $source = $group_settings;

        if (isset($group_settings['settings']) && is_array($group_settings['settings'])) {
            $source = $group_settings['settings'];
        }

        if (isset($source['toolbar1']) && is_string($source['toolbar1'])) {
            $source['toolbar1'] = glenmark_normalize_tinymce_toolbar($source['toolbar1'], $extra_buttons);
            if (isset($group_settings['settings']) && is_array($group_settings['settings'])) {
                $group_settings['settings'] = $source;
            } else {
                $group_settings = array_merge($group_settings, $source);
            }

            $settings[$group] = $group_settings;
            return $settings;
        }

        if (isset($source['toolbar']) && is_string($source['toolbar'])) {
            $source['toolbar'] = glenmark_normalize_tinymce_toolbar($source['toolbar'], $extra_buttons);
            if (isset($group_settings['settings']) && is_array($group_settings['settings'])) {
                $group_settings['settings'] = $source;
            } else {
                $group_settings = array_merge($group_settings, $source);
            }

            $settings[$group] = $group_settings;
            return $settings;
        }
    }

    if (!isset($settings['initial_settings']) || !is_array($settings['initial_settings'])) {
        $settings['initial_settings'] = [];
    }

    if (!isset($settings['initial_settings']['toolbar1']) || !is_string($settings['initial_settings']['toolbar1'])) {
        $settings['initial_settings']['toolbar1'] = implode(',', $extra_buttons);
    } else {
        $settings['initial_settings']['toolbar1'] = glenmark_normalize_tinymce_toolbar($settings['initial_settings']['toolbar1'], $extra_buttons);
    }

    return $settings;
}

add_filter('elementor/editor/localize_settings', 'glenmark_elementor_extend_tinymce_toolbar');

add_filter('tiny_mce_before_init', function ($init) {
    $buttons = ['formatselect', 'fontsizeselect', 'fontsize', 'forecolor', 'backcolor'];

    if (!empty($init['toolbar1'])) {
        $init['toolbar1'] = glenmark_normalize_tinymce_toolbar($init['toolbar1'], $buttons);
    } elseif (!empty($init['toolbar'])) {
        $init['toolbar1'] = glenmark_normalize_tinymce_toolbar($init['toolbar'], $buttons);
    } else {
        $init['toolbar1'] = implode(',', $buttons);
    }

    return $init;
});

function glenmark_enqueue_elementor_hide_pro_script()
{
    $script_uri = glenmark_get_theme_file_uri('assets/js/hide-elementor-pro.js');

    if (empty($script_uri)) {
        return;
    }

    wp_enqueue_script('glenmark-hide-elementor-pro', $script_uri, [], '1.0.0', true);
}

function glenmark_admin_enqueue_elementor_hide_pro($hook_suffix)
{
    $allowed_hooks = [
        'toplevel_page_elementor',
        'elementor_page_elementor-app',
        'post.php',
        'post-new.php',
    ];

    if (in_array($hook_suffix, $allowed_hooks, true)) {
        glenmark_enqueue_elementor_hide_pro_script();
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if ($screen && isset($screen->id) && false !== strpos((string) $screen->id, 'elementor')) {
        glenmark_enqueue_elementor_hide_pro_script();
    }
}
// add_action('admin_enqueue_scripts', 'glenmark_admin_enqueue_elementor_hide_pro', 20);

function glenmark_elementor_editor_enqueue_hide_pro()
{
    glenmark_enqueue_elementor_hide_pro_script();
}
// add_action('elementor/editor/before_enqueue_scripts', 'glenmark_elementor_editor_enqueue_hide_pro');
