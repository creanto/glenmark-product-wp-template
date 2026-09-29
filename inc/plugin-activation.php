<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TGM Plugin Activation: shows an admin notice + bulk installer for the
 * plugins this theme is built around. Free wordpress.org plugins install
 * in one click; PRO plugins (ACF PRO, AIOSEO PRO, Revolution Slider, Elementor Pro)
 * have no public download URL and must be uploaded manually - update the
 * 'source' key below with a hosted zip URL if you want to automate those too.
 */
require_once __DIR__ . '/tgm/class-tgm-plugin-activation.php';

function webrev_register_required_plugins()
{
    $plugins = [
        // Required - theme templates/hooks actively integrate with these.
        [
            'name' => 'Advanced Custom Fields',
            'slug' => 'advanced-custom-fields',
            'required' => true,
        ],
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

        // Recommended - not required for the theme to function, but part of
        // the standard site setup.
        [
            'name' => 'All in One SEO',
            'slug' => 'all-in-one-seo-pack',
            'required' => false,
        ],
        [
            'name' => 'Classic Editor',
            'slug' => 'classic-editor',
            'required' => false,
        ],
        [
            'name' => 'WP Super Cache',
            'slug' => 'wp-super-cache',
            'required' => false,
        ],
        [
            'name' => 'Wordfence Security',
            'slug' => 'wordfence',
            'required' => false,
        ],
        [
            'name' => 'UpdraftPlus - Backup/Restore',
            'slug' => 'updraftplus',
            'required' => false,
        ],
        [
            'name' => 'Regenerate Thumbnails',
            'slug' => 'regenerate-thumbnails',
            'required' => false,
        ],
        [
            'name' => 'WP Statistics',
            'slug' => 'wp-statistics',
            'required' => false,
        ],
        [
            'name' => 'User Switching',
            'slug' => 'user-switching',
            'required' => false,
        ],
    ];

    $config = [
        'id' => 'wr-pharma-product',
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
add_action('tgmpa_register', 'webrev_register_required_plugins');
