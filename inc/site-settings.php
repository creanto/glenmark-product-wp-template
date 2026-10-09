<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_get_site_settings_defaults()
{
    return [
        'logo_default' => 0,
        'logo_transparent' => 0,
        'favicon' => 0,
        'social_facebook' => '',
        'social_instagram' => '',
        'social_youtube' => '',
        'header_sticky' => true,
        'header_shrink' => true,
        'header_hide_on_scroll' => false,
        'header_mode' => 'normal',
        'header_height' => 88,
        'header_shrink_height' => 68,
        'header_logo_max_width' => 260,
        'header_layout' => 'default',
        'footer_layout' => 'default',
        'header_background_color' => '',
        'header_background_image' => 0,
        'header_background_size' => 'cover',
        'header_background_position' => 'center center',
        'header_background_repeat' => 'no-repeat',
        'header_navigation_link_color' => '',
        'header_navigation_separators' => false,
        'back_to_top' => false,
        'bottom_notice_enabled' => false,
        'bottom_notice_text' => '',
        'footer_page' => 0,
    ];
}

function glenmark_get_setting($key, $default = null)
{
    $defaults = glenmark_get_site_settings_defaults();

    if (null === $default && array_key_exists($key, $defaults)) {
        $default = $defaults[$key];
    }

    return get_theme_mod('glenmark_' . $key, $default);
}

function glenmark_get_logo_default_id()
{
    $logo_id = (int) glenmark_get_setting('logo_default', 0);

    if ($logo_id > 0) {
        return $logo_id;
    }

    return (int) get_theme_mod('custom_logo');
}

function glenmark_get_logo_transparent_id()
{
    return (int) glenmark_get_setting('logo_transparent', 0);
}

function glenmark_header_transparent_meta_key()
{
    return '_glenmark_header_transparent';
}

function glenmark_header_mode_meta_key()
{
    return '_glenmark_header_mode';
}

function glenmark_header_mode_override_meta_key()
{
    return '_glenmark_header_mode_override';
}

function glenmark_header_sticky_override_meta_key()
{
    return '_glenmark_header_sticky_override';
}

function glenmark_header_shrink_override_meta_key()
{
    return '_glenmark_header_shrink_override';
}

function glenmark_header_hide_on_scroll_override_meta_key()
{
    return '_glenmark_header_hide_on_scroll_override';
}

function glenmark_header_logo_show_on_scroll_override_meta_key()
{
    return '_glenmark_header_logo_show_on_scroll_override';
}

function glenmark_header_menu_override_meta_key()
{
    return '_glenmark_header_menu_override';
}

function glenmark_get_header_menu_override_options()
{
    $options = [
        'default' => __('Use global menu (Primary Menu location)', 'gln-pharma-product'),
    ];

    foreach (wp_get_nav_menus() as $menu) {
        $options[(string) $menu->term_id] = $menu->name;
    }

    return $options;
}

function glenmark_sanitize_header_menu_override($value)
{
    $value = trim((string) $value);

    if ('' === $value || 'default' === $value) {
        return 'default';
    }

    $menu_id = absint($value);

    if ($menu_id > 0 && wp_get_nav_menu_object($menu_id)) {
        return (string) $menu_id;
    }

    return 'default';
}

function glenmark_get_header_menu_override($post_id = null)
{
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return 'default';
    }

    $settings = glenmark_get_header_settings_store($post_id);
    if (array_key_exists('menu', $settings)) {
        return glenmark_sanitize_header_menu_override($settings['menu']);
    }

    $elementor_value = glenmark_get_elementor_page_setting($post_id, [
        'glenmark_header_menu_override_control',
        'glenmark_header_menu_override',
    ], null);
    if (null !== $elementor_value) {
        return glenmark_sanitize_header_menu_override($elementor_value);
    }

    $meta_value = trim((string) get_post_meta($post_id, glenmark_header_menu_override_meta_key(), true));
    if ('' !== $meta_value) {
        return glenmark_sanitize_header_menu_override($meta_value);
    }

    return 'default';
}

function glenmark_get_header_menu_id($post_id = null)
{
    $override = glenmark_get_header_menu_override($post_id);

    if ('default' === $override) {
        return 0;
    }

    $menu = wp_get_nav_menu_object((int) $override);

    return $menu ? (int) $menu->term_id : 0;
}

function glenmark_render_primary_nav_menu($args = [])
{
    $menu_id = glenmark_get_header_menu_id();

    $defaults = [
        'container' => false,
        'menu_class' => 'nav-menu',
        'fallback_cb' => 'glenmark_primary_menu_fallback',
    ];

    $args = array_merge($defaults, $args);

    if ($menu_id > 0) {
        $args['menu'] = $menu_id;
        unset($args['theme_location']);
    } else {
        $args['theme_location'] = 'primary';
    }

    wp_nav_menu($args);
}

function glenmark_get_header_mode_options()
{
    return [
        'normal' => __('Normal header', 'gln-pharma-product'),
        'transparent' => __('Transparent header', 'gln-pharma-product'),
        'glassy' => __('Glassy header', 'gln-pharma-product'),
        'glassy-dark' => __('Glassy dark header', 'gln-pharma-product'),
    ];
}

function glenmark_get_header_override_options()
{
    return [
        'default' => __('Use global setting', 'gln-pharma-product'),
        'enabled' => __('Enabled', 'gln-pharma-product'),
        'disabled' => __('Disabled', 'gln-pharma-product'),
    ];
}

function glenmark_get_header_mode_override_options()
{
    return [
        'default' => __('Use global setting', 'gln-pharma-product'),
        'normal' => __('Normal header', 'gln-pharma-product'),
        'transparent' => __('Transparent header', 'gln-pharma-product'),
        'glassy' => __('Glassy header', 'gln-pharma-product'),
        'glassy-dark' => __('Glassy dark header', 'gln-pharma-product'),
    ];
}

function glenmark_sanitize_header_mode_override($value)
{
    $value = sanitize_key((string) $value);
    $allowed = array_merge(['default'], array_keys(glenmark_get_header_mode_options()));

    return in_array($value, $allowed, true) ? $value : 'default';
}

