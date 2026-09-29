<?php

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="wr-page-content">
    <?php if (have_posts()): ?>
        <?php while (have_posts()):
            the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('wr-content-entry'); ?>>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php
get_footer();
