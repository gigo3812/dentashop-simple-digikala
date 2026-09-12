<?php
/**
 * Header - Topbar (ردیف اول)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-topbar">
    <div class="gpds-container">
        <div class="gpds-topbar__inner">
            
            <!-- لوگو -->
            <div class="gpds-topbar__logo">
                <?php gpds_site_logo(['height' => 44]); ?>
            </div>
            
            <!-- جستجو -->
            <div class="gpds-topbar__search">
                <form 
                    role="search" 
                    method="get" 
                    action="<?php echo esc_url(home_url('/')); ?>" 
                    class="gpds-search-form"
                    autocomplete="off"
                >
                    <div class="gpds-search-form__input-wrap">
                        <?php gpds_icon('search', 20, 'gpds-search-form__icon'); ?>
                        <input 
                            type="search" 
                            name="s" 
                            class="gpds-input gpds-input--search gpds-search-form__input"
                            placeholder="جستجو در دنتاشاپ..."
                            aria-label="جستجو"
                            data-gpds-search
                        >
                        <input type="hidden" name="post_type" value="product">
                        
                        <!-- نتایج Ajax -->
                        <div class="gpds-search-results" data-gpds-search-results hidden>
                            <div class="gpds-search-results__loading">
                                <span class="gpds-spin"><?php gpds_icon('loader', 20); ?></span>
                            </div>
                            <div class="gpds-search-results__body" data-gpds-search-body></div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- اکشن‌های سمت چپ -->
            <div class="gpds-topbar__actions">
                
                <!-- ورود / ثبت‌نام -->
                <div class="gpds-topbar__auth">
                    <?php if (is_user_logged_in()) : 
                        $current_user = wp_get_current_user();
                    ?>
                        <div class="gpds-dropdown" data-gpds-dropdown>
                            <button 
                                type="button" 
                                class="gpds-btn gpds-btn--ghost gpds-topbar__auth-btn"
                                data-gpds-dropdown-trigger
                            >
                                <?php gpds_icon('user', 20); ?>
                                <span><?php echo esc_html($current_user->display_name); ?></span>
                            </button>
                            <div class="gpds-dropdown__menu" data-gpds-dropdown-menu>
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>">
                                    <?php gpds_icon('dashboard', 16); ?> پیشخوان
                                </a>
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>">
                                    <?php gpds_icon('orders', 16); ?> سفارش‌ها
                                </a>
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>">
                                    <?php gpds_icon('location', 16); ?> آدرس‌ها
                                </a>
                                <hr>
                                <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">
                                    <?php gpds_icon('logout', 16); ?> خروج
                                </a>
                            </div>
                        </div>
                    <?php else : ?>
                        <a 
                            href="<?php echo esc_url(wp_login_url()); ?>" 
                            class="gpds-btn gpds-btn--ghost gpds-topbar__auth-btn"
                        >
                            <?php gpds_icon('user', 20); ?>
                            <span>ورود | ثبت‌نام</span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- جداکننده -->
                <div class="gpds-topbar__divider" aria-hidden="true"></div>
                
                <!-- سبد خرید -->
                <div class="gpds-mini-cart" data-gpds-mini-cart>
                    <button 
                        type="button" 
                        class="gpds-btn gpds-btn--ghost gpds-mini-cart__trigger"
                        data-gpds-cart-trigger
                        aria-label="سبد خرید"
                    >
                        <?php gpds_icon('cart', 22); ?>
                        <span 
                            class="gpds-badge gpds-badge--primary gpds-badge--circle gpds-mini-cart__count"
                            data-gpds-cart-count
                            <?php if (gpds_cart_count() === 0) echo 'hidden'; ?>
                        >
                            <?php echo esc_html(gpds_cart_count()); ?>
                        </span>
                    </button>
                    
                    <!-- پنل سبد خرید (پنهان) -->
                    <div class="gpds-mini-cart__panel" data-gpds-cart-panel hidden>
                        <div class="gpds-mini-cart__loading" data-gpds-cart-loading>
                            <span class="gpds-spin"><?php gpds_icon('loader', 24); ?></span>
                        </div>
                        <div class="gpds-mini-cart__content" data-gpds-cart-content></div>
                    </div>
                </div>
                
            </div>
            
        </div>
    </div>
</div>