<?php
/**
 * Blog Card
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$post_id    = get_the_ID();
$title      = get_the_title();
$permalink  = get_permalink();
$thumb_id   = get_post_thumbnail_id();
$cats       = get_the_category();
$excerpt    = get_the_excerpt();
$excerpt_len = 30; // تعداد کلمه
?>

<article class="gpds-blog-card" data-post-id="<?php echo esc_attr($post_id); ?>">

    <a href="<?php echo esc_url($permalink); ?>" class="gpds-blog-card__image-link" aria-label="<?php echo esc_attr($title); ?>">
        <div class="gpds-blog-card__image">
            <?php if ($thumb_id) : ?>
                <?php
                echo wp_get_attachment_image($thumb_id, 'medium_large', false, [
                    'class'   => 'gpds-blog-card__img',
                    'loading' => 'lazy',
                    'alt'     => $title,
                ]);
                ?>
            <?php else : ?>
                <div class="gpds-blog-card__placeholder">
                    <?php gpds_icon('image', 40); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($cats)) : ?>
                <span class="gpds-blog-card__cat"><?php echo esc_html($cats[0]->name); ?></span>
            <?php endif; ?>
        </div>
    </a>

    <div class="gpds-blog-card__body">
        <h3 class="gpds-blog-card__title">
            <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
        </h3>

        <?php if ($excerpt) : ?>
            <p class="gpds-blog-card__excerpt">
                <?php echo esc_html(wp_trim_words($excerpt, $excerpt_len, '…')); ?>
            </p>
        <?php endif; ?>
    </div>

</article>