function glenmark_sanitize_header_bool_override($value)
{
    $value = sanitize_key((string) $value);
    $allowed = ['default', 'enabled', 'disabled'];

    return in_array($value, $allowed, true) ? $value : 'default';
}

function glenmark_get_elementor_page_setting($post_id, $setting_key, $default = null)
{
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return $default;
    }

    $settings = get_post_meta($post_id, '_elementor_page_settings', true);

    if (!is_array($settings)) {
        return $default;
    }

    $keys = is_array($setting_key) ? $setting_key : [$setting_key];

    foreach ($keys as $key) {
        if (array_key_exists($key, $settings)) {
            $value = $settings[$key];

            return null === $value ? $default : $value;
        }
    }

    return $default;
}

function glenmark_update_elementor_page_setting($post_id, $setting_key, $value)
{
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return;
    }

    $settings = get_post_meta($post_id, '_elementor_page_settings', true);
    if (!is_array($settings)) {
        $settings = [];
    }

    $settings[$setting_key] = $value;

    update_post_meta($post_id, '_elementor_page_settings', $settings);
}

function glenmark_sync_elementor_header_settings($post_id, $header_mode, $header_sticky_override, $header_shrink_override, $header_hide_on_scroll_override, $header_logo_show_on_scroll_override = 'default', $header_menu_override = 'default')
{
    glenmark_update_elementor_page_setting($post_id, 'glenmark_header_mode_control', $header_mode);
    glenmark_update_elementor_page_setting($post_id, 'glenmark_header_sticky_override_control', $header_sticky_override);
    glenmark_update_elementor_page_setting($post_id, 'glenmark_header_shrink_override_control', $header_shrink_override);
    glenmark_update_elementor_page_setting($post_id, 'glenmark_header_hide_on_scroll_override_control', $header_hide_on_scroll_override);
    glenmark_update_elementor_page_setting($post_id, 'glenmark_header_logo_show_on_scroll_override_control', $header_logo_show_on_scroll_override);
    glenmark_update_elementor_page_setting($post_id, 'glenmark_header_menu_override_control', $header_menu_override);
}

function glenmark_get_header_settings_store($post_id)
{
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return [];
    }

    $settings = get_post_meta($post_id, '_glenmark_header_settings', true);

    return is_array($settings) ? $settings : [];
}

function glenmark_set_header_settings_store($post_id, $settings)
{
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0 || !is_array($settings)) {
        return;
    }

    $existing = glenmark_get_header_settings_store($post_id);
    $merged = array_merge($existing, $settings);

    update_post_meta($post_id, '_glenmark_header_settings', $merged);

    if (array_key_exists('mode', $merged)) {
        update_post_meta($post_id, glenmark_header_mode_meta_key(), $merged['mode']);
        update_post_meta($post_id, glenmark_header_transparent_meta_key(), 'transparent' === $merged['mode'] ? '1' : '0');
    }

    if (array_key_exists('override_mode', $merged)) {
        update_post_meta($post_id, glenmark_header_mode_override_meta_key(), $merged['override_mode']);
    }

    if (array_key_exists('sticky', $merged)) {
        update_post_meta($post_id, glenmark_header_sticky_override_meta_key(), $merged['sticky']);
    }

    if (array_key_exists('shrink', $merged)) {
        update_post_meta($post_id, glenmark_header_shrink_override_meta_key(), $merged['shrink']);
    }

    if (array_key_exists('hide_on_scroll', $merged)) {
        update_post_meta($post_id, glenmark_header_hide_on_scroll_override_meta_key(), $merged['hide_on_scroll']);
    }

    if (array_key_exists('logo_show_on_scroll', $merged)) {
        update_post_meta($post_id, glenmark_header_logo_show_on_scroll_override_meta_key(), $merged['logo_show_on_scroll']);
    }
}

function glenmark_get_page_header_override($post_id, $meta_key, $default = 'default')
{
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return $default;
    }

    $settings = glenmark_get_header_settings_store($post_id);
    if (isset($settings['override_mode']) && $meta_key === glenmark_header_mode_override_meta_key()) {
        return $settings['override_mode'];
    }

    $value = trim((string) get_post_meta($post_id, $meta_key, true));
    if ('' !== $value) {
        return $value;
    }

    $elementor_value = glenmark_get_elementor_page_setting($post_id, [
        'glenmark_header_mode_override_control',
        'glenmark_header_mode_override',
    ], null);
    if (null !== $elementor_value) {
        return glenmark_sanitize_header_mode_override($elementor_value);
    }

    return $default;
}

function glenmark_normalize_header_mode($mode)
{
    $mode = sanitize_key((string) $mode);
    $allowed = ['normal', 'transparent', 'glassy', 'glassy-dark'];

    if (!in_array($mode, $allowed, true)) {
        return 'normal';
    }

    return $mode;
}

function glenmark_get_header_mode($post_id = null)
{
    $global_mode = glenmark_normalize_header_mode(glenmark_get_setting('header_mode', 'normal'));
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return $global_mode;
    }

    $elementor_mode = glenmark_get_elementor_page_setting($post_id, [
        'glenmark_header_mode_control',
        'glenmark_header_mode',
    ], null);
    if (null !== $elementor_mode) {
        return glenmark_normalize_header_mode($elementor_mode);
    }

    $settings = glenmark_get_header_settings_store($post_id);
    if (array_key_exists('mode', $settings)) {
        return glenmark_normalize_header_mode($settings['mode']);
    }

    $override = glenmark_sanitize_header_mode_override(glenmark_get_page_header_override($post_id, glenmark_header_mode_override_meta_key(), 'default'));
    if ('default' !== $override) {
        return glenmark_normalize_header_mode($override);
    }

    $override_meta = trim((string) get_post_meta($post_id, glenmark_header_mode_override_meta_key(), true));
    if ('default' === glenmark_sanitize_header_mode_override($override_meta)) {
        return $global_mode;
    }

    $saved_mode = glenmark_normalize_header_mode(get_post_meta($post_id, glenmark_header_mode_meta_key(), true));
    if ('normal' !== $saved_mode || '' !== (string) get_post_meta($post_id, glenmark_header_mode_meta_key(), true)) {
        return $saved_mode;
    }

    // Backward compatibility for previously saved checkbox state.
    $legacy_transparent = '1' === (string) get_post_meta($post_id, glenmark_header_transparent_meta_key(), true);

    return $legacy_transparent ? 'transparent' : $global_mode;
}

