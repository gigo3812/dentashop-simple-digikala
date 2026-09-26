<?php

/**
 * Assets - مدیریت بهینه CSS/JS
 * 
 * استراتژی:
 *   - CSS پایه: 1 فایل (gpds-base.css = main + header + home + footer)
 *   - بقیه CSS: شرطی
 *   - JS: شرطی + defer
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// ============================================
// Frontend Assets
// ============================================
add_action('wp_enqueue_scripts', 'gpds_enqueue_frontend', 20);
function gpds_enqueue_frontend()
{
    $assets = GPDS_ASSETS;
    $ver    = GPDS_VERSION;

    // ============================================
    // CSS پایه (بدون تغییر)
    // ============================================
    wp_enqueue_style('gpds-fonts', $assets . '/fonts/fonts.css', [], $ver);
    wp_enqueue_style('gpds-base',  $assets . '/css/gpds-base.css', ['gpds-fonts'], $ver);

    if (gpds_page_shows_products()) {
        wp_enqueue_style('gpds-product-card', $assets . '/css/product-card.css', ['gpds-base'], $ver);
    }

    if (is_woocommerce() || is_cart() || is_checkout() || is_account_page() || get_query_var('gpds_view') === 'all_brands') {
        wp_enqueue_style('gpds-woo', $assets . '/css/woocommerce.css', ['gpds-base'], $ver);
    }

    if (function_exists('is_account_page') && is_account_page()) {
        wp_enqueue_style('gpds-account', $assets . '/css/woocommerce-account.css', ['gpds-base'], $ver);
    }

    if (function_exists('is_cart') && is_cart()) {
        wp_enqueue_style('gpds-cart', $assets . '/css/woocommerce-cart.css', ['gpds-base'], $ver);
    }

    if (function_exists('is_checkout') && is_checkout() && !is_order_received_page()) {
        wp_enqueue_style('gpds-checkout', $assets . '/css/woocommerce-checkout.css', ['gpds-base'], $ver);
    }

    if (is_404()) {
        wp_enqueue_style('gpds-404', $assets . '/css/404.css', ['gpds-base'], $ver);
    }

    if (is_home() || is_singular('post') || is_category() || is_tag() || is_author() || is_date() || is_search()) {
        wp_enqueue_style('gpds-blog', $assets . '/css/blog.css', ['gpds-base'], $ver);
    }

    if (is_post_type_archive('office') || is_singular('office')) {
        wp_enqueue_style('gpds-offices', $assets . '/css/offices.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 JS پایه — core.js (ادغام 4 فایل)
    // ============================================
    wp_enqueue_script('gpds-core', $assets . '/js/core.js', [], $ver, true);

    // Localize قبل از core
    wp_localize_script('gpds-core', 'GPDS', [
        'ajaxUrl'     => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('gpds_nonce'),
        'homeUrl'     => home_url('/'),
        'cartUrl'     => function_exists('wc_get_cart_url') ? wc_get_cart_url() : '',
        'checkoutUrl' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '',
        'isLoggedIn'  => is_user_logged_in(),
        'themeUri'    => GPDS_URI,
        'currency'    => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : 'تومان',
        'i18n'        => [
            'searchLoading'  => 'در حال جستجو...',
            'searchEmpty'    => 'نتیجه‌ای یافت نشد',
            'cartEmpty'      => 'سبد خرید شما خالی است',
            'addedToCart'    => 'به سبد خرید اضافه شد',
            'error'          => 'خطایی رخ داد، دوباره تلاش کنید',
        ],
    ]);

    // ============================================
    // 🎯 Slider — فقط صفحات با اسلایدر
    // ============================================
    if (gpds_page_has_slider()) {
        wp_enqueue_style('gpds-swiper',  GPDS_ASSETS . '/vendor/swiper/swiper-bundle.min.css', [], '11.0.0');
        wp_enqueue_script('gpds-swiper', GPDS_ASSETS . '/vendor/swiper/swiper-bundle.min.js',  [], '11.0.0', true);
        wp_enqueue_script('gpds-home',   $assets . '/js/home.js', ['gpds-swiper', 'gpds-core'], $ver, true);
    }

    // ============================================
    // 🎯 Products — ادغام product + shop
    // ============================================
    $is_products_page = is_product()
        || is_shop()
        || is_product_category()
        || is_product_tag()
        || is_tax('product_brand')
        || get_query_var('gpds_view');

    if ($is_products_page) {
        wp_enqueue_script('gpds-products', $assets . '/js/products.js', ['gpds-core'], $ver, true);
    }

    // ============================================
    // 🎯 Blog
    // ============================================
    if (is_singular('post')) {
        wp_enqueue_script('gpds-blog', $assets . '/js/blog.js', ['gpds-core'], $ver, true);
    }

    // ============================================
    // 🎯 Offices
    // ============================================
    if (is_singular('office')) {
        wp_enqueue_script('gpds-offices', $assets . '/js/offices.js', ['gpds-core'], $ver, true);
    }
}
// ============================================
// Defer اسکریپت‌های غیرحیاتی
// ============================================
add_filter('script_loader_tag', function ($tag, $handle) {
    if (is_admin()) return $tag;

    $defer = [
        'gpds-core',
        'gpds-home',
        'gpds-products',
        'gpds-blog',
        'gpds-offices',
    ];

    if (in_array($handle, $defer, true)) {
        return str_replace(' src=', ' defer src=', $tag);
    }

    return $tag;
}, 10, 2);

// ============================================
// Preload فونت‌ها
// ============================================
add_action('wp_head', 'gpds_preload_fonts', 1);

function gpds_preload_fonts()
{
    $fonts = ['dana-regular.woff', 'dana-bold.woff'];

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
function gpds_page_shows_products()
{
    return is_front_page()
        || is_shop()
        || is_product_category()
        || is_product_tag()
        || is_product()
        || is_tax('product_brand')
        || get_query_var('gpds_view')
        || is_page_template('page-templates/template-home.php')
        || is_home()
        || is_archive();
}

function gpds_page_has_slider()
{
    return is_front_page()
        || is_page_template('page-templates/template-home.php')
        || is_shop()
        || is_product();
}





/**
 * Hide WordPress Admin Bar on frontend
 */
add_filter('show_admin_bar', '__return_false');
