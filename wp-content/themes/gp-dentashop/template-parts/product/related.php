<?php
/**
 * Related Products
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

global $product;

if (!is_object($product) || !($product instanceof WC_Product)) {
    $product = wc_get_product(get_the_ID());
}

if (!$product) return;

$product_id  = $product->get_id();
$related_ids = wc_get_related_products($product_id, 6);

if (empty($related_ids)) return;

// ذخیره وضعیت اولیه برای بازگردانی
$original_product = $product;
?>

<section class="gpds-related-products">
    
    <div class="gpds-section-header">
        <h2 class="gpds-section-title">
            <?php gpds_icon('shopping-bag', 24); ?>
            محصولات مشابه
        </h2>
    </div>
    
    <div class="gpds-shop-grid">
        <?php foreach ($related_ids as $related_id) : 
            // گرفتن محصول (از object cache ووکامرس، بدون کوئری اضافه)
            $product = wc_get_product($related_id);
            if (!$product || !$product->is_visible()) continue;
        ?>
            <div class="gpds-shop-grid__item">
                <?php include get_stylesheet_directory() . '/template-parts/product/card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
</section>

<?php 
// بازگردانی $product به محصول اصلی (برای بقیه قالب)
$product = $original_product;
?>