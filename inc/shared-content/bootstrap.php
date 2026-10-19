<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/api.php';
require_once __DIR__ . '/render.php';
require_once __DIR__ . '/live.php';
if (class_exists('\Elementor\Core\DynamicTags\Tag')) {
    require_once __DIR__ . '/dynamic-tags.php';
}
require_once __DIR__ . '/editor.php';