<?php
/**
 * Single Product Template
 * قالب تک محصول
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

get_header('shop');

global $product;
?>

<main id="gpds-product-single" class="gpds-product-single">
    
    <!-- Breadcrumb -->
    <div class="gpds-container">
        <?php gpds_wc_breadcrumb(); ?>
    </div>
    
    <!-- محتوای محصول -->
    <div class="gpds-container">
        <?php while (have_posts()) : the_post(); ?>
            
            <div class="gpds-product-single__main">
                
                <!-- گالری -->
                <div class="gpds-product-gallery" data-gpds-gallery>
                    <?php
                    $product_id       = $product->get_id();
                    $main_image_id    = $product->get_image_id();
                    $gallery_ids      = $product->get_gallery_image_ids();
                    $placeholder      = wc_placeholder_img_src('woocommerce_single');
                    $main_image_url   = $main_image_id 
                        ? wp_get_attachment_image_url($main_image_id, 'woocommerce_single')
                        : $placeholder;
                    
                    // همه عکس‌ها (اصلی + گالری)
                    $all_images = array_merge(
                        [$main_image_id],
                        $gallery_ids
                    );
                    ?>
                    
                    <div class="gpds-product-gallery__main">
                        <!-- نشان‌ها -->
                        <div class="gpds-product-gallery__badges">
                            <?php if ($product->is_on_sale()) : 
                                $regular = (float) $product->get_regular_price();
                                $sale    = (float) $product->get_sale_price();
                                $discount = $regular > 0 ? round((($regular - $sale) / $regular) * 100) : 0;
                            ?>
                                <span class="gpds-discount">
                                    <?php echo esc_html($discount); ?>%
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($product->is_featured()) : ?>
                                <span class="gpds-badge gpds-badge--warning">ویژه</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- تصویر اصلی -->
                        <img 
                            id="gpds-gallery-main" 
                            src="<?php echo esc_url($main_image_url); ?>"
                            alt="<?php echo esc_attr($product->get_name()); ?>"
                            fetchpriority="high"
                            data-gpds-gallery-main
                        >
                    </div>
                    
                    <?php if (count($all_images) > 1) : ?>
                        <div class="gpds-product-gallery__thumbs">
                            <?php foreach ($all_images as $i => $img_id) : 
                                if (!$img_id) continue;
                                $thumb_url = wp_get_attachment_image_url($img_id, 'gpds-card-sm');
                                $full_url  = wp_get_attachment_image_url($img_id, 'woocommerce_single');
                            ?>
                                <button 
                                    type="button"
                                    class="gpds-product-gallery__thumb <?php echo $i === 0 ? 'is-active' : ''; ?>"
                                    data-gpds-gallery-thumb
                                    data-image="<?php echo esc_url($full_url); ?>"
                                    aria-label="تصویر <?php echo esc_attr($i + 1); ?>"
                                >
                                    <img 
                                        src="<?php echo esc_url($thumb_url); ?>" 
                                        alt=""
                                        loading="lazy"
                                    >
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- اطلاعات -->
                <div class="gpds-product-info">
                    
                    <h1 class="gpds-product-info__title">
                        <?php the_title(); ?>
                    </h1>
                    
                    <!-- متادیتا -->
                    <div class="gpds-product-info__meta">
                        
                        <?php if ($product->get_sku()) : ?>
                            <div>
                                <?php gpds_icon('tag', 14); ?>
                                <span>کد: <?php echo esc_html($product->get_sku()); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product->get_average_rating() > 0) : ?>
                            <div class="gpds-product-info__rating">
                                <?php gpds_icon('star', 14); ?>
                                <span>
                                    <?php echo esc_html(number_format($product->get_average_rating(), 1)); ?>
                                    (<?php echo esc_html($product->get_review_count()); ?> نظر)
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        $brands = get_the_terms($product_id, 'product_brand');
                        if ($brands && !is_wp_error($brands)) :
                            $brand = array_shift($brands);
                        ?>
                            <div class="gpds-product-info__brand">
                                برند: <a href="<?php echo esc_url(get_term_link($brand)); ?>">
                                    <?php echo esc_html($brand->name); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- مشخصات خلاصه -->
                    <?php
                    $short_attrs = $product->get_attributes();
                    $shown_attrs = 0;
                    if (!empty($short_attrs)) :
                    ?>
                        <div class="gpds-product-info__short-specs">
                            <div class="gpds-product-specs">
                                <?php foreach ($short_attrs as $attr_name => $attr) : 
                                    if ($shown_attrs >= 3) break;
                                    
                                    $label = wc_attribute_label($attr_name);
                                    $value = '';
                                    
                                    if ($attr->is_taxonomy()) {
                                        $terms = wc_get_product_terms($product_id, $attr_name, ['fields' => 'names']);
                                        $value = implode(', ', $terms);
                                    } else {
                                        $value = implode(', ', $attr->get_options());
                                    }
                                    
                                    if (!$value) continue;
                                    $shown_attrs++;
                                ?>
                                    <div class="gpds-product-specs__row">
                                        <span class="gpds-product-specs__label"><?php echo esc_html($label); ?></span>
                                        <span class="gpds-product-specs__value"><?php echo esc_html($value); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- جعبه خرید -->
                    <div class="gpds-product-price-box">
                        
                        <div class="gpds-product-price-box__prices">
                            <?php echo wp_kses_post($product->get_price_html()); ?>
                        </div>
                        
                        <!-- موجودی -->
                        <div class="gpds-product-info__stock">
                            <?php if ($product->is_in_stock()) : ?>
                                <span class="gpds-text-success">
                                    <?php gpds_icon('check-circle', 14); ?>
                                    موجود در انبار
                                </span>
                            <?php else : ?>
                                <span class="gpds-text-danger">
                                    <?php gpds_icon('alert-circle', 14); ?>
                                    ناموجود
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- فرم خرید -->
                        <?php if ($product->is_in_stock() && $product->is_purchasable()) : ?>
                            <form 
                                class="cart" 
                                action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" 
                                method="post" 
                                enctype="multipart/form-data"
                            >
                                <?php do_action('woocommerce_before_add_to_cart_button'); ?>
                                
                                <div class="gpds-product-add-to-cart">
                                    
                                    <?php if ($product->is_type('simple')) : ?>
                                        <!-- تعداد -->
                                        <div class="gpds-qty" data-gpds-qty>
                                            <button 
                                                type="button" 
                                                class="gpds-qty__btn"
                                                data-gpds-qty-minus
                                                aria-label="کاهش"
                                            >
                                                <?php gpds_icon('minus', 16); ?>
                                            </button>
                                            <input 
                                                type="number" 
                                                name="quantity" 
                                                value="1" 
                                                min="1" 
                                                class="gpds-qty__input"
                                                data-gpds-qty-input
                                                aria-label="تعداد"
                                            >
                                            <button 
                                                type="button" 
                                                class="gpds-qty__btn"
                                                data-gpds-qty-plus
                                                aria-label="افزایش"
                                            >
                                                <?php gpds_icon('plus', 16); ?>
                                            </button>
                                        </div>
                                        
                                        <button 
                                            type="submit" 
                                            name="add-to-cart" 
                                            value="<?php echo esc_attr($product_id); ?>" 
                                            class="single_add_to_cart_button button alt"
                                        >
                                            <?php gpds_icon('cart', 18); ?>
                                            افزودن به سبد خرید
                                        </button>
                                        
                                    <?php else : ?>
                                        <?php woocommerce_template_single_add_to_cart(); ?>
                                    <?php endif; ?>
                                    
                                </div>
                                
                                <?php do_action('woocommerce_after_add_to_cart_button'); ?>
                            </form>
                        <?php else : ?>
                            <button class="gpds-btn gpds-btn--secondary gpds-btn--block" disabled>
                                فعلاً ناموجود
                            </button>
                        <?php endif; ?>
                        
                        <!-- ویژگی‌های سریع -->
                        <div class="gpds-product-info__quick-features">
                            <div class="gpds-product-info__quick-feature">
                                <?php gpds_icon('shield', 20); ?>
                                <span>ضمانت اصالت کالا</span>
                            </div>
                            <div class="gpds-product-info__quick-feature">
                                <?php gpds_icon('truck', 20); ?>
                                <span>ارسال سریع</span>
                            </div>
                            <div class="gpds-product-info__quick-feature">
                                <?php gpds_icon('refresh', 20); ?>
                                <span>بازگشت ۷ روزه</span>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
                
            </div>
            
            <!-- تب‌ها -->
            <div class="gpds-product-tabs" data-gpds-tabs>
                
                <div class="gpds-product-tabs__nav">
                    <?php
                    $tabs = apply_filters('woocommerce_product_tabs', []);
                    $first = true;
                    foreach ($tabs as $key => $tab) :
                    ?>
                        <button 
                            type="button"
                            class="gpds-product-tabs__tab <?php echo $first ? 'is-active' : ''; ?>"
                            data-gpds-tab="<?php echo esc_attr($key); ?>"
                        >
                            <?php echo wp_kses_post($tab['title']); ?>
                        </button>
                    <?php 
                        $first = false;
                    endforeach; 
                    ?>
                </div>
                
                <div class="gpds-product-tabs__panels">
                    <?php
                    $first = true;
                    foreach ($tabs as $key => $tab) :
                    ?>
                        <div 
                            class="gpds-product-tabs__panel <?php echo $first ? 'is-active' : ''; ?>"
                            data-gpds-tab-panel="<?php echo esc_attr($key); ?>"
                        >
                            <?php
                            if (isset($tab['callback'])) {
                                call_user_func($tab['callback'], $key, $tab);
                            }
                            ?>
                        </div>
                    <?php 
                        $first = false;
                    endforeach; 
                    ?>
                </div>
                
            </div>
            
            <!-- محصولات مرتبط -->
            <?php
            $related_ids = wc_get_related_products($product_id, 6);
            if (!empty($related_ids)) :
                $related_query = new WP_Query([
                    'post_type'      => 'product',
                    'post__in'       => $related_ids,
                    'posts_per_page' => 6,
                    'orderby'        => 'post__in',
                    'no_found_rows'  => true,
                ]);
            ?>
                <section class="gpds-related-products">
                    <div class="gpds-section-header">
                        <h2 class="gpds-section-title">
                            <?php gpds_icon('shopping-bag', 24); ?>
                            محصولات مشابه
                        </h2>
                    </div>
                    
                    <div class="gpds-shop-grid">
                        <?php while ($related_query->have_posts()) : $related_query->the_post(); 
                            global $product;
                        ?>
                            <div class="gpds-shop-grid__item">
                                <?php get_template_part('template-parts/product/card', null, [
                                    'product' => $product,
                                ]); ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>
            
        <?php endwhile; ?>
    </div>
    
</main>

<?php get_footer('shop'); ?>