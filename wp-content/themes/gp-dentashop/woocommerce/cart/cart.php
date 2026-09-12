<?php
/**
 * Cart Page
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header('shop');

do_action('woocommerce_before_cart');
?>

<main id="gpds-cart" class="gpds-cart-page">
    <div class="gpds-container">
        
        <!-- Breadcrumb -->
        <?php gpds_wc_breadcrumb(); ?>
        
        <!-- Header -->
        <h1 class="gpds-cart-page__title">سبد خرید</h1>
        
        <div class="gpds-cart-page__layout">
            
            <!-- سبد خرید -->
            <div class="gpds-cart-page__main">
                
                <?php if (WC()->cart->is_empty()) : ?>
                    
                    <div class="gpds-cart-empty">
                        <div class="gpds-empty">
                            <?php gpds_icon('cart-empty', 80); ?>
                            <h3>سبد خرید شما خالی است</h3>
                            <p>هنوز محصولی به سبد خرید اضافه نکرده‌اید.</p>
                            <a href="<?php echo esc_url(gpds_shop_url()); ?>" class="gpds-btn gpds-btn--primary">
                                مشاهده محصولات
                            </a>
                        </div>
                    </div>
                    
                <?php else : ?>
                    
                    <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
                        
                        <div class="gpds-cart-items">
                            
                            <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : 
                                $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                                
                                if ($_product && $_product->exists() && $cart_item['quantity'] > 0) :
                                    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                            ?>
                                
                                <div class="gpds-cart-item">
                                    
                                    <!-- تصویر -->
                                    <div class="gpds-cart-item__image">
                                        <?php
                                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('gpds-card-sm'), $cart_item, $cart_item_key);
                                        
                                        if ($product_permalink) {
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
                                        } else {
                                            echo $thumbnail;
                                        }
                                        ?>
                                    </div>
                                    
                                    <!-- اطلاعات -->
                                    <div class="gpds-cart-item__info">
                                        <div class="gpds-cart-item__title">
                                            <?php
                                            if ($product_permalink) {
                                                printf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name());
                                            } else {
                                                echo $_product->get_name();
                                            }
                                            ?>
                                        </div>
                                        
                                        <!-- متادیتا -->
                                        <?php 
                                        $item_data = wc_get_formatted_cart_item_data($cart_item);
                                        if ($item_data) : ?>
                                            <div class="gpds-cart-item__meta">
                                                <?php echo $item_data; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- قیمت واحد -->
                                        <div class="gpds-cart-item__price">
                                            <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                                        </div>
                                        
                                        <!-- حذف -->
                                        <div class="gpds-cart-item__remove">
                                            <?php
                                            echo apply_filters(
                                                'woocommerce_cart_item_remove_link',
                                                sprintf(
                                                    '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s حذف</a>',
                                                    esc_url(wc_get_cart_remove_url($cart_item_key)),
                                                    esc_attr(sprintf('حذف %s از سبد', $_product->get_name())),
                                                    esc_attr($product_id),
                                                    esc_attr($_product->get_sku()),
                                                    gpds_get_icon('trash', 14)
                                                ),
                                                $cart_item_key
                                            );
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <!-- تعداد -->
                                    <div class="gpds-cart-item__qty">
                                        <?php
                                        if ($_product->is_sold_individually()) {
                                            $min_quantity = 1;
                                            $max_quantity = 1;
                                        } else {
                                            $min_quantity = 0;
                                            $max_quantity = $_product->get_max_purchase_quantity();
                                        }
                                        
                                        $product_quantity = woocommerce_quantity_input([
                                            'input_name'   => "cart[{$cart_item_key}][qty]",
                                            'input_value'  => $cart_item['quantity'],
                                            'max_value'    => $max_quantity,
                                            'min_value'    => $min_quantity,
                                            'product_name' => $_product->get_name(),
                                        ], $_product, false);
                                        
                                        echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item);
                                        ?>
                                    </div>
                                    
                                    <!-- جمع -->
                                    <div class="gpds-cart-item__subtotal">
                                        <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                    </div>
                                    
                                </div>
                                
                            <?php endif; endforeach; ?>
                            
                        </div>
                        
                        <!-- اکشن‌های سبد -->
                        <div class="gpds-cart-actions">
                            <?php if (wc_coupons_enabled()) : ?>
                                <div class="gpds-cart-coupon">
                                    <input 
                                        type="text" 
                                        name="coupon_code" 
                                        class="gpds-input" 
                                        placeholder="کد تخفیف"
                                        id="coupon_code"
                                        value=""
                                    >
                                    <button 
                                        type="submit" 
                                        class="gpds-btn gpds-btn--secondary" 
                                        name="apply_coupon" 
                                        value="اعمال کد"
                                    >
                                        اعمال کد
                                    </button>
                                    <?php do_action('woocommerce_cart_coupon'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <button 
                                type="submit" 
                                class="gpds-btn gpds-btn--outline" 
                                name="update_cart" 
                                value="به‌روزرسانی"
                            >
                                <?php gpds_icon('refresh', 16); ?>
                                به‌روزرسانی سبد
                            </button>
                            
                            <?php do_action('woocommerce_cart_actions'); ?>
                            <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                        </div>
                        
                    </form>
                    
                <?php endif; ?>
                
            </div>
            
            <!-- جمع کل -->
            <?php if (!WC()->cart->is_empty()) : ?>
                <div class="gpds-cart-page__sidebar">
                    <div class="gpds-cart-totals">
                        <h3 class="gpds-cart-totals__title">جمع کل سبد خرید</h3>
                        <?php woocommerce_cart_totals(); ?>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
        
    </div>
</main>

<?php 
do_action('woocommerce_after_cart');
get_footer('shop');