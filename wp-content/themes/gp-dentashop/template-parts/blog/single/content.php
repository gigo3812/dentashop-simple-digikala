<?php
/**
 * Single Post - Content
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-post__content" itemprop="articleBody">
    <?php the_content(); ?>

    <?php
    wp_link_pages([
        'before' => '<div class="gpds-post__pagination">',
        'after'  => '</div>',
    ]);
    ?>
</div>