<?php
/**
 * Loader - بارگذاری ماژولار
 * 
 * این فایل تعیین می‌کنه چه چیزهایی لود بشن
 * برای غیرفعال کردن یک ماژول، فقط کامنت کن
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// ============================================
// هسته (همیشه لود می‌شن)
// ============================================
require_once GPDS_INC . '/setup.php';
require_once GPDS_INC . '/cleanup.php';
require_once GPDS_INC . '/assets.php';
require_once GPDS_INC . '/template-tags.php';

// ============================================
// ماژول‌های اختیاری
// برای غیرفعال کردن، فقط کامنت کن
// ============================================

// Ajax (جستجو، سبد خرید، خبرنامه)
require_once GPDS_INC . '/ajax.php';

// Customizer (تنظیمات قالب)
require_once GPDS_INC . '/customizer.php';

// ============================================
// ماژول‌های نمایشی
// ============================================

// هدر و فوتر
require_once GPDS_INC . '/modules/header.php';
require_once GPDS_INC . '/modules/header-functions.php';
require_once GPDS_INC . '/modules/footer.php';
require_once GPDS_INC . '/modules/footer-functions.php';

// ============================================
// ماژول‌های صفحه اصلی
// ============================================
require_once GPDS_INC . '/modules/slider.php';
require_once GPDS_INC . '/modules/stories.php';
require_once GPDS_INC . '/modules/features.php';
require_once GPDS_INC . '/modules/brands.php';
require_once GPDS_INC . '/modules/categories.php';
require_once GPDS_INC . '/modules/products.php';
require_once GPDS_INC . '/modules/banners.php';
require_once GPDS_INC . '/modules/blog.php';
require_once GPDS_INC . '/modules/special-offer.php';


require_once GPDS_INC . '/modules/brands-filters.php';

require_once GPDS_INC . '/modules/offices.php';

// ============================================
// ماژول WooCommerce (فقط اگه ووکامرس فعاله)
// ============================================
if (class_exists('WooCommerce')) {
    require_once GPDS_INC . '/modules/woocommerce.php';
}