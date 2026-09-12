<?php
/**
 * Header - Mobile
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-mobile-header">
    <div class="gpds-mobile-header__inner">
        
        <!-- دکمه منو -->
        <button 
            type="button" 
            class="gpds-mobile-header__btn"
            data-gpds-mobile-menu-trigger
            aria-label="منو"
        >
            <?php gpds_icon('menu', 24); ?>
        </button>
        
        <!-- لوگو -->
        <div class="gpds-mobile-header__logo">
            <?php gpds_site_logo(['height' => 32]); ?>
        </div>
        
        <!-- جستجو -->
        <button 
            type="button" 
            class="gpds-mobile-header__btn"
            data-gpds-search-overlay-trigger
            aria-label="جستجو"
        >
            <?php gpds_icon('search', 22); ?>
        </button>
        
        <!-- ورود -->
        <a 
            href="<?php echo esc_url(is_user_logged_in() ? wc_get_account_endpoint_url('dashboard') : wp_login_url()); ?>" 
            class="gpds-mobile-header__btn"
            aria-label="حساب کاربری"
        >
            <?php gpds_icon('user', 22); ?>
        </a>
        
        <!-- سبد خرید -->
        <a 
            href="<?php echo esc_url(wc_get_cart_url()); ?>" 
            class="gpds-mobile-header__btn gpds-mobile-header__cart"
            aria-label="سبد خرید"
        >
            <?php gpds_icon('cart', 22); ?>
            <span 
                class="gpds-badge gpds-badge--primary gpds-badge--circle"
                data-gpds-cart-count
                <?php if (gpds_cart_count() === 0) echo 'hidden'; ?>
            >
                <?php echo esc_html(gpds_cart_count()); ?>
            </span>
        </a>
        
    </div>
</div>

<!-- Mobile Menu Panel (پنهان) -->
<aside 
    class="gpds-mobile-menu" 
    data-gpds-mobile-menu 
    hidden
    aria-hidden="true"
>
    <div class="gpds-mobile-menu__header">
        <span>منو</span>
        <button 
            type="button" 
            class="gpds-mobile-menu__close"
            data-gpds-mobile-menu-close
            aria-label="بستن"
        >
            <?php gpds_icon('close', 24); ?>
        </button>
    </div>
    
    <nav class="gpds-mobile-menu__body">
        <?php gpds_menu('gpds_main_menu', [
            'menu_class' => 'gpds-mobile-menu__nav',
        ]); ?>
    </nav>
    
    <div class="gpds-mobile-menu__footer">
        <?php if (is_user_logged_in()) : ?>
            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="gpds-btn gpds-btn--outline gpds-btn--block">
                <?php gpds_icon('logout', 18); ?> خروج از حساب
            </a>
        <?php else : ?>
            <a href="<?php echo esc_url(wp_login_url()); ?>" class="gpds-btn gpds-btn--primary gpds-btn--block">
                ورود | ثبت‌نام
            </a>
        <?php endif; ?>
    </div>
</aside>
<div class="gpds-mobile-menu-overlay" data-gpds-mobile-menu-overlay hidden></div>