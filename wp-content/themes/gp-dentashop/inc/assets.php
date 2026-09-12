<?php
/**
 * Assets - مدیریت بهینه CSS/JS
 * 
 * استراتژی: فقط چیزی که لازمه لود بشه
 */

if (!defined('ABSPATH')) exit;

// ============================================
// Frontend Assets
// ============================================
add_action('wp_enqueue_scripts', 'gpds_enqueue_frontend', 20);

function gpds_enqueue_frontend() {
    
    // ============================================
    // CSS پایه (همیشه)
    // ============================================
    wp_enqueue_style('gpds-fonts',    GPDS_ASSETS . '/fonts/fonts.css', [], GPDS_VERSION);
    wp_enqueue_style('gpds-main',     GPDS_ASSETS . '/css/main.css',    ['gpds-fonts'], GPDS_VERSION);
    wp_enqueue_style('gpds-header',   GPDS_ASSETS . '/css/header.css',  ['gpds-main'], GPDS_VERSION);
    wp_enqueue_style('gpds-footer',   GPDS_ASSETS . '/css/footer.css',  ['gpds-main'], GPDS_VERSION);
    wp_enqueue_style('gpds-responsive', GPDS_ASSETS . '/css/responsive.css', ['gpds-main'], GPDS_VERSION);
    
    // ============================================
    // CSS صفحه اصلی
    // ============================================
    if (is_front_page() || is_page_template('page-templates/template-home.php')) {
        wp_enqueue_style('gpds-home', GPDS_ASSETS . '/css/home.css', ['gpds-main'], GPDS_VERSION);
    }
    
    // ============================================
    // CSS کارت محصول (همه صفحاتی که محصول نشون می‌دن)
    // ============================================
    if (gpds_page_shows_products()) {
        wp_enqueue_style('gpds-product-card', GPDS_ASSETS . '/css/product-card.css', ['gpds-main'], GPDS_VERSION);
    }
    
    // ============================================
    // CSS ووکامرس (فقط صفحات مرتبط)
    // ============================================
    if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
        wp_enqueue_style('gpds-woo', GPDS_ASSETS . '/css/woocommerce.css', ['gpds-main'], GPDS_VERSION);
    }
    
    // ============================================
    // Swiper (فقط صفحاتی که اسلایدر دارن)
    // ============================================
    if (gpds_page_has_slider()) {
        wp_enqueue_style('gpds-swiper', GPDS_ASSETS . '/vendor/swiper/swiper-bundle.min.css', [], '11.0.0');
        wp_enqueue_script('gpds-swiper', GPDS_ASSETS . '/vendor/swiper/swiper-bundle.min.js', [], '11.0.0', true);
    }
    
    // ============================================
    // JS اصلی (همیشه)
    // ============================================
    wp_enqueue_script('gpds-main', GPDS_ASSETS . '/js/main.js', ['jquery'], GPDS_VERSION, true);
    
    // ============================================
    // JS مخصوص صفحه اصلی
    // ============================================
    if (is_front_page() || is_page_template('page-templates/template-home.php')) {
        wp_enqueue_script('gpds-slider', GPDS_ASSETS . '/js/slider.js', ['gpds-swiper', 'gpds-main'], GPDS_VERSION, true);
    }
    
    // ============================================
    // JS جستجو
    // ============================================
    wp_enqueue_script('gpds-search', GPDS_ASSETS . '/js/search.js', ['gpds-main'], GPDS_VERSION, true);
    
    // ============================================
    // JS سبد خرید
    // ============================================
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('gpds-cart', GPDS_ASSETS . '/js/cart.js', ['gpds-main'], GPDS_VERSION, true);
    }
    
    // ============================================
    // Data به JS
    // ============================================
    wp_localize_script('gpds-main', 'GPDS', [
        'ajaxUrl'     => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('gpds_nonce'),
        'homeUrl'     => home_url('/'),
        'cartUrl'     => function_exists('wc_get_cart_url') ? wc_get_cart_url() : '',
        'checkoutUrl' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '',
        'isLoggedIn'  => is_user_logged_in(),
        'currency'    => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : 'تومان',
        'i18n'        => [
            'searchLoading'  => 'در حال جستجو...',
            'searchEmpty'    => 'نتیجه‌ای یافت نشد',
            'cartEmpty'      => 'سبد خرید شما خالی است',
            'addedToCart'    => 'به سبد خرید اضافه شد',
            'error'          => 'خطایی رخ داد، دوباره تلاش کنید',
        ],
    ]);
}

// ============================================
// Defer اسکریپت‌های غیرحیاتی
// ============================================
add_filter('script_loader_tag', function($tag, $handle) {
    if (is_admin()) return $tag;
    
    $defer = ['gpds-search', 'gpds-cart', 'gpds-slider'];
    
    if (in_array($handle, $defer, true)) {
        return str_replace(' src=', ' defer src=', $tag);
    }
    
    return $tag;
}, 10, 2);

// ============================================
// Preload فونت‌ها
// ============================================
add_action('wp_head', 'gpds_preload_fonts', 1);

function gpds_preload_fonts() {
    $fonts = [
        'dana-regular.woff',
        'dana-medium.woff',
        'dana-demibold.woff',
    ];
    
    foreach ($fonts as $font) {
        printf(
            '<link rel="preload" href="%s" as="font" type="font/woff" crossorigin>' . "\n",
            esc_url(GPDS_ASSETS . '/fonts/woff/' . $font)
        );
    }
}

// ============================================
// توابع کمکی شرطی
// ============================================
function gpds_page_shows_products() {
    return is_front_page()
        || is_shop()
        || is_product_category()
        || is_product_tag()
        || is_product()
        || is_page_template('page-templates/template-home.php')
        || is_home()
        || is_archive();
}

function gpds_page_has_slider() {
    return is_front_page()
        || is_page_template('page-templates/template-home.php')
        || is_shop()
        || is_product();
}