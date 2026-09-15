<?php
/**
 * Brands Module
 * 
 * سیستم برندها با تاکسونومی WooCommerce
 * 
 * مسئولیت‌ها:
 *   - ثبت تاکسونومی product_brand (اگه وجود نداشته باشه)
 *   - فیلدهای ادمین (لوگو، URL، ترتیب، وضعیت)
 *   - ستون‌های لیست ادمین
 *   - کوئری برندهای فعال (frontend)
 *   - صفحه /brands/ (لیست همه برندها)
 *   - آرشیو /brand/{slug}/ (محصولات یه برند)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;


// ============================================
// 1. ثبت تاکسونومی برند (اگه وجود نداشته باشه)
// ============================================
add_action('init', 'gpds_check_brand_taxonomy', 100);

function gpds_check_brand_taxonomy() {
    if (!taxonomy_exists('product_brand')) {
        gpds_register_brand_taxonomy();
    }
}

function gpds_register_brand_taxonomy() {
    $labels = [
        'name'              => 'برندها',
        'singular_name'     => 'برند',
        'search_items'      => 'جستجوی برند',
        'all_items'         => 'همه برندها',
        'parent_item'       => 'برند والد',
        'parent_item_colon' => 'برند والد:',
        'edit_item'         => 'ویرایش برند',
        'update_item'       => 'به‌روزرسانی برند',
        'add_new_item'      => 'افزودن برند جدید',
        'new_item_name'     => 'نام برند جدید',
        'menu_name'         => 'برندها',
    ];

    register_taxonomy('product_brand', ['product'], [
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'brand'],
        'show_in_rest'      => true,
    ]);
}


// ============================================
// 2. فیلدهای ادمین: URL / Order / Active
// ============================================
add_action('product_brand_add_form_fields', 'gpds_brand_add_fields');
add_action('product_brand_edit_form_fields', 'gpds_brand_edit_fields', 10, 2);

function gpds_brand_add_fields() {
    ?>
    <div class="form-field">
        <label for="brand_url">لینک مقصد (اختیاری)</label>
        <input type="url" name="brand_url" id="brand_url" value="" dir="ltr" placeholder="https://example.com">
        <p class="description">اگه خالی باشه، به آرشیو محصولات برند لینک می‌شه</p>
    </div>

    <div class="form-field">
        <label for="brand_order">ترتیب نمایش</label>
        <input type="number" name="brand_order" id="brand_order" value="0" min="0">
        <p class="description">عدد کمتر = بالاتر</p>
    </div>

    <div class="form-field">
        <label for="brand_active">
            <input type="checkbox" name="brand_active" id="brand_active" value="1" checked>
            نمایش در صفحه اصلی
        </label>
    </div>
    <?php
}

function gpds_brand_edit_fields($term, $taxonomy) {
    $url    = get_term_meta($term->term_id, '_gpds_brand_url', true);
    $order  = get_term_meta($term->term_id, '_gpds_brand_order', true);
    $active = get_term_meta($term->term_id, '_gpds_brand_active', true);

    if ($active === '') $active = '1';
    ?>
    <tr class="form-field">
        <th scope="row"><label for="brand_url">لینک مقصد (اختیاری)</label></th>
        <td>
            <input type="url" name="brand_url" id="brand_url" value="<?php echo esc_attr($url); ?>" dir="ltr" placeholder="https://example.com">
            <p class="description">اگه خالی باشه، به آرشیو محصولات برند لینک می‌شه</p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row"><label for="brand_order">ترتیب نمایش</label></th>
        <td>
            <input type="number" name="brand_order" id="brand_order" value="<?php echo esc_attr($order); ?>" min="0">
            <p class="description">عدد کمتر = بالاتر</p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row"><label for="brand_active">وضعیت</label></th>
        <td>
            <label>
                <input type="checkbox" name="brand_active" id="brand_active" value="1" <?php checked($active, '1'); ?>>
                نمایش در صفحه اصلی
            </label>
        </td>
    </tr>
    <?php
}


// ============================================
// 3. ذخیره فیلدها
// ============================================
add_action('created_product_brand', 'gpds_save_brand_meta');
add_action('edited_product_brand',  'gpds_save_brand_meta');

function gpds_save_brand_meta($term_id) {
    if (isset($_POST['brand_url'])) {
        update_term_meta($term_id, '_gpds_brand_url', esc_url_raw($_POST['brand_url']));
    }

    if (isset($_POST['brand_order'])) {
        update_term_meta($term_id, '_gpds_brand_order', intval($_POST['brand_order']));
    }

    $active = isset($_POST['brand_active']) ? '1' : '0';
    update_term_meta($term_id, '_gpds_brand_active', $active);

    delete_transient('gpds_active_brands');
}


// ============================================
// 4. ستون‌های لیست ادمین
// ============================================
add_filter('manage_edit-product_brand_columns', 'gpds_brand_admin_columns');

function gpds_brand_admin_columns($columns) {
    return [
        'cb'     => $columns['cb'],
        'name'   => $columns['name'],
        'logo'   => 'لوگو',
        'url'    => 'لینک',
        'order'  => 'ترتیب',
        'active' => 'وضعیت',
        'count'  => $columns['posts'],
    ];
}

add_filter('manage_product_brand_custom_column', 'gpds_brand_admin_column_content', 10, 3);

function gpds_brand_admin_column_content($content, $column, $term_id) {
    switch ($column) {
        case 'logo':
            $logo_id = get_term_meta($term_id, 'thumbnail_id', true);
            if ($logo_id) {
                echo wp_get_attachment_image($logo_id, [60, 60], false, [
                    'style' => 'width:60px;height:60px;object-fit:contain;background:#f0f0f1;padding:4px;border-radius:6px;',
                ]);
            } else {
                echo '<span style="color:#999">—</span>';
            }
            break;

        case 'url':
            $url = get_term_meta($term_id, '_gpds_brand_url', true);
            if ($url) {
                printf(
                    '<a href="%s" target="_blank" rel="noopener" dir="ltr">%s</a>',
                    esc_url($url),
                    esc_html(wp_parse_url($url, PHP_URL_HOST) ?: '—')
                );
            } else {
                echo '<span style="color:#999">—</span>';
            }
            break;

        case 'order':
            echo intval(get_term_meta($term_id, '_gpds_brand_order', true));
            break;

        case 'active':
            $active = get_term_meta($term_id, '_gpds_brand_active', true);
            if ($active === '0') {
                echo '<span style="color:#d63638">● غیرفعال</span>';
            } else {
                echo '<span style="color:#00a32a">● فعال</span>';
            }
            break;
    }
}


// ============================================
// 5. فیلد لوگو در فرم برند
// ============================================
add_action('admin_enqueue_scripts', function() {
    $screen = get_current_screen();
    if (!$screen || $screen->taxonomy !== 'product_brand') return;
    wp_enqueue_media();
});

add_action('product_brand_add_form_fields',  'gpds_brand_logo_add_field', 5);
add_action('product_brand_edit_form_fields', 'gpds_brand_logo_edit_field', 5, 2);

function gpds_brand_logo_add_field() {
    ?>
    <div class="form-field">
        <label for="brand_logo">لوگو برند</label>
        <div style="display:flex;gap:10px;align-items:center;">
            <button type="button" class="button" id="gpds-brand-logo-btn">انتخاب لوگو</button>
            <img src="" id="gpds-brand-logo-preview" style="max-width:80px;max-height:80px;display:none;background:#f0f0f1;padding:4px;border-radius:6px;">
        </div>
        <input type="hidden" name="brand_logo_id" id="brand_logo_id" value="">
        <p class="description">لوگوی برند (ترجیحاً PNG یا SVG با پس‌زمینه شفاف)</p>
    </div>
    <?php gpds_brand_logo_script(); ?>
    <?php
}

function gpds_brand_logo_edit_field($term, $taxonomy) {
    $logo_id  = get_term_meta($term->term_id, 'thumbnail_id', true);
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : '';
    ?>
    <tr class="form-field">
        <th scope="row"><label for="brand_logo">لوگو برند</label></th>
        <td>
            <div style="display:flex;gap:10px;align-items:center;">
                <button type="button" class="button" id="gpds-brand-logo-btn">انتخاب لوگو</button>
                <img src="<?php echo esc_url($logo_url); ?>" id="gpds-brand-logo-preview" style="max-width:80px;max-height:80px;<?php echo $logo_id ? '' : 'display:none;'; ?>background:#f0f0f1;padding:4px;border-radius:6px;">
            </div>
            <input type="hidden" name="brand_logo_id" id="brand_logo_id" value="<?php echo esc_attr($logo_id); ?>">
            <p class="description">لوگوی برند (ترجیحاً PNG یا SVG با پس‌زمینه شفاف)</p>
        </td>
    </tr>
    <?php gpds_brand_logo_script(); ?>
    <?php
}

/**
 * اسکریپت مشترک برای انتخاب لوگو (DRY)
 */
