<?php
/**
 * Single Post - Prev/Next Navigation
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$prev = get_previous_post();
$next = get_next_post();

if (!$prev && !$next) return;
?>

<nav class="gpds-post__nav" aria-label="ناوبری مقالات">
    <?php if ($prev) : ?>
        <a href="<?php echo esc_url(get_permalink($prev)); ?>" class="gpds-post__nav-item gpds-post__nav-item--prev">
            <span class="gpds-post__nav-label"><?php gpds_icon('chevron-right', 14); ?> قبلی</span>
            <span class="gpds-post__nav-title"><?php echo esc_html(get_the_title($prev)); ?></span>
        </a>
    <?php endif; ?>

    <?php if ($next) : ?>
        <a href="<?php echo esc_url(get_permalink($next)); ?>" class="gpds-post__nav-item gpds-post__nav-item--next">
            <span class="gpds-post__nav-label">بعدی <?php gpds_icon('chevron-left', 14); ?></span>
            <span class="gpds-post__nav-title"><?php echo esc_html(get_the_title($next)); ?></span>
        </a>
    <?php endif; ?>
</nav>