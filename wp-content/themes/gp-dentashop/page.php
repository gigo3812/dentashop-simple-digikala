<?php
/**
 * Page Template - برای همه برگه‌ها
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<main id="gpds-main" class="gpds-main gpds-page" style="margin: 30px 20px; width: 100%;">

    <div class="gpds-container">

        <?php
        while (have_posts()) :
            the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class('gpds-page-article'); ?>>

                <header class="gpds-page-header">
                    <h1 class="gpds-page-title"><?php the_title(); ?></h1>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="gpds-page-thumb">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="gpds-page-content">
                    <?php the_content(); ?>
                </div>

            </article>

        <?php endwhile; ?>

    </div>

</main>

<?php get_footer(); ?>