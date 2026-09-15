<?php
/**
 * Blog Card Meta
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-blog-card__meta">
    <span class="gpds-blog-card__meta-item">
        <?php gpds_icon('calendar', 12); ?>
        <?php echo esc_html(get_the_date()); ?>
    </span>
    <span class="gpds-blog-card__meta-item">
        <?php gpds_icon('clock', 12); ?>
        <?php echo esc_html(gpds_get_reading_time(get_the_content())); ?> دقیقه
    </span>
</div>