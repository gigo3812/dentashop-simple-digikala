<?php
/**
 * Home - Brands Section
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$brands = gpds_get_active_brands(12,true);
if (empty($brands)) {
    return;
}
?>

<section class="gpds-section gpds-brands-section" aria-label="برندها">
    <div class="gpds-container">
        <div class="gpds-card gpds-brands">
            
            <!-- هدر -->
            <div class="gpds-section-header">
                <h2 class="gpds-section-title">
                    <?php gpds_icon('award', 24); ?>
                    محبوب‌ترین برندها
                </h2>
                <a href="<?php echo esc_url(gpds_get_all_brands_url()); ?>" class="gpds-mega-menu__all">
                    مشاهده همه <?php gpds_icon('arrow-left', 14); ?>
                </a>
            </div>
            
            <!-- کاروسل برندها -->
            <div class="swiper gpds-brands-carousel">
                <div class="swiper-wrapper">
                    <?php foreach ($brands as $brand) : ?>
                        <div class="swiper-slide">
                            <a 
                                href="<?php echo esc_url($brand['url']); ?>"
                                class="gpds-brand"
                                title="<?php echo esc_attr($brand['name']); ?>"
                                aria-label="<?php echo esc_attr($brand['name']); ?>"
                            >
                                <span class="gpds-brand__logo">
                                    <img 
                                        src="<?php echo esc_url($brand['logo']); ?>"
                                        alt="<?php echo esc_attr($brand['name']); ?>"
                                        loading="lazy"
                                        decoding="async"
                                        width="120"
                                        height="120"
                                    >
                                </span>
                                <h3 class="gpds-brand__name">
                                    <?php echo esc_html($brand['name']); ?>
                                </h3>
                              
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- ناوبری -->
                <button class="gpds-carousel-nav gpds-carousel-nav--prev swiper-button-prev" aria-label="قبلی">
                    <?php gpds_icon('chevron-right', 20); ?>
                </button>
                <button class="gpds-carousel-nav gpds-carousel-nav--next swiper-button-next" aria-label="بعدی">
                    <?php gpds_icon('chevron-left', 20); ?>
                </button>
            </div>
            
        </div>
    </div>
</section>