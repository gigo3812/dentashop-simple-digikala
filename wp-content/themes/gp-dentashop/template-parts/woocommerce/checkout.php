<?php
/**
 * GP DentaShop - Checkout Page
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="gpds-main" class="gpds-main gpds-checkout-page">
    <div class="gpds-container">

        <?php gpds_wc_breadcrumb(); ?>

        <header class="gpds-page-header gpds-page-header--compact">
            <h1 class="gpds-page-title">تسویه حساب</h1>
            <p class="gpds-page-subtitle">اطلاعات سفارش را بررسی و تکمیل کنید</p>
        </header>

        <section class="gpds-checkout-shell" aria-label="تسویه حساب">
            <?php
            while (have_posts()) :
                the_post();
                the_content();
            endwhile;
            ?>
        </section>

    </div>
</main>

<?php get_footer();
