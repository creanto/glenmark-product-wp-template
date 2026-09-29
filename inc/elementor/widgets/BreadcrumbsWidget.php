<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_Breadcrumbs_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wr-breadcrumbs';
    }

    public function get_title()
    {
        return __('Drobečková navigace', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-breadcrumbs';
    }

    public function get_categories()
    {
        return ['webrev'];
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

        echo '<nav class="wr-breadcrumbs" aria-label="' . esc_attr__('Drobečková navigace', 'wr-pharma-product') . '">';
        echo '<ol class="wr-breadcrumbs__list">';

        foreach ($items as $index => $item) {
            $is_current = empty($item['url']);

            echo '<li class="wr-breadcrumbs__item">';
            if (!$is_current && !empty($item['url'])) {
                echo '<a class="wr-breadcrumbs__link" href="' . esc_url($item['url']) . '">';
            } else {
                echo '<span class="wr-breadcrumbs__current" aria-current="page">';
            }

            if (!empty($item['home'])) {
                echo '<svg class="wr-breadcrumbs__home-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
            }
            echo esc_html($item['label']);
            echo (!$is_current && !empty($item['url'])) ? '</a>' : '</span>';
            echo '</li>';
        }

        echo '</ol>';
        echo '</nav>';
    }
}