function gpds_brand_logo_script() {
    static $printed = false;
    if ($printed) return;
    $printed = true;
    ?>
    <script>
    jQuery(function($) {
        var mediaFrame;
        $(document).on('click', '#gpds-brand-logo-btn', function(e) {
            e.preventDefault();
            if (mediaFrame) { mediaFrame.open(); return; }
            mediaFrame = wp.media({
                title: 'انتخاب لوگو برند',
                button: { text: 'استفاده از این تصویر' },
                library: { type: 'image' },
                multiple: false
            });
            mediaFrame.on('select', function() {
                var att = mediaFrame.state().get('selection').first().toJSON();
                $('#brand_logo_id').val(att.id);
                $('#gpds-brand-logo-preview').attr('src', att.url).show();
            });
            mediaFrame.open();
        });
    });
    </script>
    <?php
}

add_action('created_product_brand', 'gpds_save_brand_logo');
add_action('edited_product_brand',  'gpds_save_brand_logo');

function gpds_save_brand_logo($term_id) {
    if (!isset($_POST['brand_logo_id'])) return;

    $logo_id = absint($_POST['brand_logo_id']);
    if ($logo_id > 0) {
        update_term_meta($term_id, 'thumbnail_id', $logo_id);
    } else {
        delete_term_meta($term_id, 'thumbnail_id');
    }
}

