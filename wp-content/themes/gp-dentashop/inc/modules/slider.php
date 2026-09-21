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

const GPDS_SLIDER_META_DESKTOP = '_gpds_slider_banner_desktop';
const GPDS_SLIDER_META_TABLET  = '_gpds_slider_banner_tablet';
const GPDS_SLIDER_META_MOBILE  = '_gpds_slider_banner_mobile';
const GPDS_SLIDER_NONCE        = 'gpds_slider_nonce';

// 🎯 سایزهای تصویر اختصاصی
const GPDS_SLIDER_SIZE_DESKTOP = 'gpds-slider-desktop';
const GPDS_SLIDER_SIZE_TABLET  = 'gpds-slider-tablet';
const GPDS_SLIDER_SIZE_MOBILE  = 'gpds-slider-mobile';


// ============================================================
// ۱. ثبت سایزهای تصویر
// ============================================================

add_action('after_setup_theme', function (): void {
    add_image_size(GPDS_SLIDER_SIZE_DESKTOP, 1920, 600, true);
    add_image_size(GPDS_SLIDER_SIZE_TABLET,  1200, 500, true);
    add_image_size(GPDS_SLIDER_SIZE_MOBILE,   768, 600, true);
});


// ============================================================
// ۲. متاباکس — ۳ بنر مستقل
// ============================================================

add_action('add_meta_boxes', function (): void {
    add_meta_box(
        'gpds_slider_box',
        __('بنرهای اسلایدر (۳ سایز)', 'gp-dentashop'),
        'gpds_slider_box_render',
        'product',
        'side',
        'default'
    );
});

function gpds_slider_box_render($post): void {
    wp_nonce_field(GPDS_SLIDER_NONCE, GPDS_SLIDER_NONCE);

    $sizes = [
        'desktop' => [
            'label' => __('دسکتاپ (1920×600)', 'gp-dentashop'),
            'key'   => GPDS_SLIDER_META_DESKTOP,
        ],
        'tablet'  => [
            'label' => __('تبلت (1200×500)', 'gp-dentashop'),
            'key'   => GPDS_SLIDER_META_TABLET,
        ],
        'mobile'  => [
            'label' => __('موبایل (768×600)', 'gp-dentashop'),
            'key'   => GPDS_SLIDER_META_MOBILE,
        ],
    ];
    ?>
    <div class="gpds-slider-box">

        <?php foreach ($sizes as $slug => $conf) :
            $id      = (int) get_post_meta($post->ID, $conf['key'], true);
            $preview = $id ? wp_get_attachment_image_url($id, 'medium') : '';
            ?>
            <div class="gpds-slider-box__field" data-size="<?php echo esc_attr($slug); ?>">

                <p class="gpds-slider-box__label">
                    <?php echo esc_html($conf['label']); ?>
                </p>

                <div class="gpds-slider-box__preview" <?php echo $preview ? '' : 'style="display:none"'; ?>>
                    <img src="<?php echo esc_url($preview); ?>" alt="">
                </div>

                <input
                    type="hidden"
                    name="gpds_slider_banner_<?php echo esc_attr($slug); ?>"
                    class="gpds-slider-box__input"
                    value="<?php echo esc_attr($id); ?>"
                >

                <p>
                    <button type="button" class="button button-primary gpds-slider-box__upload">
                        <?php echo $preview
                            ? esc_html__('تغییر', 'gp-dentashop')
                            : esc_html__('انتخاب', 'gp-dentashop'); ?>
                    </button>

                    <button
                        type="button"
                        class="button gpds-slider-box__remove"
                        <?php echo $preview ? '' : 'style="display:none"'; ?>
                    >
                        <?php esc_html_e('حذف', 'gp-dentashop'); ?>
                    </button>
                </p>

            </div>
        <?php endforeach; ?>

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

    $map = [
        'desktop' => GPDS_SLIDER_META_DESKTOP,
        'tablet'  => GPDS_SLIDER_META_TABLET,
        'mobile'  => GPDS_SLIDER_META_MOBILE,
    ];

    foreach ($map as $slug => $meta_key) {
        $field = "gpds_slider_banner_{$slug}";
        $id    = isset($_POST[$field]) ? absint($_POST[$field]) : 0;

        $id
            ? update_post_meta($post_id, $meta_key, $id)
            : delete_post_meta($post_id, $meta_key);
    }
});


// ============================================================
// ۴. Assets ادمین
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
        .gpds-slider-box__field {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .gpds-slider-box__field:last-child { border-bottom: 0; }
        .gpds-slider-box__label {
            font-weight: 600;
            margin: 0 0 6px;
            font-size: 12px;
            color: #333;
        }
        .gpds-slider-box__preview { margin-bottom: 8px; }
        .gpds-slider-box__preview img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 2px;
            background: #fff;
        }
        .gpds-slider-box .button + .button { margin-right: 4px; }
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
 * تبدیل محصول به آرایه اسلاید (با ۳ تصویر)
 */
function gpds_slider_map_product($product): array {
    $pid = $product->get_id();

    return [
        'id'            => $pid,
        'title'         => $product->get_name(),
        'url'           => $product->get_permalink(),
        'price'         => $product->get_price_html(),
        'type'          => 'product',
        // 🎯 سه تصویر
        'image'         => gpds_slider_resolve_image($pid, $product, 'desktop'),
        'image_tablet'  => gpds_slider_resolve_image($pid, $product, 'tablet'),
        'image_mobile'  => gpds_slider_resolve_image($pid, $product, 'mobile'),
    ];
}


/**
 * اولویت تصویر:
 *   ۱. بنر اختصاصی همان سایز
 *   ۲. بنر دسکتاپ (fallback برای تبلت/موبایل)
 *   ۳. تصویر شاخص با سایز مربوطه
 *   ۴. placeholder
 */
function gpds_slider_resolve_image(int $pid, $product, string $size): string {
    $meta_map = [
        'desktop' => GPDS_SLIDER_META_DESKTOP,
        'tablet'  => GPDS_SLIDER_META_TABLET,
        'mobile'  => GPDS_SLIDER_META_MOBILE,
    ];

    $img_size_map = [
        'desktop' => GPDS_SLIDER_SIZE_DESKTOP,
        'tablet'  => GPDS_SLIDER_SIZE_TABLET,
        'mobile'  => GPDS_SLIDER_SIZE_MOBILE,
    ];

    // ۱. بنر اختصاصی همان سایز
    $banner_id = (int) get_post_meta($pid, $meta_map[$size], true);

    // ۲. fallback: اگه موبایل/تبلت خالی بود، از دسکتاپ استفاده کن
    if (!$banner_id && $size !== 'desktop') {
        $banner_id = (int) get_post_meta($pid, GPDS_SLIDER_META_DESKTOP, true);
    }

    if ($banner_id) {
        $image = wp_get_attachment_image_url($banner_id, $img_size_map[$size])
              ?: wp_get_attachment_image_url($banner_id, 'full');

        if ($image) return $image;
    }

    // ۳. تصویر شاخص
    $thumb_id = $product->get_image_id();
    if ($thumb_id) {
        $image = wp_get_attachment_image_url($thumb_id, $img_size_map[$size])
              ?: wp_get_attachment_image_url($thumb_id, 'full');

        if ($image) return $image;
    }

    // ۴. placeholder
    return function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '';
}