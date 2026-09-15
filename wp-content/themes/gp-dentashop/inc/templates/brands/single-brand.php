<?php
/**
 * Template: آرشیو محصولات یک برند
 * URL: /brand/{slug}/
 *
 * ساختار دقیقاً مشابه archive-product.php
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header('shop');

$current_brand = get_queried_object();

if (!$current_brand || is_wp_error($current_brand)) {
    get_footer('shop');
    return;
}

// اطلاعات برند
$brand_id    = $current_brand->term_id;
$brand_name  = $current_brand->name;
$brand_desc  = $current_brand->description;
$brand_count = $current_brand->count;

// لوگو
$logo_id  = get_term_meta($brand_id, 'thumbnail_id', true);
$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';

// لینک سفارشی (اگه تنظیم شده باشه)
$custom_url = get_term_meta($brand_id, '_gpds_brand_url', true);
?>

<main id="gpds-shop" class="gpds-shop gpds-brand-shop">
    
    <!-- Breadcrumb -->
    <div class="gpds-container">
        <?php gpds_wc_breadcrumb(); ?>
    </div>
    
    <!-- Header برند -->
    <section class="gpds-shop-header gpds-brand-header">
        <div class="gpds-container">
            <div class="gpds-brand-header__inner">
                
                <?php if ($logo_url) : ?>
                    <div class="gpds-brand-header__logo">
                        <img 
                            src="<?php echo esc_url($logo_url); ?>" 
                            alt="<?php echo esc_attr($brand_name); ?>"
                            loading="eager"
                        >
                    </div>
                <?php endif; ?>
                
                <div class="gpds-brand-header__info">
                    
                    <h1 class="gpds-shop-header__title gpds-brand-header__title">
                        <?php echo esc_html($brand_name); ?>
                    </h1>
                    
                    <div class="gpds-brand-header__meta">
                        <span class="gpds-brand-header__count">
                            <?php echo esc_html(number_format_i18n($brand_count)); ?> محصول
                        </span>
                        
                        <?php if ($custom_url) : ?>
                            <a 
                                href="<?php echo esc_url($custom_url); ?>" 
                                target="_blank" 
                                rel="noopener"
                                class="gpds-brand-header__external"
                            >
                                <?php gpds_icon('arrow-left', 14); ?>
                                وب‌سایت رسمی برند
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($brand_desc) : ?>
                        <div class="gpds-shop-header__desc gpds-brand-header__desc">
                            <?php echo wp_kses_post($brand_desc); ?>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
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

<?php get_footer('shop');