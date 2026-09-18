<?php
/**
 * GP DentaShop — Modern 404 Page
 */

get_header();
?>

<main class="gpds-404" role="main">

    <div class="gpds-container">

        <div class="gpds-404__inner">

            <!-- ==================================================
                 Dental Visual
                 ================================================== -->
            <div class="gpds-404__visual" aria-hidden="true">

                <div class="gpds-404__tooth">

                    <svg viewBox="0 0 120 140" fill="none">

                        <path
                            d="M35 18
                               C21 20 12 31 13 46
                               C14 60 23 68 25 79
                               C28 94 27 112 37 121
                               C43 127 50 124 54 113
                               L60 94
                               L66 113
                               C70 124 77 127 83 121
                               C93 112 92 94 95 79
                               C97 68 106 60 107 46
                               C108 31 99 20 85 18
                               C74 16 67 21 60 27
                               C53 21 46 16 35 18Z"
                            stroke="currentColor"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                    </svg>

                </div>

                <span class="gpds-404__pulse gpds-404__pulse--1"></span>
                <span class="gpds-404__pulse gpds-404__pulse--2"></span>

            </div>


            <!-- ==================================================
                 Error Code
                 ================================================== -->
            <div class="gpds-404__code" aria-hidden="true">
                404
            </div>


            <!-- ==================================================
                 Content
                 ================================================== -->

            <h1 class="gpds-404__title">
                این صفحه پیدا نشد
            </h1>

            <p class="gpds-404__text">
                به نظر می‌رسد این مسیر تغییر کرده یا صفحه موردنظر دیگر در دسترس نیست.
                می‌توانید از مسیرهای زیر به محصولات و خدمات DentaShop برگردید.
            </p>


            <!-- ==================================================
                 Main Actions
                 ================================================== -->

            <div class="gpds-404__actions">

                <!-- Shop -->

                <a
                    href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"
                    class="gpds-404__btn gpds-404__btn--primary"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <path d="M3 6h18"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>

                    مشاهده تجهیزات

                </a>


                <!-- Home -->

                <a
                    href="<?php echo esc_url(home_url('/')); ?>"
                    class="gpds-404__btn gpds-404__btn--secondary"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="m3 10 9-7 9 7"/>
                        <path d="M5 9v11h14V9"/>
                        <path d="M9 20v-6h6v6"/>
                    </svg>

                    صفحه اصلی

                </a>

            </div>


            <!-- ==================================================
                 Quick Links
                 ================================================== -->

            <div class="gpds-404__quick">

                <div class="gpds-404__quick-title">
                    <span>مسیرهای پیشنهادی</span>
                </div>


                <div class="gpds-404__links">


                    <!-- Products -->

                    <a
                        href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"
                    >

                        <span class="gpds-404__link-icon">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M4 4h16v16H4z"/>
                                <path d="M8 8h8"/>
                                <path d="M8 12h8"/>
                                <path d="M8 16h5"/>
                            </svg>

                        </span>


                        <span>

                            <strong>
                                محصولات
                            </strong>

                            <small>
                                مشاهده تجهیزات دندانپزشکی
                            </small>

                        </span>


                        <svg
                            class="gpds-404__arrow"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="m9 18 6-6-6-6"/>
                        </svg>

                    </a>


                    <!-- Home -->

                    <a
                        href="<?php echo esc_url(home_url('/')); ?>"
                    >

                        <span class="gpds-404__link-icon">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M3 12h18"/>
                                <path d="M12 3c3 3 3 15 0 18"/>
                            </svg>

                        </span>


                        <span>

                            <strong>
                                صفحه اصلی
                            </strong>

                            <small>
                                بازگشت به DentaShop
                            </small>

                        </span>


                        <svg
                            class="gpds-404__arrow"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="m9 18 6-6-6-6"/>
                        </svg>

                    </a>


                    <!-- Support -->

                    <a
                        href="<?php echo esc_url(home_url('/contact/')); ?>"
                    >

                        <span class="gpds-404__link-icon">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                <path d="M8 9h8"/>
                                <path d="M8 13h5"/>
                            </svg>

                        </span>


                        <span>

                            <strong>
                                پشتیبانی
                            </strong>

                            <small>
                                نیاز به راهنمایی دارید؟
                            </small>

                        </span>


                        <svg
                            class="gpds-404__arrow"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="m9 18 6-6-6-6"/>
                        </svg>

                    </a>


                </div>

            </div>

        </div>

    </div>

</main>


<?php get_footer(); ?>
