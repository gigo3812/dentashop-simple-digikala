<?php
/**
 * Home - Banner Section (نمونه)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$banners = gpds_get_banners('home-top', 3);
if (empty($banners)) return;
?>

<section class="gpds-section gpds-banners-section" aria-label="بنرهای تبلیغاتی">
    <div class="gpds-container">
        <?php foreach ($banners as $banner) : ?>
            <a 
                href="<?php echo esc_url($banner['url'] ?: '#'); ?>"
                class="gpds-banner"
                <?php if ($banner['url']) : ?>target="_blank" rel="noopener"<?php endif; ?>
                aria-label="<?php echo esc_attr($banner['title']); ?>"
            >
                <picture>
                    <?php if ($banner['image_xs']) : ?>
                        <source media="(max-width: 640px)" srcset="<?php echo esc_url($banner['image_xs']); ?>">
                    <?php endif; ?>
                    <?php if ($banner['image_sm']) : ?>
                        <source media="(max-width: 1024px)" srcset="<?php echo esc_url($banner['image_sm']); ?>">
                    <?php endif; ?>
                    <img 
                        src="<?php echo esc_url($banner['image']); ?>"
                        alt="<?php echo esc_attr($banner['title']); ?>"
                        loading="lazy"
                        decoding="async"
                        width="1920"
                        height="500"
                    >
                </picture>
            </a>
        <?php endforeach; ?>
    </div>
</section>