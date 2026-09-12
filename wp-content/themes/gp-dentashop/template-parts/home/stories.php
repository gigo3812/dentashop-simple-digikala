<?php
/**
 * Home - Stories (فعلاً از Customizer)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// فعلاً استوری‌های نمونه (بعداً از Customizer می‌خونیم)
$stories = [
    ['title' => 'پیشنهاد ویژه', 'image' => ''],
    ['title' => 'گوشی موبایل', 'image' => ''],
    ['title' => 'لپ‌تاپ', 'image' => ''],
    ['title' => 'هدفون', 'image' => ''],
    ['title' => 'ساعت هوشمند', 'image' => ''],
    ['title' => 'لوازم خانگی', 'image' => ''],
    ['title' => 'کنسول بازی', 'image' => ''],
    ['title' => 'دوربین', 'image' => ''],
    ['title' => 'تبلت', 'image' => ''],
    ['title' => 'لوازم ورزشی', 'image' => ''],
];
?>

<section class="gpds-section gpds-stories-section" aria-label="استوری‌ها">
    <div class="gpds-container">
        <div class="gpds-card gpds-stories">
            <div class="swiper" id="gpds-stories-slider">
                <div class="swiper-wrapper">
                    <?php foreach ($stories as $i => $story) : ?>
                        <div class="swiper-slide">
                            <a href="#" class="gpds-story">
                                <div class="gpds-story__ring">
                                    <div class="gpds-story__image">
                                        <?php if (!empty($story['image'])) : ?>
                                            <img 
                                                src="<?php echo esc_url($story['image']); ?>" 
                                                alt="<?php echo esc_attr($story['title']); ?>"
                                                loading="lazy"
                                                width="100"
                                                height="100"
                                            >
                                        <?php else : ?>
                                            <?php gpds_icon('image', 32); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="gpds-story__title">
                                    <?php echo esc_html($story['title']); ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>