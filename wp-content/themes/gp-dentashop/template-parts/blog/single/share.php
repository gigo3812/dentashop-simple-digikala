<?php
/**
 * Single Post - Share
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$url   = get_permalink();
$title = get_the_title();
?>

<div class="gpds-post__share">
    <span class="gpds-post__share-label">اشتراک‌گذاری:</span>

    <a href="https://t.me/share/url?url=<?php echo esc_url($url); ?>&text=<?php echo esc_attr($title); ?>"
       target="_blank" rel="noopener" class="gpds-post__share-btn gpds-post__share-btn--telegram" aria-label="تلگرام">
        <?php gpds_icon('telegram', 18); ?>
    </a>

    <a href="https://wa.me/?text=<?php echo esc_attr($title . ' ' . $url); ?>"
       target="_blank" rel="noopener" class="gpds-post__share-btn gpds-post__share-btn--whatsapp" aria-label="واتساپ">
        <?php gpds_icon('whatsapp', 18); ?>
    </a>

    <button type="button"
            class="gpds-post__share-btn gpds-post__share-btn--copy"
            data-gpds-copy-url="<?php echo esc_url($url); ?>"
            aria-label="کپی لینک">
        <?php gpds_icon('tag', 18); ?>
    </button>
</div>