<?php
/**
 * GP DentaShop - Cart Page
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="gpds-main" class="gpds-main gpds-cart-page">
    <div class="gpds-container">

        <?php gpds_wc_breadcrumb(); ?>

        <header class="gpds-page-header gpds-page-header--compact">
            <h1 class="gpds-page-title">سبد خرید</h1>
            <p class="gpds-page-subtitle">محصولات انتخاب‌شده برای سفارش شما</p>
        </header>

        <section class="gpds-cart-shell" aria-label="سبد خرید">
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
