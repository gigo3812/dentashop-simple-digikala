<?php
/**
 * Shop Empty State
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-shop-empty">
    <div class="gpds-empty">
        <?php gpds_icon('search', 80); ?>
        <h3>محصولی یافت نشد</h3>
        <p>متأسفیم، در این دسته محصولی موجود نیست.</p>
        <a href="<?php echo esc_url(gpds_shop_url()); ?>" class="gpds-btn gpds-btn--primary">
            مشاهده همه محصولات
        </a>
    </div>
</div>