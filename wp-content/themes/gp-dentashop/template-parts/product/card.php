<?php
/**
 * Product Card (Optimized)
 *
 * @package GP_DentaShop
 * @var WC_Product $product
 */

if (!defined('ABSPATH')) exit;

/**
 * تبدیل اعداد لاتین به فارسی — بهینه با strtr
 */
if ( ! function_exists( 'gpds_card_fa_num' ) ) {
    function gpds_card_fa_num( $str ) {
        static $map = [
            '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴',
            '5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',
            ','=>'٬',
        ];
        return strtr( (string) $str, $map );
    }
}

// چک‌های امنیتی
if (!isset($product) || !is_object($product) || !($product instanceof WC_Product)) {
    return;
}

$product_id  = $product->get_id();
$permalink   = $product->get_permalink();
$title       = $product->get_name();

// تصویر با srcset + sizes (لود بهینه)
$image_id = $product->get_image_id();

if ($image_id) {
    $image_url = wp_get_attachment_image_url($image_id, 'gpds-card');
    $srcset    = wp_get_attachment_image_srcset($image_id, 'gpds-card');
    $sizes     = '(max-width: 600px) 50vw, (max-width: 1024px) 33vw, 300px';
} else {
    $image_url = wc_placeholder_img_src('gpds-card');
    $srcset    = false;
    $sizes     = '';
}

$price_html = $product->get_price_html();
$on_sale    = $product->is_on_sale();
$in_stock   = $product->is_in_stock();

// امتیاز — از متای ذخیره‌شده (بدون کوئری اضافه)
$rating       = (float) $product->get_average_rating();
$rating_count = (int) get_post_meta($product_id, '_wc_review_count', true);

// محاسبه درصد تخفیف — فقط وقتی لازم است
$discount = 0;
if ($on_sale) {
    $regular = (float) $product->get_regular_price();
    $sale    = (float) $product->get_sale_price();
    if ($regular > 0 && $sale > 0 && $sale < $regular) {
        $discount = round((($regular - $sale) / $regular) * 100);
    }
}

?>

<div class="gpds-product-card" data-product-id="<?php echo esc_attr($product_id); ?>">
    
    <!-- تصویر + لینک -->
    <a href="<?php echo esc_url($permalink); ?>" class="gpds-product-card__image-link">
        <div class="gpds-product-card__image">
            <img 
                src="<?php echo esc_url($image_url); ?>"
                <?php if ($srcset) : ?>
                srcset="<?php echo esc_attr($srcset); ?>"
                sizes="<?php echo esc_attr($sizes); ?>"
                <?php endif; ?>
                alt="<?php echo esc_attr($title); ?>"
                loading="lazy"
                decoding="async"
                width="300"
                height="300"
            >
            
            <?php if ($discount > 0) : ?>
                <span class="gpds-discount gpds-product-card__discount">
                    <?php echo esc_html($discount); ?>%
                </span>
            <?php endif; ?>
        </div>
    </a>
    
    <!-- اطلاعات -->
    <div class="gpds-product-card__body">
        
        <!-- عنوان -->
        <a href="<?php echo esc_url($permalink); ?>" class="gpds-product-card__title">
            <?php echo esc_html($title); ?>
        </a>
        
        <!-- امتیاز -->
        <?php if ($rating > 0) : ?>
            <div class="gpds-rating gpds-product-card__rating">
                <?php gpds_icon('star', 14); ?>
                <span><?php echo esc_html(number_format($rating, 1)); ?></span>
                <?php if ($rating_count > 0) : ?>
                    <span class="gpds-text-tertiary">(<?php echo esc_html($rating_count); ?>)</span>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <div class="gpds-product-card__rating-empty"></div>
        <?php endif; ?>
        
        <!-- موجودی -->
        <div class="gpds-product-card__stock">
            <?php if ($in_stock) : ?>
                <span class="gpds-text-success gpds-text-xs">موجود در انبار</span>
            <?php else : ?>
                <span class="gpds-text-danger gpds-text-xs">ناموجود</span>
            <?php endif; ?>
        </div>
        
        <!-- قیمت -->
        <div class="gpds-product-card__prices">
            <?php echo wp_kses_post( gpds_card_fa_num( $price_html ) ); ?>
        </div>
        
        <!-- دکمه افزودن به سبد (اختیاری) -->
        <?php if ($in_stock && $product->is_type('simple')) : ?>
            <button 
                type="button"
                class="gpds-product-card__add-btn"
                data-gpds-add-to-cart
                data-product-id="<?php echo esc_attr($product_id); ?>"
                aria-label="افزودن به سبد خرید"
            >
                <?php gpds_icon('plus', 18); ?>
            </button>
        <?php endif; ?>
        
    </div>
    
</div>