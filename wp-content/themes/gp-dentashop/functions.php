<?php
/**
 * GP DentaShop - Entry Point
 * 
 * این فایل فقط یه نقش داره: بارگذاری loader
 * تمام منطق برنامه در inc/loader.php هست
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================
// ثابت‌های اصلی قالب
// ============================================
define('GPDS_VERSION', '1.0.0');
define('GPDS_DIR',     get_stylesheet_directory());
define('GPDS_URI',     get_stylesheet_directory_uri());
define('GPDS_INC',     GPDS_DIR . '/inc');
define('GPDS_TPL',     GPDS_DIR . '/template-parts');
define('GPDS_ASSETS',  GPDS_URI . '/assets');


// موقت - بعداً حذف کن
add_action('template_redirect', function () {
    if (!is_cart() && !is_checkout() && !is_account_page()) return;
    
    // لاگ فایل‌های لود شده
    error_log('=== GPDS DEBUG ' . $_SERVER['REQUEST_URI'] . ' ===');
    error_log('Styles done: ' . implode(', ', $GLOBALS['wp_styles']->done));
    
    add_action('wp_footer', function () {
        $files = get_included_files();
        $theme_files = array_filter($files, function ($f) {
            return strpos($f, 'gp-dentashop') !== false 
                && (strpos($f, 'cart') !== false 
                    || strpos($f, 'checkout') !== false 
                    || strpos($f, 'woocommerce') !== false);
        });
        
        echo "\n<!-- GPDS TEMPLATES DEBUG:\n";
        foreach ($theme_files as $f) {
            echo "  " . str_replace(ABSPATH, '', $f) . "\n";
        }
        echo "-->\n";
    }, 999);
});


// ============================================
// بارگذاری لودر اصلی
// ============================================
require_once GPDS_INC . '/loader.php';