<?php

if (!defined('ABSPATH')) {
    exit;
}

$header_sticky = (bool) webrev_get_header_sticky();
$header_shrink = (bool) webrev_get_header_shrink();
$header_hide_on_scroll = (bool) webrev_get_header_hide_on_scroll();
$header_mode = is_singular() ? webrev_get_header_mode() : 'normal';
$header_transparent = 'transparent' === $header_mode;
$header_glassy = 'glassy' === $header_mode;
$header_glassy_dark = 'glassy-dark' === $header_mode;
$header_overlay_mode = $header_transparent || $header_glassy || $header_glassy_dark;
$header_sticky_effective = $header_sticky || $header_overlay_mode;
$header_can_shrink = $header_sticky_effective && $header_shrink;
$header_background_color = webrev_sanitize_header_background_color(webrev_get_setting('header_background_color', ''));
$header_background_image_id = (int) webrev_get_setting('header_background_image', 0);
$header_background_image_url = $header_background_image_id > 0 ? wp_get_attachment_image_url($header_background_image_id, 'full') : '';
$header_background_size = webrev_sanitize_header_background_size(webrev_get_setting('header_background_size', 'cover'));
$header_background_position = webrev_sanitize_header_background_position(webrev_get_setting('header_background_position', 'center center'));
$header_background_repeat = webrev_sanitize_header_background_repeat(webrev_get_setting('header_background_repeat', 'no-repeat'));
$header_logo_max_width = webrev_sanitize_header_logo_max_width(webrev_get_setting('header_logo_max_width', 260));
$header_navigation_link_color = webrev_sanitize_header_background_color(webrev_get_setting('header_navigation_link_color', ''));
$header_navigation_separators = (bool) webrev_get_setting('header_navigation_separators', false);
$header_style_parts = [];

if ('' !== $header_background_color) {
    $header_style_parts[] = '--wr-header-custom-bg-color: ' . $header_background_color;
}

if ('' !== $header_background_image_url) {
    $header_style_parts[] = '--wr-header-custom-bg-image: url(' . esc_url($header_background_image_url) . ')';
    $header_style_parts[] = '--wr-header-custom-bg-size: ' . $header_background_size;
    $header_style_parts[] = '--wr-header-custom-bg-position: ' . $header_background_position;
    $header_style_parts[] = '--wr-header-custom-bg-repeat: ' . $header_background_repeat;
}

$mobile_nav_background_color = '' !== $header_background_color ? $header_background_color : 'rgba(255, 255, 255, 0.94)';
$mobile_nav_background_image = '' !== $header_background_image_url ? 'url(' . esc_url($header_background_image_url) . ')' : 'none';
$header_style_parts[] = '--wr-mobile-nav-bg-color: ' . $mobile_nav_background_color;
$header_style_parts[] = '--wr-mobile-nav-bg-image: ' . $mobile_nav_background_image;
$header_style_parts[] = '--wr-mobile-nav-bg-size: ' . ('' !== $header_background_image_url ? $header_background_size : 'cover');
$header_style_parts[] = '--wr-mobile-nav-bg-position: ' . ('' !== $header_background_image_url ? $header_background_position : 'center center');
$header_style_parts[] = '--wr-mobile-nav-bg-repeat: ' . ('' !== $header_background_image_url ? $header_background_repeat : 'no-repeat');
$header_style_parts[] = '--wr-header-logo-max-width: ' . $header_logo_max_width . 'px';

if ('' !== $header_navigation_link_color) {
    $header_style_parts[] = '--wr-header-navigation-link-color: ' . $header_navigation_link_color;
}

$header_style_attr = implode('; ', $header_style_parts);

