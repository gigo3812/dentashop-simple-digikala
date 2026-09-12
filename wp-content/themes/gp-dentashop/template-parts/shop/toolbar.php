<?php
/**
 * Shop Toolbar - نوار مرتب‌سازی و تعداد
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// پارامترهای فعلی
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'menu_order';
$current_view = isset($_COOKIE['gpds_view']) ? sanitize_text_field($_COOKIE['gpds_view']) : 'grid';

$total_products = wc_get_loop_prop('total');
$per_page = wc_get_loop_prop('per_page');
$current_page = max(1, get_query_var('paged'));

$from = (($current_page - 1) * $per_page) + 1;
$to   = min($current_page * $per_page, $total_products);
?>

<div class="gpds-shop-toolbar">
    
    <!-- تعداد نتایج -->
    <div class="gpds-shop-toolbar__count">
        نمایش <strong><?php echo esc_html($from); ?>-<?php echo esc_html($to); ?></strong>
        از <strong><?php echo esc_html($total_products); ?></strong> کالا
    </div>
    
    <!-- مرتب‌سازی -->
    <div class="gpds-shop-toolbar__sort">
        <label for="gpds-orderby" class="gpds-sr-only">مرتب‌سازی</label>
        <select 
            id="gpds-orderby" 
            class="gpds-select"
            onchange="window.location.href=this.value"
        >
            <?php
            $sort_options = [
                'menu_order' => 'پیش‌فرض',
                'popularity' => 'محبوب‌ترین',
                'rating'     => 'بیشترین امتیاز',
                'date'       => 'جدیدترین',
                'price'      => 'ارزان‌ترین',
                'price-desc' => 'گران‌ترین',
            ];
            
            foreach ($sort_options as $value => $label) {
                $url = add_query_arg('orderby', $value);
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_url($url),
                    selected($orderby, $value, false),
                    esc_html($label)
                );
            }
            ?>
        </select>
    </div>
    
    <!-- نمایش گرید/لیست -->
    <div class="gpds-shop-toolbar__view">
        <button 
            type="button" 
            class="gpds-view-btn <?php echo $current_view === 'grid' ? 'is-active' : ''; ?>"
            data-gpds-view="grid"
            aria-label="نمایش شبکه‌ای"
        >
            <?php gpds_icon('grid-2', 18); ?>
        </button>
        <button 
            type="button" 
            class="gpds-view-btn <?php echo $current_view === 'list' ? 'is-active' : ''; ?>"
            data-gpds-view="list"
            aria-label="نمایش لیستی"
        >
            <?php gpds_icon('list', 18); ?>
        </button>
    </div>
    
</div>