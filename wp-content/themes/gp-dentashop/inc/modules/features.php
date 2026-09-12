<?php
/**
 * Features Module - آیکون‌های سرویس
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

/**
 * لیست آیکون‌های سرویس پیش‌فرض
 */
function gpds_get_features() {
    return apply_filters('gpds_features', [
        [
            'icon'  => 'rocket',
            'title' => 'ارسال سریع',
            'url'   => '#',
        ],
        [
            'icon'  => 'shield',
            'title' => 'ضمانت اصالت',
            'url'   => '#',
        ],
        [
            'icon'  => 'refresh',
            'title' => 'بازگشت ۷ روزه',
            'url'   => '#',
        ],
        [
            'icon'  => 'headphones',
            'title' => 'پشتیبانی ۲۴/۷',
            'url'   => '#',
        ],
        [
            'icon'  => 'credit-card',
            'title' => 'خرید اقساطی',
            'url'   => '#',
        ],
        [
            'icon'  => 'location',
            'title' => 'پرداخت در محل',
            'url'   => '#',
        ],
        [
            'icon'  => 'percent',
            'title' => 'تخفیف ویژه',
            'url'   => '#',
        ],
        [
            'icon'  => 'star',
            'title' => 'ویژه اعضا',
            'url'   => '#',
        ],
    ]);
}