<?php

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();

        webrev_get_template_part('template-parts/content/singular');
    }
}

get_footer();