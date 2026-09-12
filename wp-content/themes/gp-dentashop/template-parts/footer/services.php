<?php
/**
 * Footer - Services (5 آیکون)
 */

if (!defined('ABSPATH')) exit;

$services = gpds_get_footer_services();

if (empty($services)) return;
?>

<div class="gpds-footer-services">
    <div class="gpds-container">
        <div class="gpds-footer-services__grid">
            <?php foreach ($services as $service) : ?>
                <div class="gpds-footer-service">
                    <div class="gpds-footer-service__icon">
                        <?php gpds_icon($service['icon'], 32); ?>
                    </div>
                    <div class="gpds-footer-service__text">
                        <div class="gpds-footer-service__title">
                            <?php echo esc_html($service['title']); ?>
                        </div>
                        <?php if (!empty($service['subtitle'])) : ?>
                            <div class="gpds-footer-service__subtitle">
                                <?php echo esc_html($service['subtitle']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>