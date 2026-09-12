<?php
/**
 * Footer Helper Functions
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// ============================================
// خدمات فوتر
// ============================================
function gpds_get_footer_services() {
    return apply_filters('gpds_footer_services', [
        [
            'icon'  => 'truck',
            'title' => 'ارسال سریع',
            'subtitle' => 'به سراسر کشور',
        ],
        [
            'icon'  => 'headphones',
            'title' => 'پشتیبانی ۲۴/۷',
            'subtitle' => 'همیشه در دسترس',
        ],
        [
            'icon'  => 'refresh',
            'title' => 'بازگشت وجه',
            'subtitle' => 'تا ۷ روز',
        ],
        [
            'icon'  => 'shield',
            'title' => 'ضمانت اصالت',
            'subtitle' => 'کالای اورجینال',
        ],
        [
            'icon'  => 'credit-card',
            'title' => 'پرداخت در محل',
            'subtitle' => 'امن و آسان',
        ],
    ]);
}

// ============================================
// لینک‌های فوتر پیش‌فرض
// ============================================
function gpds_get_footer_links() {
    return apply_filters('gpds_footer_links', [
        [
            'title' => 'با دنتاشاپ',
            'links' => [
                ['title' => 'اتاق خبر', 'url' => '#'],
                ['title' => 'درباره ما', 'url' => '#'],
                ['title' => 'تماس با ما', 'url' => '#'],
                ['title' => 'فرصت‌های شغلی', 'url' => '#'],
                ['title' => 'همکاری با ما', 'url' => '#'],
                ['title' => 'فروش در دنتاشاپ', 'url' => '#'],
            ],
        ],
        [
            'title' => 'خدمات مشتریان',
            'links' => [
                ['title' => 'پاسخ به پرسش‌ها', 'url' => '#'],
                ['title' => 'شرایط استفاده', 'url' => '#'],
                ['title' => 'حریم خصوصی', 'url' => '#'],
                ['title' => 'گزارش باگ', 'url' => '#'],
                ['title' => 'رویه بازگرداندن', 'url' => '#'],
                ['title' => 'خرید اقساطی', 'url' => '#'],
            ],
        ],
        [
            'title' => 'راهنمای خرید',
            'links' => [
                ['title' => 'راهنمای خرید موبایل', 'url' => '#'],
                ['title' => 'راهنمای خرید لپ‌تاپ', 'url' => '#'],
                ['title' => 'راهنمای خرید تبلت', 'url' => '#'],
                ['title' => 'راهنمای خرید هدفون', 'url' => '#'],
                ['title' => 'راهنمای خرید ساعت', 'url' => '#'],
                ['title' => 'بهترین محصولات', 'url' => '#'],
            ],
        ],
    ]);
}

// ============================================
// شبکه‌های اجتماعی
// ============================================
function gpds_get_socials() {
    return apply_filters('gpds_socials', [
        ['icon' => 'instagram', 'url' => '#', 'title' => 'اینستاگرام'],
        ['icon' => 'telegram',  'url' => '#', 'title' => 'تلگرام'],
        ['icon' => 'twitter',   'url' => '#', 'title' => 'توییتر'],
        ['icon' => 'youtube',   'url' => '#', 'title' => 'یوتیوب'],
    ]);
}

// ============================================
// دریافت ایمیل خبرنامه
// ============================================
add_action('wp_ajax_gpds_newsletter',        'gpds_ajax_newsletter');
add_action('wp_ajax_nopriv_gpds_newsletter', 'gpds_ajax_newsletter');

function gpds_ajax_newsletter() {
    check_ajax_referer('gpds_nonce', 'nonce');
    
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'ایمیل معتبر وارد کنید']);
    }
    
    // اینجا می‌تونی به سرویس خبرنامه وصل کنی
    do_action('gpds_newsletter_subscribe', $email);
    
    wp_send_json_success([
        'message' => 'با موفقیت در خبرنامه ثبت شدید!',
    ]);
}