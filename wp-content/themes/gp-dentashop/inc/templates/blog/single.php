<?php
/**
 * Template: Single Post
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header();

while (have_posts()) : the_post();
    
    gpds_increment_post_views(get_the_ID());
    
    ?>
    
    <main id="gpds-main" class="gpds-main gpds-single-post">
        <div class="gpds-container">

            <?php gpds_blog_breadcrumb(); ?>

            <article class="gpds-post" itemscope itemtype="https://schema.org/Article">

                <?php get_template_part('template-parts/blog/single/header'); ?>

                <div class="gpds-post__layout">

                    <div class="gpds-post__main">
                        <?php get_template_part('template-parts/blog/single/content'); ?>
                        <?php get_template_part('template-parts/blog/single/share'); ?>
                        <?php get_template_part('template-parts/blog/single/footer-meta'); ?>
                        <?php get_template_part('template-parts/blog/single/navigation'); ?>
                    </div>

                    <aside class="gpds-post__sidebar">
                        <?php get_template_part('template-parts/blog/single/toc'); ?>
                    </aside>

                </div>

                <?php get_template_part('template-parts/blog/single/related'); ?>


            </article>

        </div>
    </main>
    
    <?php
endwhile;

get_footer();