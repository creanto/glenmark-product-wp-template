<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Whether Elementor videos wait for WPConsent marketing consent.
 */
function glenmark_wpconsent_video_mode()
{
    $default_mode = get_theme_mod('glenmark_video_consent_bypass', false) ? 'necessary' : 'consent';

    return (string) apply_filters('gln-pharma-product/wpconsent/video_mode', $default_mode);
}

function glenmark_wpconsent_clear_elementor_cache_after_theme_mod_update($option, $old_value, $new_value = null)
{
    if ('theme_mods_' . get_stylesheet() !== $option) {
        return;
    }

    if (null === $new_value) {
        $new_value = $old_value;
        $old_value = [];
    }

    $old_bypass = isset($old_value['glenmark_video_consent_bypass']) ? $old_value['glenmark_video_consent_bypass'] : false;
    $new_bypass = isset($new_value['glenmark_video_consent_bypass']) ? $new_value['glenmark_video_consent_bypass'] : false;

    if ($old_bypass === $new_bypass || !class_exists('\\Elementor\\Plugin')) {
        return;
    }

    $elementor = \Elementor\Plugin::$instance;

    if (isset($elementor->files_manager) && method_exists($elementor->files_manager, 'clear_cache')) {
        $elementor->files_manager->clear_cache();
    }
}
add_action('updated_option', 'glenmark_wpconsent_clear_elementor_cache_after_theme_mod_update', 10, 3);
add_action('added_option', 'glenmark_wpconsent_clear_elementor_cache_after_theme_mod_update', 10, 2);

function glenmark_wpconsent_get_video_embed_url($settings)
{
    if (!class_exists('\Elementor\Embed')) {
        return '';
    }

    $url_keys = [
        'youtube' => 'youtube_url',
        'vimeo' => 'vimeo_url',
    ];

    $video_type = isset($settings['video_type']) ? $settings['video_type'] : '';

    if (!isset($url_keys[$video_type]) || empty($settings[$url_keys[$video_type]])) {
        return '';
    }

    $params = [];

    if ('youtube' === $video_type) {
        $params['controls'] = isset($settings['controls']) && 'yes' === $settings['controls'] ? 1 : 0;
        $params['rel'] = isset($settings['rel']) && 'yes' === $settings['rel'] ? 1 : 0;

        if (!empty($settings['start'])) {
            $params['start'] = (int) $settings['start'];
        }

        if (!empty($settings['end'])) {
            $params['end'] = (int) $settings['end'];
        }

        if (isset($settings['mute']) && 'yes' === $settings['mute']) {
            $params['mute'] = 1;
        }
    }

    $embed_url = \Elementor\Embed::get_embed_url(
        $settings[$url_keys[$video_type]],
        $params,
        ['privacy' => isset($settings['yt_privacy']) && 'yes' === $settings['yt_privacy']]
    );

    return $embed_url ? $embed_url : '';
}

function glenmark_wpconsent_render_video_placeholder($embed_url)
{
    $text = apply_filters(
        'gln-pharma-product/wpconsent/video_notice',
        __('Přehrání videa vyžaduje souhlas s marketingovými cookies.', 'gln-pharma-product')
    );

    $button = apply_filters(
        'gln-pharma-product/wpconsent/video_button',
        __('Povolit cookies a přehrát', 'gln-pharma-product')
    );

    ob_start();
    ?>
    <div class="gln-video-consent" data-src="<?php echo esc_url($embed_url); ?>"
        data-title="<?php esc_attr_e('Video', 'gln-pharma-product'); ?>">
        <div class="gln-video-consent__inner">
            <p class="gln-video-consent__text"><?php echo esc_html($text); ?></p>
            <button type="button" class="gln-video-consent__button"><?php echo esc_html($button); ?></button>
        </div>
    </div>
    <?php

    return trim((string) ob_get_clean());
}

function glenmark_wpconsent_replace_video_api_player($content, $widget)
{
    if (false === strpos($content, 'class="elementor-video"')) {
        return $content;
    }

    $embed_url = glenmark_wpconsent_get_video_embed_url($widget->get_settings_for_display());

    if ('' === $embed_url) {
        return $content;
    }

    if ('necessary' === glenmark_wpconsent_video_mode()) {
        $replacement = '<iframe class="elementor-video-iframe" allowfullscreen src="' . esc_url($embed_url) . '"></iframe>';
    } else {
        $replacement = glenmark_wpconsent_render_video_placeholder($embed_url);
    }

    return str_replace('<div class="elementor-video"></div>', $replacement, $content);
}

function glenmark_wpconsent_enqueue_video_consent_assets()
{
    if ('consent' !== glenmark_wpconsent_video_mode()) {
        return;
    }

    $css_path = glenmark_get_theme_file_path('assets/css/video-consent.css');
    $css_uri = glenmark_get_theme_file_uri('assets/css/video-consent.css');
    $js_path = glenmark_get_theme_file_path('assets/js/video-consent.js');
    $js_uri = glenmark_get_theme_file_uri('assets/js/video-consent.js');

    if (!empty($css_uri)) {
        wp_enqueue_style('glenmark-video-consent', $css_uri, [], $css_path ? (string) filemtime($css_path) : null);
    }

    if (!empty($js_uri)) {
        wp_enqueue_script('glenmark-video-consent', $js_uri, [], $js_path ? (string) filemtime($js_path) : null, true);
    }
}
add_action('wp_enqueue_scripts', 'glenmark_wpconsent_enqueue_video_consent_assets');

function glenmark_wpconsent_allow_elementor_video($content, $widget)
{
    if (!$widget instanceof \Elementor\Widget_Base || 'video' !== $widget->get_name()) {
        return $content;
    }

    return glenmark_wpconsent_replace_video_api_player($content, $widget);
}
add_filter('elementor/widget/render_content', 'glenmark_wpconsent_allow_elementor_video', 10, 2);