$default_logo_id = webrev_get_logo_default_id();
$transparent_logo_id = webrev_get_logo_transparent_id();
$has_transparent_logo = $transparent_logo_id > 0;
$social_links = [
    'facebook' => [
        'url' => trim((string) webrev_get_setting('social_facebook', '')),
        'label' => __('Facebook', 'wr-pharma-product'),
    ],
    'instagram' => [
        'url' => trim((string) webrev_get_setting('social_instagram', '')),
        'label' => __('Instagram', 'wr-pharma-product'),
    ],
    'youtube' => [
        'url' => trim((string) webrev_get_setting('social_youtube', '')),
        'label' => __('YouTube', 'wr-pharma-product'),
    ],
];
$has_social_links = false; // remporaly switched of --!empty($social_links['facebook']['url']) || !empty($social_links['instagram']['url']) || !empty($social_links['youtube']['url']);

$header_classes = ['site-header'];

if ($header_sticky) {
    $header_classes[] = 'is-sticky';
}

if ($header_can_shrink) {
    $header_classes[] = 'can-shrink';
}

if ($header_navigation_separators) {
    $header_classes[] = 'has-navigation-separators';
}

if ($header_hide_on_scroll) {
    $header_classes[] = 'is-hide-on-scroll';
}

if ($header_transparent) {
    $header_classes[] = 'is-transparent-start';
}

if ($header_glassy) {
    $header_classes[] = 'is-glassy-start';
}

if ($header_glassy_dark) {
    $header_classes[] = 'is-glassy-dark-start';
}

if ($header_transparent && $has_transparent_logo) {
    $header_classes[] = 'has-transparent-logo';
}

