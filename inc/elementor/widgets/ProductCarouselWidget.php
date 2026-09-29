<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_ProductCarousel_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wr-product-carousel';
    }

    public function get_title()
    {
        return __('Product Carousel', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-slider-push';
    }

    public function get_categories()
    {
        return ['webrev'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'wr-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Products to show', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 12,
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show excerpt', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label' => __('Show image', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button text', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Zobrazit produkt', 'wr-pharma-product'),
            ]
        );

        $this->add_control(
            'carousel_autoplay',
            [
                'label' => __('Autoplay', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'carousel_speed',
            [
                'label' => __('Speed (ms)', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5000,
                'min' => 1000,
                'step' => 100,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $visible_slides = (int) ($settings['posts_per_page'] ?? 3);
        $visible_slides = max(1, min(6, $visible_slides));
        $show_image = !empty($settings['show_image']);

        $query = new \WP_Query([
            'post_type' => 'wr_product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ]);

        if (!$query->have_posts()) {
            echo '<div class="wr-empty-state">' . esc_html__('No products available.', 'wr-pharma-product') . '</div>';
            return;
        }

        $autoplay = !empty($settings['carousel_autoplay']) ? 'true' : 'false';
        $speed = (int) ($settings['carousel_speed'] ?? 5000);

        echo '<div class="wr-product-carousel" data-autoplay="' . esc_attr($autoplay) . '" data-speed="' . esc_attr((string) $speed) . '" data-slides-to-show="' . esc_attr((string) $visible_slides) . '">';
        echo '<div class="wr-product-carousel__track">';
        $modal_markup = '';

        $pharmacy_logo_base_url = trailingslashit(get_template_directory_uri()) . 'img/lekarny/';
        $pharmacy_sources = [
            [
                'field' => 'product_pharmacy_benu',
                'label' => 'Benu',
                'logo' => $pharmacy_logo_base_url . 'benu.jpg',
            ],
            [
                'field' => 'product_pharmacy_drmax',
                'label' => 'Dr. Max',
                'logo' => $pharmacy_logo_base_url . 'drmax.jpg',
            ],
            [
                'field' => 'product_pharmacy_lekarna_cz',
                'label' => 'Lékárna.cz',
                'logo' => $pharmacy_logo_base_url . 'lakarna.jpg',
            ],
            [
                'field' => 'product_pharmacy_magistra',
                'label' => 'Magistra',
                'logo' => $pharmacy_logo_base_url . 'magistra.jpg',
            ],
            [
                'field' => 'product_pharmacy_mojelekarna',
                'label' => 'Moje lékárna',
                'logo' => $pharmacy_logo_base_url . 'moje-lekarna.jpg',
            ],
            [
                'field' => 'product_pharmacy_euclekarna',
                'label' => 'EUC lékárna',
                'logo' => $pharmacy_logo_base_url . 'euc-lekarna.jpg',
            ],
            [
                'field' => 'product_pharmacy_alza',
                'label' => 'Alza',
                'logo' => $pharmacy_logo_base_url . 'alza.jpg',
            ],
            [
                'field' => 'product_pharmacy_alphega',
                'label' => 'Alphega',
                'logo' => '',
            ],
            [
                'field' => 'product_pharmacy_pilulka',
                'label' => 'Pilulka',
                'logo' => '',
            ],
            [
                'field' => 'product_pharmacy_ave',
                'label' => 'Lékárna Ave',
                'logo' => '',
            ],
        ];

        while ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            $packshot_id = get_field('product_packshot', $product_id) ?: get_post_thumbnail_id($product_id);
            $thumbnail_id = get_post_thumbnail_id($product_id) ?: $packshot_id;
            $product_name = get_field('product_name', $product_id) ?: get_the_title($product_id);
            $product_subtitle = get_field('product_subtitle', $product_id) ?: (get_field('product_claim', $product_id) ?: '');
            $product_short_description = get_field('product_short_description', $product_id) ?: '';
            $detail_url = get_permalink($product_id);
            $pharmacy_links = [];

            $info_chip_groups = array_filter([
                ['label' => 'Indikace', 'value' => get_field('product_indication', $product_id)],
                ['label' => 'Účinné látky', 'value' => get_field('product_active_ingredients', $product_id)],
                ['label' => 'Obsažené látky', 'value' => get_field('product_contained_ingredients', $product_id)],
            ], static function ($chip_group) {
                return !empty($chip_group['value']);
            });

            foreach ($pharmacy_sources as $source) {
                $url = (string) (get_field($source['field'], $product_id) ?: '');

                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $pharmacy_links[] = [
                        'label' => $source['label'],
                        'logo' => $source['logo'],
                        'url' => $url,
                    ];
                }
            }

            $modal_id = 'wr-product-buy-modal-' . $product_id;
            $info_modal_id = 'wr-product-info-modal-' . $product_id;

            echo '<article class="wr-card wr-card--product wr-product-carousel-card">';

            if ($show_image) {
                echo '<div class="wr-card__media wr-product-carousel-card__media">';
                if ($packshot_id) {
                    echo '<a href="' . esc_url($detail_url) . '">';
                    echo wp_get_attachment_image($packshot_id, 'medium', false, ['class' => 'wr-card__image wr-product-carousel-card__image']);
                    echo '</a>';
                }
                echo '</div>';
            }

            echo '<div class="wr-card__body wr-product-carousel-card__body">';
            echo '<h3 class="wr-card__title wr-product-carousel-card__title"><a href="' . esc_url($detail_url) . '">' . esc_html($product_name);
            if ($product_subtitle) {
                echo '<span class="wr-product-carousel-card__subtitle">' . esc_html($product_subtitle) . '</span>';
            }

            echo '</a></h3>';
            echo '<div class="wr-product-carousel-card__actions">';
            echo '<a class="wr-product-action-icon wr-product-action-icon--info wr-product-info-modal-trigger" href="#' . esc_attr($info_modal_id) . '" aria-label="' . esc_attr__('Detail produktu', 'wr-pharma-product') . '">';
            echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><line x1="12" y1="11" x2="12" y2="16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></line><circle cx="12" cy="8" r="1.2" fill="currentColor"></circle></svg>';
            echo '</a>';

            echo '<div class="wr-product-action-icon-group">';
            echo '<a class="wr-product-action-icon wr-product-action-icon--buy wr-product-buy-modal-trigger" href="#' . esc_attr($modal_id) . '" aria-label="' . esc_attr__('Koupit', 'wr-pharma-product') . '">';
            echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 6h2l2.2 10.2a2 2 0 0 0 2 1.6h6.8a2 2 0 0 0 1.9-1.4l1.8-6.4H8.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path><circle cx="10.5" cy="20" r="1.4" fill="currentColor"></circle><circle cx="17" cy="20" r="1.4" fill="currentColor"></circle></svg>';
            echo '</a>';
            echo '<a class="wr-product-action-icon wr-product-action-icon--detail" href="' . esc_url($detail_url) . '" aria-label="' . esc_attr__('Přejít na detail produktu', 'wr-pharma-product') . '">';
            echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M7 12h10" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path><path d="M13 8l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
            echo '</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</article>';

            $modal_markup .= '<div id="' . esc_attr($modal_id) . '" class="wr-product-buy-modal mfp-hide">';
            $modal_markup .= '<div class="wr-product-buy-modal__dialog">';
            $modal_markup .= '<h2 class="wr-product-buy-modal__title">' . esc_html__('Kde koupit', 'wr-pharma-product') . '</h2>';
            $modal_markup .= '<p class="wr-product-buy-modal__product">' . esc_html($product_name) . '</p>';

            if ($pharmacy_links) {
                $modal_markup .= '<div class="wr-product-buy-modal__logos">';

                foreach ($pharmacy_links as $pharmacy_link) {
                    $modal_markup .= '<a class="wr-product-buy-modal__logo-link" href="' . esc_url($pharmacy_link['url']) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr($pharmacy_link['label']) . '">';

                    if (!empty($pharmacy_link['logo'])) {
                        $modal_markup .= '<img class="wr-product-buy-modal__logo" src="' . esc_url($pharmacy_link['logo']) . '" alt="' . esc_attr($pharmacy_link['label']) . '">';
                    } else {
                        $modal_markup .= '<span class="wr-product-buy-modal__logo-fallback">' . esc_html($pharmacy_link['label']) . '</span>';
                    }

                    $modal_markup .= '</a>';
                }

                $modal_markup .= '</div>';
            } else {
                $modal_markup .= '<p class="wr-product-buy-modal__empty">' . esc_html__('Produkt momentálně není dostupný v žádném e-shopu.', 'wr-pharma-product') . '</p>';
            }

            $modal_markup .= '</div>';
            $modal_markup .= '</div>';

            $modal_markup .= '<div id="' . esc_attr($info_modal_id) . '" class="wr-product-info-modal mfp-hide">';
            $modal_markup .= '<div class="wr-product-info-modal__dialog">';
            $modal_markup .= '<h2 class="wr-product-info-modal__title">' . esc_html($product_name) . '</h2>';

            if ($product_subtitle) {
                $modal_markup .= '<p class="wr-product-info-modal__subtitle">' . esc_html($product_subtitle) . '</p>';
            }

            if ($thumbnail_id) {
                $modal_markup .= '<div class="wr-product-info-modal__media">';
                $modal_markup .= wp_get_attachment_image($thumbnail_id, 'large', false, ['class' => 'wr-product-info-modal__image']);
                $modal_markup .= '</div>';
            }

            if ($info_chip_groups) {
                $modal_markup .= '<div class="wr-product-info-modal__chips">';

                foreach ($info_chip_groups as $chip_group) {
                    $modal_markup .= '<div class="wr-product-info-modal__chip">';
                    $modal_markup .= '<span class="wr-product-info-modal__chip-label">' . esc_html($chip_group['label']) . '</span>';
                    $modal_markup .= '<span class="wr-product-info-modal__chip-value">' . esc_html($chip_group['value']) . '</span>';
                    $modal_markup .= '</div>';
                }

                $modal_markup .= '</div>';
            }

            if ($product_short_description) {
                $modal_markup .= '<div class="wr-product-info-modal__description">' . wp_kses_post($product_short_description) . '</div>';
            }

            $modal_markup .= '<a class="wr-product-info-modal__button" href="' . esc_url($detail_url) . '">' . esc_html($settings['button_text'] ?? __('Detail produktu', 'wr-pharma-product')) . '</a>';
            $modal_markup .= '</div>';
            $modal_markup .= '</div>';
        }

        wp_reset_postdata();

        echo '</div>';
        echo $modal_markup;
        echo '</div>';
    }
}
