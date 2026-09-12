<?php
/**
 * Setup - راه‌اندازی قالب
 */

if (!defined('ABSPATH')) exit;

// ============================================
// پشتیبانی‌های قالب
// ============================================
add_action('after_setup_theme', 'gpds_theme_setup', 20);

function gpds_theme_setup() {
    
    // WooCommerce
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    // پایه
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', [
        'search-form', 'comment-form', 'comment-list',
        'gallery', 'caption', 'style', 'script',
    ]);
    
    // اندازه تصاویر بهینه (برای کارت محصولات)
    add_image_size('gpds-card',     300, 300, true);
    add_image_size('gpds-card-sm',  150, 150, true);
    add_image_size('gpds-banner',  1200, 300, true);
    add_image_size('gpds-cat',      200, 200, true);
    add_image_size('gpds-story',    120, 120, true);
    
    // حذف ساختارهای پیش‌فرض GP که نمی‌خوایم
    remove_action('generate_after_header', 'generate_do_header_widget_area');
    remove_action('generate_before_footer', 'generate_do_footer_widget_area');
}

// ============================================
// ثبت منوها
// ============================================
add_action('after_setup_theme', function() {
    register_nav_menus([
        'gpds_main_menu'   => 'منوی اصلی',
        'gpds_top_menu'    => 'منوی بالایی (سوپرمارکت، تخفیف‌ها، ...)',
        'gpds_mobile_menu' => 'منوی موبایل',
        'gpds_footer_1'    => 'فوتر - ستون ۱',
        'gpds_footer_2'    => 'فوتر - ستون ۲',
        'gpds_footer_3'    => 'فوتر - ستون ۳',
        'gpds_footer_4'    => 'فوتر - ستون ۴',
    ]);
});

// ============================================
// ثبت Sidebar (اگه لازم داشتی)
// ============================================
add_action('widgets_init', function() {
    register_sidebar([
        'name'          => 'سایدبار فروشگاه',
        'id'            => 'gpds-shop-sidebar',
        'before_widget' => '<div id="%1$s" class="gpds-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="gpds-widget-title">',
        'after_title'   => '</h3>',
    ]);
});

// ============================================
// Override قالب‌های GeneratePress
// ============================================
add_filter('generate_sidebar_layout', function($layout) {
    if (is_front_page()) {
        return 'no-sidebar';
    }
    return $layout;
});

// ============================================
// Wrapper Class
// ============================================
add_filter('generate_attributes', function($attrs, $context) {
    if ($context === 'site-grid-container') {
        $attrs['class'][] = 'gpds-container';
    }
    return $attrs;
}, 10, 2);