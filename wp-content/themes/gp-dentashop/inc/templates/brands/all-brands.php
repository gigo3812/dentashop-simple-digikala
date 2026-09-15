<?php
/**
 * Template: لیست همه برندها
 * 
 * URL: /brands/
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header();

$brands = function_exists('gpds_get_active_brands') 
    ? gpds_get_active_brands(200,true) 
    : [];
?>

<main id="gpds-main" class="gpds-main gpds-brands-page">
    <div class="gpds-container">

        <?php
        if (function_exists('gpds_wc_breadcrumb')) {
            gpds_wc_breadcrumb();
        }
        ?>

        <header class="gpds-page-header">
            <h1 class="gpds-page-title">همه برندها</h1>
        </header>

        <?php if (empty($brands)) : ?>

            <p class="gpds-empty">برندی یافت نشد.</p>

        <?php else : ?>

            <div class="gpds-brands-page-grid">
                <?php foreach ($brands as $brand) : ?>
                    <a href="<?php echo esc_url($brand['url']); ?>"
                       class="gpds-brand-page-item">
                        <span class="gpds-brand-page-item__logo">
                            <img src="<?php echo esc_url($brand['logo']); ?>"
                                 alt="<?php echo esc_attr($brand['name']); ?>"
                                 loading="lazy">
                        </span>
                        <span class="gpds-brand-page-item__name">
                            <?php echo esc_html($brand['name']); ?>
                        </span>
                        <span class="gpds-brand-page-item__count">
                            <?php echo esc_html($brand['count']); ?> محصول
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>