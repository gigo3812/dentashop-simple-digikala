<?php
/**
 * Shop Filters - فیلترهای سایدبار
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// فقط اگر WooCommerce فعاله
if (!function_exists('wc_get_product')) return;

// تشخیص صفحه فعلی
$is_brand_page    = is_tax('product_brand');
$is_shop_page     = is_shop() || is_product_category();
$current_brand    = $is_brand_page ? get_queried_object() : null;
$current_brand_id = $current_brand ? $current_brand->term_id : 0;

// URL پایه برای لینک‌ها (برند یا فروشگاه)
$base_url = $is_brand_page ? get_term_link($current_brand) : gpds_shop_url();

// دسته‌بندی فعلی از URL
$current_cat_slug = isset($_GET['product_cat']) ? sanitize_title($_GET['product_cat']) : '';

// ============================================
// گرفتن دسته‌بندی‌ها
// ============================================
if ($is_brand_page && $current_brand_id) {
    // توی صفحه برند: فقط دسته‌هایی که محصولات این برند توشون هستن
    
    // گرفتن ID محصولات این برند
    $product_ids = get_posts([
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => [
            [
                'taxonomy' => 'product_brand',
                'field'    => 'term_id',
                'terms'    => $current_brand_id,
            ],
        ],
    ]);
    
    if (!empty($product_ids)) {
        // گرفتن دسته‌های محصولات این برند
        $all_categories = wp_get_object_terms($product_ids, 'product_cat', [
            'orderby' => 'name',
            'order'   => 'ASC',
        ]);
        
        // حذف تکراری‌ها و خطاها
        if (!is_wp_error($all_categories)) {
            $unique = [];
            foreach ($all_categories as $cat) {
                $unique[$cat->term_id] = $cat;
            }
            $all_categories = array_values($unique);
        } else {
            $all_categories = [];
        }
    } else {
        $all_categories = [];
    }
} else {
    // توی صفحه فروشگاه: همه دسته‌های اصلی
    $all_categories = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => true,
        'number'     => 15,
    ]);
}

// ============================================
// توابع کمکی برای ساخت URL
// ============================================
if (!function_exists('gpds_filter_url')) {
    /**
     * ساخت URL فیلتر با حفظ پارامترهای فعلی
     */
    function gpds_filter_url($base_url, $params = [], $remove = []) {
        $url = $base_url;
        $query = [];
        
        // پارامترهای فعلی GET
        foreach ($_GET as $key => $value) {
            if (in_array($key, $remove, true)) continue;
            if (in_array($key, ['paged', 'page'], true)) continue;
            $query[$key] = $value;
        }
        
        // پارامترهای جدید
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
            } else {
                $query[$key] = $value;
            }
        }
        
        if (!empty($query)) {
            $url = add_query_arg($query, $url);
        }
        
        return $url;
    }
}
?>

