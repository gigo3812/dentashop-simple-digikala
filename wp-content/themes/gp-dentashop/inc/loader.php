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
// برای غیرفعال کردن، فقط # بذار قبلش
// ============================================

// Ajax (جستجو، سبد خرید)
require_once GPDS_INC . '/ajax.php';

// Customizer (تنظیمات قالب)
require_once GPDS_INC . '/customizer.php';

// ============================================
// ماژول‌های نمایشی (فقط اگه لازم داری)
// ============================================
require_once GPDS_INC . '/modules/header.php';
require_once GPDS_INC . '/modules/footer.php';

// ماژول‌های صفحه اصلی
require_once GPDS_INC . '/modules/slider.php';
require_once GPDS_INC . '/modules/stories.php';
require_once GPDS_INC . '/modules/features.php';
require_once GPDS_INC . '/modules/categories.php';
require_once GPDS_INC . '/modules/products.php';
require_once GPDS_INC . '/modules/brands.php';
require_once GPDS_INC . '/modules/banners.php';
require_once GPDS_INC . '/modules/blog.php';