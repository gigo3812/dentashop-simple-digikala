<?php
/**
 * Product Gallery
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
$main_image_id = $product->get_image_id();
$gallery_ids   = $product->get_gallery_image_ids();
$placeholder   = wc_placeholder_img_src('woocommerce_single');
$main_image_url = $main_image_id 
    ? wp_get_attachment_image_url($main_image_id, 'woocommerce_single')
    : $placeholder;

// همه عکس‌ها
$all_images = [];
if ($main_image_id) {
    $all_images[] = $main_image_id;
}
if (!empty($gallery_ids)) {
    $all_images = array_merge($all_images, $gallery_ids);
}
?>

<div class="gpds-product-gallery" data-gpds-gallery>
    
    <!-- تصویر اصلی -->
    <div class="gpds-product-gallery__main">
        
        <!-- برچسب‌ها -->
        <div class="gpds-product-gallery__badges">
            <?php if ($product->is_on_sale()) : 
                $regular = (float) $product->get_regular_price();
                $sale    = (float) $product->get_sale_price();
                $discount = ($regular > 0 && $sale > 0 && $sale < $regular) 
                    ? round((($regular - $sale) / $regular) * 100) 
                    : 0;
                if ($discount > 0) :
            ?>
                <span class="gpds-discount"><?php echo esc_html($discount); ?>%</span>
            <?php endif; endif; ?>
            
            <?php if ($product->is_featured()) : ?>
                <span class="gpds-badge gpds-badge--warning">ویژه</span>
            <?php endif; ?>
            
            <?php if (!$product->is_in_stock()) : ?>
                <span class="gpds-badge gpds-badge--danger">ناموجود</span>
            <?php endif; ?>
        </div>
        
        <!-- تصویر -->
        <img 
            id="gpds-gallery-main" 
            src="<?php echo esc_url($main_image_url); ?>"
            alt="<?php echo esc_attr($product->get_name()); ?>"
            fetchpriority="high"
            data-gpds-gallery-main
        >
        
    </div>
    
    <!-- Thumbnails -->
    <?php if (count($all_images) > 1) : ?>
        <div class="gpds-product-gallery__thumbs">
            <?php foreach ($all_images as $i => $img_id) : 
                $thumb_url = wp_get_attachment_image_url($img_id, 'gpds-card-sm');
                $full_url  = wp_get_attachment_image_url($img_id, 'woocommerce_single');
                if (!$thumb_url) continue;
            ?>
                <button 
                    type="button"
                    class="gpds-product-gallery__thumb <?php echo $i === 0 ? 'is-active' : ''; ?>"
                    data-gpds-gallery-thumb
                    data-image="<?php echo esc_url($full_url); ?>"
                    aria-label="تصویر <?php echo esc_attr($i + 1); ?>"
                >
                    <img 
                        src="<?php echo esc_url($thumb_url); ?>" 
                        alt=""
                        loading="lazy"
                    >
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
</div>