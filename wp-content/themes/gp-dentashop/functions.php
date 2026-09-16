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


// ============================================
// بارگذاری لودر اصلی
// ============================================
require_once GPDS_INC . '/loader.php';