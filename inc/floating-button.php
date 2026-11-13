<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_get_floating_button_defaults()
{
    return [
        'enabled' => false,
        'text' => __('Koupit', 'gln-pharma-product'),
        'action' => 'pharmacies',
        'url' => '',
        'style' => 'pill',
        'position_horizontal' => 'right',
        'position_vertical' => 'bottom',
    ];
}

function glenmark_sanitize_floating_button_style($value)
{
    $value = sanitize_key((string) $value);

    return in_array($value, ['pill', 'tab'], true) ? $value : 'pill';
}

function glenmark_sanitize_floating_button_action($value)
{
    $value = sanitize_key((string) $value);
    $allowed = ['pharmacies', 'url'];

    return in_array($value, $allowed, true) ? $value : 'pharmacies';
}

function glenmark_sanitize_floating_button_position_horizontal($value)
{
    $value = sanitize_key((string) $value);

    return in_array($value, ['left', 'right'], true) ? $value : 'right';
}

function glenmark_sanitize_floating_button_position_vertical($value)
{
    $value = sanitize_key((string) $value);

    return in_array($value, ['top', 'middle', 'bottom'], true) ? $value : 'bottom';
}

function glenmark_get_floating_button_settings()
{
    $defaults = glenmark_get_floating_button_defaults();

    return [
        'enabled' => (bool) glenmark_get_setting('floating_button_enabled', $defaults['enabled']),
        'text' => trim((string) glenmark_get_setting('floating_button_text', $defaults['text'])),
        'action' => glenmark_sanitize_floating_button_action(glenmark_get_setting('floating_button_action', $defaults['action'])),
        'url' => esc_url_raw((string) glenmark_get_setting('floating_button_url', $defaults['url'])),
        'style' => glenmark_sanitize_floating_button_style(glenmark_get_setting('floating_button_style', $defaults['style'])),
        'position_horizontal' => glenmark_sanitize_floating_button_position_horizontal(glenmark_get_setting('floating_button_position_horizontal', $defaults['position_horizontal'])),
        'position_vertical' => glenmark_sanitize_floating_button_position_vertical(glenmark_get_setting('floating_button_position_vertical', $defaults['position_vertical'])),
    ];
}

function glenmark_floating_button_url_control_active()
{
    return 'url' === glenmark_sanitize_floating_button_action(get_theme_mod('glenmark_floating_button_action', 'pharmacies'));
}

