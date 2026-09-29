<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_PostGrid_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wr-post-grid';
    }

    public function get_title()
    {
        return __('Post Grid', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-posts-grid';
    }

    public function get_categories()
    {
        return ['webrev'];
    }

    protected function register_controls()
    {
        $categories = get_terms([
            'taxonomy' => 'category',
            'hide_empty' => false,
        ]);
        $category_options = [];

        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $category_options[(string) $category->term_id] = $category->name;
            }
        }

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'wr-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'categories',
            [
                'label' => __('Categories', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $category_options,
                'multiple' => true,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Posts to show', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 24,
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
            ]
        );

        $this->add_control(
            'thumbnail_size',
            [
                'label' => __('Thumbnail shape', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'square',
                'options' => [
                    'square' => __('Square (1:1)', 'wr-pharma-product'),
                    'landscape' => __('Landscape (16:9)', 'wr-pharma-product'),
                    'portrait' => __('Portrait (3:4)', 'wr-pharma-product'),
                ],
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show excerpt', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'wr-pharma-product'),
                'label_off' => __('Hide', 'wr-pharma-product'),
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $posts_per_page = max(1, (int) ($settings['posts_per_page'] ?? 6));
        $columns = max(1, min(4, (int) ($settings['columns'] ?? 3)));
        $tablet_columns = min(2, $columns);
        $thumbnail_size = in_array($settings['thumbnail_size'] ?? 'square', ['square', 'landscape', 'portrait'], true)
            ? $settings['thumbnail_size']
            : 'square';
        $show_excerpt = ($settings['show_excerpt'] ?? 'yes') === 'yes';
        $category_ids = array_filter(array_map('absint', (array) ($settings['categories'] ?? [])));
        $query_args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'ignore_sticky_posts' => true,
        ];

        if ($category_ids) {
            $query_args['category__in'] = $category_ids;
        }

        $query = new \WP_Query($query_args);

        if (!$query->have_posts()) {
            echo '<div class="wr-empty-state">' . esc_html__('No posts available.', 'wr-pharma-product') . '</div>';
            return;
        }

        echo '<div class="wr-post-grid" style="--wr-post-grid-columns:' . esc_attr((string) $columns) . ';--wr-post-grid-tablet-columns:' . esc_attr((string) $tablet_columns) . ';">';

        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $title = get_the_title($post_id);
            $short_title = trim((string) get_field('short_title', $post_id));
            $grid_title = $short_title !== '' ? $short_title : $title;
            $url = get_permalink($post_id);
            $excerpt = wp_trim_words(get_the_excerpt($post_id), 22);

            echo '<article class="wr-post-card wr-post-card--thumb-' . esc_attr($thumbnail_size) . '">';
            echo '<div class="wr-post-card__media">';

            if (has_post_thumbnail($post_id)) {
                echo wp_get_attachment_image(get_post_thumbnail_id($post_id), 'medium_large', false, ['class' => 'wr-post-card__image']);
            } else {
                echo '<div class="wr-post-card__placeholder" aria-hidden="true"></div>';
            }

            echo '</div>';
            echo '<div class="wr-post-card__content">';
            echo '<h3 class="wr-post-card__title">' . esc_html($grid_title) . '</h3>';

            if ($show_excerpt && $excerpt) {
                echo '<p class="wr-post-card__excerpt">' . esc_html($excerpt) . '</p>';
            }

            echo '<span class="wr-post-card__link" aria-hidden="true">';
            echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h13M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
            echo '</span>';
            echo '</div>';
            echo '<a class="wr-post-card__stretched-link" href="' . esc_url($url) . '" aria-label="' . esc_attr(sprintf(__('Read %s', 'wr-pharma-product'), $title)) . '"></a>';
            echo '</article>';
        }

        wp_reset_postdata();
        echo '</div>';
    }
}