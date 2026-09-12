<?php
/**
 * Footer - Bottom (Trust Badges + Copyright)
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-footer-bottom">
    <div class="gpds-container">
        <div class="gpds-footer-bottom__inner">
            
            <!-- نمادها -->
            <div class="gpds-footer-badges">
                <a href="#" class="gpds-footer-badge" aria-label="ساماندهی">
                    <?php gpds_icon('badge-1', 60); ?>
                </a>
                <a href="#" class="gpds-footer-badge" aria-label="اتحادیه کشوری">
                    <?php gpds_icon('badge-2', 60); ?>
                </a>
                <a href="#" class="gpds-footer-badge" aria-label="نماد اعتماد">
                    <?php gpds_icon('badge-3', 60); ?>
                </a>
            </div>
            
            <!-- کپی‌رایت -->
            <div class="gpds-footer-copyright">
                <p>
                    استفاده از مطالب فروشگاه اینترنتی دنتاشاپ فقط برای مقاصد غیرتجاری و با ذکر منبع بلامانع است.
                    کلیه حقوق این وب‌سایت محفوظ و متعلق به <?php echo esc_html(date_i18n('Y')); ?> می‌باشد.
                </p>
            </div>
            
        </div>
    </div>
</div>