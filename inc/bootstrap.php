<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/theme-config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/plugin-activation.php';
require_once __DIR__ . '/setup.php';
require_once __DIR__ . '/post-types/product.php';
require_once __DIR__ . '/post-types/pharmacy.php';
require_once __DIR__ . '/acf/product-fields.php';
require_once __DIR__ . '/acf/pharmacy-fields.php';
require_once __DIR__ . '/acf/post-fields.php';
require_once __DIR__ . '/product-feed.php';
require_once __DIR__ . '/shortcodes/product-filters.php';
require_once __DIR__ . '/site-settings.php';
require_once __DIR__ . '/expert-gate.php';
require_once __DIR__ . '/menu-item-settings.php';
require_once __DIR__ . '/assets.php';
require_once __DIR__ . '/template-hooks.php';
require_once __DIR__ . '/cookieyes.php';
require_once __DIR__ . '/elementor-modify.php';
require_once __DIR__ . '/glenmark-elementor-animations/glenmark-elementor-animations.php';
require_once __DIR__ . '/elementor/theme-style-scope.php';
require_once __DIR__ . '/elementor/register-widgets.php';
require_once __DIR__ . '/elementor/scalable-canvas.php';
require_once __DIR__ . '/elementor-json-editor.php';

