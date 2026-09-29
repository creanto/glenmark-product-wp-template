<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
</main>
<?php
if (!function_exists('webrev_render_site_footer')) {
    $webrev_bootstrap = __DIR__ . '/inc/bootstrap.php';

    if (file_exists($webrev_bootstrap)) {
        require_once $webrev_bootstrap;
    }
}

if (function_exists('webrev_render_site_footer')) {
    webrev_render_site_footer();
} else {
    get_template_part('template-parts/footer/default');
}
?>
</div>
<?php wp_footer(); ?>
</body>

</html>