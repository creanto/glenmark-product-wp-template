<?php

if (!defined('ABSPATH')) {
    exit;
}

function webrev_render_site_header()
{
    if (function_exists('elementor_theme_do_location') && elementor_theme_do_location('header')) {
        return;
    }

    $header_layout = function_exists('webrev_sanitize_header_layout')
        ? webrev_sanitize_header_layout(webrev_get_setting('header_layout', 'default'))
        : 'default';

    $template = webrev_config("layout.header_variants.{$header_layout}.template");

    if (!$template) {
        $template = webrev_config('layout.header_variants.default.template', 'template-parts/header/default');
    }

    webrev_get_template_part($template);
}

function webrev_render_back_to_top_button()
{
    if (!(bool) webrev_get_setting('back_to_top', false)) {
        return;
    }
    ?>
    <button class="webrev-back-to-top" type="button" aria-label="<?php esc_attr_e('Back to top', 'wr-pharma-product'); ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M12 19V5M6 11l6-6 6 6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2"></path>
        </svg>
    </button>
    <?php
}
add_action('wp_footer', 'webrev_render_back_to_top_button', 20);

function webrev_render_bottom_notice()
{
    $notice_enabled = (bool) webrev_get_setting('bottom_notice_enabled', false);
    $notice_text = webrev_sanitize_rich_text(webrev_get_setting('bottom_notice_text', ''));

    if (!$notice_enabled || '' === trim(wp_strip_all_tags($notice_text))) {
        return;
    }
    ?>
    <aside class="webrev-bottom-notice" aria-label="<?php esc_attr_e('Notice', 'wr-pharma-product'); ?>">
        <div class="container webrev-bottom-notice__content">
            <?php echo wp_kses_post($notice_text); ?>
        </div>
    </aside>
    <?php
}
add_action('wp_footer', 'webrev_render_bottom_notice', 10);

function webrev_render_site_footer()
{
    $footer_page_id = (int) webrev_get_setting('footer_page', 0);

    if ($footer_page_id > 0 && 'publish' === get_post_status($footer_page_id)) {
        $footer_content = '';

        if (webrev_is_built_with_elementor($footer_page_id) && class_exists('Elementor\Plugin')) {
            $footer_content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($footer_page_id);
        }

        if ('' === trim($footer_content)) {
            $footer_post = get_post($footer_page_id);
            $footer_content = $footer_post ? apply_filters('the_content', $footer_post->post_content) : '';
        }

        if ('' !== trim($footer_content)) {
            echo '<footer class="site-footer-elementor">' . $footer_content . '</footer>';
            return;
        }
    }

    if (function_exists('elementor_theme_do_location') && elementor_theme_do_location('footer')) {
        return;
    }

    $footer_layout = function_exists('webrev_sanitize_footer_layout')
        ? webrev_sanitize_footer_layout(webrev_get_setting('footer_layout', 'default'))
        : 'default';

    $template = webrev_config("layout.footer_variants.{$footer_layout}.template");

    if (!$template) {
        $template = webrev_config('layout.footer_variants.default.template', 'template-parts/footer/default');
    }

    webrev_get_template_part($template);
}