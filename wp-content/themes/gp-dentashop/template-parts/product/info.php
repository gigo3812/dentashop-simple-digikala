<?php
/**
 * Product Info (Right Column)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

global $product;

if (!is_object($product) || !($product instanceof WC_Product)) {
    $product = wc_get_product(get_the_ID());
}

if (!$product) return;

$product_id    = $product->get_id();
$sku           = $product->get_sku();
$rating        = (float) $product->get_average_rating();
$review_count  = (int) $product->get_review_count();
$in_stock      = $product->is_in_stock();
$short_desc    = $product->get_short_description();
$attributes    = $product->get_attributes();
?>

<div class="gpds-product-info">
    
    <!-- عنوان -->
    <h1 class="gpds-product-info__title">
        <?php echo esc_html($product->get_name()); ?>
    </h1>
    
    <!-- متادیتا -->
    <div class="gpds-product-info__meta">
        
        <?php if ($sku) : ?>
            <div>
                <?php gpds_icon('tag', 14); ?>
                <span>کد: <?php echo esc_html($sku); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($rating > 0) : ?>
            <div class="gpds-product-info__rating">
                <?php gpds_icon('star', 14); ?>
                <span>
                    <?php echo esc_html(number_format($rating, 1)); ?>
                    (<?php echo esc_html($review_count); ?> نظر)
                </span>
            </div>
        <?php endif; ?>
        
        <?php
        $brands = get_the_terms($product_id, 'product_brand');
        if ($brands && !is_wp_error($brands)) :
            $brand = array_shift($brands);
        ?>
            <div class="gpds-product-info__brand">
                برند: <a href="<?php echo esc_url(get_term_link($brand)); ?>">
                    <?php echo esc_html($brand->name); ?>
                </a>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- مشخصات خلاصه -->
    <?php if (!empty($attributes)) : ?>
        <div class="gpds-product-info__short-specs">
            <div class="gpds-product-specs">
                <?php 
                $shown = 0;
                foreach ($attributes as $attr_name => $attr) : 
                    if ($shown >= 3) break;
                    
                    $label = wc_attribute_label($attr_name);
                    $value = '';
                    
                    if ($attr->is_taxonomy()) {
                        $terms = wc_get_product_terms($product_id, $attr_name, ['fields' => 'names']);
                        if (!is_wp_error($terms)) {
                            $value = implode(', ', $terms);
                        }
                    } else {
                        $value = implode(', ', $attr->get_options());
                    }
                    
                    if (!$value) continue;
                    $shown++;
                ?>
                    <div class="gpds-product-specs__row">
                        <span class="gpds-product-specs__label"><?php echo esc_html($label); ?></span>
                        <span class="gpds-product-specs__value"><?php echo esc_html($value); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- جعبه خرید -->
    <div class="gpds-product-price-box">
        
        <!-- قیمت -->
        <div class="gpds-product-price-box__prices">
            <?php echo wp_kses_post($product->get_price_html()); ?>
        </div>
        
        <!-- موجودی -->
        <div class="gpds-product-info__stock">
            <?php if ($in_stock) : ?>
                <span class="gpds-text-success">
                    <?php gpds_icon('check-circle', 14); ?>
                    موجود در انبار
                </span>
            <?php else : ?>
                <span class="gpds-text-danger">
                    <?php gpds_icon('alert-circle', 14); ?>
                    ناموجود
                </span>
            <?php endif; ?>
        </div>
        
        <!-- فرم خرید -->
        <?php if ($in_stock && $product->is_purchasable()) : ?>
            
            <?php if ($product->is_type('simple')) : ?>
                <!-- محصول ساده -->
                <form 
                    class="cart" 
                    action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" 
                    method="post" 
                    enctype="multipart/form-data"
                >
                    <div class="gpds-product-add-to-cart">
                        
                        <!-- تعداد -->
                        <div class="gpds-qty" data-gpds-qty>
                            <button 
                                type="button" 
                                class="gpds-qty__btn"
                                data-gpds-qty-minus
                                aria-label="کاهش"
                            >
                                <?php gpds_icon('minus', 16); ?>
                            </button>
                            <input 
                                type="number" 
                                name="quantity" 
                                value="1" 
                                min="1" 
                                class="gpds-qty__input"
                                data-gpds-qty-input
                                aria-label="تعداد"
                            >
                            <button 
                                type="button" 
                                class="gpds-qty__btn"
                                data-gpds-qty-plus
                                aria-label="افزایش"
                            >
                                <?php gpds_icon('plus', 16); ?>
                            </button>
                        </div>
                        
                        <!-- دکمه خرید -->
                        <button 
                            type="submit" 
                            name="add-to-cart" 
                            value="<?php echo esc_attr($product_id); ?>" 
                            class="single_add_to_cart_button button alt"
                        >
                            <?php gpds_icon('cart', 18); ?>
                            افزودن به سبد خرید
                        </button>
                        
                    </div>
                </form>
                
            <?php else : ?>
                <!-- محصول متغیر، گروهی و... -->
                <div class="gpds-product-variations">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>
            <?php endif; ?>
            
        <?php else : ?>
            <button class="gpds-btn gpds-btn--secondary gpds-btn--block" disabled>
                فعلاً ناموجود
            </button>
        <?php endif; ?>
        
        <!-- ویژگی‌های سریع -->
        <div class="gpds-product-info__quick-features">
            <div class="gpds-product-info__quick-feature">
                <?php gpds_icon('shield', 20); ?>
                <span>ضمانت اصالت</span>
            </div>
            <div class="gpds-product-info__quick-feature">
                <?php gpds_icon('truck', 20); ?>
                <span>ارسال سریع</span>
            </div>
            <div class="gpds-product-info__quick-feature">
                <?php gpds_icon('refresh', 20); ?>
                <span>بازگشت ۷ روزه</span>
            </div>
        </div>
        
    </div>
    
    <!-- توضیحات کوتاه -->
    <?php if ($short_desc) : ?>
        <div class="gpds-product-info__short-desc">
            <?php echo wp_kses_post($short_desc); ?>
        </div>
    <?php endif; ?>
    
</div>