<?php

/**
 * Footer - Links + Newsletter + Socials
 */

if (!defined('ABSPATH')) exit;

$link_groups = gpds_get_footer_links();
$socials     = gpds_get_socials();
?>

<div class="gpds-footer-links">
    <div class="gpds-container">
        <div class="gpds-footer-links__inner">

            <!-- ستون‌های لینک -->
            <?php foreach ($link_groups as $group) : ?>
                <div class="gpds-footer-links__col">
                    <h3 class="gpds-footer-links__title">
                        <?php echo esc_html($group['title']); ?>
                    </h3>
                    <ul class="gpds-footer-links__list">
                        <?php foreach ($group['links'] as $link) : ?>
                            <li>
                                <a href="<?php echo esc_url($link['url']); ?>">
                                    <?php echo esc_html($link['title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

            <!-- ستون شبکه‌ها + اینماد -->
            <div class="gpds-footer-links__col gpds-footer-links__col--social">
                <h3 class="gpds-footer-links__title">همراه ما باشید</h3>

                <!-- شبکه‌های اجتماعی -->
                <div class="gpds-footer-socials">
                    <?php foreach ($socials as $social) : ?>
                        <a
                            href="<?php echo esc_url($social['url']); ?>"
                            class="gpds-footer-social"
                            aria-label="<?php echo esc_attr($social['title']); ?>"
                            target="_blank"
                            title="<?php echo esc_attr($social['title']); ?>"
                            rel="noopener noreferrer">
                            <?php gpds_icon($social['icon'], 20); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- 🎯 اینماد -->
                <div class="gpds-footer-enamad">
                    <a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=7609827&Code=KqRP1oiHgKaInmtUtckF121R7HSl3yFN'>
                        <img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=7609827&Code=KqRP1oiHgKaInmtUtckF121R7HSl3yFN' alt='نماد اعتماد' style='cursor:pointer' code='KqRP1oiHgKaInmtUtckF121R7HSl3yFN'>
                    </a>
                </div>

                <?php /* 🚫 خبرنامه غیرفعال شد
                
                <!-- خبرنامه -->
                <form class="gpds-footer-newsletter" data-gpds-newsletter>
                    <label class="gpds-footer-newsletter__label">
                        با ثبت ایمیل، از جدیدترین تخفیف‌ها باخبر شوید
                    </label>
                    <div class="gpds-footer-newsletter__row">
                        <input 
                            type="email" 
                            name="email" 
                            class="gpds-input"
                            placeholder="ایمیل شما"
                            aria-label="ایمیل"
                            required
                        >
                        <button 
                            type="submit" 
                            class="gpds-btn gpds-btn--primary"
                            aria-label="ثبت"
                        >
                            ثبت
                        </button>
                    </div>
                    <div class="gpds-footer-newsletter__message" data-gpds-newsletter-message></div>
                </form>
                */ ?>

            </div>

        </div>
    </div>
</div>