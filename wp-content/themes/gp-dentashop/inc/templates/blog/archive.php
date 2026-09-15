<?php
/**
 * Template: Blog Archive
 * 
 * لیست مقالات برای:
 *   - صفحه اصلی وبلاگ (home)
 *   - دسته‌بندی (category)
 *   - برچسب (tag)
 *   - نویسنده (author)
 *   - تاریخ (date)
 *   - نتایج جستجو (search)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header();

$paged = max(1, get_query_var('paged'));

// عنوان صفحه
if (is_search()) {
    $page_title = 'نتایج جستجو: ' . get_search_query();
} elseif (is_category() || is_tag() || is_author() || is_date()) {
    $page_title = wp_strip_all_tags(get_the_archive_title());
} else {
    $page_title = 'آخرین مقالات';
}

// توضیح آرشیو (دسته/تگ)
$page_desc = '';
if (is_category() || is_tag()) {
    $page_desc = term_description();
}
?>

<main id="gpds-main" class="gpds-main gpds-blog-page">
    <div class="gpds-container">

        <?php gpds_blog_breadcrumb(); ?>

        <header class="gpds-blog-header">
            <h1 class="gpds-blog-header__title"><?php echo esc_html($page_title); ?></h1>
            
            <?php if ($page_desc) : ?>
                <div class="gpds-blog-header__desc"><?php echo wp_kses_post($page_desc); ?></div>
            <?php endif; ?>
        </header>

        <?php if (have_posts()) : ?>

            <div class="gpds-blog-grid gpds-blog-grid--archive">
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/blog/card'); ?>
                <?php endwhile; ?>
            </div>

            <div class="gpds-blog-pagination">
                <?php
                echo paginate_links([
                    'prev_text' => '→',
                    'next_text' => '←',
                    'type'      => 'list',
                    'mid_size'  => 2,
                ]);
                ?>
            </div>

        <?php else : ?>

            <div class="gpds-blog-empty">
                <p>موردی برای نمایش پیدا نشد.</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="gpds-btn gpds-btn--primary">
                    بازگشت به خانه
                </a>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer();