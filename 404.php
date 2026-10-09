<?php

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="entry-shell entry-shell--standard">
    <div class="container">
        <header class="entry-header">
            <h1 class="entry-title"><?php esc_html_e('Stranka nebyla nalezena', 'gln-pharma-product'); ?></h1>
        </header>
        <div class="entry-content">
            <p><?php esc_html_e('Pozadovana stranka neexistuje nebo byla presunuta.', 'gln-pharma-product'); ?></p>
        </div>
    </div>
</section>
<?php
get_footer();