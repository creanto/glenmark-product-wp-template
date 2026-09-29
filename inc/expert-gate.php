<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * "Experts only" access gate: a full-page notice shown before content that is
 * flagged (per page/post) as intended for healthcare professionals only.
 */

function webrev_expert_gate_meta_key()
{
    return '_webrev_expert_only';
}

function webrev_expert_gate_post_types()
{
    return webrev_get_header_settings_post_types();
}

function webrev_expert_gate_defaults()
{
    return [
        'text' => __('Obsah této stránky je určen výhradně odborníkům ve zdravotnictví (lékařům, lékárníkům a dalším zdravotnickým pracovníkům) ve smyslu zákona č. 40/1995 Sb. Kliknutím na tlačítko „Jsem odborník“ potvrzujete, že jste odborníkem v uvedeném smyslu.', 'wr-pharma-product'),
        'confirm_label' => __('Jsem odborník', 'wr-pharma-product'),
        'decline_label' => __('Nejsem odborník', 'wr-pharma-product'),
        'decline_url' => home_url('/'),
    ];
}

function webrev_expert_gate_get_setting($key)
{
    $defaults = webrev_expert_gate_defaults();
    $default = array_key_exists($key, $defaults) ? $defaults[$key] : '';

    return get_theme_mod('webrev_expert_gate_' . $key, $default);
}

function webrev_is_expert_gate_enabled($post_id = null)
{
    $post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();

    if ($post_id <= 0) {
        return false;
    }

    return (bool) get_post_meta($post_id, webrev_expert_gate_meta_key(), true);
}

/* --- Classic editor meta box --- */

function webrev_add_expert_gate_metabox()
{
    foreach (webrev_expert_gate_post_types() as $post_type) {
        add_meta_box(
            'webrev-expert-gate',
            __('Expert only', 'wr-pharma-product'),
            'webrev_render_expert_gate_metabox',
            $post_type,
            'side',
            'default'
        );
    }
}
add_action('add_meta_boxes', 'webrev_add_expert_gate_metabox');

function webrev_render_expert_gate_metabox($post)
{
    $post_id = isset($post->ID) ? (int) $post->ID : 0;
    $enabled = (bool) get_post_meta($post_id, webrev_expert_gate_meta_key(), true);

    wp_nonce_field('webrev_save_expert_gate', 'webrev_expert_gate_nonce');
    ?>
    <p>
        <label>
            <input type="checkbox" name="webrev_expert_gate_enabled" value="1" <?php checked($enabled); ?> />
            <?php esc_html_e('Show "experts only" notice before this page', 'wr-pharma-product'); ?>
        </label>
    </p>
    <p class="description">
        <?php esc_html_e('Text and button labels are configured in Customizer → Expert only notice.', 'wr-pharma-product'); ?>
    </p>
    <?php
}

