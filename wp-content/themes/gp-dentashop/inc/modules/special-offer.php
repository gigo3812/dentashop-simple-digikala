<?php
/**
 * Special Offer Helpers
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

/**
 * چک: آیا شگفت‌انگیزها فعاله؟
 */
function gpds_is_special_offer_active() {
    // چک فعال بودن
    if (!get_theme_mod('gpds_special_offer_enabled', true)) {
        return false;
    }
    
    // چک تاریخ انقضا
    $end_date = get_theme_mod('gpds_special_offer_end_date', '');
    
    if (!empty($end_date)) {
        $end_timestamp = strtotime($end_date);
        $now = current_time('timestamp');
        
        if ($end_timestamp && $now >= $end_timestamp) {
            return false; // منقضی شده
        }
    }
    
    return true;
}

/**
 * گرفتن timestamp پایان (برای تایمر)
 */
function gpds_get_special_offer_end() {
    $end_date = get_theme_mod('gpds_special_offer_end_date', '');
    
    if (empty($end_date)) {
        // اگه تاریخ تنظیم نشده، پایان امروز
        return strtotime('tomorrow midnight', current_time('timestamp')) - 1;
    }
    
    $end_timestamp = strtotime($end_date);
    
    if (!$end_timestamp || $end_timestamp < current_time('timestamp')) {
        // اگه منقضی شده یا نامعتبر، 24 ساعت از الان
        return current_time('timestamp') + DAY_IN_SECONDS;
    }
    
    return $end_timestamp;
}

/**
 * گرفتن محصولات شگفت‌انگیز
 */
function gpds_get_special_offer_products() {
    $slug  = get_theme_mod('gpds_special_offer_category', 'special-offer');
    $limit = (int) get_theme_mod('gpds_special_offer_limit', 8);
    
    if (!taxonomy_exists('product_cat')) {
        return [];
    }
    
    // چک وجود دسته
    $term = get_term_by('slug', $slug, 'product_cat');
    if (!$term || is_wp_error($term)) {
        return [];
    }
    
    return wc_get_products([
        'status'   => 'publish',
        'limit'    => $limit,
        'category' => [$slug],
        'orderby'  => 'date',
        'order'    => 'DESC',
    ]);
}

/**
 * گرفتن لینک "مشاهده همه"
 */
function gpds_get_special_offer_link() {
    $custom_link = get_theme_mod('gpds_special_offer_link', '');
    
    if (!empty($custom_link)) {
        return $custom_link;
    }
    
    $slug = get_theme_mod('gpds_special_offer_category', 'special-offer');
    $term = get_term_by('slug', $slug, 'product_cat');
    
    if ($term && !is_wp_error($term)) {
        return get_term_link($term);
    }
    
    return gpds_shop_url();
}