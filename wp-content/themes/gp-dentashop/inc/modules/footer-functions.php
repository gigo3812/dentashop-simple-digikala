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
function gpds_get_footer_services()
{
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
function gpds_get_footer_links()
{
    return apply_filters('gpds_footer_links', [
        [
            'title' => 'با دنتاشاپ',
            'links' => [
                ['title' => 'درباره ما', 'url' => '#'],
                ['title' => 'راه های ارتباطی', 'url' => '#'],
                ['title' => 'همکاری با ما', 'url' => '#'],
            ],
        ],
        [
            'title' => 'خدمات مشتریان',
            'links' => [
                ['title' => 'پاسخ به پرسش‌ها', 'url' => '#'],
                ['title' => 'خرید اقساطی', 'url' => '#'],
            ],
        ],
        [
            'title' => 'دفاتر مرکزی',
            'links' => [
                ['title' => 'استان خراسان رضوی', 'url' => '#'],
                ['title' => 'استان خراسان جنوبی', 'url' => '#'],
                ['title' => 'سیستان و بلوچستان', 'url' => '#'],
            ],
        ],
    ]);
}

// ============================================
// شبکه‌های اجتماعی
// ============================================
function gpds_get_socials()
{
    return apply_filters('gpds_socials', [
        ['icon' => 'instagram', 'url' => '#', 'title' => 'اینستاگرام'],
        ['icon' => 'telegram',  'url' => '#', 'title' => 'تلگرام'],
        ['icon' => 'twitter',   'url' => '#', 'title' => 'توییتر'],
        ['icon' => 'youtube',   'url' => '#', 'title' => 'یوتیوب'],
    ]);
}
