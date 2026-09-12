<?php
/**
 * Single Product Template
 * قالب تک محصول
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header('shop');

global $product;
?>

<main id="gpds-product-single" class="gpds-product-single">
    
    <!-- Breadcrumb -->
    <div class="gpds-container">
        <?php gpds_wc_breadcrumb(); ?>
    </div>
    
    <!-- محتوای محصول -->
    <div class="gpds-container">
        <?php while (have_posts()) : the_post(); 
            // اطمینان از اینکه $product داره
            if (!is_object($product) || !($product instanceof WC_Product)) {
                $product = wc_get_product(get_the_ID());
            }
            
            if (!$product) {
                echo '<p>محصول یافت نشد.</p>';
                continue;
            }
        ?>
            
            <div class="gpds-product-single__main">
                
                <!-- گالری -->
                <?php get_template_part('template-parts/product/gallery'); ?>
                
                <!-- اطلاعات -->
                <?php get_template_part('template-parts/product/info'); ?>
                
            </div>
            
            <!-- تب‌ها -->
            <?php get_template_part('template-parts/product/tabs'); ?>
            
            <!-- محصولات مشابه -->
            <?php get_template_part('template-parts/product/related'); ?>
            
        <?php endwhile; ?>
    </div>
    
</main>

<?php get_footer('shop');