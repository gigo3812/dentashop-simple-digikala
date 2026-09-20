<?php
/**
 * Banners Module
 *
 * مدیریت بنرهای تبلیغاتی مستقل از اسلایدر
 * پشتیبانی از چند سایز تصویر (دسکتاپ / تبلت / موبایل)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;


// ============================================================
// ثابت‌ها
// ============================================================

const GPDS_BANNER_CPT          = 'gpds_banner';
const GPDS_BANNER_META_DESKTOP = '_gpds_banner_desktop_id';
const GPDS_BANNER_META_TABLET  = '_gpds_banner_tablet_id';
const GPDS_BANNER_META_MOBILE  = '_gpds_banner_mobile_id';
const GPDS_BANNER_META_URL     = '_gpds_banner_url';
const GPDS_BANNER_META_POS     = '_gpds_banner_position';
const GPDS_BANNER_NONCE        = 'gpds_banner_nonce';

// سایزهای تصویر (نام → [عرض, ارتفاع, کراپ])
const GPDS_BANNER_SIZES = [
    'gpds-banner-desktop' => [1920, 500, true],
    'gpds-banner-tablet'  => [1024, 400, true],
    'gpds-banner-mobile'  => [640,  360, true],
];

// موقعیت‌های مجاز
const GPDS_BANNER_POSITIONS = [
    'home-top'      => 'بالای صفحه اصلی',
    'home-middle'   => 'میانه صفحه اصلی',
    'home-bottom'   => 'پایین صفحه اصلی',
    'sidebar'       => 'سایدبار',
    'footer'        => 'فوتر',
];


// ============================================================
// ۱. ثبت CPT
// ============================================================

add_action('init', function (): void {
    register_post_type(GPDS_BANNER_CPT, [
        'labels' => [
            'name'          => __('بنرها', 'gp-dentashop'),
            'singular_name' => __('بنر', 'gp-dentashop'),
            'add_new'       => __('افزودن بنر', 'gp-dentashop'),
            'add_new_item'  => __('افزودن بنر جدید', 'gp-dentashop'),
            'edit_item'     => __('ویرایش بنر', 'gp-dentashop'),
            'all_items'     => __('همه بنرها', 'gp-dentashop'),
        ],
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-images-alt2',
        'menu_position'      => 26,
        'supports'           => ['title'],
        'has_archive'        => false,
        'rewrite'            => false,
        'query_var'          => false,
        'capability_type'    => 'post',
    ]);
});


// ============================================================
// ۲. ثبت سایزهای تصویر
// ============================================================

add_action('after_setup_theme', function (): void {
    foreach (GPDS_BANNER_SIZES as $name => [$w, $h, $crop]) {
        add_image_size($name, $w, $h, $crop);
    }
});


// ============================================================
// ۳. متاباکس‌ها
// ============================================================

add_action('add_meta_boxes', function (): void {
    add_meta_box(
        'gpds_banner_images',
        __('تصاویر بنر', 'gp-dentashop'),
        'gpds_banner_images_render',
        GPDS_BANNER_CPT,
        'normal',
        'high'
    );

    add_meta_box(
        'gpds_banner_settings',
        __('تنظیمات بنر', 'gp-dentashop'),
        'gpds_banner_settings_render',
        GPDS_BANNER_CPT,
        'side',
        'default'
    );
});


/**
 * متاباکس تصاویر — سه سایز (دسکتاپ / تبلت / موبایل)
 */
