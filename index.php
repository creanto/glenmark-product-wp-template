<?php

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="section">
    <div class="container">
        <header class="archive-header">
            <h1 class="entry-title"><?php single_post_title(); ?></h1>
        </header>

        <?php if (have_posts()): ?>
            <div class="posts-grid">
                <?php while (have_posts()):
                    the_post(); ?>
                    <article <?php post_class('campaign-card'); ?>>
                        <?php if (has_post_thumbnail()): ?>
                            <a class="campaign-card-media" href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('webrev-card'); ?>
                            </a>
                        <?php endif; ?>
                        <div class="campaign-card-content">
                            <h2 class="campaign-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <div><?php the_excerpt(); ?></div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php the_posts_pagination(); ?>
        <?php else: ?>
            <p><?php esc_html_e('Obsah zatim neni k dispozici.', 'wr-pharma-product'); ?></p>
        <?php endif; ?>
    </div>
</section>
<?php
get_footer();