function glenmark_register_floating_button_customizer($wp_customize)
{
    $defaults = glenmark_get_floating_button_defaults();

    $wp_customize->add_section('glenmark_floating_button', [
        'title' => __('Plovoucí tlačítko', 'gln-pharma-product'),
        'description' => __('Pevné tlačítko u kraje obrazovky. Může otevřít okno s lékárnami hlavního produktu nebo odkázat na vlastní URL.', 'gln-pharma-product'),
        'priority' => 35,
    ]);

    $wp_customize->add_setting('glenmark_floating_button_enabled', [
        'default' => $defaults['enabled'],
        'sanitize_callback' => 'glenmark_sanitize_checkbox',
    ]);

    $wp_customize->add_control('glenmark_floating_button_enabled', [
        'label' => __('Zobrazit plovoucí tlačítko', 'gln-pharma-product'),
        'section' => 'glenmark_floating_button',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('glenmark_floating_button_text', [
        'default' => $defaults['text'],
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('glenmark_floating_button_text', [
        'label' => __('Text tlačítka', 'gln-pharma-product'),
        'section' => 'glenmark_floating_button',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('glenmark_floating_button_style', [
        'default' => $defaults['style'],
        'sanitize_callback' => 'glenmark_sanitize_floating_button_style',
    ]);

    $wp_customize->add_control('glenmark_floating_button_style', [
        'label' => __('Varianta vzhledu', 'gln-pharma-product'),
        'section' => 'glenmark_floating_button',
        'type' => 'radio',
        'choices' => [
            'pill' => __('Celé tlačítko', 'gln-pharma-product'),
            'tab' => __('Záložka u kraje obrazovky', 'gln-pharma-product'),
        ],
    ]);

    $wp_customize->add_setting('glenmark_floating_button_position_horizontal', [
        'default' => $defaults['position_horizontal'],
        'sanitize_callback' => 'glenmark_sanitize_floating_button_position_horizontal',
    ]);

    $wp_customize->add_control('glenmark_floating_button_position_horizontal', [
        'label' => __('Vodorovné umístění', 'gln-pharma-product'),
        'section' => 'glenmark_floating_button',
        'type' => 'radio',
        'choices' => [
            'left' => __('Vlevo', 'gln-pharma-product'),
            'right' => __('Vpravo', 'gln-pharma-product'),
        ],
    ]);

    $wp_customize->add_setting('glenmark_floating_button_position_vertical', [
        'default' => $defaults['position_vertical'],
        'sanitize_callback' => 'glenmark_sanitize_floating_button_position_vertical',
    ]);

    $wp_customize->add_control('glenmark_floating_button_position_vertical', [
        'label' => __('Svislé umístění', 'gln-pharma-product'),
        'section' => 'glenmark_floating_button',
        'type' => 'radio',
        'choices' => [
            'top' => __('Nahoře', 'gln-pharma-product'),
            'middle' => __('Uprostřed', 'gln-pharma-product'),
            'bottom' => __('Dole', 'gln-pharma-product'),
        ],
    ]);

    $wp_customize->add_setting('glenmark_floating_button_action', [
        'default' => $defaults['action'],
        'sanitize_callback' => 'glenmark_sanitize_floating_button_action',
    ]);

    $wp_customize->add_control('glenmark_floating_button_action', [
        'label' => __('Akce po kliknutí', 'gln-pharma-product'),
        'section' => 'glenmark_floating_button',
        'type' => 'radio',
        'choices' => [
            'pharmacies' => __('Otevřít okno s lékárnami', 'gln-pharma-product'),
            'url' => __('Přejít na vlastní URL', 'gln-pharma-product'),
        ],
    ]);

    $wp_customize->add_setting('glenmark_floating_button_url', [
        'default' => $defaults['url'],
        'sanitize_callback' => 'glenmark_sanitize_social_url',
    ]);

    $wp_customize->add_control('glenmark_floating_button_url', [
        'label' => __('Vlastní URL', 'gln-pharma-product'),
        'section' => 'glenmark_floating_button',
        'type' => 'url',
        'active_callback' => 'glenmark_floating_button_url_control_active',
    ]);
}
add_action('customize_register', 'glenmark_register_floating_button_customizer');

function glenmark_render_floating_button()
{
    $settings = glenmark_get_floating_button_settings();

    if (!$settings['enabled'] || '' === $settings['text']) {
        return;
    }

    $is_modal = 'pharmacies' === $settings['action'];
    $pharmacies = [];
    $product_name = '';

    if ($is_modal) {
        $product_id = glenmark_get_main_product_id();
        $pharmacies = glenmark_get_product_pharmacy_links($product_id);

        if (empty($pharmacies)) {
            return;
        }

        $product_name = get_field('product_name', $product_id) ?: get_the_title($product_id);
    } elseif ('' === $settings['url']) {
        return;
    }

    $button_classes = implode(' ', array_filter([
        'glenmark-floating-button',
        'glenmark-floating-button--' . $settings['position_horizontal'],
        'glenmark-floating-button--' . $settings['position_vertical'],
        'tab' === $settings['style'] ? 'glenmark-floating-button--tab' : '',
    ]));
    ?>
    <?php if ($is_modal): ?>
        <a class="<?php echo esc_attr($button_classes); ?> gln-product-buy-modal-trigger"
            href="#gln-floating-buy-modal"><?php echo esc_html($settings['text']); ?></a>
        <div id="gln-floating-buy-modal" class="gln-product-buy-modal mfp-hide">
            <div class="gln-product-buy-modal__dialog">
                <h2 class="gln-product-buy-modal__title">
                    <?php echo esc_html__('Kde koupit', 'gln-pharma-product'); ?>
                </h2>
                <?php if ('' !== $product_name): ?>
                    <p class="gln-product-buy-modal__product">
                        <?php echo esc_html($product_name); ?>
                    </p>
                <?php endif; ?>
                <div class="gln-product-buy-modal__logos">
                    <?php foreach ($pharmacies as $pharmacy): ?>
                        <a class="gln-product-buy-modal__logo-link" href="<?php echo esc_url($pharmacy['url']); ?>"
                            data-pharmacy-key="<?php echo esc_attr($pharmacy['key']); ?>" target="_blank" rel="noopener noreferrer"
                            aria-label="<?php echo esc_attr($pharmacy['label']); ?>">
                            <?php if ($pharmacy['logo']): ?>
                                <img class="gln-product-buy-modal__logo" src="<?php echo esc_url($pharmacy['logo']); ?>"
                                    alt="<?php echo esc_attr($pharmacy['label']); ?>">
                            <?php else: ?>
                                <span class="gln-product-buy-modal__logo-fallback">
                                    <?php echo esc_html($pharmacy['label']); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <a class="<?php echo esc_attr($button_classes); ?>" href="<?php echo esc_url($settings['url']); ?>" target="_blank"
            rel="noopener noreferrer"><?php echo esc_html($settings['text']); ?></a>
    <?php endif; ?>
<?php
}
add_action('wp_footer', 'glenmark_render_floating_button', 15);
