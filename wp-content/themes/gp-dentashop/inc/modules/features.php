<?php

/**
 * Get Home Features
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('gpds_get_features')) {
    function gpds_get_features()
    {
        return [
            [
                'icon'  => 'rocket',
                'title' => 'ارسال سریع',
                'url'   => home_url('/fast-shipping'),
            ],
            [
                'icon'  => 'shield',
                'title' => 'ضمانت اصالت',
                'url'   => home_url('/authenticity-guarantee'),
            ],
            [
                'icon'  => 'refresh',
                'title' => 'بازگشت ۷ روزه',
                'url'   => home_url('/returns'),
            ],
            [
                'icon'  => 'headphones',
                'title' => 'پشتیبانی ۲۴/۷',
                'url'   => home_url('/support'),
            ],
            [
                'icon'  => 'credit-card',
                'title' => 'خرید اقساطی',
                'url'   => home_url('/installment-payment'),
            ],
            [
                'icon'  => 'location',
                'title' => 'پرداخت در محل',
                'url'   => home_url('/cash-on-delivery'),
            ],
            [
                'icon'  => 'percent',
                'title' => 'تخفیف ویژه',
                'url'   => home_url('/special-offers'),
            ],
            [
                'icon'  => 'star',
                'title' => 'ویژه اعضا',
                'url'   => home_url('/membership'),
            ],
        ];
    }
}
