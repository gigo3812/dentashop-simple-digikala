<?php
/**
 * Checkout Form
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header('shop');

do_action('woocommerce_before_checkout_form', $checkout);

// اگه سبد خالیه
if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}
?>

<main id="gpds-checkout" class="gpds-checkout-page">
    <div class="gpds-container">
        
        <?php gpds_wc_breadcrumb(); ?>
        
        <h1 class="gpds-checkout-page__title">تسویه حساب</h1>
        
        <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
            
            <div class="gpds-checkout-layout">
                
                <!-- ستون اطلاعات -->
                <div class="gpds-checkout-layout__main">
                    
                    <?php if ($checkout->get_checkout_fields()) : ?>
                        
                        <?php do_action('woocommerce_checkout_before_customer_details'); ?>
                        
                        <div class="gpds-checkout-customer">
                            <?php do_action('woocommerce_checkout_billing'); ?>
                            <?php do_action('woocommerce_checkout_shipping'); ?>
                        </div>
                        
                        <?php do_action('woocommerce_checkout_after_customer_details'); ?>
                        
                    <?php endif; ?>
                    
                </div>
                
                <!-- ستون سفارش -->
                <div class="gpds-checkout-layout__sidebar">
                    
                    <div class="gpds-checkout-order">
                        
                        <h3 class="gpds-checkout-order__title">سفارش شما</h3>
                        
                        <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
                        
                        <?php do_action('woocommerce_checkout_before_order_review'); ?>
                        
                        <div id="order_review" class="woocommerce-checkout-review-order">
                            <?php do_action('woocommerce_checkout_order_review'); ?>
                        </div>
                        
                        <?php do_action('woocommerce_checkout_after_order_review'); ?>
                        
                    </div>
                    
                </div>
                
            </div>
            
        </form>
        
        <?php do_action('woocommerce_after_checkout_form', $checkout); ?>
        
    </div>
</main>

<?php get_footer('shop');