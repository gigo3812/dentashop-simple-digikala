<?php
/**
 * Product Tabs (Description, Attributes, Reviews)
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
$description   = $product->get_description();
$attributes    = $product->get_attributes();
$review_count  = (int) $product->get_review_count();
?>

<div class="gpds-product-tabs" data-gpds-tabs>
    
    <!-- دکمه‌های تب -->
    <div class="gpds-product-tabs__nav">
        <button 
            type="button" 
            class="gpds-product-tabs__tab is-active" 
            data-gpds-tab="description"
        >
            معرفی محصول
        </button>
        
        <button 
            type="button" 
            class="gpds-product-tabs__tab" 
            data-gpds-tab="specs"
        >
            مشخصات فنی
        </button>
        
        <button 
            type="button" 
            class="gpds-product-tabs__tab" 
            data-gpds-tab="reviews"
        >
            نظرات (<?php echo esc_html($review_count); ?>)
        </button>
    </div>
    
    <!-- محتوای تب‌ها -->
    <div class="gpds-product-tabs__panels">
        
        <!-- معرفی -->
        <div class="gpds-product-tabs__panel is-active" data-gpds-tab-panel="description">
            <?php if ($description) : ?>
                <?php echo wp_kses_post(wpautop($description)); ?>
            <?php else : ?>
                <p class="gpds-text-muted">توضیحاتی برای این محصول ثبت نشده است.</p>
            <?php endif; ?>
        </div>
        
        <!-- مشخصات فنی -->
        <div class="gpds-product-tabs__panel" data-gpds-tab-panel="specs">
            <?php if (!empty($attributes)) : ?>
                <div class="gpds-product-specs">
                    <?php foreach ($attributes as $attr_name => $attr) : 
                        $label = wc_attribute_label($attr_name);
                        $value = '';
                        
                        if ($attr->is_taxonomy()) {
                            $terms = wc_get_product_terms($product_id, $attr_name, ['fields' => 'names']);
                            if (!is_wp_error($terms)) {
                                $value = implode('، ', $terms);
                            }
                        } else {
                            $value = implode('، ', $attr->get_options());
                        }
                        
                        if (!$value) continue;
                    ?>
                        <div class="gpds-product-specs__row">
                            <span class="gpds-product-specs__label"><?php echo esc_html($label); ?></span>
                            <span class="gpds-product-specs__value"><?php echo esc_html($value); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="gpds-text-muted">مشخصات فنی برای این محصول ثبت نشده است.</p>
            <?php endif; ?>
        </div>
        
        <!-- نظرات -->
        <div class="gpds-product-tabs__panel" data-gpds-tab-panel="reviews">
            <?php 
            // فقط فرم و لیست نظرات
            if (comments_open() || get_comments_number() > 0) {
                comments_template();
            } else {
                echo '<p class="gpds-text-muted">نظرات برای این محصول فعال نیست.</p>';
            }
            ?>
        </div>
        
    </div>
    
</div>