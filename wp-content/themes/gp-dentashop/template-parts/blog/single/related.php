<?php
/**
 * Single Post - Related Posts
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$related = gpds_get_related_posts(get_the_ID(), 3);

if (!$related->have_posts()) {
    wp_reset_postdata();
    return;
}
?>

<section class="gpds-post__related">
    <h2 class="gpds-post__related-title">
        <?php gpds_icon('book-open', 20); ?>
        مقالات مرتبط
    </h2>

    <div class="gpds-blog-grid gpds-blog-grid--related">
        <?php while ($related->have_posts()) : $related->the_post(); ?>
            <?php get_template_part('template-parts/blog/card'); ?>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
</section>