<?php
/**
 * Home - Special Offer (شگفت‌انگیزها)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$products = gpds_get_products([
    'type'  => 'sale',
    'limit' => 8,
]);

if (empty($products)) {
    return;
}

// تایمر: 6 ساعت از الان
$end_time = time() + (6 * HOUR_IN_SECONDS);
?>

<section class="gpds-section" aria-label="پیشنهاد ویژه">
    <div class="gpds-container">
        <div class="gpds-card gpds-special-offer">
            
            <!-- هدر -->
            <div class="gpds-special-offer__header">
                <div class="gpds-special-offer__title-wrap">
                    <?php gpds_icon('zap', 28, 'gpds-special-offer__icon'); ?>
                    <div>
                        <div class="gpds-special-offer__title">شگفت‌انگیزهای امروز</div>
                        <div class="gpds-special-offer__subtitle">تخفیف‌های محدود</div>
                    </div>
                </div>
                
                <!-- تایمر -->
                <div 
                    class="gpds-countdown" 
                    data-gpds-countdown 
                    data-end="<?php echo esc_attr($end_time); ?>"
                >
                    <div class="gpds-countdown__box">
                        <span data-gpds-cd="hours">00</span>
                        <small>ساعت</small>
                    </div>
                    <div class="gpds-countdown__sep">:</div>
                    <div class="gpds-countdown__box">
                        <span data-gpds-cd="minutes">00</span>
                        <small>دقیقه</small>
                    </div>
                    <div class="gpds-countdown__sep">:</div>
                    <div class="gpds-countdown__box">
                        <span data-gpds-cd="seconds">00</span>
                        <small>ثانیه</small>
                    </div>
                </div>
                
                <a href="<?php echo esc_url(gpds_shop_url()); ?>" class="gpds-section-more">
                    مشاهده همه
                    <?php gpds_icon('chevron-left', 14); ?>
                </a>
            </div>
            
            <!-- محصولات -->
            <div class="swiper gpds-products-carousel gpds-products-carousel--offer">
                <div class="swiper-wrapper">
                    <?php foreach ($products as $product) : ?>
                        <div class="swiper-slide">
                            <?php 
                            get_template_part('template-parts/product/card', null, [
                                'product' => $product,
                            ]);
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
    </div>
</section>