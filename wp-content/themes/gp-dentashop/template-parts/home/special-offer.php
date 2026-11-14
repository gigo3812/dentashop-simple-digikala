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

// محاسبه نمایش تایمر (فقط اگر کمتر از یک ماه باقی مانده باشد)
$end_ts = 0;
if (!empty($end_time)) {
    $end_ts = is_numeric($end_time) ? (int) $end_time : strtotime($end_time);
}

$now_ts     = current_time('timestamp');
$diff       = ($end_ts > $now_ts) ? ($end_ts - $now_ts) : 0;
$one_month  = 30 * DAY_IN_SECONDS;
$show_timer = ($diff > 0 && $diff <= $one_month);

// محاسبه اجزای زمان برای رندر اولیه
$has_days  = ($diff >= DAY_IN_SECONDS);
$init_days = $has_days ? (int) floor($diff / DAY_IN_SECONDS) : 0;
$init_hrs  = (int) floor(($diff % DAY_IN_SECONDS) / HOUR_IN_SECONDS);
$init_min  = (int) floor(($diff % HOUR_IN_SECONDS) / MINUTE_IN_SECONDS);
$init_sec  = (int) ($diff % MINUTE_IN_SECONDS);
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
                
                <!-- تایمر (فقط اگر کمتر از یک ماه باقی مانده باشد) -->
                <?php if ($show_timer) : ?>
                    <div 
                        class="gpds-countdown" 
                        data-gpds-countdown 
                        data-end="<?php echo esc_attr($end_ts); ?>"
                        data-server-now="<?php echo esc_attr($now_ts); ?>"
                    >
                        <div class="gpds-countdown__box" data-gpds-cd-box="days" <?php echo $has_days ? '' : 'hidden'; ?>>
                            <span data-gpds-cd="days"><?php echo esc_html(str_pad($init_days, 2, '0', STR_PAD_LEFT)); ?></span>
                            <small>روز</small>
                        </div>
                        <div class="gpds-countdown__sep" data-gpds-cd-sep="days" <?php echo $has_days ? '' : 'hidden'; ?>>:</div>
                        <div class="gpds-countdown__box">
                            <span data-gpds-cd="hours"><?php echo esc_html(str_pad($init_hrs, 2, '0', STR_PAD_LEFT)); ?></span>
                            <small>ساعت</small>
                        </div>
                        <div class="gpds-countdown__sep">:</div>
                        <div class="gpds-countdown__box">
                            <span data-gpds-cd="minutes"><?php echo esc_html(str_pad($init_min, 2, '0', STR_PAD_LEFT)); ?></span>
                            <small>دقیقه</small>
                        </div>
                        <div class="gpds-countdown__sep">:</div>
                        <div class="gpds-countdown__box">
                            <span data-gpds-cd="seconds"><?php echo esc_html(str_pad($init_sec, 2, '0', STR_PAD_LEFT)); ?></span>
                            <small>ثانیه</small>
                        </div>
                    </div>
                <?php endif; ?>
                
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