<?php

if (!defined('ABSPATH')) {
    exit;
}

$built_with_elementor = glenmark_is_built_with_elementor(get_the_ID());
$article_classes = $built_with_elementor ? 'entry-shell entry-shell--elementor' : 'entry-shell entry-shell--standard';
$article_classes .= ' entry-shell--article-template';
$categories = get_the_category();
$primary_category = !empty($categories) ? $categories[0] : null;
$category_main_page = $primary_category ? get_field('category_main_page', $primary_category) : '';
$is_calm_life_article = has_category(5);
$article_excerpt = trim(get_the_excerpt());
?>
<article id="post-<?php the_ID(); ?>" <?php post_class($article_classes); ?>>
    <?php if ($is_calm_life_article): ?>
        <header class="article-hero article-hero--image-layout">
            <div class="article-hero__full-media">
                <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('gln-post-banner-large', ['class' => 'article-hero__image']); ?>
                <?php endif; ?>
            </div>
            <div class="container">
                <div class="article-hero__overlay-panel">
                    <nav class="gln-breadcrumbs gln-breadcrumbs--article" aria-label="Drobečková navigace">
                        <ol class="gln-breadcrumbs__list">
                            <li class="gln-breadcrumbs__item">
                                <a class="gln-breadcrumbs__link" href="<?php echo esc_url(home_url('/')); ?>">
                                    <svg class="gln-breadcrumbs__home-icon" viewBox="0 0 24 24" aria-hidden="true"
                                        focusable="false">
                                        <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z" fill="none"
                                            stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                                    </svg><?php echo esc_html(get_bloginfo('name')); ?>
                                </a>
                            </li>
                            <?php if ($primary_category): ?>
                                <li class="gln-breadcrumbs__item">
                                    <?php if ($category_main_page): ?>
                                        <a class="gln-breadcrumbs__link" href="<?php echo esc_url($category_main_page); ?>">
                                            <?php echo esc_html($primary_category->name); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="gln-breadcrumbs__current" aria-current="page">
                                            <?php echo esc_html($primary_category->name); ?>
                                        </span>
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                    <h1 class="article-hero__title"><?php the_title(); ?></h1>
                    <?php if ($article_excerpt): ?>
                        <div class="article-hero__excerpt"><?php echo wp_kses_post(wpautop($article_excerpt)); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </header>
    <?php else: ?>
        <header class="article-hero">
            <div class="article-hero__heading">
                <div class="container">
                    <nav class="gln-breadcrumbs gln-breadcrumbs--article" aria-label="Drobečková navigace">
                        <ol class="gln-breadcrumbs__list">
                            <li class="gln-breadcrumbs__item">
                                <a class="gln-breadcrumbs__link" href="<?php echo esc_url(home_url('/')); ?>">
                                    <svg class="gln-breadcrumbs__home-icon" viewBox="0 0 24 24" aria-hidden="true"
                                        focusable="false">
                                        <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z" fill="none"
                                            stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                                    </svg><?php echo esc_html(get_bloginfo('name')); ?>
                                </a>
                            </li>
                            <?php if ($primary_category): ?>
                                <li class="gln-breadcrumbs__item">
                                    <?php if ($category_main_page): ?>
                                        <a class="gln-breadcrumbs__link" href="<?php echo esc_url($category_main_page); ?>">
                                            <?php echo esc_html($primary_category->name); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="gln-breadcrumbs__current" aria-current="page">
                                            <?php echo esc_html($primary_category->name); ?>
                                        </span>
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                    <h1 class="article-hero__title"><?php the_title(); ?></h1>
                </div>
            </div>
            <?php if (has_post_thumbnail()): ?>
                <div class="container article-hero__media">
                    <?php the_post_thumbnail('gln-post-banner', ['class' => 'article-hero__image']); ?>
                </div>
            <?php endif; ?>
        </header>
    <?php endif; ?>

    <?php if (!$built_with_elementor): ?>
        <div class="container">
            <div class="entry-content">
                <?php the_content(); ?>
                <?php wp_link_pages(); ?>
            </div>
        </div>
    <?php else: ?>
        <?php the_content(); ?>
    <?php endif; ?>

    <?php
    if ($primary_category) {
        $related_posts = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'post__not_in' => [get_the_ID()],
            'category__in' => [$primary_category->term_id],
            'orderby' => 'rand',
            'ignore_sticky_posts' => true,
        ]);
    }
    ?>
    <?php if (!empty($related_posts) && $related_posts->have_posts()): ?>
        <section class="article-related-posts" aria-labelledby="article-related-posts-title">
            <div class="container">
                <h2 id="article-related-posts-title" class="article-related-posts__title">
                    <?php echo esc_html($primary_category->name); ?>
                </h2>
                <div class="gln-post-grid article-related-posts__grid"
                    style="--gln-post-grid-columns:3;--gln-post-grid-tablet-columns:2;">
                    <?php while ($related_posts->have_posts()): ?>
                        <?php
                        $related_posts->the_post();
                        $related_post_id = get_the_ID();
                        $related_title = get_the_title($related_post_id);
                        $related_short_title = trim((string) get_field('short_title', $related_post_id));
                        $related_grid_title = $related_short_title !== '' ? $related_short_title : $related_title;
                        ?>
                        <article class="gln-post-card gln-post-card--thumb-landscape">
                            <div class="gln-post-card__media">
                                <?php if (has_post_thumbnail($related_post_id)): ?>
                                    <?php echo wp_get_attachment_image(get_post_thumbnail_id($related_post_id), 'medium_large', false, ['class' => 'gln-post-card__image']); ?>
                                <?php else: ?>
                                    <div class="gln-post-card__placeholder" aria-hidden="true"></div>
                                <?php endif; ?>
                            </div>
                            <div class="gln-post-card__content">
                                <h3 class="gln-post-card__title"><?php echo esc_html($related_grid_title); ?></h3>
                                <span class="gln-post-card__link" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path d="M5 12h13M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                            <a class="gln-post-card__stretched-link" href="<?php the_permalink(); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Read %s', 'gln-pharma-product'), $related_title)); ?>"></a>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
        <?php wp_reset_postdata(); ?>
    <?php endif; ?>
</article>