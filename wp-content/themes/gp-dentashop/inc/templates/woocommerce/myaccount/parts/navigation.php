<?php
/**
 * My Account - Navigation
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$endpoint = function_exists('WC') ? WC()->query->get_current_endpoint() : '';
$current = $endpoint ?: 'dashboard';

// آیکون‌ها
$icons = [
    'dashboard'       => 'dashboard',
    'orders'          => 'package',
    'downloads'       => 'refresh',
    'edit-address'    => 'location',
    'payment-methods' => 'credit-card',
    'edit-account'    => 'user',
    'customer-logout' => 'log-out',
];

// آیتم‌های منو
$items = [
    'dashboard'       => 'داشبورد',
    'orders'          => 'سفارش‌ها',
    'downloads'       => 'دانلودها',
    'edit-address'    => 'آدرس‌ها',
    'edit-account'    => 'جزئیات حساب',
    'customer-logout' => 'خروج',
];

// اضافه کردن روش‌های پرداخت اگه فعال باشه
if (function_exists('wc_get_account_menu_items')) {
    $woo_items = wc_get_account_menu_items();
    if (isset($woo_items['payment-methods'])) {
        $items = [
            'dashboard'       => 'داشبورد',
            'orders'          => 'سفارش‌ها',
            'downloads'       => 'دانلودها',
            'edit-address'    => 'آدرس‌ها',
            'payment-methods' => 'روش‌های پرداخت',
            'edit-account'    => 'جزئیات حساب',
            'customer-logout' => 'خروج',
        ];
    }
}
?>

<div class="gpds-account-nav__user">
    <div class="gpds-account-nav__avatar">
        <?php echo esc_html(mb_substr($current_user->display_name, 0, 1)); ?>
    </div>
    <div class="gpds-account-nav__user-info">
        <strong><?php echo esc_html($current_user->display_name); ?></strong>
        <span><?php echo esc_html($current_user->user_email); ?></span>
    </div>
</div>

<ul class="gpds-account-nav__list">
    <?php foreach ($items as $endpoint_key => $label) : 
        $url = wc_get_account_endpoint_url($endpoint_key);
        $is_active = ($current === $endpoint_key);
        $icon = $icons[$endpoint_key] ?? 'user';
    ?>
        <li class="gpds-account-nav__item <?php echo $is_active ? 'is-active' : ''; ?>">
            <a href="<?php echo esc_url($url); ?>" class="gpds-account-nav__link">
                <?php gpds_icon($icon, 18); ?>
                <span><?php echo esc_html($label); ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>