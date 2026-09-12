<?php
/**
 * Product Card
 *
 * @package GP_DentaShop
 * @var WC_Product $product
 */

if (!defined('ABSPATH')) exit;

// چک‌های امنیتی
if (!isset($product) || !is_object($product) || !($product instanceof WC_Product)) {
    return;
}

$product_id  = $product->get_id();
$permalink   = $product->get_permalink();
$title       = $product->get_name();
$image_id    = $product->get_image_id();
$image_url   = $image_id 
    ? wp_get_attachment_image_url($image_id, 'gpds-card')
    : wc_placeholder_img_src('gpds-card');
$price_html  = $product->get_price_html();
$on_sale     = $product->is_on_sale();
$rating      = (float) $product->get_average_rating();
$rating_count= (int) $product->get_review_count();
$in_stock    = $product->is_in_stock();

// محاسبه درصد تخفیف
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
            <?php echo wp_kses_post($price_html); ?>
        </div>
        
        <!-- دکمه افزودن به سبد (اختیاری) -->
        <?php if ($product->is_type('simple') && $in_stock) : ?>
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