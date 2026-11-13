<?php

if (!defined('ABSPATH')) {
    exit;
}

function glenmark_shared_content_register_editor_block()
{
    $script_path = get_template_directory() . '/inc/shared-content/assets/js/shared-content-editor.js';
    $script_uri = get_template_directory_uri() . '/inc/shared-content/assets/js/shared-content-editor.js';
    $version = file_exists($script_path) ? (string) filemtime($script_path) : wp_get_theme()->get('Version');

    wp_register_script(
        'glenmark-shared-content-editor',
        $script_uri,
        ['wp-api-fetch', 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-element', 'wp-plugins'],
        $version,
        true
    );

    register_block_type('glenmark/shared-content-snapshot', [
        'api_version' => 2,
        'editor_script' => 'glenmark-shared-content-editor',
        'attributes' => [
            'sourceKey' => ['type' => 'string'],
            'sourceVersion' => ['type' => 'string'],
            'sourceTitle' => ['type' => 'string'],
        ],
        'supports' => ['html' => false],
    ]);
}
add_action('init', 'glenmark_shared_content_register_editor_block', 20);

function glenmark_shared_content_prepare_editor_items($force_refresh = false)
{
    $items = array_values(glenmark_shared_content_get_items($force_refresh));

    foreach ($items as &$item) {
        $item['snapshot_html'] = glenmark_shared_content_render_item($item);
    }
    unset($item);

    return $items;
}

function glenmark_shared_content_enqueue_editor_assets()
{
    wp_localize_script('glenmark-shared-content-editor', 'glenmarkSharedContent', [
        'items' => glenmark_shared_content_prepare_editor_items(),
        'refreshUrl' => rest_url('gln-pharma-product/v1/shared-content-items'),
        'nonce' => wp_create_nonce('wp_rest'),
        'labels' => [
            'panelTitle' => __('Sdílený obsah Glenmark', 'gln-pharma-product'),
            'selectItem' => __('Vyberte obsah', 'gln-pharma-product'),
            'insert' => __('Vložit / aktualizovat kopii', 'gln-pharma-product'),
            'refresh' => __('Načíst aktuální nabídku', 'gln-pharma-product'),
            'current' => __('Aktuální', 'gln-pharma-product'),
            'outdated' => __('Nová verze', 'gln-pharma-product'),
            'noItems' => __('Není dostupný žádný publikovaný obsah. Zkontrolujte URL API v nastavení webu.', 'gln-pharma-product'),
            'confirmReplace' => __('Tato stránka už obsahuje kopii této položky. Nahradit ji aktuální verzí? Vaše úpravy uvnitř nahrazeného bloku budou přepsány.', 'gln-pharma-product'),
            'refreshFailed' => __('Aktuální obsah se nepodařilo načíst. Zobrazené kopie zůstaly beze změny.', 'gln-pharma-product'),
        ],
    ]);
}
add_action('enqueue_block_editor_assets', 'glenmark_shared_content_enqueue_editor_assets');

function glenmark_shared_content_register_editor_route()
{
    register_rest_route('gln-pharma-product/v1', '/shared-content-items', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'glenmark_shared_content_editor_route',
        'permission_callback' => static function () {
            return current_user_can('edit_posts');
        },
    ]);
}
add_action('rest_api_init', 'glenmark_shared_content_register_editor_route');

function glenmark_shared_content_editor_route()
{
    if ('' === glenmark_shared_content_api_endpoint()) {
        return new WP_Error(
            'glenmark_shared_content_not_configured',
            __('Není nastavena URL centrálního REST API.', 'gln-pharma-product'),
            ['status' => 503]
        );
    }

    return rest_ensure_response([
        'items' => glenmark_shared_content_prepare_editor_items(true),
    ]);
}

function glenmark_shared_content_field_labels()
{
    return [
        'company_name' => __('Název společnosti', 'gln-pharma-product'),
        'building' => __('Budova', 'gln-pharma-product'),
        'street' => __('Ulice a číslo', 'gln-pharma-product'),
        'postal_code' => __('PSČ', 'gln-pharma-product'),
        'city' => __('Město', 'gln-pharma-product'),
        'country' => __('Země', 'gln-pharma-product'),
        'phone' => __('Telefon', 'gln-pharma-product'),
        'email_primary' => __('Hlavní e-mail', 'gln-pharma-product'),
        'email_secondary' => __('Další e-mail', 'gln-pharma-product'),
        'link_label' => __('Text odkazu', 'gln-pharma-product'),
        'website_url' => __('URL', 'gln-pharma-product'),
        'full_title' => __('Plný název', 'gln-pharma-product'),
        'document_version' => __('Verze', 'gln-pharma-product'),
        'document_date' => __('Datum', 'gln-pharma-product'),
        'approval_number' => __('Schvalovací číslo', 'gln-pharma-product'),
    ];
}

function glenmark_shared_content_link_field_names()
{
    return ['phone', 'email_primary', 'email_secondary', 'website_url'];
}

