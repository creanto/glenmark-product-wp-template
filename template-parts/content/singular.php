<?php

if (!defined('ABSPATH')) {
    exit;
}

$built_with_elementor = glenmark_is_built_with_elementor(get_the_ID());
$article_classes = $built_with_elementor ? 'entry-shell entry-shell--elementor' : 'entry-shell entry-shell--standard';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class($article_classes); ?>>
    <?php if (!$built_with_elementor): ?>
        <div class="container">
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>
            <div class="entry-content">
                <?php the_content(); ?>
                <?php wp_link_pages(); ?>
            </div>
        </div>
    <?php else: ?>
        <?php the_content(); ?>
    <?php endif; ?>
</article>