// ============================================
// 6. کوئری برندهای فعال (Frontend)
// ============================================
/**
 * گرفتن برندهای فعال
 * 
 * @param int  $limit           تعداد برند
 * @param bool $force_archive   اگه true باشه، همیشه به آرشیو برند لینک می‌ده
 *                              (حتی اگه URL سفارشی تنظیم شده باشه)
 */
function gpds_get_active_brands($limit = 12, $force_archive = false) {
    $cache_key = 'gpds_active_brands';
    $cached    = get_transient($cache_key);

    if ($cached !== false && is_array($cached)) {
        $brands = array_slice($cached, 0, $limit);
    } else {
        $terms = get_terms([
            'taxonomy'   => 'product_brand',
            'hide_empty' => false,
            'number'     => 50,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }

        $brands = [];
        foreach ($terms as $term) {
            $active = get_term_meta($term->term_id, '_gpds_brand_active', true);
            if ($active === '0') continue;

            $logo_id = get_term_meta($term->term_id, 'thumbnail_id', true);
            $logo    = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
            if (!$logo) continue;

            $custom_url   = get_term_meta($term->term_id, '_gpds_brand_url', true);
            $archive_url  = get_term_link($term);

            $brands[] = [
                'id'         => $term->term_id,
                'name'       => $term->name,
                'slug'       => $term->slug,
                'logo'       => $logo,
                'url'        => $custom_url ?: $archive_url,   // ← رفتار پیش‌فرض
                'archive_url'=> $archive_url,                   // ← همیشه آرشیو
                'custom_url' => $custom_url,                    // ← همیشه سفارشی
                'count'      => $term->count,
                'order'      => intval(get_term_meta($term->term_id, '_gpds_brand_order', true)),
            ];
        }

        usort($brands, fn($a, $b) => $a['order'] - $b['order']);

        set_transient($cache_key, $brands, HOUR_IN_SECONDS);
        $brands = array_slice($brands, 0, $limit);
    }

    // اگه force_archive روشن باشه، URL رو با archive_url جایگزین کن
    if ($force_archive) {
        foreach ($brands as &$brand) {
            $brand['url'] = $brand['archive_url'];
        }
        unset($brand);
    }

    return $brands;
}


// پاک کردن Cache
add_action('created_product_brand', 'gpds_clear_brands_cache');
add_action('edited_product_brand',  'gpds_clear_brands_cache');
add_action('delete_product_brand',  'gpds_clear_brands_cache');

function gpds_clear_brands_cache() {
    delete_transient('gpds_active_brands');
}


// ============================================
// 7. سایز تصویر برای لوگو
// ============================================
add_action('after_setup_theme', function() {
    add_image_size('gpds-brand', 200, 200, false);
}, 25);


// ============================================
// 8. سازگاری با تاکسونومی پیش‌فرض WC Brands
// ============================================
add_filter('woocommerce_product_brand_taxonomy_args', function($args) {
    $args['show_in_menu'] = true;
    return $args;
});


// ============================================
// 9. صفحه /brands/ — لیست همه برندها
// ============================================
//
// /brands/          → لیست همه برندها
// /brand/{slug}/    → محصولات یه برند خاص (پیش‌فرض وردپرس)
//
// ⚠️ بعد از افزودن این بخش، یه بار
//    تنظیمات → پیوندهای یکتا → ذخیره تغییرات
// ============================================

/**
 * 9.1 - ثبت rewrite rule
 */
add_action('init', 'gpds_brands_rewrite_rule', 5);

function gpds_brands_rewrite_rule() {
    add_rewrite_rule(
        '^brands/?$',
        'index.php?gpds_view=all_brands',
        'top'
    );
}

/**
 * 9.2 - ثبت query var
 */
add_filter('query_vars', function($vars) {
    $vars[] = 'gpds_view';
    return $vars;
});

/**
 * 9.3 - جلوگیری از 404
 */
add_action('pre_get_posts', 'gpds_brands_prevent_404');

function gpds_brands_prevent_404($query) {
    if (is_admin() || !$query->is_main_query()) return;

    if ($query->get('gpds_view') === 'all_brands') {
        $query->is_404     = false;
        $query->is_home    = false;
        $query->is_archive = false;
        $query->is_page    = true;
    }
}

/**
 * 9.4 - لود تمپلیت (تک نقطه ورود برای هر دو حالت)
 */
add_filter('template_include', 'gpds_brands_template_include', 99);

function gpds_brands_template_include($template) {
    // حالت ۱: /brands/
    if (get_query_var('gpds_view') === 'all_brands') {
        $custom = GPDS_INC . '/templates/brands/all-brands.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }

    // حالت ۲: /brand/{slug}/ → محصولات یه برند خاص
    if (is_tax('product_brand')) {
        $custom = GPDS_INC . '/templates/brands/single-brand.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }

    return $template;
}

/**
 * 9.5 - هِلپر: لینک صفحه همه برندها
 */
function gpds_get_all_brands_url() {
    return home_url('/brands/');
}

/**
 * 9.6 - عنوان صفحه /brands/
 */
add_filter('document_title_parts', function($title) {
    if (get_query_var('gpds_view') === 'all_brands') {
        $title['title'] = 'همه برندها';
    }
    return $title;
});