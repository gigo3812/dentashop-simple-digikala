<?php
/**
 * Template: Single Office
 * URL: /office/{slug}/
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header();

while (have_posts()) : the_post();
    
    $office = gpds_get_office_data(get_the_ID());
    if (empty($office)) continue;
    
    $is_open = gpds_office_is_open_now($office['hours']);
    
    // لینک نقشه (نشان)
    $map_url = '';
    if ($office['lat'] && $office['lng']) {
        $map_url = 'https://neshan.org/maps/@' . $office['lat'] . ',' . $office['lng'] . ',15z';
    }
    ?>
    
    <main id="gpds-main" class="gpds-main gpds-office-single">
        <div class="gpds-container">

            <?php gpds_wc_breadcrumb(); ?>

            <!-- HERO -->
            <section class="gpds-office-hero">
                
                <?php if ($office['thumbnail']) : ?>
                    <div class="gpds-office-hero__image">
                        <img src="<?php echo esc_url($office['thumbnail']); ?>" 
                             alt="<?php echo esc_attr($office['title']); ?>"
                             loading="eager"
                             fetchpriority="high">
                    </div>
                <?php endif; ?>

                <div class="gpds-office-hero__content">
                    
                    <div class="gpds-office-hero__status">
                        <?php if ($is_open) : ?>
                            <span class="gpds-badge gpds-badge--success">
                                <span class="gpds-dot"></span>
                                باز است
                            </span>
                        <?php else : ?>
                            <span class="gpds-badge gpds-badge--muted">
                                <span class="gpds-dot"></span>
                                بسته است
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="gpds-office-hero__title"><?php echo esc_html($office['title']); ?></h1>
                    
                    <?php if ($office['province'] || $office['city']) : ?>
                        <p class="gpds-office-hero__location">
                            <?php gpds_icon('map-pin', 16); ?>
                            <?php 
                                $location = array_filter([$office['province'], $office['city']]);
                                echo esc_html(implode(' - ', $location));
                            ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($office['excerpt']) : ?>
                        <p class="gpds-office-hero__excerpt">
                            <?php echo esc_html(wp_trim_words($office['excerpt'], 50, '…')); ?>
                        </p>
                    <?php endif; ?>

                </div>
            </section>

            <!-- MAIN INFO -->
            <div class="gpds-office-layout">

                <!-- سایدبار: اطلاعات -->
                <aside class="gpds-office-sidebar">

                    <!-- مسئول -->
                    <?php if ($office['manager_name']) : ?>
                        <div class="gpds-office-card">
                            <h3 class="gpds-office-card__title">
                                <?php gpds_icon('user', 18); ?>
                                مسئول دفتر
                            </h3>
                            <div class="gpds-office-manager">
                                <div class="gpds-office-manager__avatar">
                                    <?php echo esc_html(mb_substr($office['manager_name'], 0, 1)); ?>
                                </div>
                                <div class="gpds-office-manager__info">
                                    <strong><?php echo esc_html($office['manager_name']); ?></strong>
                                    <?php if ($office['manager_role']) : ?>
                                        <span><?php echo esc_html($office['manager_role']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($office['manager_mobile']) : ?>
                                <a href="tel:<?php echo esc_attr($office['manager_mobile']); ?>" class="gpds-office-contact-row">
                                    <?php gpds_icon('phone', 16); ?>
                                    <span dir="ltr"><?php echo esc_html($office['manager_mobile']); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- تماس -->
                    <div class="gpds-office-card">
                        <h3 class="gpds-office-card__title">
                            <?php gpds_icon('headphones', 18); ?>
                            راه‌های تماس
                        </h3>
                        
                        <div class="gpds-office-contact">
                            <?php if ($office['phone']) : ?>
                                <?php foreach (explode(',', $office['phone']) as $phone) : ?>
                                    <a href="tel:<?php echo esc_attr(trim($phone)); ?>" class="gpds-office-contact-row">
                                        <?php gpds_icon('phone', 16); ?>
                                        <span dir="ltr"><?php echo esc_html(trim($phone)); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($office['mobile']) : ?>
                                <a href="tel:<?php echo esc_attr($office['mobile']); ?>" class="gpds-office-contact-row">
                                    <?php gpds_icon('phone', 16); ?>
                                    <span dir="ltr"><?php echo esc_html($office['mobile']); ?></span>
                                </a>
                            <?php endif; ?>

                            <?php if ($office['whatsapp']) : ?>
                                <a href="https://wa.me/<?php echo esc_attr($office['whatsapp']); ?>" 
                                   target="_blank" rel="noopener" 
                                   class="gpds-office-contact-row gpds-office-contact-row--whatsapp">
                                    <?php gpds_icon('whatsapp', 16); ?>
                                    <span>واتساپ</span>
                                </a>
                            <?php endif; ?>

                            <?php if ($office['email']) : ?>
                                <a href="mailto:<?php echo esc_attr($office['email']); ?>" class="gpds-office-contact-row">
                                    <?php gpds_icon('mail', 16); ?>
                                    <span dir="ltr"><?php echo esc_html($office['email']); ?></span>
                                </a>
                            <?php endif; ?>

                            <?php if ($office['website']) : ?>
                                <a href="<?php echo esc_url($office['website']); ?>" 
                                   target="_blank" rel="noopener" 
                                   class="gpds-office-contact-row">
                                    <?php gpds_icon('globe', 16); ?>
                                    <span>وب‌سایت</span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if ($office['instagram'] || $office['telegram']) : ?>
                            <div class="gpds-office-socials">
                                <?php if ($office['instagram']) : ?>
                                    <a href="https://instagram.com/<?php echo esc_attr($office['instagram']); ?>" 
                                       target="_blank" rel="noopener" 
                                       class="gpds-office-social gpds-office-social--instagram"
                                       aria-label="اینستاگرام">
                                        <?php gpds_icon('instagram', 18); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($office['telegram']) : ?>
                                    <a href="https://t.me/<?php echo esc_attr($office['telegram']); ?>" 
                                       target="_blank" rel="noopener" 
                                       class="gpds-office-social gpds-office-social--telegram"
                                       aria-label="تلگرام">
                                        <?php gpds_icon('telegram', 18); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ساعت کاری -->
                    <?php if (!empty($office['hours'])) : ?>
                        <div class="gpds-office-card">
                            <h3 class="gpds-office-card__title">
                                <?php gpds_icon('clock', 18); ?>
                                ساعت کاری
                            </h3>
                            <ul class="gpds-office-hours">
                                <?php foreach ($office['hours'] as $day => $hours) : 
                                    $is_off = !empty($hours['off']);
                                ?>
                                    <li class="gpds-office-hours__row <?php echo $is_off ? 'is-off' : ''; ?>">
                                        <span class="gpds-office-hours__day">
                                            <?php echo esc_html(gpds_office_day_label($day)); ?>
                                        </span>
                                        <span class="gpds-office-hours__time" dir="ltr">
                                            <?php if ($is_off) : ?>
                                                تعطیل
                                            <?php else : ?>
                                                <?php echo esc_html($hours['open'] ?? '—'); ?>
                                                –
                                                <?php echo esc_html($hours['close'] ?? '—'); ?>
                                            <?php endif; ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                </aside>

                <!-- محتوای اصلی -->
                <div class="gpds-office-main">

                    <!-- آدرس -->
                    <?php if ($office['address']) : ?>
                        <div class="gpds-office-card">
                            <h3 class="gpds-office-card__title">
                                <?php gpds_icon('location', 18); ?>
                                آدرس
                            </h3>
                            <p class="gpds-office-address"><?php echo nl2br(esc_html($office['address'])); ?></p>
                            
                            <div class="gpds-office-address__actions">
                                <?php if ($map_url) : ?>
                                    <a href="<?php echo esc_url($map_url); ?>" 
                                       target="_blank" rel="noopener" 
                                       class="gpds-btn gpds-btn--primary">
                                        <?php gpds_icon('map-pin', 16); ?>
                                        مسیریابی
                                    </a>
                                <?php endif; ?>
                                <button type="button" 
                                        class="gpds-btn gpds-btn--outline" 
                                        data-gpds-copy="<?php echo esc_attr($office['address']); ?>">
                                    <?php gpds_icon('tag', 16); ?>
                                    کپی آدرس
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- نقشه -->
                    <?php if ($office['lat'] && $office['lng']) : ?>
                        <div class="gpds-office-card gpds-office-card--map">
                            <h3 class="gpds-office-card__title">
                                <?php gpds_icon('map-pin', 18); ?>
                                موقعیت روی نقشه
                            </h3>
                            <div class="gpds-office-map"
                                 data-lat="<?php echo esc_attr($office['lat']); ?>"
                                 data-lng="<?php echo esc_attr($office['lng']); ?>"
                                 data-title="<?php echo esc_attr($office['title']); ?>"
                                 id="gpds-office-map">
                                <!-- Leaflet -->
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- توضیحات -->
                    <?php if ($office['content']) : ?>
                        <div class="gpds-office-card">
                            <h3 class="gpds-office-card__title">
                                <?php gpds_icon('info', 18); ?>
                                درباره این دفتر
                            </h3>
                            <div class="gpds-office-content">
                                <?php echo apply_filters('the_content', $office['content']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </div>

        </div>
    </main>
    
    <?php
endwhile;

get_footer();