<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
</main>
<?php
if (!function_exists('glenmark_render_site_footer')) {
    $glenmark_bootstrap = __DIR__ . '/inc/bootstrap.php';

    if (file_exists($glenmark_bootstrap)) {
        require_once $glenmark_bootstrap;
    }
}

if (function_exists('glenmark_render_site_footer')) {
    glenmark_render_site_footer();
} else {
    get_template_part('template-parts/footer/default');
}
?>
</div>
<?php wp_footer(); ?>
</body>

</html>