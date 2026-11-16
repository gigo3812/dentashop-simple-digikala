
<?php
/**
 * GP_DentaShop - Thank You Hooks
 */

defined('ABSPATH') || exit;

// حذف عنوان پیش‌فرض قالب در صفحه تشکر
add_filter('generate_show_title', function ($show) {
    if (
        function_exists('is_wc_endpoint_url') &&
        is_wc_endpoint_url('order-received')
    ) {
        return false;
    }

    return $show;
}, 20);