function glenmark_get_header_sticky($post_id = null)
{
    $global_value = (bool) glenmark_get_setting('header_sticky', true);
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return $global_value;
    }

    $settings = glenmark_get_header_settings_store($post_id);
    if (array_key_exists('sticky', $settings)) {
        $stored_value = $settings['sticky'];

        if ('default' !== $stored_value) {
            return 'enabled' === $stored_value;
        }
    }

    $elementor_value = glenmark_get_elementor_page_setting($post_id, [
        'glenmark_header_sticky_override_control',
        'glenmark_header_sticky_override',
    ], 'default');
    $elementor_override = glenmark_sanitize_header_bool_override($elementor_value);
    if ('default' !== $elementor_override) {
        return 'enabled' === $elementor_override;
    }

    $override = glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_sticky_override_meta_key(), 'default'));

    if ('default' !== $override) {
        return 'enabled' === $override;
    }

    return $global_value;
}

function glenmark_get_header_shrink($post_id = null)
{
    $global_value = (bool) glenmark_get_setting('header_shrink', true);
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return $global_value;
    }

    $settings = glenmark_get_header_settings_store($post_id);
    if (array_key_exists('shrink', $settings)) {
        $stored_value = $settings['shrink'];

        if ('default' !== $stored_value) {
            return 'enabled' === $stored_value;
        }
    }

    $elementor_value = glenmark_get_elementor_page_setting($post_id, [
        'glenmark_header_shrink_override_control',
        'glenmark_header_shrink_override',
    ], 'default');
    $elementor_override = glenmark_sanitize_header_bool_override($elementor_value);
    if ('default' !== $elementor_override) {
        return 'enabled' === $elementor_override;
    }

    $override = glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_shrink_override_meta_key(), 'default'));

    if ('default' !== $override) {
        return 'enabled' === $override;
    }

    return $global_value;
}

function glenmark_get_header_hide_on_scroll($post_id = null)
{
    $global_value = (bool) glenmark_get_setting('header_hide_on_scroll', false);
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return $global_value;
    }

    $settings = glenmark_get_header_settings_store($post_id);
    if (array_key_exists('hide_on_scroll', $settings)) {
        $stored_value = $settings['hide_on_scroll'];

        if ('default' !== $stored_value) {
            return 'enabled' === $stored_value;
        }
    }

    $elementor_value = glenmark_get_elementor_page_setting($post_id, [
        'glenmark_header_hide_on_scroll_override_control',
        'glenmark_header_hide_on_scroll_override',
    ], 'default');
    $elementor_override = glenmark_sanitize_header_bool_override($elementor_value);
    if ('default' !== $elementor_override) {
        return 'enabled' === $elementor_override;
    }

    $override = glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_hide_on_scroll_override_meta_key(), 'default'));

    if ('default' !== $override) {
        return 'enabled' === $override;
    }

    return $global_value;
}

function glenmark_get_header_logo_show_on_scroll($post_id = null)
{
    $global_value = false;
    $post_id = $post_id ? (int) $post_id : get_queried_object_id();

    if ($post_id <= 0) {
        return $global_value;
    }

    $settings = glenmark_get_header_settings_store($post_id);
    if (array_key_exists('logo_show_on_scroll', $settings)) {
        $stored_value = $settings['logo_show_on_scroll'];

        if ('default' !== $stored_value) {
            return 'enabled' === $stored_value;
        }
    }

    $elementor_value = glenmark_get_elementor_page_setting($post_id, [
        'glenmark_header_logo_show_on_scroll_override_control',
        'glenmark_header_logo_show_on_scroll_override',
    ], 'default');
    $elementor_override = glenmark_sanitize_header_bool_override($elementor_value);
    if ('default' !== $elementor_override) {
        return 'enabled' === $elementor_override;
    }

    $override = glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_logo_show_on_scroll_override_meta_key(), 'default'));

    if ('default' !== $override) {
        return 'enabled' === $override;
    }

    return $global_value;
}

function glenmark_is_header_transparent_enabled($post_id = null)
{
    return 'transparent' === glenmark_get_header_mode($post_id);
}

function glenmark_get_header_settings_post_types()
{
    $post_types = get_post_types([
        'public' => true,
        'show_ui' => true,
    ], 'names');

    return is_array($post_types) ? array_values(array_filter($post_types, 'is_string')) : ['page'];
}

function glenmark_has_classic_header_settings_payload()
{
    $keys = [
        'glenmark_header_mode',
        'glenmark_header_sticky_override',
        'glenmark_header_shrink_override',
        'glenmark_header_hide_on_scroll_override',
        'glenmark_header_logo_show_on_scroll_override',
        'glenmark_header_menu_override',
    ];

    foreach ($keys as $key) {
        if (array_key_exists($key, $_POST)) {
            return true;
        }
    }

    return false;
}

function glenmark_add_header_settings_metabox()
{
    foreach (glenmark_get_header_settings_post_types() as $post_type) {
        add_meta_box(
            'glenmark-header-settings',
            __('Header settings', 'gln-pharma-product'),
            'glenmark_render_header_settings_metabox',
            $post_type,
            'side',
            'default'
        );
    }
}
add_action('add_meta_boxes', 'glenmark_add_header_settings_metabox');

