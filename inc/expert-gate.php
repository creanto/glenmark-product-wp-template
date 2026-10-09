<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * "Experts only" access gate: a full-page notice shown before content that is
 * flagged (per page/post) as intended for healthcare professionals only.
 */

function glenmark_expert_gate_meta_key()
{
    return '_glenmark_expert_only';
}

function glenmark_expert_gate_post_types()
{
    return glenmark_get_header_settings_post_types();
}

function glenmark_expert_gate_defaults()
{
    return [
        'text' => __('Obsah této stránky je určen výhradně odborníkům ve zdravotnictví (lékařům, lékárníkům a dalším zdravotnickým pracovníkům) ve smyslu zákona č. 40/1995 Sb. Kliknutím na tlačítko „Jsem odborník“ potvrzujete, že jste odborníkem v uvedeném smyslu.', 'gln-pharma-product'),
        'confirm_label' => __('Jsem odborník', 'gln-pharma-product'),
        'decline_label' => __('Nejsem odborník', 'gln-pharma-product'),
        'decline_url' => home_url('/'),
    ];
}

function glenmark_expert_gate_get_setting($key)
{
    $defaults = glenmark_expert_gate_defaults();
    $default = array_key_exists($key, $defaults) ? $defaults[$key] : '';

    return get_theme_mod('glenmark_expert_gate_' . $key, $default);
}

function glenmark_is_expert_gate_enabled($post_id = null)
{
    $post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();

    if ($post_id <= 0) {
        return false;
    }

    return (bool) get_post_meta($post_id, glenmark_expert_gate_meta_key(), true);
}

/* --- Classic editor meta box --- */

function glenmark_add_expert_gate_metabox()
{
    foreach (glenmark_expert_gate_post_types() as $post_type) {
        add_meta_box(
            'glenmark-expert-gate',
            __('Expert only', 'gln-pharma-product'),
            'glenmark_render_expert_gate_metabox',
            $post_type,
            'side',
            'default'
        );
    }
}
add_action('add_meta_boxes', 'glenmark_add_expert_gate_metabox');

function glenmark_render_expert_gate_metabox($post)
{
    $post_id = isset($post->ID) ? (int) $post->ID : 0;
    $enabled = (bool) get_post_meta($post_id, glenmark_expert_gate_meta_key(), true);

    wp_nonce_field('glenmark_save_expert_gate', 'glenmark_expert_gate_nonce');
    ?>
    <p>
        <label>
            <input type="checkbox" name="glenmark_expert_gate_enabled" value="1" <?php checked($enabled); ?> />
            <?php esc_html_e('Show "experts only" notice before this page', 'gln-pharma-product'); ?>
        </label>
    </p>
    <p class="description">
        <?php esc_html_e('Text and button labels are configured in Customizer → Expert only notice.', 'gln-pharma-product'); ?>
    </p>
    <?php
}

