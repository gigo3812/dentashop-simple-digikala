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
    // حذف کامل استایل‌های پیش‌فرض چون خودمون می‌سازیم
    return [];
}

// ============================================
// حذف Sidebar پیش‌فرض WC در صفحات فروشگاه
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
// حذف Notice پیش‌فرض WC (خودمون می‌سازیم)
// ============================================
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

// ============================================
// حذف Breadcrumb پیش‌فرض (خودمون می‌سازیم)
// ============================================
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

// ============================================
// اضافه کردن Wrap به محتوا
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
// تب‌های محصول - سفارشی‌سازی
// ============================================
add_filter('woocommerce_product_tabs', 'gpds_customize_product_tabs');

function gpds_customize_product_tabs($tabs) {
    // تغییر عنوان‌ها
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
// حذف لینک "افزودن به سبد" از Loop (خودمون داریم)
// ============================================
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

// ============================================
// حذف Sale Flash (خودمون نشون می‌دیم)
// ============================================
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);

// ============================================
// AJAX: افزودن به سبد خرید از Loop
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
    
    // فقط محصولات ساده
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