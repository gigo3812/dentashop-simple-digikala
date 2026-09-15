<?php
/**
 * Home - Best Sellers from Random Top Category
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$result   = gpds_get_best_selling_from_random_category(12, 5);
$products = $result['products'];
$category = $result['category'];

if (empty($products)) {
    return;
}

// عنوان داینامیک
$title = $category
    ? 'پرفروش‌ترین‌های ' . $category->name
    : 'پرفروش‌ترین‌ها';

// لینک «مشاهده همه»
$more_url = $category
    ? get_term_link($category)
    : gpds_shop_url();
?>

<section class="gpds-section" aria-label="<?php echo esc_attr($title); ?>">
    <div class="gpds-container">
        <div class="gpds-card">

            <div class="gpds-section-header">
                <h2 class="gpds-section-title">
                    <?php gpds_icon('trending-up', 24); ?>
                    <?php echo esc_html($title); ?>
                </h2>
                <a href="<?php echo esc_url($more_url); ?>" class="gpds-section-more">
                    مشاهده همه
                    <?php gpds_icon('chevron-left', 14); ?>
                </a>
            </div>

            <div class="swiper gpds-products-carousel">
                <div class="swiper-wrapper">
                    <?php foreach ($products as $product) : ?>
                        <div class="swiper-slide">
                            <?php include get_stylesheet_directory() . '/template-parts/product/card.php'; ?>
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