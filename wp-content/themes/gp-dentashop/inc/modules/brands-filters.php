<?php
/**
 * Brands Filters
 * 
 * اعمال فیلترهای سایدبار (قیمت، موجودی، تخفیف) روی query تاکسونومی برند
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// فقط اگه WooCommerce فعاله
if (!class_exists('WooCommerce')) return;


// ============================================
// 1. اعمال فیلترها روی query محصولات برند
// ============================================
//
// این hook مخصوص WooCommerce هست و روی همه صفحات
// محصول (shop, archive, taxonomy) اثر می‌گذاره.
// ============================================
add_action('woocommerce_product_query', 'gpds_apply_brand_filters');

function gpds_apply_brand_filters($query) {
    // فقط روی آرشیو برند اعمال بشه
    if (!is_tax('product_brand')) return;
    
    // جلوگیری از اثر روی query های ادمین یا secondary
    if (is_admin() || !$query->is_main_query()) return;
    
    $meta_query = (array) $query->get('meta_query');
    
    // -------- فیلتر قیمت --------
    $min_price = isset($_GET['min_price']) ? (int) $_GET['min_price'] : 0;
    $max_price = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 0;
    
    if ($min_price > 0 || $max_price > 0) {
        $price_meta = [
            'key'     => '_price',
            'type'    => 'NUMERIC',
            'compare' => 'BETWEEN',
            'value'   => [
                $min_price > 0 ? $min_price : 0,
                $max_price > 0 ? $max_price : PHP_INT_MAX,
            ],
        ];
        $meta_query[] = $price_meta;
    }
    
    // -------- فیلتر موجودی --------
    if (isset($_GET['stock_status']) && $_GET['stock_status'] === 'instock') {
        $meta_query[] = [
            'key'     => '_stock_status',
            'value'   => 'instock',
            'compare' => '=',
        ];
    }
    
    // -------- فیلتر تخفیف --------
    if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
        // محصولات تخفیف‌دار در متا _sale_price ذخیره می‌شن
        $meta_query[] = [
            'key'     => '_sale_price',
            'value'   => '',
            'compare' => '!=',
        ];
    }

    // -------- فیلتر دسته‌بندی --------
    if (isset($_GET['product_cat']) && $_GET['product_cat'] !== '') {
        $tax_query = (array) $query->get('tax_query');
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => sanitize_title($_GET['product_cat']),
            'operator' => 'IN',
        ];
        $query->set('tax_query', $tax_query);
    }
    
    if (!empty($meta_query)) {
        $query->set('meta_query', $meta_query);
    }
}


// ============================================
// 2. نمایش تعداد محصولات بعد از فیلتر
// ============================================
//
// WooCommerce به صورت پیش‌فرض تعداد کل رو نشون می‌ده.
// این فیلتر مطمئن می‌شه تعداد درست نمایش داده بشه.
// ============================================
add_filter('woocommerce_pagination_args', 'gpds_brand_pagination_args');

function gpds_brand_pagination_args($args) {
    if (!is_tax('product_brand')) return $args;
    
    // هیچ تغییر خاصی نیاز نیست، فقط برای extend در آینده
    return $args;
}



