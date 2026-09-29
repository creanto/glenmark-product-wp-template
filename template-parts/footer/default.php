<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div><?php dynamic_sidebar('footer_col_1'); ?></div>
            <div><?php dynamic_sidebar('footer_col_2'); ?></div>
            <div><?php dynamic_sidebar('footer_col_3'); ?></div>
        </div>

        <nav aria-label="<?php esc_attr_e('Footer navigation', 'wr-pharma-product'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'footer',
                'container' => false,
                'menu_class' => 'nav-menu',
                'fallback_cb' => '__return_empty_string',
            ]);
            ?>
        </nav>

        <p class="site-info">&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php bloginfo('name'); ?></p>
    </div>
</footer>