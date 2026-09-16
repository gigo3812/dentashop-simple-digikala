<?php
/**
 * WooCommerce Integration
 * 
 * Override قالب‌های پیش‌فرض WooCommerce
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// ============================================
// حذف استایل‌های پیش‌فرض WooCommerce
// ============================================
add_filter('woocommerce_enqueue_styles', 'gpds_remove_wc_styles');

function gpds_remove_wc_styles($styles) {
    return [];
}

// ============================================
// حذف Sidebar پیش‌فرض WC
// ============================================
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// ============================================
// تغییر تعداد محصول در هر ردیف
// ============================================
add_filter('loop_shop_columns', function() { return 4; });

// ============================================
// تعداد محصول در هر صفحه
// ============================================
add_filter('loop_shop_per_page', function() {
    return 24;
}, 20);

// ============================================
// حذف المنت‌های اضافی از Loop
// ============================================
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);

// ============================================
// حذف Notice پیش‌فرض WC
// ============================================
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

// ============================================
// حذف Breadcrumb پیش‌فرض
// ============================================
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

// ============================================
// Wrapper محتوا
// ============================================
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', 'gpds_wc_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'gpds_wc_wrapper_end', 10);

function gpds_wc_wrapper_start() {
    echo '<div class="gpds-wc-wrap"><div class="gpds-container">';
}

function gpds_wc_wrapper_end() {
    echo '</div></div>';
}

// ============================================
// تب‌های محصول
// ============================================
add_filter('woocommerce_product_tabs', 'gpds_customize_product_tabs');

function gpds_customize_product_tabs($tabs) {
    if (isset($tabs['description'])) {
        $tabs['description']['title'] = 'معرفی محصول';
        $tabs['description']['priority'] = 10;
    }
    if (isset($tabs['additional_information'])) {
        $tabs['additional_information']['title'] = 'مشخصات فنی';
        $tabs['additional_information']['priority'] = 20;
    }
    if (isset($tabs['reviews'])) {
        $tabs['reviews']['title'] = sprintf('نظرات (%d)', get_comments_number());
        $tabs['reviews']['priority'] = 30;
    }
    return $tabs;
}

// ============================================
// تعداد محصولات مرتبط
// ============================================
add_filter('woocommerce_output_related_products_args', function($args) {
    $args['posts_per_page'] = 6;
    $args['columns'] = 6;
    return $args;
});

// ============================================
// حذف لینک افزودن به سبد از Loop
// ============================================
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

// ============================================
// AJAX: افزودن سریع به سبد
// ============================================
add_action('wp_ajax_gpds_quick_add',        'gpds_ajax_quick_add');
add_action('wp_ajax_nopriv_gpds_quick_add', 'gpds_ajax_quick_add');

function gpds_ajax_quick_add() {
    check_ajax_referer('gpds_nonce', 'nonce');
    
    if (!function_exists('WC')) {
        wp_send_json_error(['message' => 'ووکامرس فعال نیست']);
    }
    
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'شناسه نامعتبر']);
    }
    
    $product = wc_get_product($product_id);
    if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
        wp_send_json_error(['message' => 'محصول موجود نیست']);
    }
    
    if (!$product->is_type('simple')) {
        wp_send_json_error([
            'message' => 'لطفاً صفحه محصول را باز کنید',
            'url'     => $product->get_permalink(),
        ]);
    }
    
    $added = WC()->cart->add_to_cart($product_id, 1);
    
    if (!$added) {
        wp_send_json_error(['message' => 'افزودن ناموفق بود']);
    }
    
    wp_send_json_success([
        'count'   => WC()->cart->get_cart_contents_count(),
        'message' => 'به سبد خرید اضافه شد',
    ]);
}

// ============================================
// حذف استایل‌های اضافی WC Blocks
// ============================================
add_action('wp_enqueue_scripts', function() {
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
        wp_dequeue_style('wc-blocks-style');
        wp_dequeue_style('wc-blocks-vendors-style');
    }
}, 99);

add_action('wp_enqueue_scripts', function() {
    if (!is_cart() && !is_checkout()) {
        wp_dequeue_style('wc-blocks-style');
    }
}, 99);

// ============================================
// Order Received page
// ============================================
remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);
add_action('woocommerce_thankyou', 'gpds_order_received_content', 10);

function gpds_order_received_content($order_id) {
    if (!$order_id) return;
    
    $order = wc_get_order($order_id);
    if (!$order) return;
    ?>
    
    <div class="gpds-thankyou">
        <div class="gpds-thankyou__icon">
            <?php gpds_icon('check-circle', 64); ?>
        </div>
        <h2 class="gpds-thankyou__title">سفارش شما با موفقیت ثبت شد!</h2>
        <p class="gpds-thankyou__subtitle">
            شماره سفارش: <strong>#<?php echo esc_html($order->get_order_number()); ?></strong>
        </p>
        
        <div class="gpds-thankyou__details">
            <div class="gpds-thankyou__row">
                <span>تاریخ:</span>
                <strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong>
            </div>
            <div class="gpds-thankyou__row">
                <span>مجموع:</span>
                <strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
            </div>
            <div class="gpds-thankyou__row">
                <span>روش پرداخت:</span>
                <strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
            </div>
        </div>
        
        <div class="gpds-thankyou__actions">
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="gpds-btn gpds-btn--primary">
                ادامه خرید
            </a>
            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="gpds-btn gpds-btn--outline">
                مشاهده جزئیات سفارش
            </a>
        </div>
    </div>
    
    <?php
}

// ============================================
// Checkout - حذف/تنظیم فیلدها
// ============================================
add_filter('woocommerce_checkout_fields', function($fields) {
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_country']);
    unset($fields['shipping']['shipping_country']);
    
    if (isset($fields['billing']['billing_first_name'])) {
        $fields['billing']['billing_first_name']['priority'] = 10;
        $fields['billing']['billing_first_name']['placeholder'] = 'نام';
    }
    if (isset($fields['billing']['billing_last_name'])) {
        $fields['billing']['billing_last_name']['priority'] = 20;
        $fields['billing']['billing_last_name']['placeholder'] = 'نام خانوادگی';
    }
    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['priority'] = 30;
    }
    if (isset($fields['billing']['billing_email'])) {
        $fields['billing']['billing_email']['priority'] = 40;
    }
    if (isset($fields['billing']['billing_state'])) {
        $fields['billing']['billing_state']['priority'] = 50;
        $fields['billing']['billing_state']['label'] = 'استان';
    }
    if (isset($fields['billing']['billing_city'])) {
        $fields['billing']['billing_city']['priority'] = 60;
        $fields['billing']['billing_city']['label'] = 'شهر';
    }
    if (isset($fields['billing']['billing_address_1'])) {
        $fields['billing']['billing_address_1']['priority'] = 70;
        $fields['billing']['billing_address_1']['label'] = 'آدرس';
        $fields['billing']['billing_address_1']['placeholder'] = 'آدرس دقیق';
    }
    if (isset($fields['billing']['billing_postcode'])) {
        $fields['billing']['billing_postcode']['priority'] = 80;
        $fields['billing']['billing_postcode']['label'] = 'کد پستی';
    }
    
    return $fields;
}, 20);

add_filter('woocommerce_checkout_fields', function($fields) {
    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['placeholder'] = 'توضیحات سفارش (اختیاری)';
    }
    return $fields;
}, 30);

// ============================================
// حذف لینک تخمین در سبد
// ============================================
add_filter('woocommerce_shipping_estimate_is_required', '__return_false');


// ============================================
// ⭐ بخش جدید: My Account
// ============================================
//
// لود تمپلیت‌های سفارشی برای /my-account/
// از مسیر inc/templates/woocommerce/myaccount/
// ============================================

add_filter('template_include', 'gpds_myaccount_template_include', 99);

function gpds_myaccount_template_include($template) {
    if (!function_exists('is_account_page') || !is_account_page()) {
        return $template;
    }

    $base = GPDS_INC . '/templates/woocommerce/myaccount';

    if (!is_user_logged_in()) {
        $custom = $base . '/auth.php';
    } else {
        $custom = $base . '/dashboard.php';
    }

    if (file_exists($custom)) {
        return $custom;
    }

    return $template;
}


// ============================================
// عنوان صفحه my-account
// ============================================

add_filter('document_title_parts', function($title) {
    if (function_exists('is_account_page') && is_account_page()) {
        if (!is_user_logged_in()) {
            $title['title'] = 'ورود / ثبت‌نام';
        } else {
            $title['title'] = 'حساب کاربری';
        }
    }
    return $title;
});


// ============================================
// حذف استایل‌های اضافی WC در صفحات my-account
// ============================================

add_action('wp_enqueue_scripts', function() {
    if (function_exists('is_account_page') && is_account_page()) {
        // حذف استایل‌های پیش‌فرض ووکامرس که با قالب ما تداخل می‌کنن
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
    }
}, 100);