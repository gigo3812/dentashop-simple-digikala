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

$related_query = new WP_Query([
    'post_type'      => 'product',
    'post__in'       => $related_ids,
    'posts_per_page' => 6,
    'orderby'        => 'post__in',
    'no_found_rows'  => true,
]);

if (!$related_query->have_posts()) {
    wp_reset_postdata();
    return;
}
?>

<section class="gpds-related-products">
    
    <div class="gpds-section-header">
        <h2 class="gpds-section-title">
            <?php gpds_icon('shopping-bag', 24); ?>
            محصولات مشابه
        </h2>
    </div>
    
    <div class="gpds-shop-grid">
        <?php while ($related_query->have_posts()) : $related_query->the_post(); 
            $rel_product = wc_get_product(get_the_ID());
            if (!$rel_product) continue;
        ?>
            <div class="gpds-shop-grid__item">
                <?php 
                
                    include get_stylesheet_directory() . '/template-parts/product/card.php';

                // get_template_part('template-parts/product/card', null, [
                //     'product' => $rel_product,
                // ]); 
                ?>
            </div>
        <?php endwhile; ?>
    </div>
    
</section>

<?php wp_reset_postdata(); ?>