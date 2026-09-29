<?php

if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div id="page" class="site">
        <?php
        if (!function_exists('webrev_render_site_header')) {
            $webrev_bootstrap = __DIR__ . '/inc/bootstrap.php';

            if (file_exists($webrev_bootstrap)) {
                require_once $webrev_bootstrap;
            }
        }

        if (function_exists('webrev_render_site_header')) {
            webrev_render_site_header();
        } else {
            get_template_part('template-parts/header/default');
        }
        ?>
        <main id="primary" class="site-main site-content">