<?php
/**
 * Office Card
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$office = gpds_get_office_data(get_the_ID());
if (empty($office)) return;

$is_open = gpds_office_is_open_now($office['hours']);
?>

<article class="gpds-office-card-item">
    
    <a href="<?php echo esc_url($office['permalink']); ?>" class="gpds-office-card-item__image-link">
        <div class="gpds-office-card-item__image">
            <?php if ($office['thumbnail']) : ?>
                <img src="<?php echo esc_url($office['thumbnail']); ?>" 
                     alt="<?php echo esc_attr($office['title']); ?>"
                     loading="lazy"
                     width="400"
                     height="240">
            <?php else : ?>
                <div class="gpds-office-card-item__placeholder">
                    <?php gpds_icon('location', 40); ?>
                </div>
            <?php endif; ?>

            <?php if ($is_open) : ?>
                <span class="gpds-badge gpds-badge--success gpds-badge--float">
                    <span class="gpds-dot"></span> باز
                </span>
            <?php else : ?>
                <span class="gpds-badge gpds-badge--muted gpds-badge--float">
                    <span class="gpds-dot"></span> بسته
                </span>
            <?php endif; ?>
        </div>
    </a>

    <div class="gpds-office-card-item__body">
        
        <h3 class="gpds-office-card-item__title">
            <a href="<?php echo esc_url($office['permalink']); ?>">
                <?php echo esc_html($office['title']); ?>
            </a>
        </h3>

        <?php if ($office['province'] || $office['city']) : ?>
            <div class="gpds-office-card-item__location">
                <?php gpds_icon('map-pin', 14); ?>
                <span><?php echo esc_html(trim($office['province'] . '، ' . $office['city'], '، ')); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($office['address']) : ?>
            <p class="gpds-office-card-item__address">
                <?php echo esc_html(wp_trim_words($office['address'], 12, '…')); ?>
            </p>
        <?php endif; ?>

        <?php if ($office['phone']) : ?>
            <div class="gpds-office-card-item__phone">
                <?php gpds_icon('phone', 14); ?>
                <a href="tel:<?php echo esc_attr(trim(explode(',', $office['phone'])[0])); ?>" dir="ltr">
                    <?php echo esc_html(trim(explode(',', $office['phone'])[0])); ?>
                </a>
            </div>
        <?php endif; ?>

        <a href="<?php echo esc_url($office['permalink']); ?>" class="gpds-office-card-item__link">
            مشاهده جزئیات
            <?php gpds_icon('chevron-left', 14); ?>
        </a>

    </div>

</article>