<div class="gpds-shop-filters">
    
    <!-- فیلتر دسته‌بندی‌ها -->
    <?php if (!empty($all_categories) && !is_wp_error($all_categories)) : ?>
    <div class="gpds-filter-group <?php echo $current_cat_slug ? 'is-open' : ''; ?>">
        <button 
            type="button" 
            class="gpds-filter-group__header" 
            data-gpds-filter-toggle
        >
            <span>دسته‌بندی‌ها</span>
            <?php gpds_icon('chevron-down', 16, 'gpds-filter-group__arrow'); ?>
        </button>
        <div class="gpds-filter-group__body">
            <ul class="gpds-filter-list">
                <?php foreach ($all_categories as $cat) : 
                    $is_active = ($current_cat_slug === $cat->slug);
                    
                    // لینک دسته: توی صفحه برند، همون صفحه برند با فیلتر دسته
                    $cat_url = $is_brand_page
                        ? gpds_filter_url($base_url, ['product_cat' => $cat->slug])
                        : get_term_link($cat);
                ?>
                    <li class="gpds-filter-list__item">
                        <a 
                            href="<?php echo esc_url($cat_url); ?>" 
                            class="gpds-filter-list__link <?php echo $is_active ? 'is-active' : ''; ?>"
                        >
                            <span><?php echo esc_html($cat->name); ?></span>
                            <span class="gpds-filter-list__count"><?php echo esc_html($cat->count); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
                
                <?php if ($current_cat_slug) : ?>
                    <li class="gpds-filter-list__item">
                        <a 
                            href="<?php echo esc_url(gpds_filter_url($base_url, [], ['product_cat'])); ?>" 
                            class="gpds-filter-list__clear"
                        >
                            <?php gpds_icon('close', 12); ?>
                            <span>حذف فیلتر دسته</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- فیلتر قیمت -->
    <div class="gpds-filter-group <?php echo (isset($_GET['min_price']) || isset($_GET['max_price'])) ? 'is-open' : ''; ?>">
        <button 
            type="button" 
            class="gpds-filter-group__header" 
            data-gpds-filter-toggle
        >
            <span>محدوده قیمت</span>
            <?php gpds_icon('chevron-down', 16, 'gpds-filter-group__arrow'); ?>
        </button>
        <div class="gpds-filter-group__body">
            <?php
            // گرفتن بازه قیمت واقعی
            // توی صفحه برند، فقط از محصولات این برند
            global $wpdb;
            
            if ($is_brand_page && $current_brand_id) {
                $min_price = (int) $wpdb->get_var($wpdb->prepare("
                    SELECT MIN(CAST(pm.meta_value AS UNSIGNED)) 
                    FROM {$wpdb->postmeta} pm
                    INNER JOIN {$wpdb->term_relationships} tr 
                        ON tr.object_id = pm.post_id
                    INNER JOIN {$wpdb->term_taxonomy} tt 
                        ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    WHERE pm.meta_key = '_price' 
                    AND pm.meta_value != ''
                    AND tt.taxonomy = 'product_brand'
                    AND tt.term_id = %d
                ", $current_brand_id));
                
                $max_price = (int) $wpdb->get_var($wpdb->prepare("
                    SELECT MAX(CAST(pm.meta_value AS UNSIGNED)) 
                    FROM {$wpdb->postmeta} pm
                    INNER JOIN {$wpdb->term_relationships} tr 
                        ON tr.object_id = pm.post_id
                    INNER JOIN {$wpdb->term_taxonomy} tt 
                        ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    WHERE pm.meta_key = '_price' 
                    AND pm.meta_value != ''
                    AND tt.taxonomy = 'product_brand'
                    AND tt.term_id = %d
                ", $current_brand_id));
            } else {
                $min_price = (int) $wpdb->get_var("
                    SELECT MIN(CAST(meta_value AS UNSIGNED)) 
                    FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_price' 
                    AND meta_value != ''
                ");
                $max_price = (int) $wpdb->get_var("
                    SELECT MAX(CAST(meta_value AS UNSIGNED)) 
                    FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_price' 
                    AND meta_value != ''
                ");
            }
            
            if ($min_price && $max_price) :
            ?>
                <form method="get" action="<?php echo esc_url($base_url); ?>" class="gpds-price-filter">
                    <?php
                    // حفظ پارامترهای فعلی (به جز قیمت و صفحه)
                    foreach ($_GET as $key => $value) {
                        if (in_array($key, ['min_price', 'max_price', 'paged', 'page'], true)) continue;
                        if (is_array($value)) continue;
                        printf('<input type="hidden" name="%s" value="%s">', esc_attr($key), esc_attr($value));
                    }
                    ?>
                    <div class="gpds-price-filter__inputs">
                        <input 
                            type="number" 
                            name="min_price" 
                            value="<?php echo isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : ''; ?>"
                            placeholder="<?php echo esc_attr(number_format($min_price)); ?>"
                            class="gpds-input gpds-input--sm"
                        >
                        <span class="gpds-price-filter__sep">تا</span>
                        <input 
                            type="number" 
                            name="max_price" 
                            value="<?php echo isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : ''; ?>"
                            placeholder="<?php echo esc_attr(number_format($max_price)); ?>"
                            class="gpds-input gpds-input--sm"
                        >
                    </div>
                    <button type="submit" class="gpds-btn gpds-btn--secondary gpds-btn--sm gpds-btn--block">
                        اعمال فیلتر قیمت
                    </button>
                    
                    <?php if (isset($_GET['min_price']) || isset($_GET['max_price'])) : ?>
                        <a 
                            href="<?php echo esc_url(gpds_filter_url($base_url, [], ['min_price', 'max_price'])); ?>" 
                            class="gpds-filter-clear-link"
                        >
                            <?php gpds_icon('close', 12); ?>
                            حذف فیلتر قیمت
                        </a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- فیلتر موجودی -->
    <div class="gpds-filter-group <?php echo (isset($_GET['stock_status']) || isset($_GET['on_sale'])) ? 'is-open' : ''; ?>">
        <button 
            type="button" 
            class="gpds-filter-group__header" 
            data-gpds-filter-toggle
        >
            <span>موجودی</span>
            <?php gpds_icon('chevron-down', 16, 'gpds-filter-group__arrow'); ?>
        </button>
        <div class="gpds-filter-group__body">
            <label class="gpds-filter-checkbox">
                <input 
                    type="checkbox" 
                    value="instock"
                    <?php checked(isset($_GET['stock_status']) && $_GET['stock_status'] === 'instock'); ?>
                    onchange="window.location.href='<?php echo esc_url(gpds_filter_url($base_url, ['stock_status' => 'instock'])); ?>'.replace('stock_status=instock','stock_status=' + (this.checked ? 'instock' : ''));"
                    data-filter-url-checked="<?php echo esc_url(gpds_filter_url($base_url, ['stock_status' => 'instock'])); ?>"
                    data-filter-url-unchecked="<?php echo esc_url(gpds_filter_url($base_url, [], ['stock_status'])); ?>"
                    data-gpds-filter-checkbox
                >
                <span>فقط کالاهای موجود</span>
            </label>
            
            <label class="gpds-filter-checkbox">
                <input 
                    type="checkbox" 
                    value="onsale"
                    <?php checked(isset($_GET['on_sale']) && $_GET['on_sale'] === '1'); ?>
                    data-filter-url-checked="<?php echo esc_url(gpds_filter_url($base_url, ['on_sale' => '1'])); ?>"
                    data-filter-url-unchecked="<?php echo esc_url(gpds_filter_url($base_url, [], ['on_sale'])); ?>"
                    data-gpds-filter-checkbox
                >
                <span>فقط کالاهای تخفیف‌دار</span>
            </label>
        </div>
    </div>
    
</div>

<script>
// مدیریت checkbox ها (تمیزتر از inline onchange)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-gpds-filter-checkbox]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var url = this.checked 
                ? this.dataset.filterUrlChecked 
                : this.dataset.filterUrlUnchecked;
            if (url) window.location.href = url;
        });
    });
});
</script>

<style>
.gpds-filter-clear-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
    padding: 4px 8px;
    font-size: var(--gpds-fs-xs);
    color: var(--gpds-color-danger);
    text-decoration: none;
    border-radius: var(--gpds-radius-sm);
    transition: opacity 0.2s;
}
.gpds-filter-clear-link:hover {
    opacity: 0.75;
    background: var(--gpds-bg-hover);
}
.gpds-filter-list__clear {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 8px;
    font-size: var(--gpds-fs-xs);
    color: var(--gpds-color-danger);
    text-decoration: none;
}
.gpds-filter-list__clear:hover {
    opacity: 0.75;
}
</style>