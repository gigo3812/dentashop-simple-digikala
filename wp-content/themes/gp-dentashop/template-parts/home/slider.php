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
                <?php foreach ($slides as $i => $slide) :
                    $is_first = ($i === 0);
                    ?>
                    <div class="swiper-slide">
                        <a
                            href="<?php echo esc_url($slide['url']); ?>"
                            class="gpds-slide"
                            aria-label="<?php echo esc_attr($slide['title']); ?>"
                        >
                            <picture>
                                <?php if (!empty($slide['image_mobile'])) : ?>
                                    <source
                                        media="(max-width: 767px)"
                                        srcset="<?php echo esc_url($slide['image_mobile']); ?>"
                                    >
                                <?php endif; ?>

                                <?php if (!empty($slide['image_tablet'])) : ?>
                                    <source
                                        media="(max-width: 1199px)"
                                        srcset="<?php echo esc_url($slide['image_tablet']); ?>"
                                    >
                                <?php endif; ?>

                                <img
                                    src="<?php echo esc_url($slide['image']); ?>"
                                    alt="<?php echo esc_attr($slide['title']); ?>"
                                    width="1920"
                                    height="600"
                                    decoding="async"
                                    <?php echo $is_first
                                        ? 'fetchpriority="high"'
                                        : 'loading="lazy"'; ?>
                                >
                            </picture>

                            <div class="gpds-slide__overlay"></div>
                            <div class="gpds-slide__info">
                                <h2 class="gpds-slide__title">
                                    <?php echo esc_html($slide['title']); ?>
                                </h2>
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