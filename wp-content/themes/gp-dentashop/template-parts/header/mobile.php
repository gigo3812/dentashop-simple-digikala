<?php

/**
 * Header - Mobile
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

/*
 * دسته‌بندی‌های اصلی محصولات
 * داده‌ها یک‌بار در PHP دریافت می‌شوند.
 */
$mobile_categories = gpds_get_top_product_categories(12);
?>

<div class="gpds-mobile-header">
    <div class="gpds-mobile-header__inner">

        <!-- منو -->
        <button
            type="button"
            class="gpds-mobile-header__btn"
            data-gpds-mobile-menu-trigger
            aria-label="باز کردن منو"
            aria-expanded="false"
            aria-controls="gpds-mobile-menu">
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
            aria-label="جستجو">
            <?php gpds_icon('search', 22); ?>
        </button>

        <!-- حساب کاربری -->
        <a
            href="<?php echo esc_url(
                        is_user_logged_in()
                            ? wc_get_account_endpoint_url('dashboard')
                            : wc_get_page_permalink('myaccount')
                    ); ?>"
            class="gpds-mobile-header__btn"
            aria-label="حساب کاربری">
            <?php gpds_icon('user', 22); ?>
        </a>

        <!-- سبد خرید -->
        <a
            href="<?php echo esc_url(wc_get_cart_url()); ?>"
            class="gpds-mobile-header__btn gpds-mobile-header__cart"
            aria-label="سبد خرید">
            <?php gpds_icon('cart', 22); ?>

            <span
                class="gpds-badge gpds-badge--primary gpds-badge--circle"
                data-gpds-cart-count
                <?php if (gpds_cart_count() === 0) echo 'hidden'; ?>>
                <?php echo esc_html(gpds_cart_count()); ?>
            </span>
        </a>

    </div>
</div>


<!-- =========================================================
     MOBILE MENU
========================================================= -->

