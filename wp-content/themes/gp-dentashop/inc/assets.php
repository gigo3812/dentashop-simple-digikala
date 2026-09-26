<?php

/**
 * Assets - مدیریت بهینه CSS/JS
 * 
 * استراتژی:
 *   - CSS پایه: gpds-base.min.css (همه صفحات)
 *   - CSS شرطی: بر اساس نوع صفحه
 *   - JS: شرطی + defer
 *   - همه فایل‌ها minified
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

    // تشخیص نوع صفحه (یک‌بار، برای استفاده در کل تابع)
    $page = gpds_detect_page_type();

    // ============================================
    // CSS پایه (همه صفحات)
    // ============================================
    wp_enqueue_style('gpds-fonts', $assets . '/fonts/fonts.min.css', [], $ver);
    wp_enqueue_style('gpds-base',  $assets . '/css/gpds-base.min.css', ['gpds-fonts'], $ver);

    // ============================================
    // 🎯 Home Styles — فقط صفحه اصلی
    // ============================================
    if ($page['has_slider']) {
        wp_enqueue_style('gpds-home', $assets . '/css/gpds-home.min.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 Product Card (کارت محصول)
    // ============================================
    if ($page['has_product_card']) {
        wp_enqueue_style('gpds-product-card', $assets . '/css/product-card.min.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 WooCommerce Shop (فقط صفحات shop/product)
    // ============================================
    if ($page['is_shop']) {
        wp_enqueue_style('gpds-woo', $assets . '/css/woocommerce.min.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 Cart (فقط /cart/)
    // ============================================
    if ($page['is_cart']) {
        wp_enqueue_style('gpds-cart', $assets . '/css/woocommerce-cart.min.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 Checkout (فقط /checkout/)
    // ============================================
    if ($page['is_checkout']) {
        wp_enqueue_style('gpds-checkout', $assets . '/css/woocommerce-checkout.min.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 Account (فقط /my-account/)
    // ============================================
    if ($page['is_account']) {
        wp_enqueue_style('gpds-account', $assets . '/css/woocommerce-account.min.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 404
    // ============================================
    if (is_404()) {
        wp_enqueue_style('gpds-404', $assets . '/css/404.min.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 Blog (صفحه اصلی + آرشیو + مقاله + جستجو)
    // ============================================
    if ($page['is_blog']) {
        wp_enqueue_style('gpds-blog', $assets . '/css/blog.min.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 Offices (دفاتر)
    // ============================================
    if ($page['is_office']) {
        wp_enqueue_style('gpds-offices', $assets . '/css/offices.min.css', ['gpds-base'], $ver);
    }

    // ============================================
    // 🎯 Slider CSS (Swiper) — فقط صفحه اصلی
    // (از قبل minified است — دست نزن)
    // ============================================
    if ($page['has_slider']) {
        wp_enqueue_style('gpds-swiper', GPDS_ASSETS . '/vendor/swiper/swiper-bundle.min.css', [], '11.0.0');
    }

    // ============================================
    // 🎯 JS پایه — core.min.js (همه صفحات)
    // ============================================
    wp_enqueue_script('gpds-core', $assets . '/js/core.min.js', [], $ver, true);

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
            'searchLoading' => 'در حال جستجو...',
            'searchEmpty'   => 'نتیجه‌ای یافت نشد',
            'cartEmpty'     => 'سبد خرید شما خالی است',
            'addedToCart'   => 'به سبد خرید اضافه شد',
            'error'         => 'خطایی رخ داد، دوباره تلاش کنید',
        ],
    ]);

    // ============================================
    // 🎯 Home Scripts (Swiper JS + home.min.js) — فقط صفحه اصلی
    // ============================================
    if ($page['has_slider']) {
        wp_enqueue_script('gpds-swiper', GPDS_ASSETS . '/vendor/swiper/swiper-bundle.min.js', [], '11.0.0', true);
        wp_enqueue_script('gpds-home',   $assets . '/js/home.min.js', ['gpds-swiper', 'gpds-core'], $ver, true);
    }

    // ============================================
    // 🎯 Products JS — فقط صفحات shop/product
    // ============================================
    if ($page['is_shop']) {
        wp_enqueue_script('gpds-products', $assets . '/js/products.min.js', ['gpds-core'], $ver, true);
    }

    // ============================================
    // 🎯 Blog JS — فقط مقاله تک
    // ============================================
    if (is_singular('post')) {
        wp_enqueue_script('gpds-blog', $assets . '/js/blog.min.js', ['gpds-core'], $ver, true);
    }

    // ============================================
    // 🎯 Offices JS — فقط دفتر تک
    // ============================================
    if (is_singular('office')) {
        wp_enqueue_script('gpds-offices', $assets . '/js/offices.min.js', ['gpds-core'], $ver, true);
    }
}


// ============================================
// تشخیص نوع صفحه (یک‌بار در هر request)
// ============================================
function gpds_detect_page_type()
{
    static $cache = null;
    if ($cache !== null) return $cache;

    // --- WooCommerce Shop / Product ---
    $is_shop = false;
    if (function_exists('is_shop')) {
        $is_shop = is_shop()
            || is_product()
            || is_product_category()
            || is_product_tag()
            || is_tax('product_brand')
            || get_query_var('gpds_view') === 'all_brands';
    }

    // --- WooCommerce Cart ---
    $is_cart = function_exists('is_cart') && is_cart();

    // --- WooCommerce Checkout (نه thank-you) ---
    $is_checkout = function_exists('is_checkout')
        && is_checkout()
        && (!function_exists('is_order_received_page') || !is_order_received_page());

    // --- WooCommerce Account ---
    $is_account = function_exists('is_account_page') && is_account_page();

    // --- Blog (شامل صفحه اصلی سایت) ---
    // نکته: is_front_page() اضافه شد تا صفحه اصلی استاتیک هم blog.css بگیرد
    $is_blog = is_home()
        || is_front_page()
        || is_singular('post')
        || is_category()
        || is_tag()
        || is_author()
        || is_date()
        || is_search();

    // --- Offices ---
    $is_office = is_post_type_archive('office') || is_singular('office');

    // --- Slider (فقط صفحه اصلی) ---
    $has_slider = is_front_page()
        || is_page_template('page-templates/template-home.php');

    // --- Product Card ---
    $has_product_card = $is_shop
        || is_front_page()
        || is_home()
        || is_page_template('page-templates/template-home.php')
        || is_archive();

    $cache = [
        'is_shop'          => $is_shop,
        'is_cart'          => $is_cart,
        'is_checkout'      => $is_checkout,
        'is_account'       => $is_account,
        'is_blog'          => $is_blog,
        'is_office'        => $is_office,
        'has_slider'       => $has_slider,
        'has_product_card' => $has_product_card,
    ];

    return $cache;
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
// Preload فونت‌ها (فقط وزن‌های حیاتی)
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
// Hide WordPress Admin Bar on frontend
// ============================================
add_filter('show_admin_bar', '__return_false');