function webrev_save_expert_gate_metabox($post_id)
{
    if (!isset($_POST['webrev_expert_gate_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(wp_unslash($_POST['webrev_expert_gate_nonce']), 'webrev_save_expert_gate')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, webrev_expert_gate_meta_key(), !empty($_POST['webrev_expert_gate_enabled']) ? '1' : '');
}
add_action('save_post', 'webrev_save_expert_gate_metabox');

/* --- Elementor document control (so the toggle is also reachable from the Elementor editor) --- */

function webrev_expert_gate_document_supported($document)
{
    if (!is_object($document) || !method_exists($document, 'get_main_id')) {
        return false;
    }

    $post_id = (int) $document->get_main_id();

    if ($post_id <= 0) {
        return false;
    }

    return in_array((string) get_post_type($post_id), webrev_expert_gate_post_types(), true);
}

function webrev_register_elementor_expert_gate_control($document)
{
    if (!class_exists('Elementor\\Controls_Manager')) {
        return;
    }

    if (!webrev_expert_gate_document_supported($document)) {
        return;
    }

    $post_id = (int) $document->get_main_id();

    $document->start_controls_section(
        'webrev_expert_gate_settings',
        [
            'label' => __('Expert only', 'wr-pharma-product'),
            'tab' => \Elementor\Controls_Manager::TAB_SETTINGS,
        ]
    );

    $document->add_control(
        'webrev_expert_gate_enabled_control',
        [
            'label' => __('Show "experts only" notice', 'wr-pharma-product'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => (bool) get_post_meta($post_id, webrev_expert_gate_meta_key(), true) ? 'yes' : '',
            'description' => __('Text and button labels are configured in Customizer → Expert only notice.', 'wr-pharma-product'),
        ]
    );

    $document->end_controls_section();
}
add_action('elementor/documents/register_controls', 'webrev_register_elementor_expert_gate_control');

function webrev_save_elementor_expert_gate_control($document, $data = [])
{
    if (!webrev_expert_gate_document_supported($document)) {
        return;
    }

    $post_id = (int) $document->get_main_id();

    $settings = is_array($data) && !empty($data) ? $data : [];

    if (empty($settings) && method_exists($document, 'get_settings')) {
        $settings = $document->get_settings();
    }

    if (!is_array($settings) || !array_key_exists('webrev_expert_gate_enabled_control', $settings)) {
        return;
    }

    update_post_meta($post_id, webrev_expert_gate_meta_key(), 'yes' === $settings['webrev_expert_gate_enabled_control'] ? '1' : '');
}
add_action('elementor/document/after_save', 'webrev_save_elementor_expert_gate_control', 10, 2);

/* --- Customizer --- */

function webrev_sanitize_expert_gate_url($value)
{
    $value = trim((string) $value);

    if ('' === $value) {
        return home_url('/');
    }

    return esc_url_raw($value);
}

function webrev_register_expert_gate_customizer($wp_customize)
{
    $defaults = webrev_expert_gate_defaults();

    webrev_register_customize_rich_text_control_class();

    $wp_customize->add_section('webrev_expert_gate', [
        'title' => __('Expert only notice', 'wr-pharma-product'),
        'priority' => 33,
    ]);

    $wp_customize->add_setting('webrev_expert_gate_text', [
        'default' => $defaults['text'],
        'sanitize_callback' => 'webrev_sanitize_rich_text',
    ]);

    $wp_customize->add_control(
        new Webrev_Customize_Rich_Text_Control($wp_customize, 'webrev_expert_gate_text', ['label' => __('Notice text', 'wr-pharma-product'), 'section' => 'webrev_expert_gate',])
    );

    $wp_customize->add_setting('webrev_expert_gate_confirm_label', [
        'default' => $defaults['confirm_label'],
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('webrev_expert_gate_confirm_label', [
        'label' => __('Confirm button label', 'wr-pharma-product'),
        'section' => 'webrev_expert_gate',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('webrev_expert_gate_decline_label', [
        'default' => $defaults['decline_label'],
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('webrev_expert_gate_decline_label', [
        'label' => __('Decline button label', 'wr-pharma-product'),
        'section' => 'webrev_expert_gate',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('webrev_expert_gate_decline_url', [
        'default' => $defaults['decline_url'],
        'sanitize_callback' => 'webrev_sanitize_expert_gate_url',
    ]);

    $wp_customize->add_control('webrev_expert_gate_decline_url', [
        'label' => __('Redirect URL when declined', 'wr-pharma-product'),
        'description' => __('Where a visitor who is not an expert gets sent.', 'wr-pharma-product'),
        'section' => 'webrev_expert_gate',
        'type' => 'url',
    ]);
}
add_action('customize_register', 'webrev_register_expert_gate_customizer');

/* --- Frontend rendering --- */

function webrev_render_expert_gate()
{
    if (is_admin() || !is_singular()) {
        return;
    }

    if (!webrev_is_expert_gate_enabled()) {
        return;
    }

    $text = wp_kses_post(webrev_expert_gate_get_setting('text'));

    if ('' === trim(wp_strip_all_tags($text))) {
        return;
    }

    $confirm_label = webrev_expert_gate_get_setting('confirm_label');
    $decline_label = webrev_expert_gate_get_setting('decline_label');
    $decline_url = webrev_sanitize_expert_gate_url(webrev_expert_gate_get_setting('decline_url'));
    ?>
    <div class="wr-expert-gate" data-decline-url="<?php echo esc_url($decline_url); ?>" role="dialog" aria-modal="true"
        aria-label="<?php esc_attr_e('Expert only notice', 'wr-pharma-product'); ?>">
        <div class="wr-expert-gate__overlay"></div>
        <div class="wr-expert-gate__dialog">
            <div class="wr-expert-gate__text"><?php echo wp_kses_post($text); ?></div>
            <div class="wr-expert-gate__actions">
                <button type="button" class="wr-expert-gate__button wr-expert-gate__button--confirm">
                    <?php echo esc_html($confirm_label); ?>
                </button>
                <button type="button" class="wr-expert-gate__button wr-expert-gate__button--decline">
                    <?php echo esc_html($decline_label); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'webrev_render_expert_gate', 5);

function webrev_enqueue_expert_gate_assets()
{
    if (is_admin() || !is_singular() || !webrev_is_expert_gate_enabled()) {
        return;
    }

    $css_path = webrev_get_theme_file_path('assets/css/expert-gate.css');
    $css_uri = webrev_get_theme_file_uri('assets/css/expert-gate.css');
    $js_path = webrev_get_theme_file_path('assets/js/expert-gate.js');
    $js_uri = webrev_get_theme_file_uri('assets/js/expert-gate.js');

    if (!empty($css_uri)) {
        wp_enqueue_style('webrev-expert-gate', $css_uri, [], $css_path ? (string) filemtime($css_path) : null);
    }

    if (!empty($js_uri)) {
        wp_enqueue_script('webrev-expert-gate', $js_uri, [], $js_path ? (string) filemtime($js_path) : null, true);
    }
}
add_action('wp_enqueue_scripts', 'webrev_enqueue_expert_gate_assets');
