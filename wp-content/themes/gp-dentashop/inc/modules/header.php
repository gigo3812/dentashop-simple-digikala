<?php
/**
 * Header Module - ماژول هدر
 * 
 * مسئول: بارگذاری CSS/JS هدر + توابع کمکی مخصوص هدر
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// ============================================
// بارگذاری توابع کمکی هدر
// ============================================
require_once GPDS_DIR . '/inc/modules/header-functions.php';

// ============================================
// Override هدر GeneratePress
// ============================================
add_action('after_setup_theme', function() {
    // حذف هدر پیش‌فرض GeneratePress
    remove_action('generate_header', 'generate_construct_header');
    
    // اضافه کردن هدر خودمون
    add_action('generate_header', 'gpds_render_header');
}, 20);

/**
 * رندر هدر سفارشی
 */
function gpds_render_header() {
    // اگه در صفحه‌ای هستیم که نباید هدر نشون بده
    if (is_page_template('page-templates/template-blank.php')) {
        return;
    }
    ?>
    <header id="gpds-header" class="gpds-header" role="banner">
        <?php
        // Topbar (ردیف اول)
        get_template_part('template-parts/header/topbar');
        
        // Mainbar (ردیف دوم) - فقط دسکتاپ
        get_template_part('template-parts/header/mainbar');
        
        // Mobile Header - فقط موبایل
        get_template_part('template-parts/header/mobile');
        
        // Mega Menu Panel (پنهان به صورت پیش‌فرض)
        get_template_part('template-parts/header/mega-menu');
        
        // Search Overlay (پنهان)
        get_template_part('template-parts/header/search-overlay');
        ?>
    </header>
    <?php
}

// ============================================
// اضافه کردن کلاس به body
// ============================================
add_filter('body_class', function($classes) {
    $classes[] = 'gpds-has-custom-header';
    return $classes;
});

// ============================================
// 🎯 Loading Screen - صفحه لودینگ
// ============================================
add_action('wp_body_open', 'gpds_render_loading_screen', 1);

function gpds_render_loading_screen() {
    ?>
    <!-- 🎯 Loading Screen -->
    <div id="gpds-loader" class="gpds-loader" aria-hidden="true">
        <div class="gpds-loader__inner">
            
            <!-- لوگو -->
            <div class="gpds-loader__logo">
                <?php 
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    echo '<span class="gpds-loader__logo-text">' . esc_html(get_bloginfo('name')) . '</span>';
                }
                ?>
            </div>
            
            <!-- Spinner (سه حلقه چرخان) -->
            <div class="gpds-loader__spinner">
                <div class="gpds-loader__spinner-circle"></div>
                <div class="gpds-loader__spinner-circle"></div>
                <div class="gpds-loader__spinner-circle"></div>
            </div>
            
            <!-- متن -->
            <div class="gpds-loader__text">
                در حال بارگذاری...
            </div>
            
        </div>
    </div>
    <!-- /Loading Screen -->
    <?php
}