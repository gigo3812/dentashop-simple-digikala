<?php
/**
 * Home - Best Sellers Carousel
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$products = gpds_get_products([
    'type'  => 'best',
    'limit' => 12,
]);

if (empty($products)) {
    // fallback به آخرین محصولات
    $products = gpds_get_products([
        'type'  => 'recent',
        'limit' => 12,
    ]);
}

if (empty($products)) {
    return;
}
?>

<section class="gpds-section" aria-label="پرفروش‌ترین‌ها">
    <div class="gpds-container">
        <div class="gpds-card">
            
            <div class="gpds-section-header">
                <h2 class="gpds-section-title">
                    <?php gpds_icon('trending-up', 24); ?>
                    پرفروش‌ترین‌ها
                </h2>
                <a href="<?php echo esc_url(gpds_shop_url()); ?>" class="gpds-section-more">
                    مشاهده همه
                    <?php gpds_icon('chevron-left', 14); ?>
                </a>
            </div>
            
            <div class="swiper gpds-products-carousel">
                <div class="swiper-wrapper">
                    <?php foreach ($products as $product) : ?>
                        <div class="swiper-slide">
                            <?php
                                include get_stylesheet_directory() . '/template-parts/product/card.php';
                            ?>
                            
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button class="gpds-carousel-nav gpds-carousel-nav--prev swiper-button-prev" aria-label="قبلی">
                    <?php gpds_icon('chevron-right', 20); ?>
                </button>
                <button class="gpds-carousel-nav gpds-carousel-nav--next swiper-button-next" aria-label="بعدی">
                    <?php gpds_icon('chevron-left', 20); ?>
                </button>
            </div>
            
        </div>
    </div>
</section>