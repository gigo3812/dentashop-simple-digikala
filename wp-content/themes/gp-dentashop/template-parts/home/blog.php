<?php
/**
 * Home - Latest Blog Posts
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$query = gpds_get_blog_posts([
    'limit' => 4,
]);

if (!$query->have_posts()) {
    wp_reset_postdata();
    return;
}

$blog_url = get_option('page_for_posts')
    ? get_permalink(get_option('page_for_posts'))
    : home_url('/');
?>

<section class="gpds-section" aria-label="خواندنی‌ها">
    <div class="gpds-container">
        <div class="gpds-card">

            <div class="gpds-section-header">
                <h2 class="gpds-section-title">
                    <?php gpds_icon('book-open', 24); ?>
                    خواندنی‌ها
                </h2>
                <a href="<?php echo esc_url($blog_url); ?>" class="gpds-section-more">
                    نوشته‌های بیشتر
                    <?php gpds_icon('chevron-left', 14); ?>
                </a>
            </div>

            <div class="gpds-blog-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <?php get_template_part('template-parts/blog/card'); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

        </div>
    </div>
</section>