<?php
/**
 * Single Post - Header
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$cats = get_the_category();
?>

<header class="gpds-post__header">

    <?php if (has_post_thumbnail()) : ?>
        <div class="gpds-post__hero-image">
            <?php the_post_thumbnail('full', [
                'loading'       => 'eager',
                'fetchpriority' => 'high',
                'class'         => 'gpds-post__hero-img',
                'alt'           => get_the_title(),
            ]); ?>
        </div>
    <?php endif; ?>

    <div class="gpds-post__header-content">

        <?php if (!empty($cats)) : ?>
            <div class="gpds-post__cats">
                <?php foreach ($cats as $cat) : ?>
                    <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="gpds-post__cat">
                        <?php echo esc_html($cat->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h1 class="gpds-post__title" itemprop="headline"><?php the_title(); ?></h1>

        <div class="gpds-post__meta">
            <span class="gpds-post__meta-item">
                <?php gpds_icon('user', 14); ?>
                <span itemprop="author"><?php the_author(); ?></span>
            </span>
            <span class="gpds-post__meta-item">
                <?php gpds_icon('calendar', 14); ?>
                <time itemprop="datePublished" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                    <?php echo esc_html(get_the_date()); ?>
                </time>
            </span>
            <span class="gpds-post__meta-item">
                <?php gpds_icon('clock', 14); ?>
                <?php echo esc_html(gpds_get_reading_time(get_the_content())); ?> دقیقه مطالعه
            </span>
            <span class="gpds-post__meta-item">
                <?php gpds_icon('eye', 14); ?>
                <?php echo esc_html(number_format_i18n(gpds_get_post_views(get_the_ID()))); ?> بازدید
            </span>
        </div>

    </div>

</header>