function gpds_banner_images_render($post): void {
    wp_nonce_field(GPDS_BANNER_NONCE, GPDS_BANNER_NONCE);

    $fields = [
        'desktop' => [
            'label' => __('بنر دسکتاپ', 'gp-dentashop'),
            'meta'  => GPDS_BANNER_META_DESKTOP,
            'dims'  => GPDS_BANNER_SIZES['gpds-banner-desktop'],
            'req'   => true,
        ],
        'tablet' => [
            'label' => __('بنر تبلت (اختیاری)', 'gp-dentashop'),
            'meta'  => GPDS_BANNER_META_TABLET,
            'dims'  => GPDS_BANNER_SIZES['gpds-banner-tablet'],
            'req'   => false,
        ],
        'mobile' => [
            'label' => __('بنر موبایل (اختیاری)', 'gp-dentashop'),
            'meta'  => GPDS_BANNER_META_MOBILE,
            'dims'  => GPDS_BANNER_SIZES['gpds-banner-mobile'],
            'req'   => false,
        ],
    ];
    ?>
    <div class="gpds-banner-box">
        <?php foreach ($fields as $key => $f) :
            $id      = (int) get_post_meta($post->ID, $f['meta'], true);
            $preview = $id ? wp_get_attachment_image_url($id, 'medium') : '';
            ?>
            <div class="gpds-banner-box__field" data-size="<?php echo esc_attr($key); ?>">
                <label class="gpds-banner-box__label">
                    <?php echo esc_html($f['label']); ?>
                    <span class="gpds-banner-box__dims">
                        (<?php echo esc_html($f['dims'][0] . '×' . $f['dims'][1]); ?>)
                    </span>
                </label>

                <div class="gpds-banner-box__preview" <?php echo $preview ? '' : 'style="display:none"'; ?>>
                    <img src="<?php echo esc_url($preview); ?>" alt="">
                </div>

                <input
                    type="hidden"
                    class="gpds-banner-box__input"
                    name="gpds_banner_<?php echo esc_attr($key); ?>_id"
                    value="<?php echo esc_attr($id); ?>"
                >

                <p class="gpds-banner-box__actions">
                    <button type="button" class="button button-primary gpds-banner-box__upload">
                        <?php echo $preview
                            ? esc_html__('تغییر تصویر', 'gp-dentashop')
                            : esc_html__('انتخاب تصویر', 'gp-dentashop'); ?>
                    </button>
                    <button
                        type="button"
                        class="button gpds-banner-box__remove"
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


/**
 * متاباکس تنظیمات (لینک + موقعیت)
 */
function gpds_banner_settings_render($post): void {
    $url      = (string) get_post_meta($post->ID, GPDS_BANNER_META_URL, true);
    $position = (string) get_post_meta($post->ID, GPDS_BANNER_META_POS, true);
    if ($position === '') $position = 'home-top';
    ?>
    <p>
        <label for="gpds_banner_url"><strong><?php esc_html_e('لینک بنر', 'gp-dentashop'); ?></strong></label>
        <input
            type="url"
            id="gpds_banner_url"
            name="gpds_banner_url"
            class="widefat"
            value="<?php echo esc_attr($url); ?>"
            placeholder="https://..."
        >
    </p>

    <p>
        <label for="gpds_banner_position"><strong><?php esc_html_e('موقعیت نمایش', 'gp-dentashop'); ?></strong></label>
        <select id="gpds_banner_position" name="gpds_banner_position" class="widefat">
            <?php foreach (GPDS_BANNER_POSITIONS as $key => $label) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($position, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}


// ============================================================
// ۴. ذخیره امن
// ============================================================

add_action('save_post_' . GPDS_BANNER_CPT, function (int $post_id): void {
    if (!isset($_POST[GPDS_BANNER_NONCE])) return;
    if (!wp_verify_nonce($_POST[GPDS_BANNER_NONCE], GPDS_BANNER_NONCE)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // تصاویر
    $map = [
        'desktop' => GPDS_BANNER_META_DESKTOP,
        'tablet'  => GPDS_BANNER_META_TABLET,
        'mobile'  => GPDS_BANNER_META_MOBILE,
    ];

    foreach ($map as $key => $meta) {
        $field = 'gpds_banner_' . $key . '_id';
        $id    = isset($_POST[$field]) ? absint($_POST[$field]) : 0;

        $id
            ? update_post_meta($post_id, $meta, $id)
            : delete_post_meta($post_id, $meta);
    }

    // لینک
    $url = isset($_POST['gpds_banner_url'])
        ? esc_url_raw(wp_unslash($_POST['gpds_banner_url']))
        : '';

    $url
        ? update_post_meta($post_id, GPDS_BANNER_META_URL, $url)
        : delete_post_meta($post_id, GPDS_BANNER_META_URL);

    // موقعیت
    $pos = isset($_POST['gpds_banner_position'])
        ? sanitize_key($_POST['gpds_banner_position'])
        : 'home-top';

    if (!array_key_exists($pos, GPDS_BANNER_POSITIONS)) {
        $pos = 'home-top';
    }

    update_post_meta($post_id, GPDS_BANNER_META_POS, $pos);
});


// ============================================================
// ۵. Assets ادمین (فقط صفحه ویرایش بنر)
// ============================================================

add_action('admin_enqueue_scripts', function (string $hook): void {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) return;
    if (get_post_type() !== GPDS_BANNER_CPT) return;

    wp_enqueue_media();

    wp_enqueue_script(
        'gpds-banner-meta',
        GPDS_ASSETS . '/js/admin/banner-meta.js',
        ['jquery'],
        GPDS_VERSION,
        true
    );

    wp_add_inline_style('wp-admin', '
        .gpds-banner-box { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
        @media (max-width:782px){ .gpds-banner-box{ grid-template-columns:1fr; } }
        .gpds-banner-box__field {
            border:1px solid #e0e0e0; border-radius:6px; padding:12px; background:#fafafa;
        }
        .gpds-banner-box__label {
            display:block; font-weight:600; margin-bottom:8px; font-size:13px;
        }
        .gpds-banner-box__dims { color:#888; font-weight:400; font-size:11px; }
        .gpds-banner-box__preview {
            margin-bottom:10px; background:#fff; border:1px solid #ddd; border-radius:4px;
            padding:4px; text-align:center;
        }
        .gpds-banner-box__preview img { max-width:100%; height:auto; display:block; margin:0 auto; }
        .gpds-banner-box__actions { margin:0; }
        .gpds-banner-box__actions .button + .button { margin-right:4px; }
    ');
});


// ============================================================
// ۶. Public API — گرفتن بنرها
// ============================================================

/**
 * گرفتن بنرهای فعال بر اساس موقعیت
 *
 * @param string $position موقعیت (home-top, sidebar, ...)
 * @param int    $limit    حداکثر تعداد
 * @return array
 */
function gpds_get_banners(string $position = 'home-top', int $limit = 5): array {
    $cache_key = 'gpds_banners_' . md5($position . '|' . $limit);
    $cached    = wp_cache_get($cache_key, 'gpds');

    if ($cached !== false) return $cached;

    $query = new WP_Query([
        'post_type'              => GPDS_BANNER_CPT,
        'post_status'            => 'publish',
        'posts_per_page'         => $limit,
        'orderby'                => 'menu_order date',
        'order'                  => 'ASC',
        'no_found_rows'          => true,
        'update_post_term_cache' => false,
        'update_post_meta_cache' => true,
        'meta_query'             => [
            [
                'key'   => GPDS_BANNER_META_POS,
                'value' => $position,
            ],
        ],
    ]);

    if (empty($query->posts)) {
        wp_cache_set($cache_key, [], 'gpds', HOUR_IN_SECONDS);
        return [];
    }

    $banners = array_filter(array_map('gpds_banner_map_post', $query->posts));

    wp_cache_set($cache_key, $banners, 'gpds', HOUR_IN_SECONDS);
    return $banners;
}


/**
 * تبدیل پست بنر به آرایه آماده نمایش
 */
function gpds_banner_map_post($post): ?array {
    $id = $post->ID;

    // تصاویر سه‌گانه (fallback: دسکتاپ → تبلت → موبایل)
    $desktop_id = (int) get_post_meta($id, GPDS_BANNER_META_DESKTOP, true);
    if (!$desktop_id) return null; // بنر بدون تصویر دسکتاپ بی‌معنیه

    $tablet_id = (int) get_post_meta($id, GPDS_BANNER_META_TABLET, true) ?: $desktop_id;
    $mobile_id = (int) get_post_meta($id, GPDS_BANNER_META_MOBILE, true) ?: $desktop_id;

    return [
        'id'        => $id,
        'title'     => get_the_title($id),
        'url'       => (string) get_post_meta($id, GPDS_BANNER_META_URL, true),
        'position'  => (string) get_post_meta($id, GPDS_BANNER_META_POS, true),
        'image'     => gpds_banner_image_url($desktop_id, 'gpds-banner-desktop'),
        'image_sm'  => gpds_banner_image_url($tablet_id,  'gpds-banner-tablet'),
        'image_xs'  => gpds_banner_image_url($mobile_id,  'gpds-banner-mobile'),
    ];
}


/**
 * گرفتن URL تصویر با fallback به full
 */
function gpds_banner_image_url(int $attachment_id, string $size): string {
    if (!$attachment_id) return '';

    $url = wp_get_attachment_image_url($attachment_id, $size);
    return $url ?: (wp_get_attachment_image_url($attachment_id, 'full') ?: '');
}


// ============================================================
// ۷. پاک‌سازی کش هنگام ذخیره/حذف بنر
// ============================================================

add_action('save_post_' . GPDS_BANNER_CPT, 'gpds_banner_flush_cache');
add_action('deleted_post', function ($post_id) {
    if (get_post_type($post_id) === GPDS_BANNER_CPT) {
        gpds_banner_flush_cache();
    }
});

function gpds_banner_flush_cache(): void {
    // پاک کردن گروه gpds در object cache
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('gpds');
        return;
    }

    // fallback: پاک کردن دستی برای موقعیت‌های شناخته‌شده
    foreach (array_keys(GPDS_BANNER_POSITIONS) as $pos) {
        foreach ([3, 5, 10] as $limit) {
            wp_cache_delete('gpds_banners_' . md5($pos . '|' . $limit), 'gpds');
        }
    }
}