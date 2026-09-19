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
                ['title' => 'درباره ما', 'url' => home_url('/about-us/')],
                ['title' => 'راه های ارتباطی', 'url' => home_url('/contact/')],
            ],
        ],
        [
            'title' => 'خدمات مشتریان',
            'links' => [
                ['title' => 'پاسخ به پرسش‌ها', 'url' => home_url('/faq/')],
                ['title' => 'خرید اقساطی', 'url' => home_url('/installment-payment/')],
            ],
        ],
        [
            'title' => 'دفاتر مرکزی',
            'links' => gpds_get_offices_footer_links(),  // ← این خط
        ],
    ]);
}

/**
 * گرفتن لینک دفاتر برای فوتر (خودکار از CPT)
 */
function gpds_get_offices_footer_links()
{
    // اگه CPT دفاتر فعال نیست
    if (!post_type_exists('office')) {
        return [];
    }

    $offices = get_posts([
        'post_type'      => 'office',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ]);

    if (empty($offices)) {
        return [];
    }

    $links = [];

    foreach ($offices as $office) {
        // گرفتن شهر از متادیتا
        $city = get_post_meta($office->ID, '_office_city', true);
        $province = get_post_meta($office->ID, '_office_province', true);

        // عنوان: «شهر» یا «استان - شهر» یا «عنوان دفتر»
        $title = '';
        if ($city && $province) {
            $title = $city;  // فقط شهر
        } elseif ($city) {
            $title = $city;
        } else {
            $title = $office->post_title;
        }

        $links[] = [
            'title' => $title,
            'url'   => get_permalink($office->ID),
        ];
    }

    // دکمه «همه دفاتر» رو آخر اضافه کن (اختیاری)
    $links[] = [
        'title' => 'مشاهده همه دفاتر ←',
        'url'   => get_post_type_archive_link('office'),
    ];

    return $links;
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
