<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * How third-party video embeds are handled while CookieYes auto-blocking is on.
 *
 * 'consent'   – render a placeholder with a button that opens the CookieYes banner,
 *               the iframe is injected only after the visitor consents.
 * 'necessary' – render the iframe directly, flagged as necessary so CookieYes
 *               never blocks it (legally questionable, use knowingly).
 */
function webrev_cookieyes_video_mode()
{
    return (string) apply_filters('wr-pharma-product/cookieyes/video_mode', 'consent');
}

/**
 * CookieYes consent category the video is waiting for ('consent' mode), or the
 * `data-cookieyes` value used to bypass blocking ('necessary' mode).
 */
function webrev_cookieyes_video_category()
{
    $default = 'necessary' === webrev_cookieyes_video_mode() ? 'cookieyes-necessary' : 'advertisement';

    return (string) apply_filters('wr-pharma-product/cookieyes/video_category', $default);
}

function webrev_cookieyes_mark_iframes($html)
{
    if (false === stripos($html, '<iframe')) {
        return $html;
    }

    return preg_replace(
        '/<iframe\b(?![^>]*\bdata-cookieyes=)/i',
        '<iframe data-cookieyes="' . esc_attr(webrev_cookieyes_video_category()) . '"',
        $html
    );
}

/**
 * Embed URL of an Elementor Video widget, or an empty string for hosted videos.
 */
function webrev_cookieyes_get_video_embed_url($settings)
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

function webrev_cookieyes_render_video_placeholder($embed_url)
{
    $category = webrev_cookieyes_video_category();

    $text = apply_filters(
        'wr-pharma-product/cookieyes/video_notice',
        __('Přehrání videa vyžaduje souhlas s marketingovými cookies služby YouTube.', 'wr-pharma-product')
    );

    $button = apply_filters(
        'wr-pharma-product/cookieyes/video_button',
        __('Povolit cookies a přehrát', 'wr-pharma-product')
    );

    ob_start();
    ?>
    <div class="wr-video-consent" data-src="<?php echo esc_url($embed_url); ?>"
        data-category="<?php echo esc_attr($category); ?>" data-title="<?php esc_attr_e('Video', 'wr-pharma-product'); ?>">
        <div class="wr-video-consent__inner">
            <p class="wr-video-consent__text"><?php echo esc_html($text); ?></p>
            <button type="button" class="wr-video-consent__button"><?php echo esc_html($button); ?></button>
        </div>
    </div>
    <?php

    return trim((string) ob_get_clean());
}

/**
 * Elementor renders YouTube/Vimeo as an empty `.elementor-video` div and builds the
 * iframe client-side through the provider's JS API, which CookieYes blocks — so no
 * iframe ever reaches the DOM. Replace that div with our own markup.
 */
function webrev_cookieyes_replace_video_api_player($content, $widget)
{
    if (false === strpos($content, 'class="elementor-video"')) {
        return $content;
    }

    $settings = $widget->get_settings_for_display();
    $embed_url = webrev_cookieyes_get_video_embed_url($settings);

    if ('' === $embed_url) {
        return $content;
    }

    if ('necessary' === webrev_cookieyes_video_mode()) {
        $replacement = '<iframe class="elementor-video-iframe" allowfullscreen src="' . esc_url($embed_url) . '"></iframe>';
    } else {
        $replacement = webrev_cookieyes_render_video_placeholder($embed_url);
    }

    return str_replace('<div class="elementor-video"></div>', $replacement, $content);
}

function webrev_cookieyes_enqueue_video_consent_assets()
{
    if ('consent' !== webrev_cookieyes_video_mode()) {
        return;
    }

    $css_path = webrev_get_theme_file_path('assets/css/video-consent.css');
    $css_uri = webrev_get_theme_file_uri('assets/css/video-consent.css');
    $js_path = webrev_get_theme_file_path('assets/js/video-consent.js');
    $js_uri = webrev_get_theme_file_uri('assets/js/video-consent.js');

    if (!empty($css_uri)) {
        wp_enqueue_style('webrev-video-consent', $css_uri, [], $css_path ? (string) filemtime($css_path) : null);
    }

    if (!empty($js_uri)) {
        wp_enqueue_script('webrev-video-consent', $js_uri, [], $js_path ? (string) filemtime($js_path) : null, true);
    }
}
add_action('wp_enqueue_scripts', 'webrev_cookieyes_enqueue_video_consent_assets');

function webrev_cookieyes_allow_elementor_video($content, $widget)
{
    if (!$widget instanceof \Elementor\Widget_Base || 'video' !== $widget->get_name()) {
        return $content;
    }

    $content = webrev_cookieyes_replace_video_api_player($content, $widget);

    if ('necessary' === webrev_cookieyes_video_mode()) {
        $content = webrev_cookieyes_mark_iframes($content);
    }

    return $content;
}
add_filter('elementor/widget/render_content', 'webrev_cookieyes_allow_elementor_video', 10, 2);
