<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_get_menu_item_hero_meta_key()
{
    return '_webrev_menu_item_hero';
}

function webrev_is_hero_menu_item($menu_item_id)
{
    return '1' === get_post_meta((int) $menu_item_id, webrev_get_menu_item_hero_meta_key(), true);
}

function webrev_render_hero_menu_item_field($item_id)
{
    $field_name = 'menu-item-webrev-hero[' . $item_id . ']';
    ?>
    <p class="field-webrev-hero description description-wide">
        <label for="edit-menu-item-webrev-hero-<?php echo esc_attr($item_id); ?>">
            <input type="checkbox" id="edit-menu-item-webrev-hero-<?php echo esc_attr($item_id); ?>"
                name="<?php echo esc_attr($field_name); ?>" value="1" <?php checked(webrev_is_hero_menu_item($item_id)); ?> />
            <?php esc_html_e('Hero', 'wr-pharma-product'); ?>
        </label>
    </p>
    <?php
}
add_action('wp_nav_menu_item_custom_fields', 'webrev_render_hero_menu_item_field', 10, 1);

function webrev_save_hero_menu_item_field($menu_id, $menu_item_db_id)
{
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    $hero_menu_items = isset($_POST['menu-item-webrev-hero']) && is_array($_POST['menu-item-webrev-hero']) ? $_POST['menu-item-webrev-hero'] : [];
    $is_hero = isset($hero_menu_items[$menu_item_db_id]) && '1' === $hero_menu_items[$menu_item_db_id];

    if ($is_hero) {
        update_post_meta($menu_item_db_id, webrev_get_menu_item_hero_meta_key(), '1');
        return;
    }

    delete_post_meta($menu_item_db_id, webrev_get_menu_item_hero_meta_key());
}
add_action('wp_update_nav_menu_item', 'webrev_save_hero_menu_item_field', 10, 2);

function webrev_add_hero_menu_item_class($classes, $menu_item)
{
    if (webrev_is_hero_menu_item($menu_item->ID)) {
        $classes[] = 'webrev-menu-item--hero';
    }

    return $classes;
}
add_filter('nav_menu_css_class', 'webrev_add_hero_menu_item_class', 10, 2);

function webrev_get_hero_menu_item_ids()
{
    $menu_item_ids = get_posts([
        'post_type' => 'nav_menu_item',
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids',
    ]);
    $hero_menu_item_ids = [];

    foreach ($menu_item_ids as $menu_item_id) {
        if (webrev_is_hero_menu_item($menu_item_id)) {
            $hero_menu_item_ids[] = (int) $menu_item_id;
        }
    }

    return $hero_menu_item_ids;
}

function webrev_enqueue_customizer_menu_item_settings_assets()
{
    $script_path = webrev_get_theme_file_path('assets/js/customizer-menu-item-settings.js');

    if (!file_exists($script_path)) {
        return;
    }

    wp_enqueue_script(
        'webrev-customizer-menu-item-settings',
        webrev_get_theme_file_uri('assets/js/customizer-menu-item-settings.js'),
        ['jquery', 'customize-controls'],
        (string) filemtime($script_path),
        true
    );

    wp_localize_script('webrev-customizer-menu-item-settings', 'webrevCustomizerMenuItemSettings', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'heroMenuItemIds' => webrev_get_hero_menu_item_ids(),
        'nonce' => wp_create_nonce('webrev-update-menu-item-hero'),
    ]);
}
add_action('customize_controls_enqueue_scripts', 'webrev_enqueue_customizer_menu_item_settings_assets');

function webrev_update_customizer_menu_item_hero()
{
    check_ajax_referer('webrev-update-menu-item-hero', 'nonce');

    $menu_item_id = isset($_POST['menu_item_id']) ? absint($_POST['menu_item_id']) : 0;

    if ($menu_item_id <= 0 || 'nav_menu_item' !== get_post_type($menu_item_id) || !current_user_can('edit_post', $menu_item_id)) {
        wp_send_json_error();
    }

    $is_hero = isset($_POST['is_hero']) && '1' === $_POST['is_hero'];

    if ($is_hero) {
        update_post_meta($menu_item_id, webrev_get_menu_item_hero_meta_key(), '1');
    } else {
        delete_post_meta($menu_item_id, webrev_get_menu_item_hero_meta_key());
    }

    wp_send_json_success();
}
add_action('wp_ajax_webrev_update_menu_item_hero', 'webrev_update_customizer_menu_item_hero');