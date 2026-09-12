<?php
/**
 * Products Module - کارت محصول + کوئری‌ها
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

/**
 * گرفتن محصولات بر اساس نوع
 */
function gpds_get_products($args = []) {
    $defaults = [
        'type'    => 'recent',  // recent | sale | best | featured
        'limit'   => 12,
        'category'=> 0,
    ];
    $args = wp_parse_args($args, $defaults);
    
    if (!post_type_exists('product')) {
        return [];
    }
    
    $query_args = [
        'status'   => 'publish',
        'limit'    => $args['limit'],
        'orderby'  => 'date',
        'order'    => 'DESC',
    ];
    
    switch ($args['type']) {
        case 'sale':
            $query_args['include'] = wc_get_product_ids_on_sale();
            $query_args['limit']   = min($args['limit'], count($query_args['include']));
            break;
            
        case 'best':
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'total_sales';
            break;
            
        case 'featured':
            $query_args['featured'] = true;
            break;
    }
    
    if ($args['category']) {
        $query_args['category'] = [get_term($args['category'], 'product_cat')->slug];
    }
    
    return wc_get_products($query_args);
}