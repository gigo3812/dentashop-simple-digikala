<?php
/**
 * Shop Filters - فیلترهای سایدبار
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// فقط اگر WooCommerce فعاله
if (!function_exists('wc_get_product')) return;
?>

<div class="gpds-shop-filters">
    
    <!-- فیلتر دسته‌بندی‌ها -->
    <?php
    $all_categories = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => true,
        'number'     => 15,
    ]);
    
    if (!is_wp_error($all_categories) && !empty($all_categories)) :
    ?>
    <div class="gpds-filter-group">
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
                    $is_active = is_product_category($cat->slug);
                ?>
                    <li class="gpds-filter-list__item">
                        <a 
                            href="<?php echo esc_url(get_term_link($cat)); ?>" 
                            class="gpds-filter-list__link <?php echo $is_active ? 'is-active' : ''; ?>"
                        >
                            <span><?php echo esc_html($cat->name); ?></span>
                            <span class="gpds-filter-list__count"><?php echo esc_html($cat->count); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- فیلتر قیمت -->
    <div class="gpds-filter-group">
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
            // گرفتن بازه قیمت واقعی از دیتابیس
            global $wpdb;
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
            
            if ($min_price && $max_price) :
            ?>
                <form method="get" action="" class="gpds-price-filter">
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
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- فیلتر موجودی -->
    <div class="gpds-filter-group">
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
                    onchange="if(this.checked){window.location.href='<?php echo esc_url(add_query_arg('stock_status', 'instock')); ?>'}else{window.location.href='<?php echo esc_url(remove_query_arg('stock_status')); ?>'}"
                >
                <span>فقط کالاهای موجود</span>
            </label>
            
            <label class="gpds-filter-checkbox">
                <input 
                    type="checkbox" 
                    value="onsale"
                    <?php checked(isset($_GET['on_sale']) && $_GET['on_sale'] === '1'); ?>
                    onchange="if(this.checked){window.location.href='<?php echo esc_url(add_query_arg('on_sale', '1')); ?>'}else{window.location.href='<?php echo esc_url(remove_query_arg('on_sale')); ?>'}"
                >
                <span>فقط کالاهای تخفیف‌دار</span>
            </label>
        </div>
    </div>
    
</div>