<?php
/**
 * Home - Blog Posts
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$posts = new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'no_found_rows'  => true,
    'ignore_sticky_posts' => true,
]);

if (!$posts->have_posts()) {
    wp_reset_postdata();
    return;
}
?>

<section class="gpds-section" aria-label="خواندنی‌ها">
    <div class="gpds-container">
        <div class="gpds-card">
            
            <div class="gpds-section-header">
                <h2 class="gpds-section-title">
                    <?php gpds_icon('book-open', 24); ?>
                    خواندنی‌ها
                </h2>
                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="gpds-section-more">
                    نوشته‌های بیشتر
                    <?php gpds_icon('chevron-left', 14); ?>
                </a>
            </div>
            
            <div class="gpds-blog-grid">
                <?php while ($posts->have_posts()) : $posts->the_post(); ?>
                    <article class="gpds-blog-card">
                        <a href="<?php the_permalink(); ?>" class="gpds-blog-card__image-link">
                            <div class="gpds-blog-card__image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('gpds-card', [
                                        'loading' => 'lazy',
                                        'class'   => 'gpds-blog-card__img',
                                    ]); ?>
                                <?php else : ?>
                                    <div class="gpds-blog-card__placeholder">
                                        <?php gpds_icon('image', 40); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="gpds-blog-card__body">
                            <a href="<?php the_permalink(); ?>" class="gpds-blog-card__title">
                                <?php the_title(); ?>
                            </a>
                            <div class="gpds-blog-card__meta">
                                <?php gpds_icon('calendar', 12); ?>
                                <span><?php echo esc_html(get_the_date()); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            
        </div>
    </div>
</section>