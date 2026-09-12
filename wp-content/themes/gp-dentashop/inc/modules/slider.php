<?php
/**
 * Slider Module
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

/**
 * گرفتن اسلایدها از محصولات ویژه
 * 
 * @param int $limit
 * @return array
 */
function gpds_get_slider_items($limit = 5) {
    if (!post_type_exists('product')) {
        return [];
    }
    
    // اول محصولات ویژه
    $products = wc_get_products([
        'status'   => 'publish',
        'featured' => true,
        'limit'    => $limit,
        'orderby'  => 'date',
        'order'    => 'DESC',
    ]);
    
    // اگه ویژه نبود، از محصولات جدید
    if (empty($products)) {
        $products = wc_get_products([
            'status'  => 'publish',
            'limit'   => $limit,
            'orderby' => 'date',
            'order'   => 'DESC',
        ]);
    }
    
    $items = [];
    foreach ($products as $product) {
        $image_id = $product->get_image_id();
        $items[] = [
            'id'     => $product->get_id(),
            'title'  => $product->get_name(),
            'url'    => $product->get_permalink(),
            'image'  => $image_id 
                ? wp_get_attachment_image_url($image_id, 'full') 
                : wc_placeholder_img_src(),
            'price'  => $product->get_price_html(),
            'type'   => 'product',
        ];
    }
    
    return $items;
}