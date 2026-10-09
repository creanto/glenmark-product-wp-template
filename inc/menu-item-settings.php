<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_get_menu_item_hero_meta_key()
{
    return '_glenmark_menu_item_hero';
}

function glenmark_is_hero_menu_item($menu_item_id)
{
    return '1' === get_post_meta((int) $menu_item_id, glenmark_get_menu_item_hero_meta_key(), true);
}

function glenmark_render_hero_menu_item_field($item_id)
{
    $field_name = 'menu-item-glenmark-hero[' . $item_id . ']';
    ?>
    <p class="field-glenmark-hero description description-wide">
        <label for="edit-menu-item-glenmark-hero-<?php echo esc_attr($item_id); ?>">
            <input type="checkbox" id="edit-menu-item-glenmark-hero-<?php echo esc_attr($item_id); ?>"
                name="<?php echo esc_attr($field_name); ?>" value="1" <?php checked(glenmark_is_hero_menu_item($item_id)); ?> />
            <?php esc_html_e('Hero', 'gln-pharma-product'); ?>
        </label>
    </p>
    <?php
}
add_action('wp_nav_menu_item_custom_fields', 'glenmark_render_hero_menu_item_field', 10, 1);

function glenmark_save_hero_menu_item_field($menu_id, $menu_item_db_id)
{
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    $hero_menu_items = isset($_POST['menu-item-glenmark-hero']) && is_array($_POST['menu-item-glenmark-hero']) ? $_POST['menu-item-glenmark-hero'] : [];
    $is_hero = isset($hero_menu_items[$menu_item_db_id]) && '1' === $hero_menu_items[$menu_item_db_id];

    if ($is_hero) {
        update_post_meta($menu_item_db_id, glenmark_get_menu_item_hero_meta_key(), '1');
        return;
    }

    delete_post_meta($menu_item_db_id, glenmark_get_menu_item_hero_meta_key());
}
add_action('wp_update_nav_menu_item', 'glenmark_save_hero_menu_item_field', 10, 2);

function glenmark_add_hero_menu_item_class($classes, $menu_item)
{
    if (glenmark_is_hero_menu_item($menu_item->ID)) {
        $classes[] = 'glenmark-menu-item--hero';
    }

    return $classes;
}
add_filter('nav_menu_css_class', 'glenmark_add_hero_menu_item_class', 10, 2);

function glenmark_get_hero_menu_item_ids()
{
    $menu_item_ids = get_posts([
        'post_type' => 'nav_menu_item',
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids',
    ]);
    $hero_menu_item_ids = [];

    foreach ($menu_item_ids as $menu_item_id) {
        if (glenmark_is_hero_menu_item($menu_item_id)) {
            $hero_menu_item_ids[] = (int) $menu_item_id;
        }
    }

    return $hero_menu_item_ids;
}

function glenmark_enqueue_customizer_menu_item_settings_assets()
{
    $script_path = glenmark_get_theme_file_path('assets/js/customizer-menu-item-settings.js');

    if (!file_exists($script_path)) {
        return;
    }

    wp_enqueue_script(
        'glenmark-customizer-menu-item-settings',
        glenmark_get_theme_file_uri('assets/js/customizer-menu-item-settings.js'),
        ['jquery', 'customize-controls'],
        (string) filemtime($script_path),
        true
    );

    wp_localize_script('glenmark-customizer-menu-item-settings', 'glenmarkCustomizerMenuItemSettings', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'heroMenuItemIds' => glenmark_get_hero_menu_item_ids(),
        'nonce' => wp_create_nonce('glenmark-update-menu-item-hero'),
    ]);
}
add_action('customize_controls_enqueue_scripts', 'glenmark_enqueue_customizer_menu_item_settings_assets');

function glenmark_update_customizer_menu_item_hero()
{
    check_ajax_referer('glenmark-update-menu-item-hero', 'nonce');

    $menu_item_id = isset($_POST['menu_item_id']) ? absint($_POST['menu_item_id']) : 0;

    if ($menu_item_id <= 0 || 'nav_menu_item' !== get_post_type($menu_item_id) || !current_user_can('edit_post', $menu_item_id)) {
        wp_send_json_error();
    }

    $is_hero = isset($_POST['is_hero']) && '1' === $_POST['is_hero'];

    if ($is_hero) {
        update_post_meta($menu_item_id, glenmark_get_menu_item_hero_meta_key(), '1');
    } else {
        delete_post_meta($menu_item_id, glenmark_get_menu_item_hero_meta_key());
    }

    wp_send_json_success();
}
add_action('wp_ajax_glenmark_update_menu_item_hero', 'glenmark_update_customizer_menu_item_hero');