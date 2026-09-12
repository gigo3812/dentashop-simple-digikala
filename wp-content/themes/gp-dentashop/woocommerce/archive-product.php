<?php
/**
 * Shop / Archive Template
 * قالب آرشیو محصولات
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header('shop');

// گرفتن دسته‌بندی فعلی
$current_cat_id = 0;
$current_cat    = null;

if (is_product_category()) {
    $current_cat = get_queried_object();
    $current_cat_id = $current_cat->term_id;
}
?>

<main id="gpds-shop" class="gpds-shop">
    
    <!-- Breadcrumb -->
    <div class="gpds-container">
        <?php gpds_wc_breadcrumb(); ?>
    </div>
    
    <!-- Header دسته -->
    <section class="gpds-shop-header">
        <div class="gpds-container">
            <div class="gpds-shop-header__inner">
                
                <?php if ($current_cat) : ?>
                    <!-- عنوان دسته -->
                    <h1 class="gpds-shop-header__title">
                        <?php echo esc_html($current_cat->name); ?>
                    </h1>
                    
                    <?php if ($current_cat->description) : ?>
                        <div class="gpds-shop-header__desc">
                            <?php echo wp_kses_post($current_cat->description); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- زیردسته‌ها -->
                    <?php
                    $children = get_terms([
                        'taxonomy'   => 'product_cat',
                        'parent'     => $current_cat_id,
                        'hide_empty' => true,
                    ]);
                    
                    if (!is_wp_error($children) && !empty($children)) :
                    ?>
                        <div class="gpds-shop-subcats">
                            <?php foreach ($children as $child) : ?>
                                <a 
                                    href="<?php echo esc_url(get_term_link($child)); ?>" 
                                    class="gpds-shop-subcat"
                                >
                                    <?php echo esc_html($child->name); ?>
                                    <span class="gpds-text-tertiary">(<?php echo esc_html($child->count); ?>)</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else : ?>
                    <h1 class="gpds-shop-header__title">
                        <?php woocommerce_page_title(); ?>
                    </h1>
                <?php endif; ?>
                
            </div>
        </div>
    </section>
    
    <!-- محصولات -->
    <section class="gpds-shop-products">
        <div class="gpds-container">
            <div class="gpds-shop-layout">
                
                <!-- سایدبار فیلتر -->
                <aside class="gpds-shop-sidebar" data-gpds-shop-sidebar>
                    <button 
                        type="button" 
                        class="gpds-shop-sidebar__close"
                        data-gpds-shop-sidebar-close
                        aria-label="بستن فیلترها"
                    >
                        <?php gpds_icon('close', 20); ?>
                    </button>
                    
                    <?php get_template_part('template-parts/shop/filters'); ?>
                </aside>
                
                <!-- گرید محصولات -->
                <div class="gpds-shop-main">
                    
                    <!-- نوار ابزار -->
                    <?php get_template_part('template-parts/shop/toolbar'); ?>
                    
                    <!-- گرید -->
                    <?php if (have_posts()) : ?>
                        
                        <div class="gpds-shop-grid">
                            <?php while (have_posts()) : the_post(); 
                                // مطمئن‌ترین راه گرفتن محصول
                                $product = wc_get_product(get_the_ID());
                                if (!$product) continue;
                            ?>
                                <div class="gpds-shop-grid__item">
                                    <?php
                                        include get_stylesheet_directory() . '/template-parts/product/card.php';
                                    ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="gpds-shop-pagination">
                            <?php
                            echo paginate_links([
                                'base'      => add_query_arg('paged', '%#%'),
                                'format'    => '',
                                'current'   => max(1, get_query_var('paged')),
                                'total'     => $GLOBALS['wp_query']->max_num_pages,
                                'prev_text' => '→',
                                'next_text' => '←',
                                'type'      => 'list',
                            ]);
                            ?>
                        </div>
                        
                    <?php else : ?>
                        <?php get_template_part('template-parts/shop/empty'); ?>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </section>
    
    <!-- دکمه موبایل: نمایش فیلترها -->
    <button 
        type="button" 
        class="gpds-shop-filters-btn"
        data-gpds-shop-sidebar-open
    >
        <?php gpds_icon('sliders', 18); ?>
        <span>فیلترها</span>
    </button>
    
</main>

<?php get_footer('shop'); ?>