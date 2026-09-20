<?php
/**
 * Home - Brands Section (Aligned with Best Sellers)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$brands = gpds_get_active_brands(12, true);
if (empty($brands)) {
    return;
}
?>

<section class="gpds-section gpds-brands-section" aria-label="برندها">
    <div class="gpds-container">
        <div class="gpds-card">

            <!-- هدر — دقیقاً مثل بخش پرفروش‌ترین‌ها -->
            <div class="gpds-section-header">
                <h2 class="gpds-section-title">
                    <?php gpds_icon('award', 24); ?>
                    محبوب‌ترین برندها
                </h2>
                <a href="<?php echo esc_url(gpds_get_all_brands_url()); ?>" class="gpds-section-more">
                    مشاهده همه
                    <?php gpds_icon('chevron-left', 14); ?>
                </a>
            </div>

            <!-- کاروسل برندها — با همان ساختار کاروسل محصولات -->
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
                                        width="160"
                                        height="160"
                                    >
                                </span>
                                <h3 class="gpds-brand__name">
                                    <?php echo esc_html($brand['name']); ?>
                                </h3>
                                <span class="gpds-brand__cta">
                                    مشاهده محصولات
                                    <?php gpds_icon('chevron-left', 12); ?>
                                </span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- دکمه‌های ناوبری — دقیقاً با کلاس‌های قالب اصلی -->
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