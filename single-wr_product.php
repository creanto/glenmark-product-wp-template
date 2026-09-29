<?php

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();
        $product_id = get_the_ID();
        $preview_product_id = isset($_GET['elementor-preview']) ? absint(wp_unslash($_GET['elementor-preview'])) : 0;
        $parent_product_id = wp_is_post_revision($product_id) ?: wp_is_post_autosave($product_id);

        if ($parent_product_id) {
            $product_id = (int) $parent_product_id;
        }

        if ($preview_product_id > 0 && 'wr_product' === get_post_type($preview_product_id)) {
            $product_id = $preview_product_id;
        }

        $product_name = get_field('product_name', $product_id) ?: get_the_title($product_id);
        $product_subtitle = get_field('product_subtitle', $product_id) ?: '';
        $product_subtitle_more = get_field('product_subtitle_more', $product_id) ?: '';
        $full_name = get_field('product_full_name', $product_id) ?: trim($product_name . ' ' . $product_subtitle);
        $short_description = get_field('product_short_description', $product_id) ?: get_the_excerpt($product_id);
        $description = get_field('product_description', $product_id) ?: '';
        $claim = get_field('product_claim', $product_id) ?: get_field('product_highlight', $product_id);
        $main_benefits = get_field('product_main_benefits', $product_id) ?: '';
        $indication = get_field('product_indication', $product_id) ?: '';
        $active_ingredients = get_field('product_active_ingredients', $product_id) ?: '';
        $contained_ingredients = get_field('product_contained_ingredients', $product_id) ?: '';
        $packaging = get_field('product_packaging', $product_id) ?: '';
        $dosage = get_field('product_dosage', $product_id) ?: '';
        $category_label = get_field('product_category_label', $product_id) ?: '';
        $packshot_id = get_post_thumbnail_id($product_id) ?: get_field('product_packshot', $product_id);
        $gallery = get_field('product_gallery', $product_id) ?: [];
        $icons = get_field('product_icons', $product_id) ?: [];
        $composition = get_field('product_composition', $product_id) ?: '';
        // $short_text = get_field('product_short_text', $product_id) ?: '';
        $built_with_elementor = webrev_is_built_with_elementor($product_id);
        $has_long_description = $built_with_elementor || $description || '' !== trim((string) get_post_field('post_content', $product_id));
        $legal = get_field('product_legal_notice', $product_id) ?: '';
        $spc = get_field('product_spc', $product_id) ?: '';
        $registered_packages = get_field('product_registered_packages', $product_id) ?: [];
        $registered_packages_url = get_field('product_registered_packages_url', $product_id) ?: '';
        $pharmacy_logo_base_url = trailingslashit(get_template_directory_uri()) . 'img/lekarny/';
        $pharmacy_sources = [
            ['key' => 'benu', 'field' => 'product_pharmacy_benu', 'label' => 'Benu', 'logo' => 'benu.jpg'],
            ['key' => 'drmax', 'field' => 'product_pharmacy_drmax', 'label' => 'Dr. Max', 'logo' => 'drmax.jpg'],
            ['key' => 'lekarna_cz', 'field' => 'product_pharmacy_lekarna_cz', 'label' => 'Lékárna.cz', 'logo' => 'lakarna.jpg'],
            ['key' => 'magistra', 'field' => 'product_pharmacy_magistra', 'label' => 'Magistra', 'logo' => 'magistra.jpg'],
            ['key' => 'mojelekarna', 'field' => 'product_pharmacy_mojelekarna', 'label' => 'Moje lékárna', 'logo' => 'moje-lekarna.jpg'],
            ['key' => 'euclekarna', 'field' => 'product_pharmacy_euclekarna', 'label' => 'EUC lékárna', 'logo' => 'euc-lekarna.jpg'],
            ['key' => 'alza', 'field' => 'product_pharmacy_alza', 'label' => 'Alza.cz', 'logo' => 'alza.jpg'],
            ['key' => 'alphega', 'field' => 'product_pharmacy_alphega', 'label' => 'Alphega', 'logo' => ''],
            ['key' => 'pilulka', 'field' => 'product_pharmacy_pilulka', 'label' => 'Pilulka', 'logo' => ''],
            ['key' => 'ave', 'field' => 'product_pharmacy_ave', 'label' => 'Lékárna Ave', 'logo' => ''],
        ];
        $pharmacies = [];

        foreach ($pharmacy_sources as $source) {
            $url = (string) (get_field($source['field'], $product_id) ?: '');

            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $pharmacies[] = [
                    'key' => $source['key'],
                    'label' => $source['label'],
                    'logo' => $source['logo'] ? $pharmacy_logo_base_url . $source['logo'] : '',
                    'url' => $url,
                ];
            }
        }

        $main_packshot_url = $packshot_id ? wp_get_attachment_image_url($packshot_id, 'large') : '';
        $package_variants_raw = get_field('product_package_variants', $product_id) ?: [];
        $package_variants = [];
        $variant_box_max_height = 400;
        $variant_box_width = 0;
        $variant_box_height = 0;

        foreach ($package_variants_raw as $variant_index => $variant_data) {
            $variant_name = trim((string) ($variant_data['variant_name'] ?? ''));
            $variant_count = trim((string) ($variant_data['variant_count'] ?? ''));
            $variant_image_id = $variant_data['variant_image'] ?? 0;
            $variant_image_url = $variant_image_id ? wp_get_attachment_image_url($variant_image_id, 'large') : '';
            $variant_pharmacy_data = $variant_data['variant_pharmacies'] ?? [];
            $variant_pharmacy_urls = [];

            foreach ($pharmacy_sources as $source) {
                $variant_url = (string) ($variant_pharmacy_data['variant_pharmacy_' . $source['key']] ?? '');

                if (!filter_var($variant_url, FILTER_VALIDATE_URL)) {
                    $variant_url = (string) (get_field($source['field'], $product_id) ?: '');
                }

                if (filter_var($variant_url, FILTER_VALIDATE_URL)) {
                    $variant_pharmacy_urls[$source['key']] = $variant_url;
                }
            }

            $variant_image_src = wp_get_attachment_image_src($variant_image_id ?: $packshot_id, 'large');

            if ($variant_image_src) {
                $image_width = (float) $variant_image_src[1];
                $image_height = (float) $variant_image_src[2];

                if ($image_height > $variant_box_max_height) {
                    $image_width *= $variant_box_max_height / $image_height;
                    $image_height = $variant_box_max_height;
                }

                $variant_box_width = max($variant_box_width, $image_width);
                $variant_box_height = max($variant_box_height, $image_height);
            }

            $package_variants[] = [
                'label' => $variant_name ?: ($variant_count ? $variant_count . ' ks' : sprintf('Varianta %d', $variant_index + 1)),
                'image' => $variant_image_url ?: $main_packshot_url,
                'pharmacies' => $variant_pharmacy_urls,
            ];
        }

        $has_package_variants = count($package_variants) > 1;
        ?>
        <article id="product-<?php echo esc_attr($product_id); ?>" class="wr-product-template">
            <section class="wr-product-hero radiant-bg">
                <div class="container wr-product-hero__inner">
                    <div class="wr-product-hero__text">
                        <?php if ($claim): ?>
                            <div class="wr-product-hero__claim">
                                <h2 class="wr-product-highlight">
                                    <?php echo esc_html($claim); ?>
                                </h2>
                                <?php if ($product_subtitle_more): ?>
                                    <p class="wr-product-subtitle">
                                        <?php echo esc_html($product_subtitle_more); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="wr-product-hero__row">
                            <div class="wr-product-hero__copy">
                                <div class="wr-product-hero__copy-text">
                                    <!--  <h1><?php echo esc_html($product_name); ?></h1>
                            <?php if ($product_subtitle): ?>
                                <p class="wr-product-subtitle"><?php echo esc_html($product_subtitle); ?></p>
                            <?php endif; ?> -->
                                    <?php if ($main_benefits): ?>
                                        <div class="wr-product-benefits reveal-line">
                                            <?php echo wp_kses_post($main_benefits); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($indication || $active_ingredients || $contained_ingredients): ?>
                                    <div class="wr-product-chips" aria-label="Vlastnosti produktu">
                                        <?php foreach ([
                                            ['label' => 'Indikace', 'value' => $indication],
                                            ['label' => 'Účinné látky', 'value' => $active_ingredients],
                                            ['label' => 'Obsažené látky', 'value' => $contained_ingredients],
                                        ] as $property): ?>
                                            <?php if (!$property['value']) {
                                                continue;
                                            } ?>
                                            <div class="wr-product-chip-group">
                                                <span class="wr-product-chip-label"><?php echo esc_html($property['label']); ?></span>
                                                <span class="wr-product-chip">
                                                    <?php echo nl2br(esc_html($property['value'])); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($packshot_id && ($has_package_variants || $pharmacies)): ?>
                                <div class="wr-product-hero__actions">
                                    <?php if ($has_package_variants): ?>
                                        <div class="wr-variant-switch-group">
                                            <span class="wr-variant-switch__label">Vyberte velikost balení</span>
                                            <div class="wr-variant-switch" role="group" aria-label="Vyberte variantu balíčku">
                                                <?php foreach ($package_variants as $variant_index => $variant): ?>
                                                    <button type="button"
                                                        class="wr-variant-switch__item<?php echo $variant_index === 0 ? ' is-active' : ''; ?>"
                                                        data-image="<?php echo esc_url($variant['image']); ?>"
                                                        data-pharmacies='<?php echo esc_attr(wp_json_encode($variant['pharmacies'])); ?>'
                                                        aria-pressed="<?php echo $variant_index === 0 ? 'true' : 'false'; ?>">
                                                        <?php echo esc_html($variant['label']); ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($pharmacies): ?>
                                        <a class="wr-button wr-button--buy wr-product-buy-modal-trigger"
                                            href="#wr-product-buy-modal-<?php echo esc_attr($product_id); ?>">Koupit</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($packshot_id): ?>
                        <div class="wr-product-hero__media" <?php if ($has_package_variants && $variant_box_width && $variant_box_height): ?>
                                style="width: <?php echo esc_attr(ceil($variant_box_width)); ?>px; height: <?php echo esc_attr(ceil($variant_box_height)); ?>px;"
                            <?php endif; ?>>
                            <?php if ($has_package_variants): ?>
                                <img id="wr-product-packshot-<?php echo esc_attr($product_id); ?>"
                                    class="wr-product-packshot reveal-line" src="<?php echo esc_url($package_variants[0]['image']); ?>"
                                    alt="<?php echo esc_attr($full_name); ?>">
                            <?php else: ?>
                                <?php echo wp_get_attachment_image($packshot_id, 'large', false, ['class' => 'wr-product-packshot reveal-line']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($pharmacies): ?>
                <div id="wr-product-buy-modal-<?php echo esc_attr($product_id); ?>" class="wr-product-buy-modal mfp-hide">
                    <div class="wr-product-buy-modal__dialog">
                        <h2 class="wr-product-buy-modal__title">Kde koupit</h2>
                        <p class="wr-product-buy-modal__product">
                            <?php echo esc_html($product_name); ?>
                        </p>
                        <div class="wr-product-buy-modal__logos">
                            <?php foreach ($pharmacies as $pharmacy): ?>
                                <a class="wr-product-buy-modal__logo-link" href="<?php echo esc_url($pharmacy['url']); ?>"
                                    data-pharmacy-key="<?php echo esc_attr($pharmacy['key']); ?>" target="_blank"
                                    rel="noopener noreferrer" aria-label="<?php echo esc_attr($pharmacy['label']); ?>">
                                    <?php if ($pharmacy['logo']): ?>
                                        <img class="wr-product-buy-modal__logo" src="<?php echo esc_url($pharmacy['logo']); ?>"
                                            alt="<?php echo esc_attr($pharmacy['label']); ?>">
                                    <?php else: ?>
                                        <span class="wr-product-buy-modal__logo-fallback">
                                            <?php echo esc_html($pharmacy['label']); ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($short_description || $has_long_description || !empty($gallery)): ?>
                <section class="wr-product-description">

                    <div class="container wr-product-description__inner">

                        <h2>
                            <?php echo esc_html($full_name); ?>
                        </h2>
                        <?php if ($short_description): ?>
                            <div>
                                <?php echo wp_kses_post($short_description); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($has_long_description): ?>
                            <div>
                                <?php
                                // Elementor content must always go through the_content so its editor can locate the preview region.
                                if ($built_with_elementor) {
                                    echo apply_filters('the_content', get_post_field('post_content', $product_id));
                                } elseif ($description) {
                                    echo wp_kses_post($description);
                                } else {
                                    echo apply_filters('the_content', get_post_field('post_content', $product_id));
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($gallery)): ?>
                            <div class="wr-product-gallery__grid">
                                <?php foreach ($gallery as $image_id): ?>
                                    <?php $full_url = wp_get_attachment_image_url($image_id, 'large'); ?>
                                    <?php if (!$full_url): ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    <a class="wr-product-gallery__item" href="<?php echo esc_url($full_url); ?>"
                                        data-elementor-open-lightbox="yes"
                                        data-elementor-lightbox-slideshow="product-gallery-<?php echo esc_attr($product_id); ?>">
                                        <?php echo wp_get_attachment_image($image_id, 'medium', false, ['class' => 'wr-product-gallery__image', 'alt' => '']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($icons)): ?>
                <section class="wr-product-icons radiant-bg">
                    <div class="container wr-product-icons__grid">
                        <?php foreach ($icons as $icon): ?>
                            <?php $image_id = $icon['image'] ?? 0;
                            $label = $icon['label'] ?? '';
                            $description = $icon['description'] ?? ''; ?>
                            <?php if ($image_id || $label || $description): ?>
                                <div class="wr-product-icon">
                                    <?php if ($image_id): ?>
                                        <div class="wr-product-icon__image">
                                            <?php echo wp_get_attachment_image($image_id, 'medium', false, ['alt' => '']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="wr-product-icon__content">
                                        <?php if ($label): ?>
                                            <p class="wr-product-icon__label">
                                                <?php echo esc_html($label); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($description): ?>
                                            <p class="wr-product-icon__description">
                                                <?php echo nl2br(esc_html($description)); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="wr-product-details">
                <div class="container wr-product-details__inner">
                    <h3 class="wr-product-eyebrow">Detail produktu</h3>
                    <div class="wr-product-specification">
                        <?php foreach ([
                            ['label' => 'Indikace', 'value' => $indication, 'show_label' => true],
                            ['label' => 'Účinné látky', 'value' => $active_ingredients, 'show_label' => true],
                            ['label' => 'Obsažené látky', 'value' => $contained_ingredients, 'show_label' => true],
                            ['label' => 'Složení', 'value' => $composition, 'show_label' => true, 'html' => true],
                            ['label' => 'Dávkování', 'value' => $dosage, 'show_label' => true],
                            ['label' => 'Balení', 'value' => $packaging, 'show_label' => true],

                        ] as $spec): ?>
                            <?php if ($spec['value']): ?>
                                <div class="wr-product-specification__row">
                                    <?php if ($spec['show_label']): ?>
                                        <strong class="wr-product-specification__label">
                                            <?php echo esc_html($spec['label']); ?>
                                        </strong>
                                    <?php endif; ?>
                                    <div class="wr-product-specification__value">
                                        <?php echo !empty($spec['html']) ? wp_kses_post($spec['value']) : nl2br(esc_html($spec['value'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($registered_packages)): ?>
                        <div class="wr-product-packages">
                            <h4 class="wr-product-packages__title">Dostupná balení</h4>
                            <div class="wr-product-packages__table-wrap">
                                <table class="wr-product-packages__table">
                                    <thead>
                                        <tr>
                                            <th>Síla</th>
                                            <th>Množství</th>
                                            <th>Kód SÚKL</th>
                                            <th>SPC</th>
                                            <th>PIL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registered_packages as $package): ?>
                                            <?php
                                            $package_strength = trim((string) ($package['strength'] ?? ''));
                                            $package_quantity = trim((string) ($package['quantity'] ?? ''));
                                            $package_sukl_code = trim((string) ($package['sukl_code'] ?? ''));
                                            $package_spc = trim((string) ($package['spc'] ?? ''));
                                            $package_pil = trim((string) ($package['pil'] ?? ''));
                                            ?>
                                            <tr>
                                                <td data-label="Síla"><?php echo esc_html($package_strength); ?></td>
                                                <td data-label="Množství"><?php echo esc_html($package_quantity); ?></td>
                                                <td data-label="Kód SÚKL"><?php echo esc_html($package_sukl_code); ?></td>
                                                <td data-label="SPC">
                                                    <?php if ($package_spc): ?>
                                                        <a href="<?php echo esc_url($package_spc); ?>" target="_blank"
                                                            rel="noopener noreferrer">Zobrazit</a>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="PIL">
                                                    <?php if ($package_pil): ?>
                                                        <a href="<?php echo esc_url($package_pil); ?>" target="_blank"
                                                            rel="noopener noreferrer">Zobrazit</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($registered_packages_url): ?>
                                <a class="wr-product-packages__link" href="<?php echo esc_url($registered_packages_url); ?>"
                                    target="_blank" rel="noopener noreferrer">Seznam všech registrovaných balení</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($legal || $spc): ?>
                <section class="wr-product-legal">
                    <div class="container wr-product-legal__inner">
                        <div class="wr-product-legal__actions">
                            <?php if ($legal): ?>
                                <button type="button" class="wr-product-legal__toggle" aria-expanded="false">Upozornění</button>
                            <?php endif; ?>
                            <?php if ($spc): ?>
                                <a class="wr-product-legal__toggle" href="<?php echo esc_url($spc); ?>" target="_blank"
                                    rel="noopener noreferrer">Příbalová informace</a>
                            <?php endif; ?>
                        </div>
                        <?php if ($legal): ?>
                            <div class="wr-product-legal__content" hidden>
                                <?php echo wp_kses_post($legal); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($pharmacies): ?>
                <section class="wr-product-pharmacies">
                    <div class="container">
                        <h2 class="text-center">Kde koupit</h2>
                        <div class="wr-product-pharmacies__grid">
                            <?php foreach ($pharmacies as $pharmacy): ?>
                                <article class="wr-product-pharmacy">
                                    <div class="wr-product-pharmacy__logo">
                                        <?php if ($pharmacy['logo']): ?>
                                            <img src="<?php echo esc_url($pharmacy['logo']); ?>"
                                                alt="<?php echo esc_attr($pharmacy['label']); ?>">
                                        <?php else: ?>
                                            <span>
                                                <?php echo esc_html($pharmacy['label']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <a class="wr-button wr-button--primary" href="<?php echo esc_url($pharmacy['url']); ?>"
                                        target="_blank" rel="noopener noreferrer">E-shop
                                        <?php echo esc_html($pharmacy['label']); ?>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </article>
        <?php
    }
}

get_footer();