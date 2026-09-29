<?php
/**
 * Theme module: lightweight entrance animations for Elementor Free widgets, containers, sections, and columns.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WR_ELEMENTOR_ANIMATIONS_VERSION', '1.0.0');
define('WR_ELEMENTOR_ANIMATIONS_FILE', __FILE__);
define('WR_ELEMENTOR_ANIMATIONS_PATH', plugin_dir_path(__FILE__));
define('WR_ELEMENTOR_ANIMATIONS_URL', trailingslashit(get_theme_file_uri('inc/webrevolution-elementor-animations')));

function wr_elementor_animations_load(): void
{
    if (!did_action('elementor/loaded') && !class_exists('\Elementor\Plugin')) {
        add_action('admin_notices', 'wr_elementor_animations_missing_elementor_notice');
        return;
    }

    require_once WR_ELEMENTOR_ANIMATIONS_PATH . 'includes/class-controls.php';

    $controls = new WR_Elementor_Animations_Controls();
    $controls->init();
}
wr_elementor_animations_load();

function wr_elementor_animations_missing_elementor_notice(): void
{
    if (!current_user_can('activate_plugins')) {
        return;
    }

    printf(
        '<div class="notice notice-warning"><p>%s</p></div>',
        esc_html__('Glenmark Elementor Animations requires Elementor to be installed and active.', 'webrevolution-elementor-animations')
    );
}