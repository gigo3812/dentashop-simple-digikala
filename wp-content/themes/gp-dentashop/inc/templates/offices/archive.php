<?php
/**
 * Template: Offices Archive
 * URL: /offices/
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header();

$query = gpds_get_offices(['limit' => -1]);
?>

<main id="gpds-main" class="gpds-main gpds-offices-page">
    <div class="gpds-container">

        <?php gpds_wc_breadcrumb(); ?>

        <header class="gpds-page-header gpds-offices-header">
            <h1 class="gpds-page-title">دفاتر و مراکز ما</h1>
            <p class="gpds-page-subtitle">
                <?php echo esc_html($query->found_posts); ?> دفتر در سراسر کشور
            </p>
        </header>

        <?php if ($query->have_posts()) : ?>

            <div class="gpds-offices-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <?php get_template_part('template-parts/offices/card'); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

        <?php else : ?>

            <div class="gpds-empty-state">
                <p>هنوز دفتری ثبت نشده است.</p>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer();