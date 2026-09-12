<?php
/**
 * Home - Categories Section
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$categories = gpds_get_showcase_categories(8);

if (empty($categories)) {
    return;
}
?>

<section class="gpds-section gpds-categories-section" aria-label="دسته‌بندی‌ها">
    <div class="gpds-container">
        <div class="gpds-card">
            
            <div class="gpds-section-header">
                <h2 class="gpds-section-title">
                    <?php gpds_icon('grid', 24); ?>
                    دسته‌بندی‌های محصولات
                </h2>
            </div>
            
            <div class="gpds-categories-grid">
                <?php foreach ($categories as $cat) : 
                    $thumb_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                    $thumb_url = $thumb_id 
                        ? wp_get_attachment_image_url($thumb_id, 'gpds-cat')
                        : '';
                ?>
                    <a 
                        href="<?php echo esc_url(get_term_link($cat)); ?>" 
                        class="gpds-category-card"
                    >
                        <div class="gpds-category-card__image">
                            <?php if ($thumb_url) : ?>
                                <img 
                                    src="<?php echo esc_url($thumb_url); ?>" 
                                    alt="<?php echo esc_attr($cat->name); ?>"
                                    loading="lazy"
                                    width="100"
                                    height="100"
                                >
                            <?php else : ?>
                                <?php gpds_icon('category', 48); ?>
                            <?php endif; ?>
                        </div>
                        <div class="gpds-category-card__title">
                            <?php echo esc_html($cat->name); ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
        </div>
    </div>
</section>