function glenmark_save_expert_gate_metabox($post_id)
{
    if (!isset($_POST['glenmark_expert_gate_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(wp_unslash($_POST['glenmark_expert_gate_nonce']), 'glenmark_save_expert_gate')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, glenmark_expert_gate_meta_key(), !empty($_POST['glenmark_expert_gate_enabled']) ? '1' : '');
}
add_action('save_post', 'glenmark_save_expert_gate_metabox');

/* --- Elementor document control (so the toggle is also reachable from the Elementor editor) --- */

function glenmark_expert_gate_document_supported($document)
{
    if (!is_object($document) || !method_exists($document, 'get_main_id')) {
        return false;
    }

    $post_id = (int) $document->get_main_id();

    if ($post_id <= 0) {
        return false;
    }

    return in_array((string) get_post_type($post_id), glenmark_expert_gate_post_types(), true);
}

function glenmark_register_elementor_expert_gate_control($document)
{
    if (!class_exists('Elementor\\Controls_Manager')) {
        return;
    }

    if (!glenmark_expert_gate_document_supported($document)) {
        return;
    }

    $post_id = (int) $document->get_main_id();

    $document->start_controls_section(
        'glenmark_expert_gate_settings',
        [
            'label' => __('Expert only', 'gln-pharma-product'),
            'tab' => \Elementor\Controls_Manager::TAB_SETTINGS,
        ]
    );

    $document->add_control(
        'glenmark_expert_gate_enabled_control',
        [
            'label' => __('Show "experts only" notice', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => (bool) get_post_meta($post_id, glenmark_expert_gate_meta_key(), true) ? 'yes' : '',
            'description' => __('Text and button labels are configured in Customizer → Expert only notice.', 'gln-pharma-product'),
        ]
    );

    $document->end_controls_section();
}
add_action('elementor/documents/register_controls', 'glenmark_register_elementor_expert_gate_control');

function glenmark_save_elementor_expert_gate_control($document, $data = [])
{
    if (!glenmark_expert_gate_document_supported($document)) {
        return;
    }

    $post_id = (int) $document->get_main_id();

    $settings = is_array($data) && !empty($data) ? $data : [];

    if (empty($settings) && method_exists($document, 'get_settings')) {
        $settings = $document->get_settings();
    }

    if (!is_array($settings) || !array_key_exists('glenmark_expert_gate_enabled_control', $settings)) {
        return;
    }

    update_post_meta($post_id, glenmark_expert_gate_meta_key(), 'yes' === $settings['glenmark_expert_gate_enabled_control'] ? '1' : '');
}
add_action('elementor/document/after_save', 'glenmark_save_elementor_expert_gate_control', 10, 2);

/* --- Customizer --- */

function glenmark_sanitize_expert_gate_url($value)
{
    $value = trim((string) $value);

    if ('' === $value) {
        return home_url('/');
    }

    return esc_url_raw($value);
}

function glenmark_register_expert_gate_customizer($wp_customize)
{
    $defaults = glenmark_expert_gate_defaults();

    glenmark_register_customize_rich_text_control_class();

    $wp_customize->add_section('glenmark_expert_gate', [
        'title' => __('Expert only notice', 'gln-pharma-product'),
        'priority' => 33,
    ]);

    $wp_customize->add_setting('glenmark_expert_gate_text', [
        'default' => $defaults['text'],
        'sanitize_callback' => 'glenmark_sanitize_rich_text',
    ]);

    $wp_customize->add_control(
        new Glenmark_Customize_Rich_Text_Control($wp_customize, 'glenmark_expert_gate_text', ['label' => __('Notice text', 'gln-pharma-product'), 'section' => 'glenmark_expert_gate',])
    );

    $wp_customize->add_setting('glenmark_expert_gate_confirm_label', [
        'default' => $defaults['confirm_label'],
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('glenmark_expert_gate_confirm_label', [
        'label' => __('Confirm button label', 'gln-pharma-product'),
        'section' => 'glenmark_expert_gate',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('glenmark_expert_gate_decline_label', [
        'default' => $defaults['decline_label'],
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('glenmark_expert_gate_decline_label', [
        'label' => __('Decline button label', 'gln-pharma-product'),
        'section' => 'glenmark_expert_gate',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('glenmark_expert_gate_decline_url', [
        'default' => $defaults['decline_url'],
        'sanitize_callback' => 'glenmark_sanitize_expert_gate_url',
    ]);

    $wp_customize->add_control('glenmark_expert_gate_decline_url', [
        'label' => __('Redirect URL when declined', 'gln-pharma-product'),
        'description' => __('Where a visitor who is not an expert gets sent.', 'gln-pharma-product'),
        'section' => 'glenmark_expert_gate',
        'type' => 'url',
    ]);
}
add_action('customize_register', 'glenmark_register_expert_gate_customizer');

/* --- Frontend rendering --- */

function glenmark_render_expert_gate()
{
    if (is_admin() || !is_singular()) {
        return;
    }

    if (!glenmark_is_expert_gate_enabled()) {
        return;
    }

    $text = wp_kses_post(glenmark_expert_gate_get_setting('text'));

    if ('' === trim(wp_strip_all_tags($text))) {
        return;
    }

    $confirm_label = glenmark_expert_gate_get_setting('confirm_label');
    $decline_label = glenmark_expert_gate_get_setting('decline_label');
    $decline_url = glenmark_sanitize_expert_gate_url(glenmark_expert_gate_get_setting('decline_url'));
    ?>
    <div class="gln-expert-gate" data-decline-url="<?php echo esc_url($decline_url); ?>" role="dialog" aria-modal="true"
        aria-label="<?php esc_attr_e('Expert only notice', 'gln-pharma-product'); ?>">
        <div class="gln-expert-gate__overlay"></div>
        <div class="gln-expert-gate__dialog">
            <div class="gln-expert-gate__text"><?php echo wp_kses_post($text); ?></div>
            <div class="gln-expert-gate__actions">
                <button type="button" class="gln-expert-gate__button gln-expert-gate__button--confirm">
                    <?php echo esc_html($confirm_label); ?>
                </button>
                <button type="button" class="gln-expert-gate__button gln-expert-gate__button--decline">
                    <?php echo esc_html($decline_label); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'glenmark_render_expert_gate', 5);

function glenmark_enqueue_expert_gate_assets()
{
    if (is_admin() || !is_singular() || !glenmark_is_expert_gate_enabled()) {
        return;
    }

    $css_path = glenmark_get_theme_file_path('assets/css/expert-gate.css');
    $css_uri = glenmark_get_theme_file_uri('assets/css/expert-gate.css');
    $js_path = glenmark_get_theme_file_path('assets/js/expert-gate.js');
    $js_uri = glenmark_get_theme_file_uri('assets/js/expert-gate.js');

    if (!empty($css_uri)) {
        wp_enqueue_style('glenmark-expert-gate', $css_uri, [], $css_path ? (string) filemtime($css_path) : null);
    }

    if (!empty($js_uri)) {
        wp_enqueue_script('glenmark-expert-gate', $js_uri, [], $js_path ? (string) filemtime($js_path) : null, true);
    }
}
add_action('wp_enqueue_scripts', 'glenmark_enqueue_expert_gate_assets');
