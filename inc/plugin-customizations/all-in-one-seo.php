<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_remove_aioseo_taxonomy_upsells()
{

    if (!function_exists('aioseo') || aioseo()->pro) {
        return;
    }

    $post_settings = aioseo()->postSettings;
    $taxonomies = get_taxonomies([], 'objects');

    foreach ($taxonomies as $taxonomy) {
        remove_action(
            $taxonomy->name . '_edit_form',
            [$post_settings, 'addTaxonomyUpsell']
        );

        remove_action(
            'after-' . $taxonomy->name . '-table',
            [$post_settings, 'addTaxonomyUpsell']
        );
    }
}

add_action('admin_init', 'glenmark_remove_aioseo_taxonomy_upsells', 1001);

