<?php

/**
 * Footer - Top Bar (لوگو + بازگشت به بالا)
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-footer-top">
    <div class="gpds-container">
        <div class="gpds-footer-top__inner">

            <!-- لوگو -->
            <div class="gpds-footer-top__logo">
                <?php gpds_site_logo(['height' => 40]); ?>
            </div>

            <!-- تلفن پشتیبانی -->
            <div class="gpds-footer-top__support">
                <?php gpds_icon('phone', 20); ?>
                <span>پشتیبانی ۲۴ ساعته: <strong>056-0000000</strong></span>
            </div>

            <!-- بازگشت به بالا -->
            <button
                type="button"
                class="gpds-back-to-top"
                data-gpds-back-to-top
                aria-label="بازگشت به بالا">
                <?php gpds_icon('arrow-up', 22); ?>
            </button>

        </div>
    </div>
</div>