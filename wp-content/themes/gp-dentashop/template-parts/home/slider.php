<?php
/**
 * Home - Slider Section
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$slides = gpds_get_slider_items(5);

if (empty($slides)) {
    return;
}
?>

<section class="gpds-section gpds-slider-section" aria-label="اسلایدر اصلی">
    <div class="gpds-container">
        <div class="gpds-slider swiper" id="gpds-home-slider">
            <div class="swiper-wrapper">
                <?php foreach ($slides as $i => $slide) : ?>
                    <div class="swiper-slide">
                        <a 
                            href="<?php echo esc_url($slide['url']); ?>" 
                            class="gpds-slide"
                            aria-label="<?php echo esc_attr($slide['title']); ?>"
                        >
                            <img 
                                src="<?php echo esc_url($slide['image']); ?>"
                                alt="<?php echo esc_attr($slide['title']); ?>"
                                <?php echo $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
                                decoding="async"
                                width="1200"
                                height="400"
                            >
                            <div class="gpds-slide__overlay"></div>
                            <div class="gpds-slide__info">
                                <h2 class="gpds-slide__title"><?php echo esc_html($slide['title']); ?></h2>
                                <div class="gpds-slide__price">
                                    <?php echo wp_kses_post($slide['price']); ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="swiper-pagination"></div>
            <button class="gpds-slider__nav gpds-slider__nav--prev swiper-button-prev" aria-label="قبلی"></button>
            <button class="gpds-slider__nav gpds-slider__nav--next swiper-button-next" aria-label="بعدی"></button>
        </div>
    </div>
</section>