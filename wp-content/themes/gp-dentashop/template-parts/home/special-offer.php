<?php
/**
 * Home - Special Offer (شگفت‌انگیزها)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// چک فعال بودن
if (!gpds_is_special_offer_active()) {
    return;
}

// گرفتن محصولات
$products = gpds_get_special_offer_products();

if (empty($products)) {
    return;
}

// تنظیمات
$title     = get_theme_mod('gpds_special_offer_title', 'شگفت‌انگیزهای امروز');
$subtitle  = get_theme_mod('gpds_special_offer_subtitle', 'تخفیف‌های محدود');
$shop_link = gpds_get_special_offer_link();
$end_time  = gpds_get_special_offer_end();

// محاسبه بیشترین درصد تخفیف
$max_discount = 0;
foreach ($products as $product) {
    if (!$product->is_on_sale()) continue;
    
    $regular = (float) $product->get_regular_price();
    $sale    = (float) $product->get_sale_price();
    
    if ($product->is_type('variable')) {
        $regular = (float) $product->get_variation_regular_price('min');
        $sale    = (float) $product->get_variation_sale_price('min');
    }
    
    if ($regular > 0 && $sale > 0 && $sale < $regular) {
        $discount = round((($regular - $sale) / $regular) * 100);
        if ($discount > $max_discount) {
            $max_discount = $discount;
        }
    }
}
?>

<section class="gpds-section" aria-label="<?php echo esc_attr($title); ?>">
    <div class="gpds-container">
        <div class="gpds-card gpds-special-offer">
            
            <!-- هدر -->
            <div class="gpds-special-offer__header">
                <div class="gpds-special-offer__title-wrap">
                    <?php gpds_icon('zap', 28, 'gpds-special-offer__icon'); ?>
                    <div>
                        <div class="gpds-special-offer__title">
                            <?php echo esc_html($title); ?>
                        </div>
                        <div class="gpds-special-offer__subtitle">
                            <?php if ($max_discount > 0) : ?>
                                تا <?php echo esc_html($max_discount); ?>% تخفیف
                            <?php else : ?>
                                <?php echo esc_html($subtitle); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- تایمر (از سرور) -->
               <div 
                    class="gpds-countdown" 
                    data-gpds-countdown 
                    data-end="<?php echo esc_attr($end_time); ?>"
                    data-server-now="<?php echo esc_attr(current_time('timestamp')); ?>"
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
                
                <a href="<?php echo esc_url($shop_link); ?>" class="gpds-section-more">
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
                                include get_stylesheet_directory() . '/template-parts/product/card.php';
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
    </div>
</section>