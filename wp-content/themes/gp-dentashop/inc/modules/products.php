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
function gpds_get_products($args = [])
{
    $defaults = [
        'type'    => 'recent',  // recent | sale | best | featured
        'limit'   => 12,
        'category' => 0,
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


/**
 * محصولات پرفروش از یک دسته پرفروش (رندوم)
 * 
 * الگوریتم:
 *   1. top N دسته پرفروش رو بر اساس مجموع فروش پیدا می‌کنه
 *   2. یکی رو رندوم انتخاب می‌کنه
 *   3. محصولات اون دسته رو بر اساس فروش مرتب می‌کنه
 *   4. تعداد درخواستی رو برمی‌گردونه
 * 
 * @param int $limit     تعداد محصولات
 * @param int $top_pool  از بین چند تا دسته top انتخاب رندوم بشه
 * @return array{products: array, category: WP_Term|null}
 */
function gpds_get_best_selling_from_random_category($limit = 12, $top_pool = 5)
{
    $cache_key = 'gpds_best_from_cat_' . $limit . '_' . $top_pool;
    $cached    = get_transient($cache_key);

    // ---------- کش ----------
    if ($cached !== false && isset($cached['category_id'], $cached['product_ids'])) {
        $category = get_term($cached['category_id'], 'product_cat');

        if ($category && !is_wp_error($category) && !empty($cached['product_ids'])) {
            $products = [];
            foreach ($cached['product_ids'] as $pid) {
                $p = wc_get_product($pid);
                if ($p) $products[] = $p;
            }
            if (!empty($products)) {
                return ['products' => $products, 'category' => $category];
            }
        }
    }

    global $wpdb;

    // ---------- ۱. top دسته‌های پرفروش ----------
    $top_categories = $wpdb->get_results($wpdb->prepare("
        SELECT tt.term_id, SUM(CAST(pm.meta_value AS UNSIGNED)) AS total_sales
        FROM {$wpdb->term_taxonomy} tt
        INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
        INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'total_sales'
        WHERE tt.taxonomy = 'product_cat'
          AND p.post_type = 'product'
          AND p.post_status = 'publish'
          AND pm.meta_value > 0
        GROUP BY tt.term_id
        HAVING total_sales > 0
        ORDER BY total_sales DESC
        LIMIT %d
    ", $top_pool));

    // ---------- fallback: پرتعدادترین دسته‌ها ----------
    if (empty($top_categories)) {
        $top_categories = $wpdb->get_results($wpdb->prepare("
            SELECT tt.term_id, tt.count AS total_sales
            FROM {$wpdb->term_taxonomy} tt
            WHERE tt.taxonomy = 'product_cat'
              AND tt.count > 0
            ORDER BY tt.count DESC
            LIMIT %d
        ", $top_pool));
    }

    if (empty($top_categories)) {
        return ['products' => [], 'category' => null];
    }

    // ---------- ۲. انتخاب رندوم ----------
    $chosen      = $top_categories[array_rand($top_categories)];
    $category_id = (int) $chosen->term_id;

    $category = get_term($category_id, 'product_cat');
    if (!$category || is_wp_error($category)) {
        return ['products' => [], 'category' => null];
    }

    // ---------- ۳. محصولات این دسته ----------
    $product_ids = $wpdb->get_col($wpdb->prepare("
        SELECT p.ID
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
        INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
        LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'total_sales'
        WHERE p.post_type = 'product'
          AND p.post_status = 'publish'
          AND tt.taxonomy = 'product_cat'
          AND tt.term_id = %d
        ORDER BY CAST(COALESCE(pm.meta_value, 0) AS UNSIGNED) DESC, p.post_date DESC
        LIMIT %d
    ", $category_id, $limit));

    if (empty($product_ids)) {
        return ['products' => [], 'category' => null];
    }

    // ---------- ۴. ساخت آبجکت ----------
    $products = [];
    foreach ($product_ids as $pid) {
        $p = wc_get_product($pid);
        if ($p) $products[] = $p;
    }

    // ---------- ۵. کش (۳۰ دقیقه) ----------
    set_transient($cache_key, [
        'category_id' => $category_id,
        'product_ids' => $product_ids,
    ], 30 * MINUTE_IN_SECONDS);

    return ['products' => $products, 'category' => $category];
}

/**
 * پاک کردن کش پرفروش‌ها هنگام تغییر
 */
add_action('woocommerce_order_status_completed', 'gpds_clear_best_sellers_cache');
add_action('woocommerce_update_product',        'gpds_clear_best_sellers_cache');
add_action('save_post_product',                 'gpds_clear_best_sellers_cache');

function gpds_clear_best_sellers_cache()
{
    global $wpdb;
    $wpdb->query("
        DELETE FROM {$wpdb->options}
        WHERE option_name LIKE '_transient_gpds_best_from_cat_%'
           OR option_name LIKE '_transient_timeout_gpds_best_from_cat_%'
    ");
}
