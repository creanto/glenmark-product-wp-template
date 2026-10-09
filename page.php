<?php

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();
        glenmark_get_template_part('template-parts/content/page');
    }
}

get_footer();