function glenmark_shared_content_add_text_editor_controls($element)
{
    $items = glenmark_shared_content_get_items();
    $options = ['' => __('Vyberte sdílenou šablonu', 'gln-pharma-product')];

    foreach ($items as $key => $item) {
        if (empty($item['data']['content_html'])) {
            continue;
        }
        $options[$key] = (string) ($item['title'] ?? $key) . ' (' . $key . ')';
    }

    $element->add_control('glenmark_shared_template_key', [
        'label' => __('Načíst obsah z Glenmark Hubu', 'gln-pharma-product'),
        'type' => \Elementor\Controls_Manager::SELECT2,
        'options' => $options,
        'label_block' => true,
        'description' => __('Vyberte šablonu a vložte ji jako editovatelný HTML obsah.', 'gln-pharma-product'),
    ]);
    $element->add_control('glenmark_shared_template_import', [
        'type' => \Elementor\Controls_Manager::RAW_HTML,
        'raw' => '<button type="button" class="elementor-button elementor-button-default glenmark-shared-template-import">'
            . esc_html__('Vložit jako editovatelnou kopii', 'gln-pharma-product') . '</button>'
            . '<span class="glenmark-shared-template-import-status" role="status" aria-live="polite"></span>',
        'content_classes' => 'elementor-control-field',
    ]);

    $field_labels = glenmark_shared_content_field_labels();
    $field_options = ['' => __('Vyberte sdílený údaj', 'gln-pharma-product')];

    foreach ($items as $key => $item) {
        if ('contact' !== (string) ($item['type'] ?? '') || empty($item['data']) || !is_array($item['data'])) {
            continue;
        }
        foreach ($item['data'] as $field_name => $value) {
            if ('content_html' === $field_name || !is_scalar($value) || '' === (string) $value) {
                continue;
            }
            $field_options[$key . '|' . $field_name] = (string) ($item['title'] ?? $key)
                . ' – ' . (string) ($field_labels[$field_name] ?? $field_name);
        }
    }

    $element->add_control('glenmark_shared_field_key', [
        'label' => __('Vložit sdílený údaj', 'gln-pharma-product'),
        'type' => \Elementor\Controls_Manager::SELECT2,
        'options' => $field_options,
        'label_block' => true,
        'description' => __('Vyberte údaj a kliknutím vložte jeho shortcode na pozici kurzoru v editoru.', 'gln-pharma-product'),
    ]);
    $element->add_control('glenmark_shared_field_insert', [
        'type' => \Elementor\Controls_Manager::RAW_HTML,
        'raw' => '<button type="button" class="elementor-button elementor-button-default glenmark-shared-field-insert">'
            . esc_html__('Vložit shortcode na pozici kurzoru', 'gln-pharma-product') . '</button>'
            . '<span class="glenmark-shared-field-insert-status" role="status" aria-live="polite"></span>',
        'content_classes' => 'elementor-control-field',
    ]);
}
add_action(
    'elementor/element/text-editor/section_editor/before_section_end',
    'glenmark_shared_content_add_text_editor_controls'
);

function glenmark_shared_content_enqueue_elementor_editor_script()
{
    $script_path = get_template_directory() . '/inc/shared-content/assets/js/shared-content-elementor-editor.js';
    $script_uri = get_template_directory_uri() . '/inc/shared-content/assets/js/shared-content-elementor-editor.js';
    $version = file_exists($script_path) ? (string) filemtime($script_path) : wp_get_theme()->get('Version');

    wp_enqueue_script('glenmark-shared-content-elementor-editor', $script_uri, ['jquery'], $version, true);
    wp_localize_script('glenmark-shared-content-elementor-editor', 'glenmarkSharedContentElementor', [
        'refreshUrl' => rest_url('gln-pharma-product/v1/shared-content-items'),
        'nonce' => wp_create_nonce('wp_rest'),
        'linkFields' => glenmark_shared_content_link_field_names(),
        'labels' => [
            'selectTemplate' => __('Nejprve vyberte sdílenou šablonu.', 'gln-pharma-product'),
            'loading' => __('Načítám obsah…', 'gln-pharma-product'),
            'inserted' => __('Obsah je vložený a lze jej upravovat.', 'gln-pharma-product'),
            'failed' => __('Obsah se nepodařilo načíst. Zkontrolujte připojení k Hubu.', 'gln-pharma-product'),
            'confirmReplace' => __('Vložením se nahradí celý současný obsah Textového editoru. Pokračovat?', 'gln-pharma-product'),
            'selectField' => __('Nejprve vyberte sdílený údaj.', 'gln-pharma-product'),
            'fieldInserted' => __('Shortcode je vložený na pozici kurzoru.', 'gln-pharma-product'),
        ],
    ]);
}
add_action('elementor/editor/after_enqueue_scripts', 'glenmark_shared_content_enqueue_elementor_editor_script');