function glenmark_render_header_settings_metabox($post)
{
    $post_id = isset($post->ID) ? (int) $post->ID : 0;
    $header_mode = glenmark_get_header_mode($post_id);
    $header_mode_override = glenmark_sanitize_header_mode_override(glenmark_get_page_header_override($post_id, glenmark_header_mode_override_meta_key(), 'default'));
    $header_sticky_override = glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_sticky_override_meta_key(), 'default'));
    $header_shrink_override = glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_shrink_override_meta_key(), 'default'));
    $header_hide_on_scroll_override = glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_hide_on_scroll_override_meta_key(), 'default'));
    $header_logo_show_on_scroll_override = glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_logo_show_on_scroll_override_meta_key(), 'default'));
    $header_menu_override = glenmark_get_header_menu_override($post_id);

    wp_nonce_field('glenmark_save_header_settings', 'glenmark_header_settings_nonce');
    ?>
    <p>
        <label for="glenmark_header_mode"><strong><?php esc_html_e('Header mode', 'gln-pharma-product'); ?></strong></label>
        <select id="glenmark_header_mode" name="glenmark_header_mode" class="widefat" style="margin-top:6px;">
            <?php foreach (glenmark_get_header_mode_override_options() as $mode_key => $mode_label): ?>
                <option value="<?php echo esc_attr($mode_key); ?>" <?php selected($header_mode_override, $mode_key); ?>>
                    <?php echo esc_html($mode_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="glenmark_header_sticky_override"><strong><?php esc_html_e('Sticky header', 'gln-pharma-product'); ?></strong></label>
        <select id="glenmark_header_sticky_override" name="glenmark_header_sticky_override" class="widefat" style="margin-top:6px;">
            <?php foreach (glenmark_get_header_override_options() as $override_key => $override_label): ?>
                <option value="<?php echo esc_attr($override_key); ?>" <?php selected($header_sticky_override, $override_key); ?>>
                    <?php echo esc_html($override_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="glenmark_header_shrink_override"><strong><?php esc_html_e('Shrink header on scroll', 'gln-pharma-product'); ?></strong></label>
        <select id="glenmark_header_shrink_override" name="glenmark_header_shrink_override" class="widefat" style="margin-top:6px;">
            <?php foreach (glenmark_get_header_override_options() as $override_key => $override_label): ?>
                <option value="<?php echo esc_attr($override_key); ?>" <?php selected($header_shrink_override, $override_key); ?>>
                    <?php echo esc_html($override_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="glenmark_header_hide_on_scroll_override"><strong><?php esc_html_e('Show header on scroll', 'gln-pharma-product'); ?></strong></label>
        <select id="glenmark_header_hide_on_scroll_override" name="glenmark_header_hide_on_scroll_override" class="widefat" style="margin-top:6px;">
            <?php foreach (glenmark_get_header_override_options() as $override_key => $override_label): ?>
                <option value="<?php echo esc_attr($override_key); ?>" <?php selected($header_hide_on_scroll_override, $override_key); ?>>
                    <?php echo esc_html($override_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="glenmark_header_logo_show_on_scroll_override"><strong><?php esc_html_e('Show logo on scroll', 'gln-pharma-product'); ?></strong></label>
        <select id="glenmark_header_logo_show_on_scroll_override" name="glenmark_header_logo_show_on_scroll_override" class="widefat" style="margin-top:6px;">
            <?php foreach (glenmark_get_header_override_options() as $override_key => $override_label): ?>
                <option value="<?php echo esc_attr($override_key); ?>" <?php selected($header_logo_show_on_scroll_override, $override_key); ?>>
                    <?php echo esc_html($override_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p class="description">
        <?php esc_html_e('Only applies to transparent and glassy header modes. When enabled, the logo stays hidden until the visitor scrolls.', 'gln-pharma-product'); ?>
    </p>
    <p>
        <label for="glenmark_header_menu_override"><strong><?php esc_html_e('Header menu', 'gln-pharma-product'); ?></strong></label>
        <select id="glenmark_header_menu_override" name="glenmark_header_menu_override" class="widefat" style="margin-top:6px;">
            <?php foreach (glenmark_get_header_menu_override_options() as $menu_key => $menu_label): ?>
                <option value="<?php echo esc_attr($menu_key); ?>" <?php selected($header_menu_override, $menu_key); ?>>
                    <?php echo esc_html($menu_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p class="description">
        <?php esc_html_e('Use global setting = inherit the site-wide value. Choose a value to override it for this page only.', 'gln-pharma-product'); ?>
    </p>
    <?php
}

function glenmark_save_header_settings_metabox($post_id)
{
    if (!isset($_POST['glenmark_header_settings_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(wp_unslash($_POST['glenmark_header_settings_nonce']), 'glenmark_save_header_settings')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!glenmark_has_classic_header_settings_payload()) {
        return;
    }

    $header_mode_value = isset($_POST['glenmark_header_mode']) ? wp_unslash($_POST['glenmark_header_mode']) : 'normal';
    $header_mode_override = isset($_POST['glenmark_header_mode']) ? glenmark_sanitize_header_mode_override(wp_unslash($_POST['glenmark_header_mode'])) : 'default';
    $header_mode = glenmark_normalize_header_mode($header_mode_value);
    $header_sticky_override = isset($_POST['glenmark_header_sticky_override']) ? glenmark_sanitize_header_bool_override(wp_unslash($_POST['glenmark_header_sticky_override'])) : 'default';
    $header_shrink_override = isset($_POST['glenmark_header_shrink_override']) ? glenmark_sanitize_header_bool_override(wp_unslash($_POST['glenmark_header_shrink_override'])) : 'default';
    $header_hide_on_scroll_override = isset($_POST['glenmark_header_hide_on_scroll_override']) ? glenmark_sanitize_header_bool_override(wp_unslash($_POST['glenmark_header_hide_on_scroll_override'])) : 'default';
    $header_logo_show_on_scroll_override = isset($_POST['glenmark_header_logo_show_on_scroll_override']) ? glenmark_sanitize_header_bool_override(wp_unslash($_POST['glenmark_header_logo_show_on_scroll_override'])) : 'default';
    $header_menu_override = isset($_POST['glenmark_header_menu_override']) ? glenmark_sanitize_header_menu_override(wp_unslash($_POST['glenmark_header_menu_override'])) : 'default';

    glenmark_set_header_settings_store($post_id, [
        'mode' => $header_mode,
        'override_mode' => $header_mode_override,
        'sticky' => $header_sticky_override,
        'shrink' => $header_shrink_override,
        'hide_on_scroll' => $header_hide_on_scroll_override,
        'logo_show_on_scroll' => $header_logo_show_on_scroll_override,
        'menu' => $header_menu_override,
    ]);

    update_post_meta($post_id, glenmark_header_mode_override_meta_key(), $header_mode_override);
    update_post_meta($post_id, glenmark_header_sticky_override_meta_key(), $header_sticky_override);
    update_post_meta($post_id, glenmark_header_shrink_override_meta_key(), $header_shrink_override);
    update_post_meta($post_id, glenmark_header_hide_on_scroll_override_meta_key(), $header_hide_on_scroll_override);
    update_post_meta($post_id, glenmark_header_logo_show_on_scroll_override_meta_key(), $header_logo_show_on_scroll_override);
    update_post_meta($post_id, glenmark_header_menu_override_meta_key(), $header_menu_override);

    update_post_meta($post_id, glenmark_header_mode_meta_key(), $header_mode);
    update_post_meta($post_id, glenmark_header_transparent_meta_key(), 'transparent' === $header_mode ? '1' : '0');

    glenmark_sync_elementor_header_settings($post_id, $header_mode, $header_sticky_override, $header_shrink_override, $header_hide_on_scroll_override, $header_logo_show_on_scroll_override, $header_menu_override);
}
add_action('save_post', 'glenmark_save_header_settings_metabox');

function glenmark_elementor_document_supports_header_mode($document)
{
    if (!is_object($document) || !method_exists($document, 'get_main_id')) {
        return false;
    }

    $post_id = (int) $document->get_main_id();
    if ($post_id <= 0) {
        return false;
    }

    $post_type = get_post_type($post_id);

    return in_array((string) $post_type, glenmark_get_header_settings_post_types(), true);
}

function glenmark_register_elementor_header_mode_control($document)
{
    if (!class_exists('Elementor\\Controls_Manager')) {
        return;
    }

    if (!glenmark_elementor_document_supports_header_mode($document)) {
        return;
    }

    $post_id = (int) $document->get_main_id();

    $document->start_controls_section(
        'glenmark_header_settings',
        [
            'label' => __('Header settings', 'gln-pharma-product'),
            'tab' => \Elementor\Controls_Manager::TAB_SETTINGS,
        ]
    );

    $document->add_control(
        'glenmark_header_mode_control',
        [
            'label' => __('Header mode', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => glenmark_get_header_mode_override_options(),
            'default' => glenmark_sanitize_header_mode_override(glenmark_get_page_header_override($post_id, glenmark_header_mode_override_meta_key(), 'default')),
            'description' => __('Choose the header appearance for this page. Use the site-wide value by leaving the override empty.', 'gln-pharma-product'),
        ]
    );

    $document->add_control(
        'glenmark_header_sticky_override_control',
        [
            'label' => __('Sticky header override', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => glenmark_get_header_override_options(),
            'default' => glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_sticky_override_meta_key(), 'default')),
        ]
    );

    $document->add_control(
        'glenmark_header_shrink_override_control',
        [
            'label' => __('Shrink header override', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => glenmark_get_header_override_options(),
            'default' => glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_shrink_override_meta_key(), 'default')),
        ]
    );

    $document->add_control(
        'glenmark_header_hide_on_scroll_override_control',
        [
            'label' => __('Show header on scroll override', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => glenmark_get_header_override_options(),
            'default' => glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_hide_on_scroll_override_meta_key(), 'default')),
        ]
    );

    $document->add_control(
        'glenmark_header_logo_show_on_scroll_override_control',
        [
            'label' => __('Show logo on scroll', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => glenmark_get_header_override_options(),
            'default' => glenmark_sanitize_header_bool_override(glenmark_get_page_header_override($post_id, glenmark_header_logo_show_on_scroll_override_meta_key(), 'default')),
            'description' => __('Only applies to transparent and glassy header modes. When enabled, the logo stays hidden until the visitor scrolls.', 'gln-pharma-product'),
        ]
    );

    $document->add_control(
        'glenmark_header_menu_override_control',
        [
            'label' => __('Header menu', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => glenmark_get_header_menu_override_options(),
            'default' => glenmark_get_header_menu_override($post_id),
            'description' => __('Choose a menu (Vzhled > Menu) to use for this page only. Use the site-wide Primary Menu location by leaving the override empty.', 'gln-pharma-product'),
        ]
    );

    $document->end_controls_section();
}
add_action('elementor/documents/register_controls', 'glenmark_register_elementor_header_mode_control');

function glenmark_save_elementor_header_mode_control($document, $data = [])
{
    if (!glenmark_elementor_document_supports_header_mode($document)) {
        return;
    }

    $post_id = (int) $document->get_main_id();

    $settings = is_array($data) && !empty($data) ? $data : [];

    if (empty($settings) && method_exists($document, 'get_settings')) {
        $settings = $document->get_settings();
    }

    if (!is_array($settings)) {
        $settings = get_post_meta($post_id, '_elementor_page_settings', true);
    }

    if (!is_array($settings)) {
        return;
    }

    $header_mode_value = isset($settings['glenmark_header_mode_control']) ? $settings['glenmark_header_mode_control'] : null;

    if (null !== $header_mode_value && '' !== (string) $header_mode_value) {
        $header_mode = glenmark_normalize_header_mode($header_mode_value);
    } else {
        $header_mode = glenmark_get_header_mode($post_id);
    }

    $header_sticky_override = isset($settings['glenmark_header_sticky_override_control']) ? glenmark_sanitize_header_bool_override($settings['glenmark_header_sticky_override_control']) : (isset($settings['glenmark_header_sticky_override']) ? glenmark_sanitize_header_bool_override($settings['glenmark_header_sticky_override']) : 'default');
    $header_shrink_override = isset($settings['glenmark_header_shrink_override_control']) ? glenmark_sanitize_header_bool_override($settings['glenmark_header_shrink_override_control']) : (isset($settings['glenmark_header_shrink_override']) ? glenmark_sanitize_header_bool_override($settings['glenmark_header_shrink_override']) : 'default');
    $header_hide_on_scroll_override = isset($settings['glenmark_header_hide_on_scroll_override_control']) ? glenmark_sanitize_header_bool_override($settings['glenmark_header_hide_on_scroll_override_control']) : (isset($settings['glenmark_header_hide_on_scroll_override']) ? glenmark_sanitize_header_bool_override($settings['glenmark_header_hide_on_scroll_override']) : 'default');
    $header_logo_show_on_scroll_override = isset($settings['glenmark_header_logo_show_on_scroll_override_control']) ? glenmark_sanitize_header_bool_override($settings['glenmark_header_logo_show_on_scroll_override_control']) : (isset($settings['glenmark_header_logo_show_on_scroll_override']) ? glenmark_sanitize_header_bool_override($settings['glenmark_header_logo_show_on_scroll_override']) : 'default');
    $header_menu_override = isset($settings['glenmark_header_menu_override_control']) ? glenmark_sanitize_header_menu_override($settings['glenmark_header_menu_override_control']) : (isset($settings['glenmark_header_menu_override']) ? glenmark_sanitize_header_menu_override($settings['glenmark_header_menu_override']) : 'default');

    glenmark_set_header_settings_store($post_id, [
        'mode' => $header_mode,
        'override_mode' => glenmark_sanitize_header_mode_override($header_mode),
        'sticky' => $header_sticky_override,
        'shrink' => $header_shrink_override,
        'hide_on_scroll' => $header_hide_on_scroll_override,
        'logo_show_on_scroll' => $header_logo_show_on_scroll_override,
        'menu' => $header_menu_override,
    ]);

    update_post_meta($post_id, glenmark_header_mode_override_meta_key(), glenmark_sanitize_header_mode_override($header_mode));
    update_post_meta($post_id, glenmark_header_sticky_override_meta_key(), $header_sticky_override);
    update_post_meta($post_id, glenmark_header_shrink_override_meta_key(), $header_shrink_override);
    update_post_meta($post_id, glenmark_header_hide_on_scroll_override_meta_key(), $header_hide_on_scroll_override);
    update_post_meta($post_id, glenmark_header_logo_show_on_scroll_override_meta_key(), $header_logo_show_on_scroll_override);
    update_post_meta($post_id, glenmark_header_menu_override_meta_key(), $header_menu_override);
    update_post_meta($post_id, glenmark_header_mode_meta_key(), $header_mode);
    update_post_meta($post_id, glenmark_header_transparent_meta_key(), 'transparent' === $header_mode ? '1' : '0');

    glenmark_sync_elementor_header_settings($post_id, $header_mode, $header_sticky_override, $header_shrink_override, $header_hide_on_scroll_override, $header_logo_show_on_scroll_override, $header_menu_override);
}
add_action('elementor/document/after_save', 'glenmark_save_elementor_header_mode_control', 10, 2);

function glenmark_sanitize_checkbox($value)
{
    return !empty($value);
}

function glenmark_sanitize_rich_text($value)
{
    return wp_kses_post($value);
}

function glenmark_sanitize_header_height($value)
{
    $value = absint($value);

    if ($value < 56) {
        return 56;
    }

    if ($value > 220) {
        return 220;
    }

    return $value;
}

function glenmark_sanitize_header_logo_max_width($value)
{
    $value = absint($value);

    if ($value < 40) {
        return 40;
    }

    if ($value > 600) {
        return 600;
    }

    return $value;
}

function glenmark_get_header_layout_options()
{
    $variants = glenmark_config('layout.header_variants', []);
    $options = [];

    foreach ($variants as $key => $variant) {
        $options[$key] = __($variant['label'] ?? $key, 'gln-pharma-product');
    }

    return $options ?: ['default' => __('Standard header', 'gln-pharma-product')];
}

function glenmark_sanitize_header_layout($value)
{
    $value = sanitize_key((string) $value);
    $allowed = array_keys(glenmark_get_header_layout_options());

    if (!in_array($value, $allowed, true)) {
        return 'default';
    }

    return $value;
}

function glenmark_get_footer_layout_options()
{
    $variants = glenmark_config('layout.footer_variants', []);
    $options = [];

    foreach ($variants as $key => $variant) {
        $options[$key] = __($variant['label'] ?? $key, 'gln-pharma-product');
    }

    return $options ?: ['default' => __('Standard footer', 'gln-pharma-product')];
}

function glenmark_sanitize_footer_layout($value)
{
    $value = sanitize_key((string) $value);
    $allowed = array_keys(glenmark_get_footer_layout_options());

    if (!in_array($value, $allowed, true)) {
        return 'default';
    }

    return $value;
}

function glenmark_sanitize_header_background_color($value)
{
    $value = trim((string) $value);

    if ('' === $value) {
        return '';
    }

    return sanitize_hex_color($value) ?: '';
}

function glenmark_sanitize_header_background_size($value)
{
    $value = sanitize_key((string) $value);
    $allowed = ['cover', 'contain', 'auto'];

    return in_array($value, $allowed, true) ? $value : 'cover';
}

function glenmark_sanitize_header_background_position($value)
{
    $value = trim((string) $value);
    $allowed = [
        'center center',
        'top center',
        'bottom center',
        'left center',
        'right center',
        'top left',
        'top right',
        'bottom left',
        'bottom right',
        'center left',
        'center right',
    ];

    return in_array($value, $allowed, true) ? $value : 'center center';
}

function glenmark_sanitize_header_background_repeat($value)
{
    $value = sanitize_key((string) $value);
    $allowed = ['no-repeat', 'repeat', 'repeat-x', 'repeat-y'];

    return in_array($value, $allowed, true) ? $value : 'no-repeat';
}

function glenmark_sanitize_social_url($value)
{
    $value = trim((string) $value);

    if ('' === $value) {
        return '';
    }

    return esc_url_raw($value);
}

function glenmark_register_customize_rich_text_control_class()
{
    if (class_exists('Glenmark_Customize_Rich_Text_Control') || !class_exists('WP_Customize_Control')) {
        return;
    }

    class Glenmark_Customize_Rich_Text_Control extends WP_Customize_Control
    {
        public $type = 'glenmark_rich_text';

        public function render_content()
        {
            ?>
            <label for="<?php echo esc_attr($this->id); ?>">
                <?php if (!empty($this->label)) : ?>
                    <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                <?php endif; ?>
                <?php if (!empty($this->description)) : ?>
                    <span class="description customize-control-description"><?php echo wp_kses_post($this->description); ?></span>
                <?php endif; ?>
            </label>
            <textarea
                id="<?php echo esc_attr($this->id); ?>"
                class="widefat glenmark-customizer-rich-text"
                rows="8"
                data-glenmark-rich-text="1"
                <?php $this->link(); ?>
            ><?php echo esc_textarea($this->value()); ?></textarea>
            <?php
        }
    }
}

function glenmark_register_customizer_site_settings($wp_customize)
{
    glenmark_register_customize_rich_text_control_class();

    $wp_customize->add_section('glenmark_bottom_notice', [
        'title' => __('Notice', 'gln-pharma-product'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('glenmark_bottom_notice_enabled', [
        'default' => false,
        'sanitize_callback' => 'glenmark_sanitize_checkbox',
    ]);

    $wp_customize->add_control('glenmark_bottom_notice_enabled', [
        'label' => __('Show notice', 'gln-pharma-product'),
        'section' => 'glenmark_bottom_notice',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('glenmark_bottom_notice_text', [
        'default' => '',
        'sanitize_callback' => 'glenmark_sanitize_rich_text',
    ]);

    $wp_customize->add_control(new Glenmark_Customize_Rich_Text_Control($wp_customize, 'glenmark_bottom_notice_text', [
        'label' => __('Notice text', 'gln-pharma-product'),
        'section' => 'glenmark_bottom_notice',
    ]));

    $wp_customize->add_section('glenmark_site_controls', [
        'title' => __('Site controls', 'gln-pharma-product'),
        'priority' => 31,
    ]);

    $wp_customize->add_setting('glenmark_back_to_top', [
        'default' => false,
        'sanitize_callback' => 'glenmark_sanitize_checkbox',
    ]);

    $wp_customize->add_control('glenmark_back_to_top', [
        'label' => __('Show back to top button', 'gln-pharma-product'),
        'section' => 'glenmark_site_controls',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_section('glenmark_header', [
        'title' => __('Header', 'gln-pharma-product'),
        'priority' => 32,
    ]);

    $wp_customize->add_setting('glenmark_header_layout', [
        'default' => 'default',
        'sanitize_callback' => 'glenmark_sanitize_header_layout',
    ]);

    $wp_customize->add_control('glenmark_header_layout', [
        'label' => __('Header layout', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'select',
        'choices' => glenmark_get_header_layout_options(),
    ]);

    $wp_customize->add_setting('glenmark_header_background_color', [
        'default' => '',
        'sanitize_callback' => 'glenmark_sanitize_header_background_color',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'glenmark_header_background_color', [
        'label' => __('Header background color', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'priority' => 15,
    ]));

    $wp_customize->add_setting('glenmark_header_background_image', [
        'default' => 0,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'glenmark_header_background_image', [
        'label' => __('Header background image', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'mime_type' => 'image',
        'priority' => 16,
    ]));

    $wp_customize->add_setting('glenmark_header_background_size', [
        'default' => 'cover',
        'sanitize_callback' => 'glenmark_sanitize_header_background_size',
    ]);

    $wp_customize->add_control('glenmark_header_background_size', [
        'label' => __('Background size', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'select',
        'choices' => [
            'cover' => __('Cover', 'gln-pharma-product'),
            'contain' => __('Contain', 'gln-pharma-product'),
            'auto' => __('Auto', 'gln-pharma-product'),
        ],
        'priority' => 17,
    ]);

    $wp_customize->add_setting('glenmark_header_background_position', [
        'default' => 'center center',
        'sanitize_callback' => 'glenmark_sanitize_header_background_position',
    ]);

    $wp_customize->add_control('glenmark_header_background_position', [
        'label' => __('Background position', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'select',
        'choices' => [
            'center center' => __('Center center', 'gln-pharma-product'),
            'top center' => __('Top center', 'gln-pharma-product'),
            'bottom center' => __('Bottom center', 'gln-pharma-product'),
            'left center' => __('Left center', 'gln-pharma-product'),
            'right center' => __('Right center', 'gln-pharma-product'),
            'top left' => __('Top left', 'gln-pharma-product'),
            'top right' => __('Top right', 'gln-pharma-product'),
            'bottom left' => __('Bottom left', 'gln-pharma-product'),
            'bottom right' => __('Bottom right', 'gln-pharma-product'),
            'center left' => __('Center left', 'gln-pharma-product'),
            'center right' => __('Center right', 'gln-pharma-product'),
        ],
        'priority' => 18,
    ]);

    $wp_customize->add_setting('glenmark_header_background_repeat', [
        'default' => 'no-repeat',
        'sanitize_callback' => 'glenmark_sanitize_header_background_repeat',
    ]);

    $wp_customize->add_control('glenmark_header_background_repeat', [
        'label' => __('Background repeat', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'select',
        'choices' => [
            'no-repeat' => __('No repeat', 'gln-pharma-product'),
            'repeat' => __('Repeat', 'gln-pharma-product'),
            'repeat-x' => __('Repeat X', 'gln-pharma-product'),
            'repeat-y' => __('Repeat Y', 'gln-pharma-product'),
        ],
        'priority' => 19,
    ]);

    $wp_customize->add_setting('glenmark_header_navigation_link_color', [
        'default' => '',
        'sanitize_callback' => 'glenmark_sanitize_header_background_color',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'glenmark_header_navigation_link_color', [
        'label' => __('Navigation link color', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'priority' => 20,
    ]));

    $wp_customize->add_setting('glenmark_header_navigation_separators', [
        'default' => false,
        'sanitize_callback' => 'glenmark_sanitize_checkbox',
    ]);

    $wp_customize->add_control('glenmark_header_navigation_separators', [
        'label' => __('Show separators between navigation items', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'checkbox',
        'priority' => 21,
    ]);

    $wp_customize->add_section('glenmark_site_branding', [
        'title' => __('Branding', 'gln-pharma-product'),
        'priority' => 33,
    ]);

    $wp_customize->add_section('glenmark_footer', [
        'title' => __('Footer', 'gln-pharma-product'),
        'priority' => 34,
    ]);

    $wp_customize->add_setting('glenmark_footer_page', [
        'default' => 0,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control('glenmark_footer_page', [
        'label' => __('Footer page', 'gln-pharma-product'),
        'description' => __('Select an Elementor page to display as the site footer.', 'gln-pharma-product'),
        'section' => 'glenmark_footer',
        'type' => 'dropdown-pages',
    ]);

    $footer_layout_options = glenmark_get_footer_layout_options();

    if (count($footer_layout_options) > 1) {
        $wp_customize->add_setting('glenmark_footer_layout', [
            'default' => 'default',
            'sanitize_callback' => 'glenmark_sanitize_footer_layout',
        ]);

        $wp_customize->add_control('glenmark_footer_layout', [
            'label' => __('Footer layout', 'gln-pharma-product'),
            'section' => 'glenmark_footer',
            'type' => 'select',
            'choices' => $footer_layout_options,
        ]);
    }

    $wp_customize->add_setting('glenmark_logo_default', [
        'default' => 0,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'glenmark_logo_default', [
        'label' => __('Primary logo', 'gln-pharma-product'),
        'section' => 'glenmark_site_branding',
        'mime_type' => 'image',
    ]));

    $wp_customize->add_setting('glenmark_logo_transparent', [
        'default' => 0,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'glenmark_logo_transparent', [
        'label' => __('Transparent header logo', 'gln-pharma-product'),
        'section' => 'glenmark_site_branding',
        'mime_type' => 'image',
    ]));

    $wp_customize->add_setting('glenmark_favicon', [
        'default' => 0,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'glenmark_favicon', [
        'label' => __('Favicon override', 'gln-pharma-product'),
        'section' => 'glenmark_site_branding',
        'mime_type' => 'image',
    ]));

    $wp_customize->add_setting('glenmark_social_facebook', [
        'default' => '',
        'sanitize_callback' => 'glenmark_sanitize_social_url',
    ]);

    $wp_customize->add_control('glenmark_social_facebook', [
        'label' => __('Facebook URL', 'gln-pharma-product'),
        'section' => 'glenmark_site_branding',
        'type' => 'url',
    ]);

    $wp_customize->add_setting('glenmark_social_instagram', [
        'default' => '',
        'sanitize_callback' => 'glenmark_sanitize_social_url',
    ]);

    $wp_customize->add_control('glenmark_social_instagram', [
        'label' => __('Instagram URL', 'gln-pharma-product'),
        'section' => 'glenmark_site_branding',
        'type' => 'url',
    ]);

    $wp_customize->add_setting('glenmark_social_youtube', [
        'default' => '',
        'sanitize_callback' => 'glenmark_sanitize_social_url',
    ]);

    $wp_customize->add_control('glenmark_social_youtube', [
        'label' => __('YouTube URL', 'gln-pharma-product'),
        'section' => 'glenmark_site_branding',
        'type' => 'url',
    ]);

    $wp_customize->add_setting('glenmark_header_mode', [
        'default' => 'normal',
        'sanitize_callback' => 'glenmark_sanitize_header_mode_override',
    ]);

    $wp_customize->add_control('glenmark_header_mode', [
        'label' => __('Header mode', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'select',
        'choices' => glenmark_get_header_mode_options(),
        'priority' => 10,
    ]);

    $wp_customize->add_setting('glenmark_header_sticky', [
        'default' => true,
        'sanitize_callback' => 'glenmark_sanitize_checkbox',
    ]);

    $wp_customize->add_control('glenmark_header_sticky', [
        'label' => __('Sticky header', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('glenmark_header_shrink', [
        'default' => true,
        'sanitize_callback' => 'glenmark_sanitize_checkbox',
    ]);

    $wp_customize->add_control('glenmark_header_shrink', [
        'label' => __('Shrink header on scroll', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('glenmark_header_hide_on_scroll', [
        'default' => false,
        'sanitize_callback' => 'glenmark_sanitize_checkbox',
    ]);

    $wp_customize->add_control('glenmark_header_hide_on_scroll', [
        'label' => __('Show header on scroll', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('glenmark_header_height', [
        'default' => 88,
        'sanitize_callback' => 'glenmark_sanitize_header_height',
    ]);

    $wp_customize->add_control('glenmark_header_height', [
        'label' => __('Header height (px)', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'number',
        'input_attrs' => [
            'min' => 56,
            'max' => 220,
            'step' => 1,
        ],
    ]);

    $wp_customize->add_setting('glenmark_header_logo_max_width', [
        'default' => 260,
        'sanitize_callback' => 'glenmark_sanitize_header_logo_max_width',
    ]);

    $wp_customize->add_control('glenmark_header_logo_max_width', [
        'label' => __('Max logo width (px)', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'number',
        'input_attrs' => [
            'min' => 40,
            'max' => 600,
            'step' => 1,
        ],
    ]);

    $wp_customize->add_setting('glenmark_header_shrink_height', [
        'default' => 68,
        'sanitize_callback' => 'glenmark_sanitize_header_height',
    ]);

    $wp_customize->add_control('glenmark_header_shrink_height', [
        'label' => __('Header height when shrunk (px)', 'gln-pharma-product'),
        'section' => 'glenmark_header',
        'type' => 'number',
        'input_attrs' => [
            'min' => 40,
            'max' => 200,
            'step' => 1,
        ],
    ]);
}
add_action('customize_register', 'glenmark_register_customizer_site_settings');

function glenmark_enqueue_customizer_rich_text_assets()
{
    $script_path = glenmark_get_theme_file_path('assets/js/customizer-rich-text.js');

    if (!file_exists($script_path)) {
        return;
    }

    wp_enqueue_editor();
    wp_enqueue_script(
        'glenmark-customizer-rich-text',
        glenmark_get_theme_file_uri('assets/js/customizer-rich-text.js'),
        ['jquery', 'customize-controls'],
        (string) filemtime($script_path),
        true
    );
}
add_action('customize_controls_enqueue_scripts', 'glenmark_enqueue_customizer_rich_text_assets');