<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Glenmark_Elementor_Json_Editor')) {
    class Glenmark_Elementor_Json_Editor
    {
        const BACKUP_META_KEY = '_glenmark_elementor_backups';

        public static function init()
        {
            add_action('admin_menu', [__CLASS__, 'register_admin_page']);
            add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
            add_action('rest_api_init', [__CLASS__, 'register_rest_routes']);
        }

        public static function register_admin_page()
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            add_management_page(
                __('Elementor JSON Editor', 'gln-pharma-product'),
                __('Elementor JSON Editor', 'gln-pharma-product'),
                'manage_options',
                'glenmark-elementor-json-editor',
                [__CLASS__, 'render_page']
            );
        }

        public static function enqueue_assets($hook_suffix)
        {
            if ('tools_page_glenmark-elementor-json-editor' !== $hook_suffix) {
                return;
            }

            $editor_settings = wp_enqueue_code_editor([
                'type' => 'application/json',
                'codemirror' => [
                    'mode' => 'application/json',
                    'lineNumbers' => true,
                    'lineWrapping' => false,
                    'matchBrackets' => true,
                    'autoCloseBrackets' => true,
                    'indentUnit' => 2,
                    'tabSize' => 2,
                    'theme' => 'default',
                ],
            ]);

            if (false !== $editor_settings) {
                wp_enqueue_style('wp-codemirror');
                wp_enqueue_script('wp-codemirror');
            }

            $theme_dir = get_stylesheet_directory();
            $theme_uri = get_stylesheet_directory_uri();

            $js_path = trailingslashit($theme_dir) . 'assets/js/elementor-json-editor.js';
            $js_uri = trailingslashit($theme_uri) . 'assets/js/elementor-json-editor.js';
            if (file_exists($js_path)) {
                wp_enqueue_script(
                    'glenmark-elementor-json-editor',
                    $js_uri,
                    ['wp-codemirror', 'jquery'],
                    filemtime($js_path),
                    true
                );
            }

            $css_path = trailingslashit($theme_dir) . 'assets/css/elementor-json-editor.css';
            $css_uri = trailingslashit($theme_uri) . 'assets/css/elementor-json-editor.css';
            if (file_exists($css_path)) {
                wp_enqueue_style(
                    'glenmark-elementor-json-editor',
                    $css_uri,
                    ['wp-codemirror'],
                    filemtime($css_path)
                );
            }

            wp_localize_script(
                'glenmark-elementor-json-editor',
                'glenmarkElementorJsonEditor',
                [
                    'restRoot' => esc_url_raw(rest_url('gln-pharma-product/v1')),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'labels' => [
                        'loading' => __('Načítám…', 'gln-pharma-product'),
                        'empty' => __('Nenalezena žádná data.', 'gln-pharma-product'),
                        'save' => __('Uložit', 'gln-pharma-product'),
                    ],
                ]
            );
        }

        public static function register_rest_routes()
        {
            register_rest_route('gln-pharma-product/v1', '/elementor-documents', [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [__CLASS__, 'rest_get_documents'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]);

            register_rest_route('gln-pharma-product/v1', '/elementor-documents/(?P<id>\d+)', [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [__CLASS__, 'rest_get_document'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]);

            register_rest_route('gln-pharma-product/v1', '/elementor-documents/(?P<id>\d+)/save', [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [__CLASS__, 'rest_save_document'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]);

            register_rest_route('gln-pharma-product/v1', '/elementor-documents/(?P<id>\d+)/restore', [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [__CLASS__, 'rest_restore_document'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]);

            register_rest_route('gln-pharma-product/v1', '/elementor-documents/(?P<id>\d+)/backups', [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [__CLASS__, 'rest_get_backups'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]);
        }

        public static function render_page()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('Nemáte oprávnění pro přístup k tomuto nástroji.', 'gln-pharma-product'));
            }

            $selected_post_id = 0;
            $selected_post = null;
            $selected_post_type = '';

            if (!empty($_GET['post_id'])) {
                $selected_post_id = absint(wp_unslash($_GET['post_id']));
                $selected_post = get_post($selected_post_id);
                if ($selected_post instanceof WP_Post) {
                    $selected_post_type = $selected_post->post_type;
                }
            }
            ?>
            <div class="wrap glenmark-elementor-json-editor-wrap">
                <h1><?php echo esc_html__('Elementor JSON Editor', 'gln-pharma-product'); ?></h1>

                <div class="glenmark-editor-toolbar">
                    <div class="glenmark-post-picker">
                        <label
                            for="glenmark-elementor-post-search"><?php echo esc_html__('Vybrat stránku', 'gln-pharma-product'); ?></label>
                        <input id="glenmark-elementor-post-search" type="search"
                            placeholder="<?php echo esc_attr__('Hledat podle názvu...', 'gln-pharma-product'); ?>" autocomplete="off" />
                        <select id="glenmark-elementor-post-select" size="1">
                            <option value=""><?php echo esc_html__('Vyberte příspěvek', 'gln-pharma-product'); ?></option>
                        </select>
                    </div>

                    <div class="glenmark-document-meta">
                        <div><strong><?php echo esc_html__('Page:', 'gln-pharma-product'); ?></strong> <span
                                id="glenmark-document-title">—</span></div>
                        <div><strong><?php echo esc_html__('ID:', 'gln-pharma-product'); ?></strong> <span
                                id="glenmark-document-id">—</span></div>
                        <div><strong><?php echo esc_html__('Post type:', 'gln-pharma-product'); ?></strong> <span
                                id="glenmark-document-type">—</span></div>
                        <div><strong><?php echo esc_html__('Elementor document:', 'gln-pharma-product'); ?></strong> <span
                                id="glenmark-document-elementor">—</span></div>
                        <div><strong><?php echo esc_html__('Status:', 'gln-pharma-product'); ?></strong> <span
                                id="glenmark-document-status">—</span></div>
                    </div>
                </div>

                <div class="glenmark-editor-actions">
                    <button type="button" class="button button-primary"
                        id="glenmark-save-json"><?php echo esc_html__('Uložit', 'gln-pharma-product'); ?></button>
                    <button type="button" class="button"
                        id="glenmark-format-json"><?php echo esc_html__('Formátovat JSON', 'gln-pharma-product'); ?></button>
                    <button type="button" class="button"
                        id="glenmark-minify-json"><?php echo esc_html__('Minify', 'gln-pharma-product'); ?></button>
                    <button type="button" class="button"
                        id="glenmark-reload-json"><?php echo esc_html__('Reload', 'gln-pharma-product'); ?></button>
                    <button type="button" class="button"
                        id="glenmark-search-json"><?php echo esc_html__('Search', 'gln-pharma-product'); ?></button>
                    <button type="button" class="button"
                        id="glenmark-search-replace-json"><?php echo esc_html__('Search & Replace', 'gln-pharma-product'); ?></button>
                    <button type="button" class="button"
                        id="glenmark-restore-last-version"><?php echo esc_html__('Obnovit předchozí verzi', 'gln-pharma-product'); ?></button>
                    <a class="button" id="glenmark-open-page" target="_blank"
                        rel="noopener noreferrer"><?php echo esc_html__('Otevřít stránku', 'gln-pharma-product'); ?></a>
                    <a class="button" id="glenmark-open-elementor" target="_blank"
                        rel="noopener noreferrer"><?php echo esc_html__('Upravit v Elementoru', 'gln-pharma-product'); ?></a>
                </div>

                <div class="glenmark-editor-status" id="glenmark-editor-status" aria-live="polite">
                    <?php echo esc_html__('Vyberte stránku pro načtení Elementor JSON.', 'gln-pharma-product'); ?>
                </div>

                <div class="glenmark-editor-layout">
                    <aside class="glenmark-structure-panel">
                        <h2><?php echo esc_html__('Struktura', 'gln-pharma-product'); ?></h2>
                        <div id="glenmark-structure-tree" class="glenmark-structure-tree">
                            <p><?php echo esc_html__('Žádná data načtena.', 'gln-pharma-product'); ?></p>
                        </div>
                        <div class="glenmark-element-id-search">
                            <label
                                for="glenmark-element-id-input"><?php echo esc_html__('Element ID', 'gln-pharma-product'); ?></label>
                            <input type="text" id="glenmark-element-id-input" placeholder="např. 72ac91" />
                            <button type="button" class="button"
                                id="glenmark-find-element-id"><?php echo esc_html__('Najít', 'gln-pharma-product'); ?></button>
                        </div>
                    </aside>

                    <div class="glenmark-code-panel">
                        <textarea id="glenmark-elementor-json-editor" aria-label="Elementor JSON editor"></textarea>
                    </div>
                </div>
            </div>
            <?php
        }

        public static function rest_get_documents(WP_REST_Request $request)
        {
            $search = trim((string) $request->get_param('search'));
            $post_types = get_post_types(['public' => true, 'show_ui' => true], 'names');
            $post_types = array_values($post_types);

            $query = new WP_Query([
                'post_type' => $post_types,
                'post_status' => ['publish', 'draft', 'pending', 'private'],
                'posts_per_page' => 30,
                'orderby' => 'title',
                'order' => 'ASC',
                's' => $search,
                'post_parent' => 0,
            ]);

            $items = [];
            foreach ($query->posts as $post) {
                $items[] = self::prepare_document_payload($post);
            }

            return rest_ensure_response(['items' => $items]);
        }

        public static function rest_get_document(WP_REST_Request $request)
        {
            $post_id = absint($request->get_param('id'));
            $post = get_post($post_id);

            if (!$post || !current_user_can('manage_options')) {
                return new WP_Error('invalid_post', __('Příspěvek neexistuje nebo nemáte oprávnění.', 'gln-pharma-product'), ['status' => 404]);
            }

            $document = null;
            if (did_action('elementor/loaded') && class_exists('Elementor\\Plugin')) {
                $document = \Elementor\Plugin::$instance->documents->get($post_id);
            }

            $raw_data = get_post_meta($post_id, '_elementor_data', true);
            $decoded_data = self::decode_elementor_data($raw_data);
            $backups = self::get_backups($post_id);

            $payload = self::prepare_document_payload($post);
            $payload['data'] = $decoded_data;
            $payload['elementor_document'] = (bool) $document;
            $payload['is_elementor_built'] = $document && method_exists($document, 'is_built_with_elementor') ? (bool) $document->is_built_with_elementor() : false;
            $payload['has_elementor_data'] = !empty($decoded_data);
            $payload['backups'] = $backups;
            $payload['frontend_url'] = get_permalink($post);
            $payload['editor_url'] = admin_url('post.php?post=' . $post_id . '&action=elementor');

            return rest_ensure_response($payload);
        }

        public static function rest_get_backups(WP_REST_Request $request)
        {
            $post_id = absint($request->get_param('id'));
            $post = get_post($post_id);

            if (!$post) {
                return new WP_Error('invalid_post', __('Příspěvek neexistuje.', 'gln-pharma-product'), ['status' => 404]);
            }

            return rest_ensure_response(['backups' => self::get_backups($post_id)]);
        }

        public static function rest_save_document(WP_REST_Request $request)
        {
            $post_id = absint($request->get_param('id'));
            $json = $request->get_param('json');

            if ($post_id <= 0) {
                return new WP_Error('invalid_post_id', __('Nesprávné ID příspěvku.', 'gln-pharma-product'), ['status' => 400]);
            }

            $post = get_post($post_id);
            if (!$post) {
                return new WP_Error('invalid_post', __('Příspěvek neexistuje.', 'gln-pharma-product'), ['status' => 404]);
            }

            if (!is_string($json) || '' === trim($json)) {
                return new WP_Error('empty_json', __('JSON nesmí být prázdný.', 'gln-pharma-product'), ['status' => 400]);
            }

            $parsed = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
                $error_message = self::format_json_error_message($json);
                return new WP_Error('invalid_json', $error_message, ['status' => 400]);
            }

            $old_data = self::decode_elementor_data(get_post_meta($post_id, '_elementor_data', true));
            self::store_backup($post_id, $old_data);

            $save_result = self::save_elementor_data($post_id, $parsed);
            if (is_wp_error($save_result)) {
                return $save_result;
            }

            return rest_ensure_response([
                'success' => true,
                'message' => __('Elementor JSON byl úspěšně uložen.', 'gln-pharma-product'),
                'backups' => self::get_backups($post_id),
            ]);
        }

        public static function rest_restore_document(WP_REST_Request $request)
        {
            $post_id = absint($request->get_param('id'));
            $index = absint($request->get_param('backup_index'));

            if ($post_id <= 0) {
                return new WP_Error('invalid_post_id', __('Nesprávné ID příspěvku.', 'gln-pharma-product'), ['status' => 400]);
            }

            $post = get_post($post_id);
            if (!$post) {
                return new WP_Error('invalid_post', __('Příspěvek neexistuje.', 'gln-pharma-product'), ['status' => 404]);
            }

            $backups = self::get_backups($post_id);
            if (!isset($backups[$index]) || !is_array($backups[$index])) {
                return new WP_Error('invalid_backup', __('Záloha neexistuje.', 'gln-pharma-product'), ['status' => 404]);
            }

            $current_data = self::decode_elementor_data(get_post_meta($post_id, '_elementor_data', true));
            self::store_backup($post_id, $current_data);

            $restore_data = self::decode_elementor_data($backups[$index]['data'] ?? []);
            $save_result = self::save_elementor_data($post_id, $restore_data);
            if (is_wp_error($save_result)) {
                return $save_result;
            }

            return rest_ensure_response([
                'success' => true,
                'message' => __('Předchozí verze byla obnovena.', 'gln-pharma-product'),
                'backups' => self::get_backups($post_id),
            ]);
        }

        private static function prepare_document_payload($post)
        {
            if (!$post instanceof WP_Post) {
                return [];
            }

            $document = null;
            if (did_action('elementor/loaded') && class_exists('Elementor\\Plugin')) {
                $document = \Elementor\Plugin::$instance->documents->get((int) $post->ID);
            }

            return [
                'id' => (int) $post->ID,
                'title' => get_the_title($post),
                'post_type' => $post->post_type,
                'status' => $post->post_status,
                'status_label' => self::get_status_label($post->post_status),
                'elementor' => (bool) $document,
                'edit_url' => admin_url('post.php?post=' . (int) $post->ID . '&action=elementor'),
                'frontend_url' => get_permalink($post),
            ];
        }

        private static function get_status_label($status)
        {
            $labels = [
                'publish' => __('Publikováno', 'gln-pharma-product'),
                'draft' => __('Koncept', 'gln-pharma-product'),
                'pending' => __('Čeká na kontrolu', 'gln-pharma-product'),
                'private' => __('Soukromé', 'gln-pharma-product'),
                'future' => __('Plánováno', 'gln-pharma-product'),
                'trash' => __('Koš', 'gln-pharma-product'),
            ];

            return $labels[$status] ?? ucfirst((string) $status);
        }

        private static function decode_elementor_data($value)
        {
            if (is_array($value)) {
                return $value;
            }

            if (!is_string($value)) {
                return [];
            }

            $trimmed = trim($value);
            if ('' === $trimmed) {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return [];
        }

        private static function get_backups($post_id)
        {
            $backups = get_post_meta($post_id, self::BACKUP_META_KEY, true);

            if (!is_array($backups)) {
                return [];
            }

            return array_values($backups);
        }

        private static function store_backup($post_id, $data)
        {
            $current = get_post_meta($post_id, self::BACKUP_META_KEY, true);
            $backups = is_array($current) ? $current : [];

            $snapshot = [
                'timestamp' => current_time('timestamp'),
                'user_id' => get_current_user_id(),
                'data' => $data,
            ];

            array_unshift($backups, $snapshot);
            $backups = array_slice($backups, 0, 10);
            update_post_meta($post_id, self::BACKUP_META_KEY, $backups);

            return $backups;
        }

        private static function save_elementor_data($post_id, $data)
        {
            $new_data = self::normalize_elementor_data($data);

            if (did_action('elementor/loaded') && class_exists('Elementor\\Plugin')) {
                $document = \Elementor\Plugin::$instance->documents->get($post_id);

                if ($document && method_exists($document, 'save')) {
                    $save_response = $document->save([
                        'elements' => $new_data,
                    ]);

                    if (!is_wp_error($save_response)) {
                        self::invalidate_elementor_cache($post_id);
                        return true;
                    }

                    return $save_response;
                }

                if (method_exists(\Elementor\Plugin::$instance->documents, 'save')) {
                    $save_response = \Elementor\Plugin::$instance->documents->save($post_id, [
                        'elements' => $new_data,
                    ]);

                    if (!is_wp_error($save_response)) {
                        self::invalidate_elementor_cache($post_id);
                        return true;
                    }

                    return $save_response;
                }
            }

            $json_string = wp_json_encode($new_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (false === $json_string) {
                return new WP_Error('json_encoding_failed', __('Nepodařilo se serializovat JSON pro Elementor data.', 'gln-pharma-product'), ['status' => 500]);
            }

            $updated = update_post_meta($post_id, '_elementor_data', wp_slash($json_string));
            if (false === $updated) {
                return new WP_Error('meta_update_failed', __('Uložení Elementor JSON do post meta selhalo.', 'gln-pharma-product'), ['status' => 500]);
            }

            self::invalidate_elementor_cache($post_id);
            return true;
        }

        private static function normalize_elementor_data($data)
        {
            if (!is_array($data)) {
                return [];
            }

            return wp_unslash($data);
        }

        private static function invalidate_elementor_cache($post_id)
        {
            if (!did_action('elementor/loaded') || !class_exists('Elementor\\Plugin')) {
                return;
            }

            $plugin = \Elementor\Plugin::$instance;
            if (isset($plugin->files_manager) && method_exists($plugin->files_manager, 'clear_cache')) {
                $plugin->files_manager->clear_cache();
            }

            delete_post_meta($post_id, '_elementor_css');
            delete_post_meta($post_id, 'elementor_css');
        }

        private static function format_json_error_message($json)
        {
            $decoded = json_decode($json);
            if (null === $decoded && JSON_ERROR_NONE !== json_last_error()) {
                $message = json_last_error_msg();
                preg_match('/position\s+(\d+)/i', $message, $matches);
                $position = isset($matches[1]) ? (int) $matches[1] : 0;

                $line = 1;
                $column = 1;
                if ($position > 0) {
                    $prefix = substr($json, 0, $position);
                    $line = substr_count($prefix, "\n") + 1;
                    $last_newline = strrpos($prefix, "\n");
                    $column = false === $last_newline ? $position + 1 : ($position - $last_newline);
                }

                return sprintf(__('Neplatný JSON: %s (řádek %d, sloupec %d)', 'gln-pharma-product'), $message, $line, $column);
            }

            return __('Neplatný JSON.', 'gln-pharma-product');
        }
    }

    Glenmark_Elementor_Json_Editor::init();
}
