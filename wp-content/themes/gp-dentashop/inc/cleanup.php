<?php
/**
 * Cleanup - حذف تمام کندکننده‌ها
 * 
 * هدف: سبک‌سازی کامل head و assets
 */

if (!defined('ABSPATH')) exit;

// ============================================
// 1. حذف Meta Tags اضافی
// ============================================
add_action('init', function() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
});

// ============================================
// 2. حذف Emoji کامل
// ============================================
add_action('init', function() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
});

add_filter('emoji_svg_url', '__return_false');

// ============================================
// 3. حذف jQuery Migrate
// ============================================
add_action('wp_default_scripts', function($scripts) {
    if (is_admin() || empty($scripts->registered['jquery'])) return;
    $scripts->registered['jquery']->deps = array_diff(
        $scripts->registered['jquery']->deps,
        ['jquery-migrate']
    );
});

// ============================================
// 4. حذف Dashicons برای غیرلاگین
// ============================================
add_action('wp_enqueue_scripts', function() {
    if (!is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
}, 100);

// ============================================
// 5. حذف Block Library CSS
// ============================================
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-blocks-style');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('global-styles');
}, 100);

// ============================================
// 6. حذف WooCommerce Assets در صفحات غیرمرتبط
// ============================================
add_action('wp_enqueue_scripts', 'gpds_dequeue_wc_on_non_wc_pages', 99);

function gpds_dequeue_wc_on_non_wc_pages() {
    if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
        return;
    }
    
    // CSS
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('woocommerce-inline');
    
    // JS
    wp_dequeue_script('wc-add-to-cart');
    wp_dequeue_script('woocommerce');
    wp_dequeue_script('wc-cart-fragments');
    wp_dequeue_script('wc-order-attribution');
    wp_dequeue_script('sourcebuster-js');
    wp_dequeue_script('wc-jquery-blockui');
    wp_dequeue_script('wc-js-cookie');
    wp_dequeue_script('wc-add-to-cart-variation');
}

// ============================================
// 7. غیرفعال کردن XML-RPC و Pingback
// ============================================
add_filter('xmlrpc_enabled', '__return_false');

add_filter('xmlrpc_methods', function($methods) {
    unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
    return $methods;
});

add_filter('wp_headers', function($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});

// ============================================
// 8. حذف Query Strings از Static
// ============================================
add_filter('script_loader_src', 'gpds_remove_query_strings', 15);
add_filter('style_loader_src',  'gpds_remove_query_strings', 15);

function gpds_remove_query_strings($src) {
    if (strpos($src, 'ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

// ============================================
// 9. حذف Embeds
// ============================================
add_action('init', function() {
    remove_action('rest_api_init', 'wp_oembed_register_route');
    add_filter('embed_oembed_discover', '__return_false');
    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
});

add_action('wp_footer', function() {
    wp_deregister_script('wp-embed');
});

// ============================================
// 10. Heartbeat کاهش فرکانس
// ============================================
add_filter('heartbeat_settings', function($settings) {
    $settings['interval'] = 60;
    return $settings;
});

// ============================================
// 11. حذف Emoji Styles
// ============================================
add_action('init', function() {
    remove_action('wp_print_styles', 'print_emoji_styles');
});

// ============================================
// 12. کاهش Revisions
// ============================================
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 5);
}

// ============================================
// 13. حذف wc-blocks enqueue
// ============================================
add_filter('woocommerce_blocks_enqueue_block_styles_after', '__return_false');