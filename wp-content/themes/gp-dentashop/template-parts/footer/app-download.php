<?php
/**
 * Footer - App Download
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-footer-apps">
    <div class="gpds-container">
        <div class="gpds-footer-apps__inner">
            
            <!-- لوگو اپ -->
            <div class="gpds-footer-apps__logo">
                <?php gpds_icon('app', 40); ?>
                <div>
                    <div class="gpds-footer-apps__title">اپلیکیشن دنتاشاپ</div>
                    <div class="gpds-footer-apps__subtitle">تجربه خرید آسان‌تر</div>
                </div>
            </div>
            
            <!-- دکمه‌های دانلود -->
            <div class="gpds-footer-apps__buttons">
                <a href="#" class="gpds-footer-app-btn" aria-label="دانلود از گوگل پلی">
                    <?php gpds_icon('google-play', 24); ?>
                    <span>
                        <small>دریافت از</small>
                        <strong>گوگل پلی</strong>
                    </span>
                </a>
                <a href="#" class="gpds-footer-app-btn" aria-label="دانلود از اپ استور">
                    <?php gpds_icon('apple', 24); ?>
                    <span>
                        <small>دریافت از</small>
                        <strong>اپ استور</strong>
                    </span>
                </a>
                <a href="#" class="gpds-footer-app-btn" aria-label="دانلود از مایکت">
                    <?php gpds_icon('myket', 24); ?>
                    <span>
                        <small>دریافت از</small>
                        <strong>مایکت</strong>
                    </span>
                </a>
                <a href="#" class="gpds-footer-app-btn" aria-label="دانلود از بازار">
                    <?php gpds_icon('bazaar', 24); ?>
                    <span>
                        <small>دریافت از</small>
                        <strong>بازار</strong>
                    </span>
                </a>
            </div>
            
        </div>
    </div>
</div>