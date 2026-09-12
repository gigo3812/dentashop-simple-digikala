<?php
/**
 * Categories Module
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

/**
 * گرفتن دسته‌بندی‌های برتر
 */
function gpds_get_showcase_categories($limit = 8) {
    if (!taxonomy_exists('product_cat')) {
        return [];
    }
    
    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => true,
        'number'     => $limit,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ]);
    
    if (is_wp_error($terms)) {
        return [];
    }
    
    return $terms;
}