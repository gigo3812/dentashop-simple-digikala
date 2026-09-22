<?php
/**
 * Blog Card - کارت مقاله
 * 
 * استفاده در: لیست مقالات صفحه اصلی، آرشیو، جستجو، مقالات مرتبط
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// اگه $post_id تعریف شده ازش استفاده کن، وگرنه post جاری
$post_id = $post_id ?? get_the_ID();
if (!$post_id) return;

$permalink   = get_permalink($post_id);
$title       = get_the_title($post_id);
$date        = get_the_date('', $post_id);
$thumb_id    = get_post_thumbnail_id($post_id);
$categories  = get_the_category($post_id);
$reading_min = gpds_get_reading_time(get_post_field('post_content', $post_id));

// اندازه‌ها از پارامتر قابل تنظیم
$img_size    = $img_size    ?? 'gpds-card';
$show_excerpt = $show_excerpt ?? false;
$excerpt_len  = $excerpt_len  ?? 30; // تعداد کلمه
?>

<article class="gpds-blog-card" data-post-id="<?php echo esc_attr($post_id); ?>">
    
    <!-- تصویر -->
    <a href="<?php echo esc_url($permalink); ?>" class="gpds-blog-card__image-link" aria-label="<?php echo esc_attr($title); ?>">
        <div class="gpds-blog-card__image">
            <?php if ($thumb_id) : ?>
                <?php
                echo wp_get_attachment_image($thumb_id, $img_size, false, [
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
            
            <?php if (!empty($categories)) : ?>
                <span class="gpds-blog-card__cat">
                    <?php echo esc_html($categories[0]->name); ?>
                </span>
            <?php endif; ?>
        </div>
    </a>
    
    <!-- اطلاعات -->
    <div class="gpds-blog-card__body">
        
        <h3 class="gpds-blog-card__title">
            <a href="<?php echo esc_url($permalink); ?>">
                <?php echo esc_html($title); ?>
            </a>
        </h3>
        
        <?php if ($show_excerpt) : ?>
            <p class="gpds-blog-card__excerpt">
                <?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), $excerpt_len, '…')); ?>
            </p>
        <?php endif; ?>
        
        <div class="gpds-blog-card__meta">
            <span class="gpds-blog-card__meta-item">
                <?php gpds_icon('calendar', 12); ?>
                <?php echo esc_html($date); ?>
            </span>
            <span class="gpds-blog-card__meta-item">
                <?php gpds_icon('clock', 12); ?>
                <?php echo esc_html($reading_min); ?> دقیقه
            </span>
        </div>
        
    </div>
    
</article>