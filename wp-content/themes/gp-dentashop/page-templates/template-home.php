<?php
/**
 * Template Name: صفحه اصلی دنتاشاپ
 * Template Post Type: page
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<main id="gpds-main" class="gpds-main gpds-home">

    <?php
    // 1. استوری‌ها
    get_template_part('template-parts/home/stories');
    
    // 2. اسلایدر
    get_template_part('template-parts/home/slider');
    
    // 3. خدمات (آیکون‌ها)
    get_template_part('template-parts/home/features');
    
    // 4. شگفت‌انگیزها
    get_template_part('template-parts/home/special-offer');
    
    // 5. بنرها
    get_template_part('template-parts/home/banners');
    
    // 6. دسته‌بندی‌ها
    get_template_part('template-parts/home/categories');
    
    // 7. برندها
    get_template_part('template-parts/home/brands');
    
    // 8. پرفروش‌ترین‌ها
    get_template_part('template-parts/home/best-sellers');
    
    // 9. خواندنی‌ها
    get_template_part('template-parts/home/blog');
    ?>

</main>

<?php get_footer();