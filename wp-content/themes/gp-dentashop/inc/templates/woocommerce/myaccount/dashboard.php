<?php
/**
 * Template: My Account - Dashboard
 * URL: /my-account/ (وقتی لاگین هستی)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header();

$current_user = wp_get_current_user();
$endpoint = function_exists('WC') ? WC()->query->get_current_endpoint() : '';
?>

<main id="gpds-main" class="gpds-main gpds-account-page">
    <div class="gpds-container">

        <?php gpds_wc_breadcrumb(); ?>

        <div class="gpds-account-layout">

            <!-- نوار کناری -->
            <aside class="gpds-account-nav">
                <?php get_template_part('inc/templates/woocommerce/myaccount/parts/navigation'); ?>
            </aside>

            <!-- محتوا -->
            <div class="gpds-account-content">

                <?php if (function_exists('wc_print_notices')) wc_print_notices(); ?>

                <?php if (!$endpoint) : ?>
                    <div class="gpds-account-welcome">
                        <h1 class="gpds-account-welcome__title">
                            سلام، <?php echo esc_html($current_user->display_name); ?> 👋
                        </h1>
                        <p class="gpds-account-welcome__subtitle">
                            به حساب کاربری‌ت خوش آمدی
                        </p>
                    </div>
                <?php endif; ?>

                <?php do_action('woocommerce_account_content'); ?>

            </div>

        </div>

    </div>
</main>

<?php get_footer();