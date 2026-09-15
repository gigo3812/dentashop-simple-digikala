<?php
/**
 * Template Tags - توابع کمکی
 * 
 * توابع عمومی که در تمپلیت‌ها استفاده می‌شن
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// ============================================
// توابع آیکون SVG
// ============================================

/**
 * چاپ آیکون SVG از پوشه assets/icons
 * 
 * @param string $name    نام فایل (بدون .svg)
 * @param int    $size    اندازه (پیکسل)
 * @param string $class   کلاس اضافه
 * @param array  $attrs   ویژگی‌های اضافی
 */
function gpds_icon($name, $size = 20, $class = '', $attrs = []) {
    $file = GPDS_DIR . '/assets/icons/' . sanitize_file_name($name) . '.svg';
    
    if (!file_exists($file)) {
        return;
    }
    
    $svg = file_get_contents($file);
    
    if (!$svg) {
        return;
    }
    
    // اضافه کردن کلاس
    $classes = 'gpds-icon gpds-icon--' . esc_attr($name);
    if ($class) {
        $classes .= ' ' . esc_attr($class);
    }
    
    // جایگزینی ویژگی‌ها
    $svg = preg_replace(
        '/<svg([^>]*)>/',
        '<svg$1 class="' . $classes . '" width="' . intval($size) . '" height="' . intval($size) . '" aria-hidden="true">',
        $svg,
        1
    );
    
    echo $svg; // phpcs:ignore
}

/**
 * گرفتن آیکون به صورت رشته (برای بازگشت)
 */
function gpds_get_icon($name, $size = 20, $class = '') {
    ob_start();
    gpds_icon($name, $size, $class);
    return ob_get_clean();
}

// ============================================
// توابع کمکی قالب
// ============================================

/**
 * چاپ لوگو
 */
function gpds_site_logo($args = []) {
    $defaults = [
        'height' => 40,
        'class'  => 'gpds-logo',
    ];
    $args = wp_parse_args($args, $defaults);
    
    if (has_custom_logo()) {
        the_custom_logo();
    } else {
        printf(
            '<a href="%s" class="%s" aria-label="%s">%s</a>',
            esc_url(home_url('/')),
            esc_attr($args['class']),
            esc_attr(get_bloginfo('name')),
            esc_html(get_bloginfo('name'))
        );
    }
}

/**
 * چاپ منو با کلاس خاص
 */
function gpds_menu($location, $args = []) {
    if (!has_nav_menu($location)) {
        return;
    }
    
    $defaults = [
        'theme_location' => $location,
        'container'      => false,
        'menu_class'     => 'gpds-menu',
        'depth'          => 3,
        'fallback_cb'    => false,
    ];
    
    wp_nav_menu(wp_parse_args($args, $defaults));
}

/**
 * چک: آیا صفحه فعلی فروشگاهی است؟
 */
function gpds_is_shop_page() {
    if (!function_exists('is_woocommerce')) return false;
    
    return is_woocommerce()
        || is_cart()
        || is_checkout()
        || is_account_page()
        || is_shop()
        || is_product()
        || is_product_category()
        || is_product_tag();
}

/**
 * گرفتن تعداد اقلام سبد خرید
 */
function gpds_cart_count() {
    if (!function_exists('WC')) return 0;
    
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    return intval($count);
}

/**
 * گرفتن مبلغ سبد خرید
 */
function gpds_cart_subtotal() {
    if (!function_exists('WC')) return '';
    
    return WC()->cart ? WC()->cart->get_cart_subtotal() : '';
}

// ============================================
// توابع اختیاری (اختصار)
// ============================================

/**
 * خواندن فایل تمپلیت با متغیر
 */
function gpds_part($slug, $name = '', $data = []) {
    $template = 'template-parts/' . $slug;
    if ($name) {
        $template .= '-' . $name;
    }
    
    if (!empty($data)) {
        // استخراج متغیرها برای استفاده در تمپلیت
        extract($data, EXTR_SKIP);
    }
    
    get_template_part($template, null, $data);
}

/**
 * چاپ کلاس‌های شرطی برای بدنه
 */
function gpds_body_classes($classes = []) {
    if (is_front_page()) {
        $classes[] = 'gpds-is-home';
    }
    if (gpds_is_shop_page()) {
        $classes[] = 'gpds-is-shop';
    }
    if (is_product()) {
        $classes[] = 'gpds-is-single-product';
    }
    if (is_user_logged_in()) {
        $classes[] = 'gpds-user-logged-in';
    }
    return $classes;
}
add_filter('body_class', 'gpds_body_classes');

/**
 * گرفتن URL صفحه اصلی فروشگاه
 */
function gpds_shop_url() {
    if (function_exists('wc_get_page_id')) {
        $shop_id = wc_get_page_id('shop');
        if ($shop_id > 0) {
            return get_permalink($shop_id);
        }
    }
    return home_url('/shop/');
}

/**
 * Breadcrumb سفارشی (RTL + سازگار با WooCommerce)
 */
function gpds_wc_breadcrumb() {
    if (is_front_page()) return;
    
    $sep = gpds_get_icon('chevron-left', 14);
    
    echo '<nav class="gpds-breadcrumb" aria-label="مسیر">';
    echo '<a href="' . esc_url(home_url('/')) . '">خانه</a>';
    
    if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<a href="' . esc_url(gpds_shop_url()) . '">فروشگاه</a>';
        
        if (is_product_category() || is_product_tag()) {
            $term = get_queried_object();
            
            // والدها
            $parents = array_reverse(get_ancestors($term->term_id, 'product_cat'));
            foreach ($parents as $parent_id) {
                $parent = get_term($parent_id);
                if ($parent && !is_wp_error($parent)) {
                    echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
                    echo '<a href="' . esc_url(get_term_link($parent)) . '">' . esc_html($parent->name) . '</a>';
                }
            }
            
            echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
            echo '<span>' . esc_html($term->name) . '</span>';
        }
    }
    


    // ===== برند (product_brand) =====
    if (is_tax('product_brand')) {
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<a href="' . esc_url(gpds_shop_url()) . '">فروشگاه</a>';
        
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<a href="' . esc_url(gpds_get_all_brands_url()) . '">برندها</a>';
        
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<span>' . esc_html(get_queried_object()->name) . '</span>';
    }
    
    // ===== صفحه همه برندها (/brands/) =====
    if (get_query_var('gpds_view') === 'all_brands') {
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<span>برندها</span>';
    }

    if (is_product()) {
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<a href="' . esc_url(gpds_shop_url()) . '">فروشگاه</a>';
        
        $terms = get_the_terms(get_the_ID(), 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            $term = array_shift($terms);
            $parents = array_reverse(get_ancestors($term->term_id, 'product_cat'));
            foreach ($parents as $parent_id) {
                $parent = get_term($parent_id);
                if ($parent && !is_wp_error($parent)) {
                    echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
                    echo '<a href="' . esc_url(get_term_link($parent)) . '">' . esc_html($parent->name) . '</a>';
                }
            }
            echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
            echo '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
        }
        
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<span>' . esc_html(get_the_title()) . '</span>';
    }
    
    if (is_singular('post')) {
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<span>' . esc_html(get_the_title()) . '</span>';
    }
    
    echo '</nav>';
}