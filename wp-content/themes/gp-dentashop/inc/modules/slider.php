<?php
/**
 * Slider Module
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;


// ============================================================
// ثابت‌ها
// ============================================================

const GPDS_SLIDER_META_KEY   = '_gpds_slider_banner_id';
const GPDS_SLIDER_IMAGE_SIZE = 'gpds-slider-banner';
const GPDS_SLIDER_DIMS       = [1920, 600];
const GPDS_SLIDER_NONCE      = 'gpds_slider_nonce';


// ============================================================
// ۱. ثبت سایز تصویر بنر
// ============================================================

add_action('after_setup_theme', function (): void {
    add_image_size(
        GPDS_SLIDER_IMAGE_SIZE,
        GPDS_SLIDER_DIMS[0],
        GPDS_SLIDER_DIMS[1],
        true
    );
});


// ============================================================
// ۲. متاباکس در صفحه ویرایش محصول
// ============================================================

add_action('add_meta_boxes', function (): void {
    add_meta_box(
        'gpds_slider_box',
        __('بنر اسلایدر', 'gp-dentashop'),
        'gpds_slider_box_render',
        'product',
        'side',
        'default'
    );
});

function gpds_slider_box_render($post): void {
    $banner_id = (int) get_post_meta($post->ID, GPDS_SLIDER_META_KEY, true);
    $preview   = $banner_id ? wp_get_attachment_image_url($banner_id, 'medium') : '';

    wp_nonce_field(GPDS_SLIDER_NONCE, GPDS_SLIDER_NONCE);
    ?>
    <div class="gpds-slider-box">

        <div class="gpds-slider-box__preview" <?php echo $preview ? '' : 'style="display:none"'; ?>>
            <img src="<?php echo esc_url($preview); ?>" alt="">
        </div>

        <input
            type="hidden"
            name="gpds_slider_banner_id"
            id="gpds_slider_banner_id"
            value="<?php echo esc_attr($banner_id); ?>"
        >

        <p>
            <button type="button" class="button button-primary gpds-slider-box__upload">
                <?php echo $preview
                    ? esc_html__('تغییر بنر', 'gp-dentashop')
                    : esc_html__('انتخاب بنر', 'gp-dentashop'); ?>
            </button>

            <button
                type="button"
                class="button gpds-slider-box__remove"
                <?php echo $preview ? '' : 'style="display:none"'; ?>
            >
                <?php esc_html_e('حذف', 'gp-dentashop'); ?>
            </button>
        </p>

        <p style="font-size:11px;color:#666;margin:0;">
            <?php printf(
                esc_html__('ابعاد: %s پیکسل', 'gp-dentashop'),
                GPDS_SLIDER_DIMS[0] . '×' . GPDS_SLIDER_DIMS[1]
            ); ?>
        </p>

    </div>
    <?php
}


// ============================================================
// ۳. ذخیره امن
// ============================================================

add_action('save_post_product', function (int $post_id): void {
    if (!isset($_POST[GPDS_SLIDER_NONCE])) return;
    if (!wp_verify_nonce($_POST[GPDS_SLIDER_NONCE], GPDS_SLIDER_NONCE)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $banner_id = isset($_POST['gpds_slider_banner_id'])
        ? absint($_POST['gpds_slider_banner_id'])
        : 0;

    $banner_id
        ? update_post_meta($post_id, GPDS_SLIDER_META_KEY, $banner_id)
        : delete_post_meta($post_id, GPDS_SLIDER_META_KEY);
});


// ============================================================
// ۴. Assets ادمین (فقط صفحه ویرایش محصول)
// ============================================================

add_action('admin_enqueue_scripts', function (string $hook): void {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) return;
    if (get_post_type() !== 'product') return;

    wp_enqueue_media();

    wp_enqueue_script(
        'gpds-slider-meta',
        GPDS_ASSETS . '/js/admin/slider-meta.js',
        ['jquery'],
        GPDS_VERSION,
        true
    );

    wp_add_inline_style('wp-admin', '
        .gpds-slider-box__preview { margin-bottom:10px; }
        .gpds-slider-box__preview img {
            max-width:100%; height:auto; border:1px solid #ddd;
            border-radius:4px; padding:2px; background:#fff;
        }
        .gpds-slider-box .button + .button { margin-right:4px; }
    ');
});


// ============================================================
// ۵. Public API — آیتم‌های اسلایدر
// ============================================================

function gpds_get_slider_items(int $limit = 5): array {
    if (!function_exists('wc_get_products')) return [];

    $base = [
        'status'  => 'publish',
        'limit'   => $limit,
        'orderby' => 'date',
        'order'   => 'DESC',
    ];

    $products = wc_get_products(['featured' => true] + $base)
             ?: wc_get_products($base);

    return array_map('gpds_slider_map_product', $products);
}


/**
 * تبدیل محصول به آرایه اسلاید
 */
function gpds_slider_map_product($product): array {
    return [
        'id'    => $product->get_id(),
        'title' => $product->get_name(),
        'url'   => $product->get_permalink(),
        'image' => gpds_slider_resolve_image($product),
        'price' => $product->get_price_html(),
        'type'  => 'product',
    ];
}


/**
 * اولویت تصویر: بنر → تصویر شاخص → placeholder
 */
function gpds_slider_resolve_image($product): string {
    $pid = $product->get_id();

    // ۱. بنر اختصاصی
    $banner_id = (int) get_post_meta($pid, GPDS_SLIDER_META_KEY, true);

    if ($banner_id) {
        $image = wp_get_attachment_image_url($banner_id, GPDS_SLIDER_IMAGE_SIZE)
              ?: wp_get_attachment_image_url($banner_id, 'full');

        if ($image) return $image;
    }

    // ۲. تصویر شاخص
    $thumb_id = $product->get_image_id();

    if ($thumb_id) {
        $image = wp_get_attachment_image_url($thumb_id, 'full');
        if ($image) return $image;
    }

    // ۳. placeholder
    return function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '';
}