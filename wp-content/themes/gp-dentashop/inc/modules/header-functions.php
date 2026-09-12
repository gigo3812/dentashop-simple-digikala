<?php
/**
 * Header Helper Functions
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// ============================================
// گرفتن دسته‌بندی‌های محصولات برای Mega Menu
// ============================================

/**
 * گرفتن دسته‌بندی‌های سطح اول محصولات
 * 
 * @param int $limit تعداد
 * @return array
 */
function gpds_get_top_product_categories($limit = 12) {
    if (!taxonomy_exists('product_cat')) {
        return [];
    }
    
    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => true,
        'number'     => $limit,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
    ]);
    
    if (is_wp_error($terms)) {
        return [];
    }
    
    return $terms;
}

/**
 * گرفتن زیردسته‌های یک دسته
 * 
 * @param int $parent_id
 * @param int $limit
 * @return array
 */
function gpds_get_child_categories($parent_id, $limit = 8) {
    if (!taxonomy_exists('product_cat')) {
        return [];
    }
    
    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => intval($parent_id),
        'hide_empty' => true,
        'number'     => $limit,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
    ]);
    
    if (is_wp_error($terms)) {
        return [];
    }
    
    return $terms;
}

// ============================================
// Ajax Search Endpoint
// ============================================
add_action('wp_ajax_gpds_search',        'gpds_ajax_search_handler');
add_action('wp_ajax_nopriv_gpds_search', 'gpds_ajax_search_handler');

function gpds_ajax_search_handler() {
    check_ajax_referer('gpds_nonce', 'nonce');
    
    $term = isset($_POST['term']) ? sanitize_text_field(wp_unslash($_POST['term'])) : '';
    
    if (mb_strlen($term) < 2) {
        wp_send_json_error([
            'message' => 'حداقل ۲ حرف وارد کنید',
        ]);
    }
    
    // جستجوی محصولات
    $products = [];
    if (post_type_exists('product')) {
        $query = new WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 6,
            's'              => $term,
            'no_found_rows'  => true,
            'fields'         => 'ids',
        ]);
        
        foreach ($query->posts as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) continue;
            
            $products[] = [
                'id'    => $product_id,
                'title' => $product->get_name(),
                'url'   => $product->get_permalink(),
                'price' => wp_strip_all_tags($product->get_price_html()),
                'image' => wp_get_attachment_image_url(
                    $product->get_image_id(),
                    'gpds-card-sm'
                ) ?: wc_placeholder_img_src('gpds-card-sm'),
            ];
        }
    }
    
    // جستجوی دسته‌بندی‌ها
    $categories = [];
    if (taxonomy_exists('product_cat')) {
        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'name__like' => $term,
            'number'     => 4,
            'hide_empty' => true,
        ]);
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $cat) {
                $categories[] = [
                    'id'    => $cat->term_id,
                    'title' => $cat->name,
                    'url'   => get_term_link($cat),
                    'count' => $cat->count,
                ];
            }
        }
    }
    
    wp_send_json_success([
        'products'   => $products,
        'categories' => $categories,
        'total'      => count($products) + count($categories),
        'term'       => $term,
    ]);
}

// ============================================
// Mini Cart Ajax
// ============================================
add_action('wp_ajax_gpds_get_cart',        'gpds_ajax_get_cart');
add_action('wp_ajax_nopriv_gpds_get_cart', 'gpds_ajax_get_cart');

function gpds_ajax_get_cart() {
    check_ajax_referer('gpds_nonce', 'nonce');
    
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error();
    }
    
    $cart = WC()->cart;
    $items = [];
    
    foreach ($cart->get_cart() as $key => $item) {
        $product = $item['data'];
        if (!$product) continue;
        
        $items[] = [
            'key'      => $key,
            'title'    => $product->get_name(),
            'qty'      => $item['quantity'],
            'price'    => wp_strip_all_tags($product->get_price_html()),
            'image'    => wp_get_attachment_image_url(
                $product->get_image_id(),
                'gpds-card-sm'
            ),
            'url'      => $product->get_permalink(),
        ];
    }
    
    wp_send_json_success([
        'count'    => $cart->get_cart_contents_count(),
        'subtotal' => wp_strip_all_tags($cart->get_cart_subtotal()),
        'items'    => $items,
        'cart_url' => wc_get_cart_url(),
        'checkout_url' => wc_get_checkout_url(),
    ]);
}