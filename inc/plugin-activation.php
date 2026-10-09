<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TGM Plugin Activation: installs required plugins available from WordPress.org.
 * Commercial plugins are installed separately from their licensed sources.
 */
require_once __DIR__ . '/tgm/class-tgm-plugin-activation.php';

function glenmark_register_required_plugins()
{
    $plugins = [
        [
            'name' => 'Elementor',
            'slug' => 'elementor',
            'required' => true,
        ],
        [
            'name' => 'CookieYes | GDPR Cookie Consent',
            'slug' => 'cookie-law-info',
            'required' => true,
        ],
        [
            'name' => 'Git Updater',
            'slug' => 'git-updater',
            'source' => 'https://github.com/afragen/git-updater/releases/download/14.4.2/git-updater-14.4.2.zip',
            'required' => false,
        ],
    ];

    $config = [
        'id' => 'gln-pharma-product',
        'default_path' => '',
        'menu' => 'tgmpa-install-plugins',
        'parent_slug' => 'themes.php',
        'capability' => 'edit_theme_options',
        'has_notices' => true,
        'dismissable' => true,
        'is_automatic' => false,
        'message' => '',
    ];

    tgmpa($plugins, $config);
}
add_action('tgmpa_register', 'glenmark_register_required_plugins');

function glenmark_notice_acf_pro_required()
{
    if (function_exists('acf_is_pro') && acf_is_pro()) {
        return;
    }

    if (!current_user_can('install_plugins')) {
        return;
    }

    echo '<div class="notice notice-warning"><p>'
        . esc_html__('Tato šablona vyžaduje ACF PRO pro kompletní produktová pole (galerie a opakovatelná pole). Nainstalujte ACF PRO z licencovaného zdroje; bezplatná verze ACF nestačí.', 'gln-pharma-product')
        . '</p></div>';
}
add_action('admin_notices', 'glenmark_notice_acf_pro_required');
