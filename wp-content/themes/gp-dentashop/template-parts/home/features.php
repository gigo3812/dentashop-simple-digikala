<?php
/**
 * Home - Features Section
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$features = gpds_get_features();

if (empty($features)) {
    return;
}
?>

<section class="gpds-section gpds-features-section" aria-label="خدمات">
    <div class="gpds-container">
        <div class="gpds-card gpds-features">
            <?php foreach ($features as $feature) : ?>
                <a 
                    href="<?php echo esc_url($feature['url']); ?>" 
                    class="gpds-feature"
                >
                    <span class="gpds-feature__icon">
                        <?php gpds_icon($feature['icon'], 32); ?>
                    </span>
                    <span class="gpds-feature__title">
                        <?php echo esc_html($feature['title']); ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>