<aside
    id="gpds-mobile-menu"
    class="gpds-mobile-menu"
    data-gpds-mobile-menu
    hidden
    aria-hidden="true">

    <!-- Header -->
    <div class="gpds-mobile-menu__header">

        <div class="gpds-mobile-menu__title">
            <span class="gpds-mobile-menu__title-icon">
                <?php gpds_icon('menu', 20); ?>
            </span>

            <span>منوی سایت</span>
        </div>

        <button
            type="button"
            class="gpds-mobile-menu__close"
            data-gpds-mobile-menu-close
            aria-label="بستن منو">
            <?php gpds_icon('close', 24); ?>
        </button>

    </div>


    <!-- =====================================================
         MOBILE MENU BODY
    ====================================================== -->

    <div class="gpds-mobile-menu__body">


        <!-- ================================================
             سطح اصلی
        ================================================= -->

        <div
            class="gpds-mobile-menu__level gpds-mobile-menu__level--root is-active"
            data-gpds-mobile-level="root">

            <nav
                class="gpds-mobile-menu__nav"
                aria-label="منوی اصلی موبایل">

                <!-- دسته‌بندی محصولات -->
                <?php if (!empty($mobile_categories)) : ?>

                    <button
                        type="button"
                        class="gpds-mobile-menu__category-trigger"
                        data-gpds-mobile-categories-trigger
                        aria-expanded="false">

                        <span class="gpds-mobile-menu__item-icon">
                            <?php gpds_icon('category', 20); ?>
                        </span>

                        <span class="gpds-mobile-menu__item-content">

                            <strong>دسته‌بندی محصولات</strong>

                            <small>
                                مشاهده محصولات بر اساس دسته‌بندی
                            </small>

                        </span>

                        <span class="gpds-mobile-menu__item-arrow">
                            <?php gpds_icon('chevron-left', 18); ?>
                        </span>

                    </button>

                <?php endif; ?>


                <!-- منوی اصلی وردپرس -->

                <div class="gpds-mobile-menu__main-nav">

                    <?php
                    gpds_menu('gpds_main_menu', [
                        'menu_class' => 'gpds-mobile-menu__nav-list',
                    ]);
                    ?>

                    <!-- اکشن‌های اضافی (سمت چپ) -->
                    <div class="gpds-mainbar__extra">
                        <a href="<?php echo esc_url(get_post_type_archive_link('office')); ?>" class="gpds-mainbar__extra-link">
                            <?php gpds_icon('map-pin', 14); ?>
                            <span>شعبه‌ها</span>

                        </a>
                    </div>
                </div>

            </nav>

        </div>


        <!-- ================================================
             سطح دسته‌بندی‌ها
        ================================================= -->

        <div
            class="gpds-mobile-menu__level gpds-mobile-menu__level--categories"
            data-gpds-mobile-level="categories"
            hidden>

            <div class="gpds-mobile-menu__level-header">

                <button
                    type="button"
                    class="gpds-mobile-menu__back"
                    data-gpds-mobile-back="root"
                    aria-label="بازگشت">
                    <?php gpds_icon('arrow-right', 20); ?>
                </button>

                <div class="gpds-mobile-menu__level-title">
                    دسته‌بندی محصولات
                </div>

            </div>


            <div class="gpds-mobile-menu__category-list">

                <?php foreach ($mobile_categories as $cat) :

                    $children = gpds_get_child_categories(
                        $cat->term_id,
                        12
                    );

                    $has_children = !empty($children);

                    $cat_link = get_term_link($cat);

                    if (is_wp_error($cat_link)) {
                        $cat_link = '#';
                    }

                ?>

                    <div
                        class="gpds-mobile-menu__category"
                        data-gpds-mobile-category="<?php echo esc_attr($cat->term_id); ?>">

                        <?php if ($has_children) : ?>

                            <button
                                type="button"
                                class="gpds-mobile-menu__category-row"
                                data-gpds-mobile-category-trigger="<?php echo esc_attr($cat->term_id); ?>"
                                aria-expanded="false">

                                <span class="gpds-mobile-menu__category-icon">
                                    <?php gpds_icon('category', 20); ?>
                                </span>

                                <span class="gpds-mobile-menu__category-name">
                                    <?php echo esc_html($cat->name); ?>
                                </span>

                                <span class="gpds-mobile-menu__category-arrow">
                                    <?php gpds_icon('chevron-left', 17); ?>
                                </span>

                            </button>

                        <?php else : ?>

                            <a
                                href="<?php echo esc_url($cat_link); ?>"
                                class="gpds-mobile-menu__category-row">

                                <span class="gpds-mobile-menu__category-icon">
                                    <?php gpds_icon('category', 20); ?>
                                </span>

                                <span class="gpds-mobile-menu__category-name">
                                    <?php echo esc_html($cat->name); ?>
                                </span>

                                <span class="gpds-mobile-menu__category-arrow">
                                    <?php gpds_icon('arrow-left', 16); ?>
                                </span>

                            </a>

                        <?php endif; ?>


                        <?php if ($has_children) : ?>

                            <!-- زیرمنوی این دسته -->

                            <div
                                class="gpds-mobile-menu__sublevel"
                                data-gpds-mobile-sublevel="<?php echo esc_attr($cat->term_id); ?>"
                                hidden>

                                <div class="gpds-mobile-menu__sublevel-header">

                                    <button
                                        type="button"
                                        class="gpds-mobile-menu__back"
                                        data-gpds-mobile-category-back
                                        aria-label="بازگشت">
                                        <?php gpds_icon('arrow-right', 20); ?>
                                    </button>

                                    <div class="gpds-mobile-menu__level-title">
                                        <?php echo esc_html($cat->name); ?>
                                    </div>

                                </div>


                                <a
                                    href="<?php echo esc_url($cat_link); ?>"
                                    class="gpds-mobile-menu__view-all">

                                    <span>
                                        <?php gpds_icon('grid', 18); ?>

                                        مشاهده همه محصولات
                                    </span>

                                    <?php gpds_icon('arrow-left', 16); ?>

                                </a>


                                <div class="gpds-mobile-menu__children">

                                    <?php foreach ($children as $child) :

                                        $child_link = get_term_link($child);

                                        if (is_wp_error($child_link)) {
                                            continue;
                                        }

                                    ?>

                                        <a
                                            href="<?php echo esc_url($child_link); ?>"
                                            class="gpds-mobile-menu__child">

                                            <span>
                                                <?php echo esc_html($child->name); ?>
                                            </span>

                                            <?php gpds_icon('arrow-left', 15); ?>

                                        </a>

                                    <?php endforeach; ?>

                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>
        </div>



    </div>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <div class="gpds-mobile-menu__footer">

        <?php if (is_user_logged_in()) : ?>

            <a
                href="<?php echo esc_url(wp_logout_url(home_url())); ?>"
                class="gpds-mobile-menu__account">
                <?php gpds_icon('logout', 18); ?>
                <span>خروج از حساب</span>
            </a>

        <?php else : ?>

            <a
                href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"
                class="gpds-mobile-menu__account gpds-mobile-menu__account--primary">
                <?php gpds_icon('user', 18); ?>
                <span>ورود | ثبت‌نام</span>
            </a>

        <?php endif; ?>

    </div>

</aside>


<!-- Overlay -->

<div
    class="gpds-mobile-menu-overlay"
    data-gpds-mobile-menu-overlay
    hidden></div>