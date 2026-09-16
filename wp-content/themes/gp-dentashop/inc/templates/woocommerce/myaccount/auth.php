<?php
/**
 * Template: My Account - Auth (Login/Register)
 * URL: /my-account/ (وقتی لاگین نیستی)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header();
?>

<main id="gpds-main" class="gpds-main gpds-account-page">
    <div class="gpds-container">

        <?php gpds_wc_breadcrumb(); ?>

        <header class="gpds-page-header">
            <h1 class="gpds-page-title">حساب کاربری</h1>
        </header>

        <?php if (function_exists('wc_print_notices')) wc_print_notices(); ?>

        <div class="gpds-account-auth">

            <!-- فرم ورود -->
            <div class="gpds-account-auth__box">
                <h2 class="gpds-account-auth__title">
                    <?php gpds_icon('user', 22); ?>
                    ورود به حساب
                </h2>

                <form class="woocommerce-form woocommerce-form-login login" method="post">

                    <?php do_action('woocommerce_login_form_start'); ?>

                    <p class="form-row">
                        <label for="username">نام کاربری یا ایمیل <span class="required">*</span></label>
                        <input type="text" 
                               class="input-text" 
                               name="username" 
                               id="username" 
                               autocomplete="username" 
                               value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>">
                    </p>

                    <p class="form-row">
                        <label for="password">رمز عبور <span class="required">*</span></label>
                        <input class="input-text" 
                               type="password" 
                               name="password" 
                               id="password" 
                               autocomplete="current-password">
                    </p>

                    <?php do_action('woocommerce_login_form'); ?>

                    <p class="form-row">
                        <label class="woocommerce-form__label">
                            <input class="woocommerce-form__input-checkbox" 
                                   name="rememberme" 
                                   type="checkbox" 
                                   id="rememberme" 
                                   value="forever">
                            <span>مرا به خاطر بسپار</span>
                        </label>
                    </p>

                    <p class="form-row">
                        <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                        <button type="submit" 
                                class="gpds-btn gpds-btn--primary gpds-btn--block" 
                                name="login" 
                                value="ورود">ورود</button>
                        <input type="hidden" name="redirect" value="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
                    </p>

                    <p class="gpds-account-auth__lost">
                        <a href="<?php echo esc_url(wc_lostpassword_url()); ?>">رمز عبور را فراموش کرده‌اید؟</a>
                    </p>

                    <?php do_action('woocommerce_login_form_end'); ?>

                </form>
            </div>

            <!-- فرم ثبت‌نام -->
            <?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>

                <div class="gpds-account-auth__box">
                    <h2 class="gpds-account-auth__title">
                        <?php gpds_icon('user-plus', 22); ?>
                        ساخت حساب جدید
                    </h2>

                    <form method="post" class="woocommerce-form woocommerce-form-register register">

                        <?php do_action('woocommerce_register_form_start'); ?>

                        <?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
                            <p class="form-row">
                                <label for="reg_username">نام کاربری <span class="required">*</span></label>
                                <input type="text" 
                                       class="input-text" 
                                       name="username" 
                                       id="reg_username" 
                                       autocomplete="username" 
                                       value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>">
                            </p>
                        <?php endif; ?>

                        <p class="form-row">
                            <label for="reg_email">ایمیل <span class="required">*</span></label>
                            <input type="email" 
                                   class="input-text" 
                                   name="email" 
                                   id="reg_email" 
                                   autocomplete="email" 
                                   value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>">
                        </p>

                        <?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
                            <p class="form-row">
                                <label for="reg_password">رمز عبور <span class="required">*</span></label>
                                <input type="password" 
                                       class="input-text" 
                                       name="password" 
                                       id="reg_password" 
                                       autocomplete="new-password">
                            </p>
                        <?php else : ?>
                            <p class="form-row">
                                یک لینک برای تنظیم رمز عبور به ایمیل شما ارسال می‌شود.
                            </p>
                        <?php endif; ?>

                        <?php do_action('woocommerce_register_form'); ?>

                        <p class="form-row">
                            <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                            <button type="submit" 
                                    class="gpds-btn gpds-btn--outline gpds-btn--block" 
                                    name="register" 
                                    value="ثبت‌نام">ثبت‌نام</button>
                        </p>

                        <?php do_action('woocommerce_register_form_end'); ?>

                    </form>
                </div>

            <?php endif; ?>

        </div>

    </div>
</main>

<?php get_footer();