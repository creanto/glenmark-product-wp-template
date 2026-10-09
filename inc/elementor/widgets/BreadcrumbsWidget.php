<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Breadcrumbs_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'gln-breadcrumbs';
    }

    public function get_title()
    {
        return __('Drobečková navigace', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-breadcrumbs';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    protected function render()
    {
        $post_id = get_queried_object_id() ?: get_the_ID();
        $items = [
            [
                'label' => get_bloginfo('name'),
                'url' => home_url('/'),
                'home' => true,
            ],
        ];

        if ($post_id && is_singular('post')) {
            $categories = get_the_category($post_id);

            if (!empty($categories)) {
                $category_main_page = get_field('category_main_page', $categories[0]);
                $items[] = [
                    'label' => $categories[0]->name,
                    'url' => $category_main_page ?: '',
                ];
            }
        } elseif ($post_id && is_page()) {
            $items[] = [
                'label' => get_the_title($post_id),
            ];
        }

        echo '<nav class="gln-breadcrumbs" aria-label="' . esc_attr__('Drobečková navigace', 'gln-pharma-product') . '">';
        echo '<ol class="gln-breadcrumbs__list">';

        foreach ($items as $index => $item) {
            $is_current = empty($item['url']);

            echo '<li class="gln-breadcrumbs__item">';
            if (!$is_current && !empty($item['url'])) {
                echo '<a class="gln-breadcrumbs__link" href="' . esc_url($item['url']) . '">';
            } else {
                echo '<span class="gln-breadcrumbs__current" aria-current="page">';
            }

            if (!empty($item['home'])) {
                echo '<svg class="gln-breadcrumbs__home-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
            }
            echo esc_html($item['label']);
            echo (!$is_current && !empty($item['url'])) ? '</a>' : '</span>';
            echo '</li>';
        }

        echo '</ol>';
        echo '</nav>';
    }
}