if ($header_overlay_mode && (bool) webrev_get_header_logo_show_on_scroll()) {
    $header_classes[] = 'has-logo-show-on-scroll';
}
?>
<header class="<?php echo esc_attr(implode(' ', $header_classes)); ?>"
    style="<?php echo esc_attr($header_style_attr); ?>"
    data-sticky="<?php echo $header_sticky_effective ? '1' : '0'; ?>"
    data-hide-on-scroll="<?php echo $header_hide_on_scroll ? '1' : '0'; ?>"
    data-shrink="<?php echo $header_can_shrink ? '1' : '0'; ?>"
    data-transparent="<?php echo $header_overlay_mode ? '1' : '0'; ?>"
    data-scroll-watch="<?php echo ($header_can_shrink || $header_overlay_mode || $header_hide_on_scroll) ? '1' : '0'; ?>">
    <div class="container site-header-inner">
        <div class="site-branding">
            <?php if ($default_logo_id > 0): ?>
                <a class="custom-logo-link" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                    <?php echo webrev_get_logo_image_html($default_logo_id, 'site-logo-default'); ?>
                    <?php if ($has_transparent_logo): ?>
                        <?php echo webrev_get_logo_image_html($transparent_logo_id, 'site-logo-transparent'); ?>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a class="site-title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
            <?php endif; ?>
        </div>

        <a class="header-menu-toggle" aria-expanded="false" aria-controls="site-navigation"
            aria-label="<?php esc_attr_e('Menu', 'wr-pharma-product'); ?>">
            <span class="header-menu-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
        </a>

        <nav id="site-navigation" class="site-navigation"
            aria-label="<?php esc_attr_e('Primary navigation', 'wr-pharma-product'); ?>">
            <a class="site-navigation-close" aria-label="<?php esc_attr_e('Close menu', 'wr-pharma-product'); ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round"></path>
                </svg>
            </a>
            <?php webrev_render_primary_nav_menu(); ?>

        </nav>

        <?php if ($has_social_links): ?>
            <div class="site-header-social site-header-social--desktop"
                aria-label="<?php esc_attr_e('Social links', 'wr-pharma-product'); ?>">
                <?php foreach ($social_links as $network => $data): ?>
                    <?php if (empty($data['url'])) {
                        continue;
                    } ?>
                    <a class="site-header-social__link site-header-social__link--<?php echo esc_attr($network); ?>"
                        href="<?php echo esc_url($data['url']); ?>" target="_blank" rel="noopener noreferrer"
                        aria-label="<?php echo esc_attr($data['label']); ?>">
                        <?php if ('facebook' === $network): ?>
                            <svg class="site-header-social__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path
                                    d="M13.5 8.5V6.7c0-.8.6-1.2 1.3-1.2H16V3h-2.4C11.6 3 10.5 4.3 10.5 6.2v2.3H9V11h1.5v10h3V11H16l.4-2.5h-2.9z"
                                    fill="currentColor" />
                            </svg>
                        <?php elseif ('instagram' === $network): ?>
                            <svg class="site-header-social__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path
                                    d="M12 8.1A3.9 3.9 0 1 0 12 16a3.9 3.9 0 0 0 0-7.9zm0 6.4A2.5 2.5 0 1 1 12 9.5a2.5 2.5 0 0 1 0 5zm5-6.5a.9.9 0 1 0 0-1.9.9.9 0 0 0 0 1.9z"
                                    fill="currentColor" />
                                <path
                                    d="M12 2.8c3 0 3.3 0 4.4.1 1 .1 1.6.2 2 .4.5.2.9.4 1.3.8.4.4.7.8.8 1.3.2.5.3 1 .4 2 .1 1.1.1 1.4.1 4.4s0 3.3-.1 4.4c-.1 1-.2 1.6-.4 2-.2.5-.4.9-.8 1.3-.4.4-.8.7-1.3.8-.5.2-1 .3-2 .4-1.1.1-1.4.1-4.4.1s-3.3 0-4.4-.1c-1-.1-1.6-.2-2-.4a3.3 3.3 0 0 1-1.3-.8 3.3 3.3 0 0 1-.8-1.3c-.2-.5-.3-1-.4-2C2.8 15.3 2.8 15 2.8 12s0-3.3.1-4.4c.1-1 .2-1.6.4-2 .2-.5.4-.9.8-1.3.4-.4.8-.7 1.3-.8.5-.2 1-.3 2-.4C8.7 2.8 9 2.8 12 2.8zm0-1.5c-3 0-3.4 0-4.5.1-1.1.1-1.9.2-2.6.5-.7.3-1.3.7-1.9 1.2A5 5 0 0 0 1.8 5c-.3.7-.4 1.5-.5 2.6C1.3 8.7 1.3 9 1.3 12s0 3.4.1 4.5c.1 1.1.2 1.9.5 2.6.3.7.7 1.3 1.2 1.9.6.6 1.2 1 1.9 1.2.7.3 1.5.4 2.6.5 1.1.1 1.5.1 4.5.1s3.4 0 4.5-.1c1.1-.1 1.9-.2 2.6-.5.7-.3 1.3-.7 1.9-1.2.6-.6 1-1.2 1.2-1.9.3-.7.4-1.5.5-2.6.1-1.1.1-1.5.1-4.5s0-3.4-.1-4.5c-.1-1.1-.2-1.9-.5-2.6-.3-.7-.7-1.3-1.2-1.9-.6-.6-1.2-1-1.9-1.2-.7-.3-1.5-.4-2.6-.5C15.4 1.3 15 1.3 12 1.3z"
                                    fill="currentColor" />
                            </svg>
                        <?php elseif ('youtube' === $network): ?>
                            <svg class="site-header-social__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path
                                    d="M21.6 7.2a3 3 0 0 0-2.1-2.1C17.9 4.7 12 4.7 12 4.7s-5.9 0-7.5.4A3 3 0 0 0 2.4 7.2 31.8 31.8 0 0 0 2 12a31.8 31.8 0 0 0 .4 4.8 3 3 0 0 0 2.1 2.1c1.6.4 7.5.4 7.5.4s5.9 0 7.5-.4a3 3 0 0 0 2.1-2.1A31.8 31.8 0 0 0 22 12a31.8 31.8 0 0 0-.4-4.8zM10 15.5v-7l6 3.5-6 3.5z"
                                    fill="currentColor" />
                            </svg>
                        <?php endif; ?>
                        <span class="screen-reader-text"><?php echo esc_html($data['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</header>