<?php
/**
 * WooCommerce Full Importer - نسخه نهایی
 *
 * ویژگی‌ها:
 *  - تاکسونومی «برند» به صورت جداگانه (product_brand)
 *  - دسته‌بندی‌های موضوعی و زیردسته‌ها
 *  - محصولات با توضیحات و مشخصات فنی
 *  - ویژگی‌های سراسری با لیبل فارسی (فیکس نمایش)
 *  - سازگار با Rank Math SEO
 *
 * نحوه استفاده:
 *  1. این فایل را در ریشه وردپرس آپلود کنید (کنار wp-config.php)
 *  2. آدرس https://yoursite.com/woo-full-importer.php را باز کنید
 *  3. بلافاصله بعد از اجرا فایل را از سرور حذف کنید
 */


return ;




require_once __DIR__ . '/wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'دسترسی غیرمجاز.' );
}

set_time_limit( 2400 );
ini_set( 'memory_limit', '2048M' );

if ( ! class_exists( 'WooCommerce' ) ) {
    wp_die( 'ووکامرس فعال نیست.' );
}

// ============================================================
// ۰. ثبت تاکسونومی «برند» (product_brand)
// ============================================================

function mm_register_brand_taxonomy() {
    if ( taxonomy_exists( 'product_brand' ) ) {
        return;
    }

    register_taxonomy( 'product_brand', 'product', array(
        'labels' => array(
            'name'              => 'برندها',
            'singular_name'     => 'برند',
            'search_items'      => 'جستجوی برند',
            'all_items'         => 'همه برندها',
            'edit_item'         => 'ویرایش برند',
            'update_item'       => 'به‌روزرسانی برند',
            'add_new_item'      => 'افزودن برند جدید',
            'new_item_name'     => 'نام برند جدید',
            'menu_name'         => 'برندها',
        ),
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => false,
        'query_var'         => true,
        'rewrite'           => array(
            'slug'       => 'brand',
            'with_front' => false,
        ),
    ) );
}
mm_register_brand_taxonomy();

// ============================================================
// توابع کمکی عمومی
// ============================================================

function mm_ensure_term( $name, $slug, $parent = 0, $description = '' ) {
    $term = get_term_by( 'slug', $slug, 'product_cat' );
    if ( $term ) {
        if ( $parent && $term->parent != $parent ) {
            wp_update_term( $term->term_id, 'product_cat', array( 'parent' => $parent ) );
        }
        if ( $description ) {
            wp_update_term( $term->term_id, 'product_cat', array( 'description' => $description ) );
        }
        return $term->term_id;
    }
    $args = array( 'slug' => $slug, 'parent' => $parent );
    if ( $description ) $args['description'] = $description;
    $r = wp_insert_term( $name, 'product_cat', $args );
    if ( is_wp_error( $r ) ) {
        echo "خطا در «{$name}»: " . $r->get_error_message() . "<br>";
        return 0;
    }
    return $r['term_id'];
}

/**
 * ساخت یا بازیابی یک برند (تاکسونومی product_brand)
 */
function mm_ensure_brand_term( $name, $slug ) {
    $term = get_term_by( 'slug', $slug, 'product_brand' );
    if ( $term ) {
        if ( $term->name !== $name ) {
            wp_update_term( $term->term_id, 'product_brand', array( 'name' => $name ) );
        }
        return $term->term_id;
    }

    $r = wp_insert_term( $name, 'product_brand', array( 'slug' => $slug ) );
    if ( is_wp_error( $r ) ) {
        echo "خطا در برند «{$name}»: " . $r->get_error_message() . "<br>";
        return 0;
    }
    return $r['term_id'];
}

function mm_build_specs_table( $specs ) {
    if ( empty( $specs ) ) return '';
    $html = '<h3>مشخصات فنی</h3>';
    $html .= '<table class="shop_attributes" style="width:100%;border-collapse:collapse;">';
    foreach ( $specs as $key => $value ) {
        $html .= '<tr>';
        $html .= '<th style="background:#f7f7f7;padding:10px;border:1px solid #eee;text-align:right;width:35%;">' . esc_html( $key ) . '</th>';
        $html .= '<td style="padding:10px;border:1px solid #eee;">' . esc_html( $value ) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

// ============================================================
// توابع ویژگی‌های سراسری (فیکس نمایش فارسی)
// ============================================================

function mm_persian_to_slug( $persian ) {
    static $map = array(
        'نوع'            => 'type',
        'حجم'            => 'volume',
        'سایز'           => 'size',
        'رنگ'            => 'color',
        'تعداد'          => 'quantity',
        'کاربرد'         => 'usage',
        'اتصال'          => 'connection',
        'گیج'            => 'gauge',
        'جنس'            => 'material',
        'وزن'            => 'weight',
        'عرض'            => 'width',
        'طول'            => 'length',
        'قطر'            => 'diameter',
        'غلظت'           => 'concentration',
        'ماده مؤثر'      => 'active-ingredient',
        'نام تجاری'      => 'trade-name',
        'نسل'            => 'generation',
        'راه'            => 'way',
        'استاندارد'      => 'standard',
        'تعداد لایه'     => 'layers',
        'سازگاری'        => 'compatibility',
        'ترکیبات'        => 'composition',
        'زمان اچ'        => 'etch-time',
        'شستشو'          => 'washing',
        'مدل'            => 'model',
        'طعم'            => 'flavor',
        'تعداد قطعات'    => 'parts',
        'رنگ‌ها'         => 'colors',
        'سایزها'         => 'sizes',
        'درجه'           => 'grade',
        'محتویات'        => 'contents',
        'حجم کل'         => 'total-volume',
    );

    if ( isset( $map[ $persian ] ) ) {
        return $map[ $persian ];
    }
    return 'spec-' . substr( md5( $persian ), 0, 8 );
}

function mm_ensure_global_attribute( $persian_label ) {
    global $wpdb;

    $slug = mm_persian_to_slug( $persian_label );

    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
        $slug
    ) );

    if ( ! $exists ) {
        $result = wc_create_attribute( array(
            'name'     => $persian_label,
            'slug'     => $slug,
            'type'     => 'select',
            'order_by' => 'menu_order',
        ) );

        if ( is_wp_error( $result ) ) {
            return false;
        }

        delete_transient( 'wc_attribute_taxonomies' );
        wp_cache_flush();
    }

    return 'pa_' . $slug;
}

function mm_ensure_attribute_term( $term_name, $taxonomy ) {
    if ( ! taxonomy_exists( $taxonomy ) ) {
        register_taxonomy( $taxonomy, 'product', array(
            'hierarchical' => false,
            'show_ui'      => false,
            'query_var'    => false,
            'rewrite'      => false,
        ) );
    }

    $term = term_exists( $term_name, $taxonomy );
    if ( ! $term ) {
        $term = wp_insert_term( $term_name, $taxonomy );
    }

    if ( is_wp_error( $term ) ) {
        return false;
    }

    return is_array( $term ) ? $term['term_id'] : $term;
}

// ============================================================
// تابع ساخت محصول
// ============================================================

function mm_insert_product( $product ) {
    global $brand_ids, $cat_ids;

    $sku = ! empty( $product['sku'] ) ? $product['sku'] : sanitize_title( $product['name'] );
    $existing_id = wc_get_product_id_by_sku( $sku );

    $content = ! empty( $product['description'] ) ? $product['description'] : '';
    if ( ! empty( $product['specs'] ) ) {
        $content .= mm_build_specs_table( $product['specs'] );
    }

    $args = array(
        'post_title'   => $product['name'],
        'post_content' => $content,
        'post_excerpt' => ! empty( $product['short_description'] ) ? $product['short_description'] : '',
        'post_status'  => 'publish',
        'post_type'    => 'product',
    );

    if ( $existing_id ) {
        $args['ID'] = $existing_id;
        $product_id = wp_update_post( $args );
        $status = '🔄';
    } else {
        $product_id = wp_insert_post( $args );
        $status = '✅';
    }

    if ( is_wp_error( $product_id ) || ! $product_id ) {
        echo "خطا در: {$product['name']}<br>";
        return 0;
    }

    echo "{$status} {$product['name']}<br>";

    wp_set_object_terms( $product_id, 'simple', 'product_type' );
    update_post_meta( $product_id, '_sku', $sku );
    update_post_meta( $product_id, '_regular_price', '' );
    update_post_meta( $product_id, '_price', '' );
    update_post_meta( $product_id, '_sale_price', '' );
    update_post_meta( $product_id, '_manage_stock', 'no' );
    update_post_meta( $product_id, '_stock_status', 'instock' );
    update_post_meta( $product_id, '_virtual', 'no' );
    update_post_meta( $product_id, '_downloadable', 'no' );

    // ===== ویژگی‌های محصول =====
    // برند دیگر در ویژگی‌ها ذخیره نمی‌شود (چون تاکسونومی جداگانه است)
    if ( ! empty( $product['specs'] ) ) {
        $attributes = array();
        $i = 0;

        foreach ( $product['specs'] as $persian_name => $value ) {

            // از برند صرف‌نظر کن (در تاکسونومی جدا ذخیره می‌شود)
            if ( $persian_name === 'برند' ) {
                continue;
            }

            $taxonomy = mm_ensure_global_attribute( $persian_name );
            if ( ! $taxonomy ) {
                continue;
            }

            $term_id = mm_ensure_attribute_term( $value, $taxonomy );
            if ( $term_id ) {
                wp_set_object_terms( $product_id, array( $term_id ), $taxonomy, false );
            }

            $attributes[ $taxonomy ] = array(
                'name'         => $taxonomy,
                'value'        => '',
                'position'     => $i,
                'is_visible'   => 1,
                'is_variation' => 0,
                'is_taxonomy'  => 1,
            );

            $i++;
        }

        update_post_meta( $product_id, '_product_attributes', $attributes );

        if ( function_exists( 'wc_delete_product_transients' ) ) {
            wc_delete_product_transients( $product_id );
        }
    }

    // ===== دسته‌بندی موضوعی =====
    $term_ids = array();
    if ( ! empty( $product['categories'] ) ) {
        foreach ( $product['categories'] as $slug ) {
            if ( isset( $cat_ids[ $slug ] ) && $cat_ids[ $slug ] ) {
                $term_ids[] = $cat_ids[ $slug ];
            }
        }
    }
    if ( ! empty( $term_ids ) ) {
        wp_set_object_terms( $product_id, $term_ids, 'product_cat', false );
    }

    // ===== برند (تاکسونومی جدا) =====
    if ( ! empty( $product['brand'] ) && isset( $brand_ids[ $product['brand'] ] ) && $brand_ids[ $product['brand'] ] ) {
        wp_set_object_terms( $product_id, array( $brand_ids[ $product['brand'] ] ), 'product_brand', false );
    }

    // ===== Rank Math SEO =====
    update_post_meta( $product_id, 'rank_math_title', $product['name'] . ' | فروشگاه دندانپزشکی' );
    if ( ! empty( $product['short_description'] ) ) {
        update_post_meta( $product_id, 'rank_math_description', wp_trim_words( $product['short_description'], 25 ) );
    }
    update_post_meta( $product_id, 'rank_math_focus_keyword', $product['name'] );

    return $product_id;
}

// ============================================================
// ۱. ساخت برندها (تاکسونومی product_brand)
// ============================================================

$brands = array(
    'cobalt'       => 'کبالت (Cobalt)',
    'morvabon'     => 'مروابن (Morvabon)',
    'meta-pars'    => 'متا (Meta - آزاد تجارت پارس پرنیان)',
    'morvarid-teb' => 'مروارید طب اصفهان',
    'no-brand'     => 'بدون برند',
);

$brand_ids = array();
foreach ( $brands as $slug => $name ) {
    $brand_ids[ $slug ] = mm_ensure_brand_term( $name, $slug );
}
echo "برندها ساخته شدند.<br>";

// ============================================================
// ۲. ساخت دسته‌بندی‌های موضوعی
// ============================================================

$structure = array(
    'restorative' => array(
        'name' => 'مواد ترمیمی و کامپوزیت',
        'desc' => 'کامپوزیت‌ها، اسید اچ، باندینگ، ماتریس، پرداخت و بلیچینگ',
        'subs' => array(
            'composites'         => array( 'کامپوزیت‌ها', 'کامپوزیت میکروهیبرید، فلو شید، فلو Pink و گلس نوری' ),
            'etch-bonding'       => array( 'اسید اچ و باندینگ', 'اسید اچ، پرایمر باند، پرسلن باند، وتینگ رزین و لاینرها' ),
            'matrix-ring-wedge'  => array( 'سیستم ماتریس، رینگ و وج', 'سکشنال ماتریس، رینگ کلمپ، وج و نوارهای ماتریس' ),
            'polish-discs'       => array( 'دیسک و نوار پرداخت/پالیش', 'دیسک‌های پرداخت مرکزدار و نوارهای پالیش کامپوزیت' ),
            'cements-aux'        => array( 'سمان و مواد کمکی ترمیم', 'سمان MTA، پانسمان موقت، فیشور سیلانت و خون‌بند' ),
            'prophylaxis-bleach' => array( 'جرم‌گیری و بلیچینگ', 'خمیر جرم‌گیری، کیت بلیچینگ و ژل‌های سفیدکننده' ),
        ),
    ),
    'endo' => array(
        'name' => 'اندو - درمان ریشه',
        'desc' => 'مواد اندو، شستشو کانال، گوتاپرکا و کن کاغذی',
        'subs' => array(
            'endo-materials'   => array( 'مواد و خمیرهای اندو', 'سیلر، خمیر کلسیم هیدروکساید، فرموکرزول و اوژنول' ),
            'canal-irrigation' => array( 'شستشو و ضدعفونی کانال', 'هیپوکلریت سدیم، EDTA، کلرهگزیدین و آرسی‌پرپ' ),
            'gutta-percha'     => array( 'گوتاپرکا و کن کاغذی (متا گوتا)', 'گوتاپرکا و کن کاغذی استاندارد، تقاربی و پروتیپر' ),
            'anesthesia'       => array( 'بی‌حسی موضعی', 'ژل بی‌حسی و مواد بی‌حسی سطحی' ),
        ),
    ),
    'syringe-suction' => array(
        'name' => 'سرنگ، سوند و لوازم تزریق/ساکشن',
        'desc' => 'انواع سرنگ، سرسوزن، سوند، لوله ساکشن و لوازم تزریق',
        'subs' => array(
            'syringes'        => array( 'سرنگ', 'انواع سرنگ ۲cc تا ۵۰cc، انسولین، دندانپزشکی و گاواژ' ),
            'needles'         => array( 'سرسوزن', 'سرسوزن بلند و کوتاه' ),
            'catheters'       => array( 'سوند و کاتتر', 'سوند نلاتون، فولی و معده' ),
            'other-injection' => array( 'سایر لوازم تزریق/ساکشن', 'لوله ساکشن، میکروست، هپارین لاک و ماسک' ),
        ),
    ),
    'consumables' => array(
        'name' => 'لوازم مصرفی و تجهیزات عمومی کلینیک',
        'desc' => 'آینه، پیشبند، روکش‌ها، سینی، شان و گان پزشکی',
        'subs' => array(),
    ),
);

$cat_ids = array();
foreach ( $structure as $parent_slug => $data ) {
    $parent_id = mm_ensure_term( $data['name'], $parent_slug, 0, $data['desc'] );
    $cat_ids[ $parent_slug ] = $parent_id;
    foreach ( $data['subs'] as $sub_slug => $sub_data ) {
        $cat_ids[ $sub_slug ] = mm_ensure_term( $sub_data[0], $sub_slug, $parent_id, $sub_data[1] );
    }
}
echo "دسته‌بندی‌ها ساخته شدند.<hr>";

// ============================================================
// ۳. محصولات
// ============================================================

$products = array();

// ==================== کبالت (Cobalt) ====================
$cobalt_products = array(
    array(
        'name' => 'اسید اچ پلیمری کبالت (Cobalt Etch)',
        'sku'  => 'cobalt-etch-polymer',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'اسید اچ پلیمری تخصصی برای آماده‌سازی سطوح کامپوزیتی و پرسلنی قبل از باندینگ',
        'description' => '<h3>معرفی محصول</h3>
<p>اسید اچ پلیمری کبالت (Cobalt Etch) یک عامل اچینگ تخصصی است که برای آماده‌سازی سطوح پلیمری، کامپوزیتی و پرسلنی قبل از باندینگ طراحی شده است. این محصول با ایجاد میکرو-رتنشن روی سطح، چسبندگی باندینگ را به‌طور چشمگیری افزایش می‌دهد.</p>
<h3>مزایای کلیدی</h3>
<ul>
<li>افزایش قدرت باند کامپوزیت به پرسلن و کامپوزیت قدیمی</li>
<li>بدون نیاز به شستشوی اضافی</li>
<li>سازگار با تمام سیستم‌های باندینگ</li>
<li>فرمولاسیون پایدار و ماندگاری بالا</li>
</ul>
<h3>نحوه استفاده</h3>
<ol>
<li>سطح را تمیز و خشک کنید.</li>
<li>اسید اچ را به مدت ۳۰-۶۰ ثانیه روی سطح قرار دهید.</li>
<li>سطح را با آب شستشو و خشک کنید.</li>
<li>باندینگ را طبق دستورالعمل اعمال کنید.</li>
</ol>
<h3>کاربرد</h3>
<p>مناسب برای ترمیم‌های زیبایی، تعمیر روکش‌های پرسلنی، و باند کامپوزیت جدید به کامپوزیت قدیمی.</p>',
        'specs' => array('برند' => 'کبالت (Cobalt)','نوع' => 'اسید اچ پلیمری','کاربرد' => 'آماده‌سازی سطوح کامپوزیت و پرسلن','زمان اچ' => '۳۰ تا ۶۰ ثانیه','شستشو' => 'نیاز دارد'),
    ),
    array(
        'name' => 'اسید اچ سیلیکایی کبالت (Classic Etch)',
        'sku'  => 'cobalt-classic-etch',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'اسید اچ سیلیکایی برای آماده‌سازی سطوح سرامیکی و شیشه‌ای',
        'description' => '<h3>معرفی</h3>
<p>اسید اچ سیلیکایی کبالت (Classic Etch) برای اچ کردن سطوح سرامیکی و سیلیکایی طراحی شده است. این محصول با ایجاد بافت میکروسکوپی روی سطح سرامیک، امکان باند قوی با رزین را فراهم می‌کند.</p>
<h3>مزایا</h3>
<ul>
<li>ایجاد باند قوی بین سرامیک و رزین</li>
<li>مناسب برای ترمیم‌های سرامیکی</li>
<li>سازگار با سیستم‌های باندینگ مدرن</li>
<li>کنترل دقیق عمق اچ</li>
</ul>
<h3>کاربرد</h3>
<p>مورد استفاده در باند کامپوزیت به سرامیک‌های سیلیکایی، تعمیر روکش‌های سرامیکی، و ترمیم‌های زیبایی.</p>',
        'specs' => array('برند' => 'کبالت (Cobalt)','نوع' => 'اسید اچ سیلیکایی','کاربرد' => 'آماده‌سازی سرامیک و سیلیکات'),
    ),
    array(
        'name' => 'اسید اچ پرسلن کبالت (Porcelain Etch)',
        'sku'  => 'cobalt-porcelain-etch',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'اسید اچ تخصصی پرسلن برای باند مطمئن کامپوزیت به روکش‌های پرسلنی',
        'description' => '<h3>معرفی</h3>
<p>اسید اچ پرسلن کبالت (Porcelain Etch) برای آماده‌سازی سطح پرسلن قبل از باندینگ استفاده می‌شود. این محصول با فرمولاسیون ویژه، سطوح پرسلن را بدون آسیب به ساختار، برای باند آماده می‌کند.</p>
<h3>مزایا</h3>
<ul>
<li>باند قوی و پایدار کامپوزیت به پرسلن</li>
<li>بدون آسیب به لبه‌های روکش</li>
<li>مورد استفاده در تعمیرات داخل دهانی</li>
</ul>
<h3>نحوه استفاده</h3>
<ol>
<li>سطح پرسلن را با اسید فسفریک ۳۷٪ تمیز کنید.</li>
<li>اسید اچ پرسلن را به مدت ۶۰ ثانیه اعمال کنید.</li>
<li>سطح را با آب شستشو و خشک کنید.</li>
<li>سایلن و باندینگ را اعمال کنید.</li>
</ol>',
        'specs' => array('برند' => 'کبالت','نوع' => 'اسید اچ پرسلن','زمان اچ' => '۶۰ ثانیه','کاربرد' => 'باند کامپوزیت به پرسلن'),
    ),
    array(
        'name' => 'دای آشکارساز پوسیدگی کبالت (Caries Detector)',
        'sku'  => 'cobalt-caries-detector',
        'categories' => array('restorative','cements-aux'),
        'short_description' => 'دای رنگی آشکارساز پوسیدگی برای تشخیص دقیق و حذف کامل بافت پوسیده',
        'description' => '<h3>معرفی</h3>
<p>دای آشکارساز پوسیدگی کبالت (Caries Detector) یک رنگ مخصوص است که بافت پوسیده دندان را رنگی کرده و تشخیص و حذف کامل پوسیدگی را آسان می‌کند.</p>
<h3>مزایا</h3>
<ul>
<li>تشخیص دقیق مرز پوسیدگی</li>
<li>حفظ حداکثر بافت سالم دندان</li>
<li>قابل استفاده در ترمیم‌های مستقیم و غیرمستقیم</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'دای آشکارساز','کاربرد' => 'تشخیص پوسیدگی'),
    ),
    array(
        'name' => 'ژل بندآورنده خون کبالت (Aluminostat)',
        'sku'  => 'cobalt-aluminostat',
        'categories' => array('restorative','cements-aux'),
        'short_description' => 'ژل بندآورنده خون حاوی کلراید آلومینیوم برای کنترل خونریزی لثه',
        'description' => '<h3>معرفی</h3>
<p>ژل بندآورنده خون کبالت (Aluminostat) با فرمولاسیون کلراید آلومینیوم، برای کنترل خونریزی لثه در حین کارهای ترمیمی و پروتزی استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>بندآوری سریع خون در ۳۰-۶۰ ثانیه</li>
<li>بدون اثر منفی روی باندینگ</li>
<li>مناسب برای قالب‌گیری‌های دقیق</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'ژل بندآورنده خون','ماده مؤثر' => 'کلراید آلومینیوم','کاربرد' => 'کنترل خونریزی لثه'),
    ),
    array(
        'name' => 'کلرهگزیدین ۲٪ ترمیمی کبالت (SepsiCob)',
        'sku'  => 'cobalt-sepsicob',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'کلرهگزیدین ۲٪ برای ضدعفونی کانال ریشه، جایگزین مناسب هیپوکلریت',
        'description' => '<h3>معرفی</h3>
<p>کلرهگزیدین ۲٪ ترمیمی کبالت (SepsiCob) به عنوان محلول شستشوی کانال ریشه با خاصیت ضدعفونی‌کنندگی بالا استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>طیف ضدباکتریایی وسیع</li>
<li>بدون سمیت بافتی بالا</li>
<li>ماندگاری طولانی‌مدت در کانال</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'محلول شستشو','غلظت' => '۲٪','کاربرد' => 'ضدعفونی کانال ریشه'),
    ),
    array(
        'name' => 'ژل ضدحساسیت کبالت (Desensitizing Gel)',
        'sku'  => 'cobalt-desensitizing-gel',
        'categories' => array('restorative','prophylaxis-bleach'),
        'short_description' => 'ژل ضدحساسیت برای کاهش حساسیت عاجی و درمان حساسیت‌های دندانی',
        'description' => '<h3>معرفی</h3>
<p>ژل ضدحساسیت کبالت برای کاهش حساسیت عاجی و درمان حساسیت‌های دندانی استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>تسکین سریع حساسیت دندانی</li>
<li>ماندگاری طولانی‌مدت</li>
<li>قابل استفاده در مطب و خانگی</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'ژل ضدحساسیت','کاربرد' => 'کاهش حساسیت عاجی'),
    ),
    array(
        'name' => 'ژل سفیدکننده خانگی کبالت (Home Bleach)',
        'sku'  => 'cobalt-home-bleach',
        'categories' => array('restorative','prophylaxis-bleach'),
        'short_description' => 'ژل سفیدکننده خانگی با غلظت مناسب برای استفاده در قالب‌های شخصی',
        'description' => '<h3>معرفی</h3>
<p>ژل سفیدکننده خانگی کبالت (Home Bleach) برای استفاده در قالب‌های سفیدکننده خانگی طراحی شده است.</p>
<h3>مزایا</h3>
<ul>
<li>سفیدی تدریجی و ایمن</li>
<li>کاهش حساسیت نسبت به بلیچینگ آفیس</li>
<li>مناسب برای سفیدکردن طولانی‌مدت</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'ژل سفیدکننده خانگی','کاربرد' => 'بلیچینگ خانگی'),
    ),
    array(
        'name' => 'آرسی‌پرپ کبالت (Cobalt Prep)',
        'sku'  => 'cobalt-prep',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'ژل نرم‌کننده و ضدعفونی‌کننده کانال ریشه',
        'description' => '<h3>معرفی</h3>
<p>آرسی‌پرپ کبالت (Cobalt Prep) یک ژل نرم‌کننده و ضدعفونی‌کننده کانال ریشه است.</p>
<h3>مزایا</h3>
<ul>
<li>کاهش اصطکاک بین فایل و دیواره کانال</li>
<li>ضدعفونی همزمان کانال</li>
<li>کاهش خطر شکستگی فایل</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'ژل اندو','کاربرد' => 'نرم‌کننده و ضدعفونی کانال'),
    ),
    array(
        'name' => 'محلول EDTA ۱۷٪ کبالت (Canal Clean)',
        'sku'  => 'cobalt-canal-clean',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'محلول EDTA ۱۷٪ برای حذف اسمیر لایه و نرم‌کردن کانال ریشه',
        'description' => '<h3>معرفی</h3>
<p>محلول EDTA ۱۷٪ کبالت (Canal Clean) برای حذف اسمیر لایه و نرم‌کردن کانال ریشه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>حذف مؤثر اسمیر لایه</li>
<li>باز کردن توبول‌های عاجی</li>
<li>افزایش نفوذ سیلر</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'محلول شستشو','غلظت' => '۱۷٪','کاربرد' => 'حذف اسمیر لایه'),
    ),
    array(
        'name' => 'ژل فسفات فلوراید اسیدی کبالت (Fluoride Gel)',
        'sku'  => 'cobalt-fluoride-gel',
        'categories' => array('restorative','prophylaxis-bleach'),
        'short_description' => 'ژل فلوراید اسیدی برای پیشگیری از پوسیدگی و کاهش حساسیت',
        'description' => '<h3>معرفی</h3>
<p>ژل فسفات فلوراید اسیدی کبالت (Fluoride Gel) برای پیشگیری از پوسیدگی و کاهش حساسیت دندانی استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>افزایش مقاومت مینا به پوسیدگی</li>
<li>کاهش حساسیت دندانی</li>
<li>مناسب برای بیماران با خطر پوسیدگی بالا</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'ژل فلوراید','کاربرد' => 'پیشگیری از پوسیدگی'),
    ),
    array(
        'name' => 'ژل تمیزکننده رستوریشن کبالت (Zirconia Clean)',
        'sku'  => 'cobalt-zirconia-clean',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'ژل تمیزکننده مخصوص رستوریشن‌های زیرکونیا قبل از باندینگ',
        'description' => '<h3>معرفی</h3>
<p>ژل تمیزکننده رستوریشن کبالت (Zirconia Clean) برای پاکسازی سطوح زیرکونیا قبل از باندینگ استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>پاکسازی مؤثر سطوح زیرکونیا</li>
<li>افزایش قدرت باند</li>
<li>بدون آسیب به ساختار رستوریشن</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'ژل تمیزکننده','کاربرد' => 'پاکسازی زیرکونیا'),
    ),
    array(
        'name' => 'خمیر کلسیم هیدروکساید کبالت (Cobalt Paste)',
        'sku'  => 'cobalt-paste',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'خمیر کلسیم هیدروکساید برای پانسمان داخل کانال',
        'description' => '<h3>معرفی</h3>
<p>خمیر کلسیم هیدروکساید کبالت (Cobalt Paste) برای پانسمان داخل کانال ریشه و درمان ضایعات پری‌اپیکال استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>خاصیت ضدباکتریایی بالا</li>
<li>تحریک تشکیل بافت سخت</li>
<li>مناسب برای پانسمان طولانی‌مدت</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'خمیر پانسمان کانال','کاربرد' => 'پانسمان اندو'),
    ),
    array(
        'name' => 'خمیر کلسیم هیدروکساید دارای یدوفرم کبالت (Filpex)',
        'sku'  => 'cobalt-filpex',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'خمیر کلسیم هیدروکساید + یدوفرم برای پانسمان طولانی‌مدت کانال',
        'description' => '<h3>معرفی</h3>
<p>خمیر Filpex ترکیبی از کلسیم هیدروکساید و یدوفرم است که برای پانسمان طولانی‌مدت کانال ریشه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>خاصیت ضدباکتریایی قوی‌تر</li>
<li>رادیواوپاک (قابل مشاهده در رادیوگرافی)</li>
<li>مناسب برای پانسمان طولانی‌مدت</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'خمیر پانسمان کانال','ترکیبات' => 'کلسیم هیدروکساید + یدوفرم','کاربرد' => 'پانسمان طولانی‌مدت اندو'),
    ),
    array(
        'name' => 'پرسلن باند کبالت (Porcelain Bond)',
        'sku'  => 'cobalt-porcelain-bond',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'سیستم باندینگ تخصصی برای اتصال کامپوزیت به پرسلن و سرامیک',
        'description' => '<h3>معرفی</h3>
<p>پرسلن باند کبالت (Porcelain Bond) یک سیستم باندینگ تخصصی برای اتصال کامپوزیت به پرسلن و سرامیک است.</p>
<h3>مزایا</h3>
<ul>
<li>باند قوی و پایدار به پرسلن</li>
<li>سازگار با انواع کامپوزیت‌ها</li>
<li>نتیجه زیبایی عالی</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'سیستم باندینگ','کاربرد' => 'اتصال کامپوزیت به پرسلن'),
    ),
    array(
        'name' => 'وتینگ رزین کبالت (Wetting Resin)',
        'sku'  => 'cobalt-wetting-resin',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'رزین خیس‌کننده برای بهبود نفوذ باندینگ روی سطوح دشوار',
        'description' => '<h3>معرفی</h3>
<p>وتینگ رزین کبالت (Wetting Resin) برای بهبود خیس‌شوندگی سطح و افزایش نفوذ باندینگ استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>بهبود خیس‌شوندگی سطح</li>
<li>افزایش نفوذ باندینگ</li>
<li>کاهش حباب هوا در باند</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'رزین خیس‌کننده','کاربرد' => 'بهبود باندینگ'),
    ),
    array(
        'name' => 'کیت رزین اینفیلترانت کبالت (Resin Infiltrant Kit)',
        'sku'  => 'cobalt-resin-infiltrant-kit',
        'categories' => array('restorative','cements-aux'),
        'short_description' => 'کیت رزین اینفیلترانت برای درمان ضایعات اولیه (White Spot) بدون تراش',
        'description' => '<h3>معرفی</h3>
<p>کیت رزین اینفیلترانت کبالت برای درمان ضایعات پوسیدگی اولیه (White Spot) بدون تراش استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>درمان غیرتهاجمی ضایعات اولیه</li>
<li>حفظ ساختار دندان</li>
<li>نتیجه زیبایی عالی</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'کیت رزین اینفیلترانت','کاربرد' => 'درمان White Spot'),
    ),
    array(
        'name' => 'فیشور سیلانت کبالت (Cobalt Sealant)',
        'sku'  => 'cobalt-sealant',
        'categories' => array('restorative','cements-aux'),
        'short_description' => 'فیشور سیلانت نورپلیمریزه برای پیشگیری از پوسیدگی شیارهای دندان',
        'description' => '<h3>معرفی</h3>
<p>فیشور سیلانت کبالت (Cobalt Sealant) برای پر کردن شیارهای دندان‌های خلفی و پیشگیری از پوسیدگی استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>پیشگیری مؤثر از پوسیدگی</li>
<li>نورپلیمریزه سریع</li>
<li>ماندگاری بالا</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'فیشور سیلانت','کاربرد' => 'پیشگیری از پوسیدگی'),
    ),
    array(
        'name' => 'رزین محافظ لثه کبالت (Cobalt Dam)',
        'sku'  => 'cobalt-dam',
        'categories' => array('restorative','cements-aux'),
        'short_description' => 'رزین محافظ لثه به عنوان سد فیزیکی موقت در حین درمان',
        'description' => '<h3>معرفی</h3>
<p>رزین محافظ لثه کبالت (Cobalt Dam) یک سد فیزیکی موقت برای محافظت از لثه در حین بلیچینگ و ترمیم‌های شیمیایی است.</p>
<h3>مزایا</h3>
<ul>
<li>محافظت از بافت لثه</li>
<li>نورپلیمریزه سریع</li>
<li>برداشت آسان</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'رزین محافظ','کاربرد' => 'محافظت از لثه'),
    ),
    array(
        'name' => 'ژل گلیسیرین کبالت (Oxybar)',
        'sku'  => 'cobalt-oxybar',
        'categories' => array('restorative','prophylaxis-bleach'),
        'short_description' => 'ژل گلیسیرین محافظ لثه قبل از بلیچینگ',
        'description' => '<h3>معرفی</h3>
<p>ژل گلیسیرین کبالت (Oxybar) به عنوان یک سد محافظ روی لثه قبل از بلیچینگ استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>محافظت مؤثر از لثه</li>
<li>بدون تداخل با مواد بلیچینگ</li>
<li>برداشت آسان</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'ژل محافظ','کاربرد' => 'محافظت از لثه در بلیچینگ'),
    ),
    array(
        'name' => 'پودر کلسیم هیدروکساید + مایع کلسیم کبالت',
        'sku'  => 'cobalt-calcium-hydroxide-powder',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'پودر و مایع کلسیم هیدروکساید برای تهیه خمیر پانسمان تازه',
        'description' => '<h3>معرفی</h3>
<p>پودر و مایع کلسیم هیدروکساید کبالت برای تهیه خمیر پانسمان کانال ریشه به‌صورت تازه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>تهیه خمیر تازه با غلظت دلخواه</li>
<li>خاصیت ضدباکتریایی بالا</li>
<li>ماندگاری طولانی‌مدت پودر</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'پودر + مایع','کاربرد' => 'تهیه خمیر پانسمان'),
    ),
    array(
        'name' => 'محلول ۲٪ کلرهگزیدین کبالت (Cobixidine)',
        'sku'  => 'cobalt-cobixidine',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'محلول ۲٪ کلرهگزیدین برای شستشو و ضدعفونی کانال ریشه',
        'description' => '<h3>معرفی</h3>
<p>محلول ۲٪ کلرهگزیدین کبالت (Cobixidine) برای شستشو و ضدعفونی کانال ریشه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>طیف ضدباکتریایی وسیع</li>
<li>ماندگاری طولانی در کانال</li>
<li>سازگار با بافت پری‌اپیکال</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'محلول شستشو','غلظت' => '۲٪','کاربرد' => 'ضدعفونی کانال'),
    ),
    array(
        'name' => 'محلول هیپوکلریت کبالت (CobaCid)',
        'sku'  => 'cobalt-cobacid',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'محلول هیپوکلریت سدیم برای شستشو، ضدعفونی و حل بافت نکروزه',
        'description' => '<h3>معرفی</h3>
<p>محلول هیپوکلریت کبالت (CobaCid) برای شستشو، ضدعفونی و حل بافت نکروزه کانال ریشه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>حل بافت نکروزه و ارگانیک</li>
<li>ضدعفونی مؤثر کانال</li>
<li>سازگار با EDTA</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'محلول شستشو','ماده مؤثر' => 'هیپوکلریت سدیم','کاربرد' => 'ضدعفونی و حل بافت'),
    ),
    array(
        'name' => 'محلول سیتریک اسید کبالت (Citric Acid)',
        'sku'  => 'cobalt-citric-acid',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'محلول سیتریک اسید به عنوان جایگزین EDTA برای حذف اسمیر لایه',
        'description' => '<h3>معرفی</h3>
<p>محلول سیتریک اسید کبالت (Citric Acid) به عنوان جایگزین EDTA برای حذف اسمیر لایه در کانال ریشه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>حذف اسمیر لایه</li>
<li>جایگزین مناسب EDTA</li>
<li>سازگار با هیپوکلریت</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'محلول شستشو','کاربرد' => 'حذف اسمیر لایه'),
    ),
    array(
        'name' => 'پانسمان موقت نوری کبالت (Temp LC)',
        'sku'  => 'cobalt-temp-lc',
        'categories' => array('restorative','cements-aux'),
        'short_description' => 'پانسمان موقت نورپلیمریزه برای ترمیم‌های موقت بین جلسات',
        'description' => '<h3>معرفی</h3>
<p>پانسمان موقت نوری کبالت (Temp LC) برای ترمیم‌های موقت با نور پلیمریزه می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>نورپلیمریزه سریع</li>
<li>برداشت آسان</li>
<li>محافظت از دندان بین جلسات</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'پانسمان موقت','کاربرد' => 'ترمیم موقت'),
    ),
    array(
        'name' => 'محلول نقره فلوراید کبالت (Cobalt SDF)',
        'sku'  => 'cobalt-sdf',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'محلول نقره فلوراید (SDF) برای توقف پوسیدگی و کاهش حساسیت',
        'description' => '<h3>معرفی</h3>
<p>محلول نقره فلوراید کبالت (Cobalt SDF) برای توقف پوسیدگی و کاهش حساسیت در دندان‌ها استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>توقف سریع پوسیدگی</li>
<li>کاهش حساسیت</li>
<li>مناسب برای بیماران کم‌همکار</li>
</ul>
<h3>نکته مهم</h3>
<p>این محلول باعث تیرگی موقت دندان می‌شود؛ بنابراین در دندان‌های خلفی و شیری کاربرد بیشتری دارد.</p>',
        'specs' => array('برند' => 'کبالت','نوع' => 'محلول نقره فلوراید','کاربرد' => 'توقف پوسیدگی'),
    ),
    array(
        'name' => 'خشک‌کننده کانال کبالت (Canal Dry)',
        'sku'  => 'cobalt-canal-dry',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'خشک‌کننده کانال ریشه برای جذب رطوبت قبل از پر کردن',
        'description' => '<h3>معرفی</h3>
<p>خشک‌کننده کانال کبالت (Canal Dry) برای جذب رطوبت باقی‌مانده در کانال ریشه قبل از پر کردن استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>جذب سریع رطوبت</li>
<li>آماده‌سازی کانال برای پر کردن</li>
<li>کاهش خطر آلودگی</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'خشک‌کننده','کاربرد' => 'خشک کردن کانال'),
    ),
    array(
        'name' => 'مدلینگ رزین کبالت (Modeling Resin)',
        'sku'  => 'cobalt-modeling-resin',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'مدلینگ رزین برای فرم‌دهی و مدل‌سازی ترمیم‌های کامپوزیتی',
        'description' => '<h3>معرفی</h3>
<p>مدلینگ رزین کبالت (Modeling Resin) برای فرم‌دهی و مدل‌سازی ترمیم‌های کامپوزیتی قبل از پلیمریزاسیون استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>فرم‌دهی آسان</li>
<li>عدم چسبندگی به ابزار</li>
<li>نتیجه زیبایی طبیعی</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'رزین مدلینگ','کاربرد' => 'فرم‌دهی ترمیم'),
    ),
    array(
        'name' => 'لاینر نوری کلسیم هیدروکساید کبالت (CobaLine LC)',
        'sku'  => 'cobalt-cobaline-lc',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'لاینر نوری کلسیم هیدروکساید برای محافظت از پالپ در ترمیم‌های عمیق',
        'description' => '<h3>معرفی</h3>
<p>لاینر نوری کلسیم هیدروکساید کبالت (CobaLine LC) برای محافظت از پالپ در ترمیم‌های عمیق استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>محافظت از پالپ</li>
<li>نورپلیمریزه سریع</li>
<li>تحریک تشکیل عاج ثانویه</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'لاینر نوری','کاربرد' => 'محافظت از پالپ'),
    ),
    array(
        'name' => 'لاینر نوری کلسیم سیلیکات کبالت (CobaCal LC)',
        'sku'  => 'cobalt-cobacal-lc',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'لاینر نوری کلسیم سیلیکات برای تحریک تشکیل عاج ثانویه',
        'description' => '<h3>معرفی</h3>
<p>لاینر نوری کلسیم سیلیکات کبالت (CobaCal LC) برای محافظت از پالپ و تحریک تشکیل عاج ثانویه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>تحریک تشکیل عاج ثانویه</li>
<li>محافظت قوی از پالپ</li>
<li>نورپلیمریزه سریع</li>
</ul>',
        'specs' => array('برند' => 'کبالت','نوع' => 'لاینر نوری','ترکیبات' => 'کلسیم سیلیکات','کاربرد' => 'محافظت از پالپ'),
    ),
);

foreach ( $cobalt_products as $p ) {
    $p['brand'] = 'cobalt';
    $products[] = $p;
}

// ==================== مروابن (Morvabon) ====================
$morvabon_products = array(
    array(
        'name' => 'کیت آسورت سکشنال ۵۰ عددی + رینگ کلمپ',
        'sku'  => 'morvabon-sectional-assorted-kit-50',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'کیت کامل سکشنال ۵۰ عددی در سایزهای مختلف به همراه رینگ کلمپ',
        'description' => '<h3>معرفی</h3>
<p>کیت آسورت سکشنال مروابن شامل ۵۰ عدد سکشنال در سایزهای بزرگ، متوسط و کوچک به همراه رینگ کلمپ است. این کیت برای ترمیم‌های دقیق مولر و پرمولر (کلاس II) طراحی شده است.</p>
<h3>مزایا</h3>
<ul>
<li>پوشش کامل سایزهای مختلف در یک کیت</li>
<li>رینگ کلمپ با فنر قوی برای تثبیت ماتریس</li>
<li>مناسب برای ترمیم‌های کلاس II</li>
<li>اقتصادی و کاربردی</li>
</ul>
<h3>محتویات کیت</h3>
<ul>
<li>۵۰ عدد سکشنال ماتریس (سایزهای مختلف)</li>
<li>۱ عدد رینگ کلمپ</li>
</ul>
<h3>نحوه استفاده</h3>
<ol>
<li>سکشنال مناسب را انتخاب کنید.</li>
<li>رینگ کلمپ را روی ماتریس قرار دهید.</li>
<li>ماتریس را در محل ترمیم قرار دهید.</li>
<li>ترمیم را انجام دهید.</li>
</ol>',
        'specs' => array('برند' => 'مروابن (Morvabon)','نوع' => 'کیت سکشنال ماتریس','تعداد' => '۵۰ عدد سکشنال + ۱ رینگ کلمپ','سایزها' => 'بزرگ، متوسط، کوچک','کاربرد' => 'ترمیم‌های کلاس II'),
    ),
    array(
        'name' => 'سکشنال ماتریس بزرگ (۵۰ عددی)',
        'sku'  => 'morvabon-sectional-matrix-large',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'سکشنال ماتریس سایز بزرگ، بسته ۵۰ عددی',
        'description' => '<h3>معرفی</h3>
<p>سکشنال ماتریس بزرگ مروابن برای ترمیم‌های کلاس II در مولرهای بزرگ استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>کانتور دقیق ترمیم</li>
<li>ضخامت مناسب</li>
<li>قابل استفاده با رینگ کلمپ</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'سکشنال ماتریس','سایز' => 'بزرگ','تعداد' => '۵۰ عدد','کاربرد' => 'مولرهای بزرگ'),
    ),
    array(
        'name' => 'سکشنال ماتریس متوسط (۵۰ عددی)',
        'sku'  => 'morvabon-sectional-matrix-medium',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'سکشنال ماتریس سایز متوسط، بسته ۵۰ عددی',
        'description' => '<h3>معرفی</h3>
<p>سکشنال ماتریس متوسط مروابن برای ترمیم‌های کلاس II در پرمولرها و مولرهای کوچک استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'سکشنال ماتریس','سایز' => 'متوسط','تعداد' => '۵۰ عدد','کاربرد' => 'پرمولر و مولرهای کوچک'),
    ),
    array(
        'name' => 'سکشنال ماتریس کوچک (۵۰ عددی)',
        'sku'  => 'morvabon-sectional-matrix-small',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'سکشنال ماتریس سایز کوچک، بسته ۵۰ عددی',
        'description' => '<h3>معرفی</h3>
<p>سکشنال ماتریس کوچک مروابن برای ترمیم‌های کلاس II در دندان‌های کوچک استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'سکشنال ماتریس','سایز' => 'کوچک','تعداد' => '۵۰ عدد','کاربرد' => 'دندان‌های کوچک'),
    ),
    array(
        'name' => 'کیت سکشنال زین اسبی (۱۸ عدد + ۱ عدد کلیپس)',
        'sku'  => 'morvabon-saddle-sectional-kit',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'کیت سکشنال زین اسبی ۱۸ عددی با کلیپس',
        'description' => '<h3>معرفی</h3>
<p>کیت سکشنال زین اسبی مروابن شامل ۱۸ عدد سکشنال و ۱ عدد کلیپس برای ترمیم‌های کلاس II است.</p>
<h3>مزایا</h3>
<ul>
<li>انطباق عالی با آناتومی دندان</li>
<li>کانتور طبیعی ترمیم</li>
<li>کلیپس تثبیت‌کننده قوی</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کیت سکشنال زین اسبی','تعداد' => '۱۸ عدد + ۱ کلیپس','کاربرد' => 'ترمیم کلاس II'),
    ),
    array(
        'name' => 'کوهان مولاریدون باله (۱۲ عددی)',
        'sku'  => 'morvabon-molaridun-fin-12',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'کوهان مولاریدون باله، بسته ۱۲ عددی',
        'description' => '<h3>معرفی</h3>
<p>کوهان مولاریدون باله مروابن برای ترمیم‌های مولر و ایجاد کانتور مناسب استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>کانتور مناسب مولر</li>
<li>باله تثبیت‌کننده</li>
<li>مناسب برای ترمیم‌های وسیع</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کوهان مولاریدون','تعداد' => '۱۲ عدد','کاربرد' => 'ترمیم مولر'),
    ),
    array(
        'name' => 'وج چوبی آناتومیک نارنجی (۱۰۰ عددی)',
        'sku'  => 'morvabon-anatomical-wooden-wedge-orange',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'وج چوبی آناتومیک نارنجی، بسته ۱۰۰ عددی',
        'description' => '<h3>معرفی</h3>
<p>وج چوبی آناتومیک نارنجی مروابن برای جدا کردن دندان‌ها و ماتریس‌بندی استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>جدا کردن دقیق دندان‌ها</li>
<li>طراحی آناتومیک</li>
<li>چوب با کیفیت بالا</li>
<li>مناسب برای ماتریس‌بندی</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'وج چوبی','رنگ' => 'نارنجی','تعداد' => '۱۰۰ عدد','جنس' => 'چوب','کاربرد' => 'ماتریس‌بندی'),
    ),
    array(
        'name' => 'نوار شفاف رولی (عرض ۸ میلی‌متر، طول ۱۰ متر)',
        'sku'  => 'morvabon-clear-roll-tape-8mm',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'نوار شفاف رولی عرض ۸ میلی‌متر، طول ۱۰ متر',
        'description' => '<h3>معرفی</h3>
<p>نوار شفاف رولی مروابن با عرض ۸ میلی‌متر و طول ۱۰ متر برای ترمیم‌های زیبایی و ماتریس‌بندی استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>شفافیت بالا برای نوردهی</li>
<li>انعطاف‌پذیری مناسب</li>
<li>مناسب ترمیم‌های قدامی و خلفی</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'نوار ماتریس','عرض' => '۸ میلی‌متر','طول' => '۱۰ متر','رنگ' => 'شفاف'),
    ),
    array(
        'name' => 'نوار شفاف رولی (عرض ۱۰ میلی‌متر، طول ۱۰ متر)',
        'sku'  => 'morvabon-clear-roll-tape-10mm',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'نوار شفاف رولی عرض ۱۰ میلی‌متر، طول ۱۰ متر',
        'description' => '<h3>معرفی</h3>
<p>نوار شفاف رولی مروابن با عرض ۱۰ میلی‌متر و طول ۱۰ متر برای ترمیم‌های زیبایی استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'نوار ماتریس','عرض' => '۱۰ میلی‌متر','طول' => '۱۰ متر','رنگ' => 'شفاف'),
    ),
    array(
        'name' => 'نوار سلولوئیدی سایز ۱۰',
        'sku'  => 'morvabon-celluloid-strip-10',
        'categories' => array('restorative','matrix-ring-wedge'),
        'short_description' => 'نوار سلولوئیدی سایز ۱۰',
        'description' => '<h3>معرفی</h3>
<p>نوار سلولوئیدی سایز ۱۰ مروابن برای ماتریس‌بندی در ترمیم‌های قدامی و خلفی استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'نوار سلولوئیدی','سایز' => '۱۰','کاربرد' => 'ماتریس‌بندی'),
    ),
    array(
        'name' => 'دیسک پرداخت مرکزدار زرشکی (۵۰ عددی)',
        'sku'  => 'morvabon-polishing-disc-burgundy',
        'categories' => array('restorative','polish-discs'),
        'short_description' => 'دیسک پرداخت مرکزدار زرشکی، بسته ۵۰ عددی',
        'description' => '<h3>معرفی</h3>
<p>دیسک پرداخت مرکزدار زرشکی مروابن برای پرداخت اولیه کامپوزیت استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>حذف سریع اضافات</li>
<li>مرکزدار برای نصب آسان</li>
<li>سازگار با ماندرل 3M</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'دیسک پرداخت','رنگ' => 'زرشکی','تعداد' => '۵۰ عدد','کاربرد' => 'پرداخت اولیه'),
    ),
    array(
        'name' => 'دیسک پرداخت سورمه‌ای (۴۰ عددی)',
        'sku'  => 'morvabon-polishing-disc-navy',
        'categories' => array('restorative','polish-discs'),
        'short_description' => 'دیسک پرداخت سورمه‌ای، بسته ۴۰ عددی',
        'description' => '<h3>معرفی</h3>
<p>دیسک پرداخت سورمه‌ای مروابن برای پرداخت میانی کامپوزیت استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'دیسک پرداخت','رنگ' => 'سورمه‌ای','تعداد' => '۴۰ عدد','کاربرد' => 'پرداخت میانی'),
    ),
    array(
        'name' => 'دیسک پرداخت مرکزدار زرد (۴۰ عددی، ۱۴ میل)',
        'sku'  => 'morvabon-polishing-disc-yellow',
        'categories' => array('restorative','polish-discs'),
        'short_description' => 'دیسک پرداخت مرکزدار زرد قطر ۱۴ میلی‌متر',
        'description' => '<h3>معرفی</h3>
<p>دیسک پرداخت مرکزدار زرد مروابن با قطر ۱۴ میلی‌متر برای پرداخت نهایی کامپوزیت استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'دیسک پرداخت','رنگ' => 'زرد','قطر' => '۱۴ میلی‌متر','تعداد' => '۴۰ عدد'),
    ),
    array(
        'name' => 'دیسک پرداخت مرکزدار سبز (۴۰ عددی)',
        'sku'  => 'morvabon-polishing-disc-green',
        'categories' => array('restorative','polish-discs'),
        'short_description' => 'دیسک پرداخت مرکزدار سبز',
        'description' => '<h3>معرفی</h3>
<p>دیسک پرداخت مرکزدار سبز مروابن برای پرداخت و پالیش نهایی کامپوزیت استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'دیسک پرداخت','رنگ' => 'سبز','تعداد' => '۴۰ عدد'),
    ),
    array(
        'name' => 'دیسک پرداخت مرکزدار سفید (۴۰ عددی)',
        'sku'  => 'morvabon-polishing-disc-white',
        'categories' => array('restorative','polish-discs'),
        'short_description' => 'دیسک پرداخت مرکزدار سفید',
        'description' => '<h3>معرفی</h3>
<p>دیسک پرداخت مرکزدار سفید مروابن برای پالیش نهایی و ایجاد براقی استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'دیسک پرداخت','رنگ' => 'سفید','تعداد' => '۴۰ عدد'),
    ),
    array(
        'name' => 'دیسک پرداخت مرکزدار آبی (۴۰ عددی)',
        'sku'  => 'morvabon-polishing-disc-blue',
        'categories' => array('restorative','polish-discs'),
        'short_description' => 'دیسک پرداخت مرکزدار آبی',
        'description' => '<h3>معرفی</h3>
<p>دیسک پرداخت مرکزدار آبی مروابن برای پرداخت نهایی کامپوزیت استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'دیسک پرداخت','رنگ' => 'آبی','تعداد' => '۴۰ عدد'),
    ),
    array(
        'name' => 'نوار پرداخت کامپوزیت ۲۵ عددی (آبی-سبز) Coarse',
        'sku'  => 'morvabon-composite-polishing-strip-coarse',
        'categories' => array('restorative','polish-discs'),
        'short_description' => 'نوار پرداخت کامپوزیت Coarse، بسته ۲۵ عددی',
        'description' => '<h3>معرفی</h3>
<p>نوار پرداخت کامپوزیت مروابن با درجه Coarse برای پرداخت اولیه و حذف اضافات کامپوزیت استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'نوار پرداخت','درجه' => 'Coarse (زبر)','رنگ' => 'آبی-سبز','تعداد' => '۲۵ عدد'),
    ),
    array(
        'name' => 'کیت نوار پرداخت کامپوزیت ۴ رنگ (۷۵ عددی)',
        'sku'  => 'morvabon-composite-polishing-strip-kit-4color',
        'categories' => array('restorative','polish-discs'),
        'short_description' => 'کیت نوار پرداخت کامپوزیت ۴ رنگ، ۷۵ عددی',
        'description' => '<h3>معرفی</h3>
<p>کیت نوار پرداخت کامپوزیت ۴ رنگ مروابن شامل نوارهای پرداخت در ۴ درجه مختلف برای پرداخت کامل کامپوزیت است.</p>
<h3>مزایا</h3>
<ul>
<li>پرداخت کامل از زبر تا نرم</li>
<li>مناسب برای ترمیم‌های بین دندانی</li>
<li>انعطاف‌پذیری بالا</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کیت نوار پرداخت','تعداد' => '۷۵ عدد','رنگ‌ها' => '۴ رنگ (درجات مختلف)'),
    ),
    array(
        'name' => 'ماندرل فشاری مخصوص دیسک‌های پولیش مرکزدار (مشابه 3M)',
        'sku'  => 'morvabon-mandrel-for-centered-discs',
        'categories' => array('restorative','polish-discs'),
        'short_description' => 'ماندرل فشاری مشابه 3M برای نصب دیسک‌های مرکزدار',
        'description' => '<h3>معرفی</h3>
<p>ماندرل فشاری مروابن (مشابه 3M) برای نصب دیسک‌های پولیش مرکزدار روی هندپیس استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>نصب آسان دیسک‌ها</li>
<li>سازگار با دیسک‌های 3M</li>
<li>دوام بالا</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'ماندرل فشاری','سازگاری' => 'مشابه 3M','کاربرد' => 'نصب دیسک‌های پولیش'),
    ),
    array(
        'name' => 'ژل اسید اچ ۳۷٪ جامبو (سرنگی ۵۰ میل، ۶۷ گرم)',
        'sku'  => 'morvabon-etching-gel-37-jumbo',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'ژل اسید اچ ۳۷٪ جامبو، سرنگی ۵۰ میل (۶۷ گرم)',
        'description' => '<h3>معرفی</h3>
<p>ژل اسید اچ ۳۷٪ جامبو مروابن در سرنگ ۵۰ میلی‌لیتری (۶۷ گرم) برای اچ کردن مینا و عاج استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>حجم زیاد و اقتصادی</li>
<li>کنترل دقیق اعمال</li>
<li>سازگار با تمام سیستم‌های باندینگ</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'ژل اسید اچ','غلظت' => '۳۷٪','حجم' => '۵۰ میلی‌لیتر (۶۷ گرم)'),
    ),
    array(
        'name' => 'ژل اسید اچ فسفریک ۳۷٪ (سه تیوب ۲.۵ میل)',
        'sku'  => 'morvabon-phosphoric-etching-gel-37',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'ژل اسید اچ فسفریک ۳۷٪، سه تیوب ۲.۵ میل، جمعاً ۱۰ گرم',
        'description' => '<h3>معرفی</h3>
<p>ژل اسید اچ فسفریک ۳۷٪ مروابن، سه تیوب ۲.۵ میل، جمعاً ۱۰ گرم، برای اچ کردن مینا و عاج استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'ژل اسید اچ','غلظت' => '۳۷٪','تعداد' => '۳ تیوب ۲.۵ میل','وزن کل' => '۱۰ گرم'),
    ),
    array(
        'name' => 'پرایمر باند نسل ۵ (۵ میلی‌لیتری)',
        'sku'  => 'morvabon-primer-bond-5th-gen',
        'categories' => array('restorative','etch-bonding'),
        'short_description' => 'پرایمر باند نسل ۵، ظرف قطره‌چکان ۵ میلی‌لیتری معادل ۲.۵ گرم',
        'description' => '<h3>معرفی</h3>
<p>پرایمر باند نسل ۵ مروابن، ظرف قطره‌چکان ۵ میلی‌لیتری معادل ۲.۵ گرم، برای باندینگ کامپوزیت به عاج و مینا استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>باند قوی به عاج و مینا</li>
<li>کاهش حساسیت پس از ترمیم</li>
<li>سازگار با تمام کامپوزیت‌ها</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'پرایمر باند','نسل' => '۵','حجم' => '۵ میلی‌لیتر (۲.۵ گرم)'),
    ),
    array(
        'name' => 'کامپوزیت میکروهیبرید A1',
        'sku'  => 'morvabon-composite-microhybrid-a1',
        'categories' => array('restorative','composites'),
        'short_description' => 'کامپوزیت میکروهیبرید رنگ A1 برای ترمیم‌های قدامی و خلفی',
        'description' => '<h3>معرفی</h3>
<p>کامپوزیت میکروهیبرید A1 مروابن برای ترمیم‌های قدامی و خلفی با استحکام بالا و زیبایی مناسب استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>استحکام بالا برای ترمیم‌های خلفی</li>
<li>زیبایی مناسب برای ترمیم‌های قدامی</li>
<li>پالیش‌پذیری عالی</li>
<li>ماندگاری طولانی‌مدت</li>
</ul>
<h3>نحوه استفاده</h3>
<ol>
<li>دندان را آماده و اچ کنید.</li>
<li>باندینگ را اعمال کنید.</li>
<li>کامپوزیت را لایه‌لایه قرار دهید.</li>
<li>هر لایه را نوردهی کنید.</li>
<li>پرداخت و پالیش نهایی.</li>
</ol>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کامپوزیت میکروهیبرید','رنگ' => 'A1','کاربرد' => 'ترمیم قدامی و خلفی'),
    ),
    array(
        'name' => 'کامپوزیت میکروهیبرید A2',
        'sku'  => 'morvabon-composite-microhybrid-a2',
        'categories' => array('restorative','composites'),
        'short_description' => 'کامپوزیت میکروهیبرید رنگ A2',
        'description' => '<h3>معرفی</h3>
<p>کامپوزیت میکروهیبرید A2 مروابن برای ترمیم‌های قدامی و خلفی استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کامپوزیت میکروهیبرید','رنگ' => 'A2'),
    ),
    array(
        'name' => 'کامپوزیت میکروهیبرید A3',
        'sku'  => 'morvabon-composite-microhybrid-a3',
        'categories' => array('restorative','composites'),
        'short_description' => 'کامپوزیت میکروهیبرید رنگ A3',
        'description' => '<h3>معرفی</h3>
<p>کامپوزیت میکروهیبرید A3 مروابن برای ترمیم‌های قدامی و خلفی استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کامپوزیت میکروهیبرید','رنگ' => 'A3'),
    ),
    array(
        'name' => 'کامپوزیت فلو شید A1',
        'sku'  => 'morvabon-flowable-composite-a1',
        'categories' => array('restorative','composites'),
        'short_description' => 'کامپوزیت فلو شید A1، سرنگ ۱ میلی‌لیتری معادل ۲ گرم',
        'description' => '<h3>معرفی</h3>
<p>کامپوزیت فلو شید A1 مروابن، سرنگ ۱ میلی‌لیتری معادل ۲ گرم، برای ترمیم‌های کم‌عمق و لاینر استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>روان بودن و نفوذ آسان</li>
<li>مناسب برای نواحی دشوار</li>
<li>کاربرد به عنوان لاینر</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کامپوزیت فلو شید','رنگ' => 'A1','حجم' => '۱ میلی‌لیتر (۲ گرم)'),
    ),
    array(
        'name' => 'کامپوزیت فلو شید A2',
        'sku'  => 'morvabon-flowable-composite-a2',
        'categories' => array('restorative','composites'),
        'short_description' => 'کامپوزیت فلو شید A2، سرنگ ۱ میلی‌لیتری',
        'description' => '<h3>معرفی</h3>
<p>کامپوزیت فلو شید A2 مروابن، سرنگ ۱ میلی‌لیتری معادل ۲ گرم.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کامپوزیت فلو شید','رنگ' => 'A2','حجم' => '۱ میلی‌لیتر (۲ گرم)'),
    ),
    array(
        'name' => 'کامپوزیت فلو شید A3',
        'sku'  => 'morvabon-flowable-composite-a3',
        'categories' => array('restorative','composites'),
        'short_description' => 'کامپوزیت فلو شید A3، سرنگ ۱ میلی‌لیتری',
        'description' => '<h3>معرفی</h3>
<p>کامپوزیت فلو شید A3 مروابن، سرنگ ۱ میلی‌لیتری معادل ۲ گرم.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کامپوزیت فلو شید','رنگ' => 'A3','حجم' => '۱ میلی‌لیتر (۲ گرم)'),
    ),
    array(
        'name' => 'کامپوزیت فلو Pink',
        'sku'  => 'morvabon-flowable-composite-pink',
        'categories' => array('restorative','composites'),
        'short_description' => 'کامپوزیت فلو رنگ صورتی برای شبیه‌سازی لثه',
        'description' => '<h3>معرفی</h3>
<p>کامپوزیت فلو Pink مروابن برای شبیه‌سازی رنگ لثه و ترمیم‌های زیبایی استفاده می‌شود.</p>
<h3>کاربرد</h3>
<ul>
<li>ترمیم تحلیلات لثه</li>
<li>پوشش تحلیل‌های گردنی</li>
<li>زیبایی لبخند</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کامپوزیت فلو','رنگ' => 'صورتی (Pink)','کاربرد' => 'شبیه‌سازی لثه'),
    ),
    array(
        'name' => 'گلس نوری',
        'sku'  => 'morvabon-light-cure-glass-ionomer',
        'categories' => array('restorative','composites'),
        'short_description' => 'گلس نوری، سرنگ ۱ میلی‌لیتری معادل ۴ گرم',
        'description' => '<h3>معرفی</h3>
<p>گلس نوری مروابن، سرنگ ۱ میلی‌لیتری معادل ۴ گرم، برای ترمیم‌های پایه و لاینر استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>آزادسازی فلوراید</li>
<li>چسبندگی به عاج و مینا</li>
<li>مناسب برای ترمیم‌های پایه</li>
<li>بیوکمپتیبل</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'گلس آینومر نوری','حجم' => '۱ میلی‌لیتر (۴ گرم)','کاربرد' => 'ترمیم پایه و لاینر'),
    ),
    array(
        'name' => 'خمیر پانسمان موقت ۴۰ گرم',
        'sku'  => 'morvabon-temporary-dressing-paste',
        'categories' => array('restorative','cements-aux'),
        'short_description' => 'خمیر پانسمان موقت ۴۰ گرم',
        'description' => '<h3>معرفی</h3>
<p>خمیر پانسمان موقت مروابن برای پانسمان موقت دندان‌ها بین جلسات درمانی استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>محافظت از دندان بین جلسات</li>
<li>برداشت آسان</li>
<li>سازگار با بافت دهان</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'خمیر پانسمان موقت','وزن' => '۴۰ گرم'),
    ),
    array(
        'name' => 'سمان MTA (۱ گرمی)',
        'sku'  => 'morvabon-mta-cement-1g',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'سمان MTA ۱ گرمی برای پوشش پالپ و ترمیم‌های اندو',
        'description' => '<h3>معرفی</h3>
<p>سمان MTA مروابن برای پوشش پالپ، آپکس‌فیکیشن و ترمیم‌های اندو استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>زیست‌سازگاری عالی</li>
<li>تحریک تشکیل بافت سخت</li>
<li>آب‌بندی عالی</li>
<li>خاصیت ضدباکتریایی</li>
</ul>
<h3>کاربردها</h3>
<ul>
<li>پوشش مستقیم پالپ</li>
<li>آپکس‌فیکیشن</li>
<li>ترمیم پرفوراسیون</li>
<li>پر کردن رتروگرید</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'سمان MTA','وزن' => '۱ گرم','کاربرد' => 'پوشش پالپ، آپکس‌فیکیشن'),
    ),
    array(
        'name' => 'محلول انعقاد خون کلراید آلومینیوم ۲۵٪ (۱۸ میل)',
        'sku'  => 'morvabon-aluminum-chloride-25',
        'categories' => array('restorative','cements-aux'),
        'short_description' => 'محلول انعقاد خون کلراید آلومینیوم ۲۵٪، فاقد رنگ، ۱۸ میلی‌لیتر',
        'description' => '<h3>معرفی</h3>
<p>محلول انعقاد خون مروابن با کلراید آلومینیوم ۲۵٪، فاقد رنگ، ۱۸ میلی‌لیتر، برای بندآوری خون لثه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>بندآوری سریع خون</li>
<li>فاقد رنگ (بدون لکه)</li>
<li>مناسب برای قالب‌گیری دقیق</li>
<li>بدون تداخل با باندینگ</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'محلول انعقاد خون','غلظت' => '۲۵٪','حجم' => '۱۸ میلی‌لیتر','رنگ' => 'فاقد رنگ'),
    ),
    array(
        'name' => 'فیشور سیلانت (سفید، ۲ سرنگ)',
        'sku'  => 'morvabon-fissure-sealant',
        'categories' => array('restorative','cements-aux'),
        'short_description' => 'فیشور سیلانت سفید، ۲ سرنگ',
        'description' => '<h3>معرفی</h3>
<p>فیشور سیلانت مروابن، سفید، ۲ سرنگ، برای پیشگیری از پوسیدگی شیارهای دندان استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>پیشگیری مؤثر از پوسیدگی</li>
<li>نورپلیمریزه</li>
<li>رنگ سفید برای تشخیص آسان</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'فیشور سیلانت','رنگ' => 'سفید','تعداد' => '۲ سرنگ'),
    ),
    array(
        'name' => 'سیلر اندو رزینی (خمیر ۸ گرم + محلول رزین ۱۰ گرم)',
        'sku'  => 'morvabon-resin-endo-sealer',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'سیلر اندو رزینی، خمیر ۸ گرم + محلول رزین ۱۰ گرم',
        'description' => '<h3>معرفی</h3>
<p>سیلر اندو رزینی مروابن شامل خمیر ۸ گرم و محلول رزین ۱۰ گرم برای پر کردن کانال ریشه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>چسبندگی بالا به دیواره کانال</li>
<li>آب‌بندی عالی</li>
<li>ماندگاری طولانی</li>
<li>سازگار با گوتاپرکا</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'سیلر اندو رزینی','محتویات' => 'خمیر ۸ گرم + محلول ۱۰ گرم'),
    ),
    array(
        'name' => 'خمیر اندوسورام (سرنگ ۱ میلی‌لیتری)',
        'sku'  => 'morvabon-endosure-paste',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'خمیر اندوسورام، سرنگ ۱ میلی‌لیتری معادل ۲ گرم',
        'description' => '<h3>معرفی</h3>
<p>خمیر اندوسورام مروابن برای پر کردن کانال ریشه استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'خمیر اندو','حجم' => '۱ میلی‌لیتر (۲ گرم)'),
    ),
    array(
        'name' => 'کلسی مور/دایکال نوری (سرنگ ۱ میلی‌لیتری)',
        'sku'  => 'morvabon-calci-mor-light-cure',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'کلسی مور/دایکال نوری، سرنگ ۱ میلی‌لیتری معادل ۲ گرم',
        'description' => '<h3>معرفی</h3>
<p>کلسی مور/دایکال نوری مروابن برای پوشش پالپ و تحریک تشکیل عاج استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کلسیم هیدروکساید نوری','حجم' => '۱ میلی‌لیتر (۲ گرم)'),
    ),
    array(
        'name' => 'محلول فرموکرزول',
        'sku'  => 'morvabon-formocresol-solution',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'محلول فرموکرزول برای درمان پالپوتومی دندان‌های شیری',
        'description' => '<h3>معرفی</h3>
<p>محلول فرموکرزول مروابن برای درمان پالپوتومی دندان‌های شیری استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'محلول فرموکرزول','کاربرد' => 'پالپوتومی'),
    ),
    array(
        'name' => 'اوژنول خالص ۱۸ میل',
        'sku'  => 'morvabon-pure-eugenol-18ml',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'اوژنول خالص ۱۸ میلی‌لیتر',
        'description' => '<h3>معرفی</h3>
<p>اوژنول خالص مروابن، ۱۸ میلی‌لیتر، برای ترکیب با اکسید روی و ساخت سمان موقت استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'اوژنول','حجم' => '۱۸ میلی‌لیتر'),
    ),
    array(
        'name' => 'محلول کلروفرم ۳۰ میلی‌لیتر',
        'sku'  => 'morvabon-chloroform-solution-30ml',
        'categories' => array('endo','endo-materials'),
        'short_description' => 'محلول کلروفرم ۳۰ میلی‌لیتر برای حل گوتاپرکا',
        'description' => '<h3>معرفی</h3>
<p>محلول کلروفرم مروابن برای حل کردن گوتا پرکا در درمان‌های مجدد اندو استفاده می‌شود.</p>',
        'specs' => array('برند' => 'مروابن','نوع' => 'محلول کلروفرم','حجم' => '۳۰ میلی‌لیتر'),
    ),
    array(
        'name' => 'آرسی‌پرپ مروابن (۳ سرنگ ۵ میل)',
        'sku'  => 'morvabon-rc-prep',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'آرسی‌پرپ، ژل نرم‌کننده و ضدعفونی‌کننده کانال، ۳ سرنگ ۵ میل',
        'description' => '<h3>معرفی</h3>
<p>آرسی‌پرپ مروابن، ژل نرم‌کننده و ضدعفونی‌کننده کانال، ۳ سرنگ ۵ میل (جمعاً ۱۵ میل)، برای Facilitate کردن ورود فایل‌های اندو استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>کاهش اصطکاک فایل</li>
<li>ضدعفونی همزمان</li>
<li>تسهیل ورود فایل</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'ژل اندو','تعداد' => '۳ سرنگ ۵ میل','حجم کل' => '۱۵ میلی‌لیتر'),
    ),
    array(
        'name' => 'محلول هیپوکلریت سدیم ۵.۲۵٪ (۱ لیتر)',
        'sku'  => 'morvabon-sodium-hypochlorite-5-25',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'محلول هیپوکلریت سدیم ۵.۲۵٪، ۱ لیتر',
        'description' => '<h3>معرفی</h3>
<p>محلول هیپوکلریت سدیم ۵.۲۵٪ مروابن، ۱ لیتر، برای ضدعفونی و پاکسازی کانال ریشه استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>حل بافت نکروزه</li>
<li>ضدعفونی مؤثر</li>
<li>مورد استفاده استاندارد در اندو</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'محلول شستشو','غلظت' => '۵.۲۵٪','حجم' => '۱ لیتر'),
    ),
    array(
        'name' => 'محلول ادتا ۱۷٪ (۳۰ میل)',
        'sku'  => 'morvabon-edta-solution-17',
        'categories' => array('endo','canal-irrigation'),
        'short_description' => 'محلول ادتا ۱۷٪، ۳۰ میل',
        'description' => '<h3>معرفی</h3>
<p>محلول ادتا ۱۷٪ مروابن، ۳۰ میلی‌لیتر، برای ضدعفونی کانال و اینسترومنت استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>حذف اسمیر لایه</li>
<li>نرم‌کردن کانال</li>
<li>سازگار با هیپوکلریت</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'محلول شستشو','غلظت' => '۱۷٪','حجم' => '۳۰ میلی‌لیتر'),
    ),
    array(
        'name' => 'خمیر جرم‌گیری ۱۵۰ گرم',
        'sku'  => 'morvabon-prophylaxis-paste-150g',
        'categories' => array('restorative','prophylaxis-bleach'),
        'short_description' => 'خمیر جرم‌گیری ۱۵۰ گرم',
        'description' => '<h3>معرفی</h3>
<p>خمیر جرم‌گیری مروابن برای پاکسازی و براق‌کردن سطح دندان قبل از درمان‌های ترمیمی استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>پاکسازی مؤثر پلاک و لکه</li>
<li>براق‌کردن سطح دندان</li>
<li>آماده‌سازی برای ترمیم</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'خمیر جرم‌گیری','وزن' => '۱۵۰ گرم'),
    ),
    array(
        'name' => 'کیت بلیچینگ آفیس',
        'sku'  => 'morvabon-office-bleaching-kit',
        'categories' => array('restorative','prophylaxis-bleach'),
        'short_description' => 'کیت بلیچینگ آفیس مروابن',
        'description' => '<h3>معرفی</h3>
<p>کیت بلیچینگ آفیس مروابن برای سفیدکردن دندان‌ها در مطب استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>سفیدی سریع در یک جلسه</li>
<li>نتیجه قابل مشاهده فوری</li>
<li>کنترل کامل توسط دندانپزشک</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'کیت بلیچینگ','کاربرد' => 'بلیچینگ آفیس'),
    ),
    array(
        'name' => 'ژل بی‌حسی کایین (۴۰ میل، طعم توت‌فرنگی)',
        'sku'  => 'morvabon-marvacaine-anesthetic-gel',
        'categories' => array('endo','anesthesia'),
        'short_description' => 'ژل بی‌حسی کایین ۴۰ میل، طعم توت‌فرنگی، مدل ۲',
        'description' => '<h3>معرفی</h3>
<p>ژل بی‌حسی کایین مروابن، ۴۰ میلی‌لیتر، طعم توت‌فرنگی، مدل ۲، برای بی‌حسی سطحی مخاط استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>بی‌حسی سریع سطحی</li>
<li>طعم مطبوع توت‌فرنگی</li>
<li>مناسب برای بیماران و کودکان</li>
</ul>',
        'specs' => array('برند' => 'مروابن','نوع' => 'ژل بی‌حسی','حجم' => '۴۰ میلی‌لیتر','طعم' => 'توت‌فرنگی','مدل' => '۲'),
    ),
);

foreach ( $morvabon_products as $p ) {
    $p['brand'] = 'morvabon';
    $products[] = $p;
}

// ==================== متا (Meta) ====================
$meta_items = array(
    array( 'متا گوتا سایز ۱۵', 'meta-gutta-15', 'گوتا پرکا استاندارد سایز ۱۵', 'گوتا پرکا استاندارد ۲٪ برای پر کردن کانال ریشه' ),
    array( 'متا گوتا سایز ۲۰', 'meta-gutta-20', 'گوتا پرکا استاندارد سایز ۲۰', 'گوتا پرکا استاندارد ۲٪ برای پر کردن کانال ریشه' ),
    array( 'متا گوتا سایز ۲۵', 'meta-gutta-25', 'گوتا پرکا استاندارد سایز ۲۵', 'گوتا پرکا استاندارد ۲٪ برای پر کردن کانال ریشه' ),
    array( 'متا گوتا سایز ۳۰', 'meta-gutta-30', 'گوتا پرکا استاندارد سایز ۳۰', 'گوتا پرکا استاندارد ۲٪ برای پر کردن کانال ریشه' ),
    array( 'متا گوتا سایز ۳۵', 'meta-gutta-35', 'گوتا پرکا استاندارد سایز ۳۵', 'گوتا پرکا استاندارد ۲٪ برای پر کردن کانال ریشه' ),
    array( 'متا گوتا سایز ۴۰', 'meta-gutta-40', 'گوتا پرکا استاندارد سایز ۴۰', 'گوتا پرکا استاندارد ۲٪ برای پر کردن کانال ریشه' ),
    array( 'متا گوتا سایز ۴۵', 'meta-gutta-45', 'گوتا پرکا استاندارد سایز ۴۵', 'گوتا پرکا استاندارد ۲٪ برای پر کردن کانال ریشه' ),
    array( 'متا گوتا سایز ۴۰-۱۵', 'meta-gutta-15-40', 'گوتا پرکا استاندارد سایز ۴۰-۱۵', 'گوتا پرکا استاندارد ۲٪ سایزهای ترکیبی ۱۵ تا ۴۰' ),
    array( 'متا گوتا سایز ۸۰-۴۵', 'meta-gutta-45-80', 'گوتا پرکا استاندارد سایز ۸۰-۴۵', 'گوتا پرکا استاندارد ۲٪ سایزهای ترکیبی ۴۵ تا ۸۰' ),
    array( 'متا گوتا تقاربی ۴٪ سایز ۱۵', 'meta-gutta-taper-4-15', 'گوتا پرکا تقاربی ۴٪ سایز ۱۵', 'گوتا پرکا تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا گوتا تقاربی ۴٪ سایز ۲۰', 'meta-gutta-taper-4-20', 'گوتا پرکا تقاربی ۴٪ سایز ۲۰', 'گوتا پرکا تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا گوتا تقاربی ۴٪ سایز ۲۵', 'meta-gutta-taper-4-25', 'گوتا پرکا تقاربی ۴٪ سایز ۲۵', 'گوتا پرکا تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا گوتا تقاربی ۴٪ سایز ۳۰', 'meta-gutta-taper-4-30', 'گوتا پرکا تقاربی ۴٪ سایز ۳۰', 'گوتا پرکا تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا گوتا تقاربی ۴٪ سایز ۳۵', 'meta-gutta-taper-4-35', 'گوتا پرکا تقاربی ۴٪ سایز ۳۵', 'گوتا پرکا تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا گوتا تقاربی ۴٪ سایز ۴۰', 'meta-gutta-taper-4-40', 'گوتا پرکا تقاربی ۴٪ سایز ۴۰', 'گوتا پرکا تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا گوتا تقاربی ۴٪ سایز ۸۰-۴۰', 'meta-gutta-taper-4-40-80', 'گوتا پرکا تقاربی ۴٪ سایز ۸۰-۴۰', 'گوتا پرکا تقاربی ۴٪ سایزهای ترکیبی' ),
    array( 'متا گوتا تقاربی ۶٪ سایز ۲۰', 'meta-gutta-taper-6-20', 'گوتا پرکا تقاربی ۶٪ سایز ۲۰', 'گوتا پرکا تقاربی ۶٪ برای کانال‌های وسیع' ),
    array( 'متا گوتا تقاربی ۶٪ سایز ۲۵', 'meta-gutta-taper-6-25', 'گوتا پرکا تقاربی ۶٪ سایز ۲۵', 'گوتا پرکا تقاربی ۶٪ برای کانال‌های وسیع' ),
    array( 'متا گوتا تقاربی ۶٪ سایز ۳۰', 'meta-gutta-taper-6-30', 'گوتا پرکا تقاربی ۶٪ سایز ۳۰', 'گوتا پرکا تقاربی ۶٪ برای کانال‌های وسیع' ),
    array( 'متا گوتا تقاربی ۶٪ سایز ۳۵', 'meta-gutta-taper-6-35', 'گوتا پرکا تقاربی ۶٪ سایز ۳۵', 'گوتا پرکا تقاربی ۶٪ برای کانال‌های وسیع' ),
    array( 'متا گوتا جانبی سایز MF', 'meta-gutta-mf', 'گوتا پرکا جانبی سایز MF', 'گوتا پرکا جانبی برای پر کردن کانال‌های جانبی' ),
    array( 'متا گوتا پروتیپر سایز F1', 'meta-gutta-protaper-f1', 'گوتا پرکا پروتیپر سایز F1', 'گوتا پرکا پروتیپر برای سیستم ProTaper' ),
    array( 'متا گوتا پروتیپر سایز F2', 'meta-gutta-protaper-f2', 'گوتا پرکا پروتیپر سایز F2', 'گوتا پرکا پروتیپر برای سیستم ProTaper' ),
    array( 'متا گوتا پروتیپر سایز F3', 'meta-gutta-protaper-f3', 'گوتا پرکا پروتیپر سایز F3', 'گوتا پرکا پروتیپر برای سیستم ProTaper' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۱۵', 'meta-paper-point-2-15', 'کن کاغذی استاندارد ۲٪ سایز ۱۵', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۲۰', 'meta-paper-point-2-20', 'کن کاغذی استاندارد ۲٪ سایز ۲۰', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۲۵', 'meta-paper-point-2-25', 'کن کاغذی استاندارد ۲٪ سایز ۲۵', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۳۰', 'meta-paper-point-2-30', 'کن کاغذی استاندارد ۲٪ سایز ۳۰', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۳۵', 'meta-paper-point-2-35', 'کن کاغذی استاندارد ۲٪ سایز ۳۵', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۴۰', 'meta-paper-point-2-40', 'کن کاغذی استاندارد ۲٪ سایز ۴۰', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۴۵', 'meta-paper-point-2-45', 'کن کاغذی استاندارد ۲٪ سایز ۴۵', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۵۰', 'meta-paper-point-2-50', 'کن کاغذی استاندارد ۲٪ سایز ۵۰', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۵۵', 'meta-paper-point-2-55', 'کن کاغذی استاندارد ۲٪ سایز ۵۵', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۶۰', 'meta-paper-point-2-60', 'کن کاغذی استاندارد ۲٪ سایز ۶۰', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۷۰', 'meta-paper-point-2-70', 'کن کاغذی استاندارد ۲٪ سایز ۷۰', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۸۰', 'meta-paper-point-2-80', 'کن کاغذی استاندارد ۲٪ سایز ۸۰', 'کن کاغذی استاندارد ۲٪ برای خشک کردن کانال' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۴۰-۱۵', 'meta-paper-point-2-15-40', 'کن کاغذی استاندارد ۲٪ سایز ۴۰-۱۵', 'کن کاغذی استاندارد ۲٪ سایزهای ترکیبی' ),
    array( 'متا کن کاغذی استاندارد ۲٪ سایز ۸۰-۴۵', 'meta-paper-point-2-45-80', 'کن کاغذی استاندارد ۲٪ سایز ۸۰-۴۵', 'کن کاغذی استاندارد ۲٪ سایزهای ترکیبی' ),
    array( 'متا کن کاغذی تقاربی ۴٪ سایز ۲۵', 'meta-paper-point-taper-4-25', 'کن کاغذی تقاربی ۴٪ سایز ۲۵', 'کن کاغذی تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا کن کاغذی تقاربی ۴٪ سایز ۳۰', 'meta-paper-point-taper-4-30', 'کن کاغذی تقاربی ۴٪ سایز ۳۰', 'کن کاغذی تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا کن کاغذی تقاربی ۴٪ سایز ۳۵', 'meta-paper-point-taper-4-35', 'کن کاغذی تقاربی ۴٪ سایز ۳۵', 'کن کاغذی تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا کن کاغذی تقاربی ۴٪ سایز ۴۰', 'meta-paper-point-taper-4-40', 'کن کاغذی تقاربی ۴٪ سایز ۴۰', 'کن کاغذی تقاربی ۴٪ برای سیستم‌های چرخشی' ),
    array( 'متا کن کاغذی تقاربی ۶٪ سایز ۲۵', 'meta-paper-point-taper-6-25', 'کن کاغذی تقاربی ۶٪ سایز ۲۵', 'کن کاغذی تقاربی ۶٪ برای کانال‌های وسیع' ),
    array( 'متا کن کاغذی تقاربی ۶٪ سایز ۳۰', 'meta-paper-point-taper-6-30', 'کن کاغذی تقاربی ۶٪ سایز ۳۰', 'کن کاغذی تقاربی ۶٪ برای کانال‌های وسیع' ),
    array( 'متا کن کاغذی تقاربی ۶٪ سایز ۳۵', 'meta-paper-point-taper-6-35', 'کن کاغذی تقاربی ۶٪ سایز ۳۵', 'کن کاغذی تقاربی ۶٪ برای کانال‌های وسیع' ),
    array( 'متا کن کاغذی تقاربی ۶٪ سایز ۴۰', 'meta-paper-point-taper-6-40', 'کن کاغذی تقاربی ۶٪ سایز ۴۰', 'کن کاغذی تقاربی ۶٪ برای کانال‌های وسیع' ),
    array( 'متا کن کاغذی تقاربی ۶٪ سایز ۴۰-۱۵', 'meta-paper-point-taper-6-15-40', 'کن کاغذی تقاربی ۶٪ سایز ۴۰-۱۵', 'کن کاغذی تقاربی ۶٪ سایزهای ترکیبی' ),
    array( 'متا کن کاغذی تقاربی ۶٪ سایز ۸۰-۴۵', 'meta-paper-point-taper-6-45-80', 'کن کاغذی تقاربی ۶٪ سایز ۸۰-۴۵', 'کن کاغذی تقاربی ۶٪ سایزهای ترکیبی' ),
    array( 'متا کن کاغذی پروتیپر سایز F1', 'meta-paper-point-protaper-f1', 'کن کاغذی پروتیپر سایز F1', 'کن کاغذی پروتیپر برای سیستم ProTaper' ),
    array( 'متا کن کاغذی پروتیپر سایز F2', 'meta-paper-point-protaper-f2', 'کن کاغذی پروتیپر سایز F2', 'کن کاغذی پروتیپر برای سیستم ProTaper' ),
    array( 'متا کن کاغذی پروتیپر سایز F3', 'meta-paper-point-protaper-f3', 'کن کاغذی پروتیپر سایز F3', 'کن کاغذی پروتیپر برای سیستم ProTaper' ),
);

foreach ( $meta_items as $p ) {
    $type = strpos( $p[0], 'گوتا' ) !== false ? 'گوتا پرکا' : 'کن کاغذی';
    $size = preg_replace('/[^۰-۹0-9\-٪]/u', '', $p[0]);
    $products[] = array(
        'name' => $p[0],
        'sku' => $p[1],
        'brand' => 'meta-pars',
        'categories' => array('endo','gutta-percha'),
        'short_description' => $p[2],
        'description' => '<h3>معرفی</h3>
<p>' . $p[3] . ' از برند متا (آزاد تجارت پارس پرنیان).</p>
<h3>مزایا</h3>
<ul>
<li>کیفیت بالای ساخت</li>
<li>انطباق دقیق با استانداردهای ISO</li>
<li>سازگار با سیستم‌های چرخشی رایج</li>
<li>قیمت مناسب</li>
</ul>
<h3>نحوه استفاده</h3>
<ol>
<li>سایز مناسب را انتخاب کنید.</li>
<li>' . ($type === 'گوتا پرکا' ? 'گوتا را با سیلر مناسب داخل کانال قرار دهید.' : 'کن را داخل کانال قرار دهید.') . '</li>
<li>' . ($type === 'گوتا پرکا' ? 'با تکنیک استاندارد پر کنید.' : 'پس از جذب رطوبت، کن را خارج کنید.') . '</li>
</ol>',
        'specs' => array('برند' => 'متا (آزاد تجارت پارس پرنیان)','نوع' => $type,'سایز' => $size,'استاندارد' => 'ISO'),
    );
}

$products[] = array(
    'name' => 'متا سیلر بیس رزینی PLUS',
    'sku' => 'meta-resin-based-sealer-plus',
    'brand' => 'meta-pars',
    'categories' => array('endo','endo-materials'),
    'short_description' => 'سیلر بیس رزینی PLUS برای پر کردن کانال ریشه',
    'description' => '<h3>معرفی</h3>
<p>سیلر بیس رزینی PLUS متا برای پر کردن کانال ریشه با خاصیت چسبندگی بالا استفاده می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>چسبندگی عالی به دیواره کانال</li>
<li>آب‌بندی مطمئن</li>
<li>ماندگاری طولانی</li>
<li>سازگار با گوتاپرکا</li>
</ul>
<h3>نحوه استفاده</h3>
<p>با کن یا فایل، سیلر را داخل کانال قرار دهید و سپس با گوتاپرکا پر کنید.</p>',
    'specs' => array('برند' => 'متا (آزاد تجارت پارس پرنیان)','نوع' => 'سیلر بیس رزینی','مدل' => 'PLUS'),
);

// ==================== مروارید طب ====================
$morvarid_items = array(
    array( 'آینه یکبار مصرف', 'morvarid-disposable-mirror', 'آینه یکبار مصرف دندانپزشکی', 'آینه یکبار مصرف دندانپزشکی برای معاینه دهان و دندان. جنس مقاوم و بدون خطر انتقال عفونت.' ),
    array( 'پیشبند ۴۰۰ گرمی', 'morvarid-bib-400', 'پیشبند ۴۰۰ گرمی', 'پیشبند ۴۰۰ گرمی مروارید طب برای محافظت از لباس بیمار. جنس نرم و جاذب.' ),
    array( 'پیشبند ۵۰۰ گرمی', 'morvarid-bib-500', 'پیشبند ۵۰۰ گرمی', 'پیشبند ۵۰۰ گرمی مروارید طب برای محافظت از لباس بیمار. جنس ضخیم و مقاوم.' ),
    array( 'روکش پوآر', 'morvarid-powar-cover', 'روکش پوآر', 'روکش پوآر مروارید طب برای پوشش پوآر و جلوگیری از آلودگی متقابل.' ),
    array( 'روکش دسته چراغ', 'morvarid-light-handle-cover', 'روکش دسته چراغ', 'روکش دسته چراغ مروارید طب برای پوشش دسته چراغ یونیت.' ),
    array( 'روکش زیرسری نایلونی', 'morvarid-nylon-headrest-cover', 'روکش زیرسری نایلونی', 'روکش زیرسری نایلونی مروارید طب برای پوشش زیرسری یونیت.' ),
    array( 'روکش ساکشن', 'morvarid-suction-cover', 'روکش ساکشن', 'روکش ساکشن مروارید طب برای پوشش سرساکشن.' ),
    array( 'روکش سنسور گرافی', 'morvarid-sensor-cover', 'روکش سنسور گرافی', 'روکش سنسور گرافی مروارید طب برای پوشش سنسور رادیوگرافی.' ),
    array( 'روکش شیلنگ ساکشن', 'morvarid-suction-hose-cover', 'روکش شیلنگ ساکشن', 'روکش شیلنگ ساکشن مروارید طب برای پوشش شیلنگ ساکشن.' ),
    array( 'روکش یونیت الیافی بنددار', 'morvarid-fiber-unit-cover', 'روکش یونیت الیافی بنددار', 'روکش یونیت الیافی بنددار مروارید طب برای پوشش کامل یونیت.' ),
    array( 'روکش یونیت نایلونی', 'morvarid-nylon-unit-cover', 'روکش یونیت نایلونی', 'روکش یونیت نایلونی مروارید طب برای پوشش یونیت.' ),
    array( 'رول کف سینی ۲۰×۲۰', 'morvarid-tray-cover-20x20', 'رول کف سینی ۲۰×۲۰', 'رول کف سینی ۲۰×۲۰ مروارید طب برای پوشش سینی ابزار.' ),
    array( 'سرساکشن', 'morvarid-suction-tip', 'سرساکشن', 'سرساکشن مروارید طب برای مکش مایعات و ذرات در حین درمان.' ),
    array( 'سینی یکبار مصرف ۸۰۰ گرمی سفید', 'morvarid-disposable-tray-800', 'سینی یکبار مصرف ۸۰۰ گرمی سفید', 'سینی یکبار مصرف ۸۰۰ گرمی سفید مروارید طب برای قرار دادن ابزار.' ),
    array( 'سینی یکبار مصرف ۶۰۰ گرمی سفید', 'morvarid-disposable-tray-600', 'سینی یکبار مصرف ۶۰۰ گرمی سفید', 'سینی یکبار مصرف ۶۰۰ گرمی سفید مروارید طب برای قرار دادن ابزار.' ),
    array( 'شان پرفوره جراحی ۶۰×۸۰', 'morvarid-surgical-drape-60x80', 'شان پرفوره جراحی ۶۰×۸۰', 'شان پرفوره جراحی ۶۰×۸۰ مروارید طب برای پوشش بیمار در حین جراحی.' ),
    array( 'شان ساده اتوکلاو ۶۰×۸۰', 'morvarid-autoclave-drape-60x80', 'شان ساده اتوکلاو ۶۰×۸۰', 'شان ساده اتوکلاو ۶۰×۸۰ مروارید طب برای پوشش بیمار.' ),
    array( 'گان پزشک مچی‌دار قد ۱۱۰ گرماژ ۴۰', 'morvarid-physician-gown-110', 'گان پزشک مچی‌دار قد ۱۱۰ گرماژ ۴۰', 'گان پزشک مچی‌دار مروارید طب، قد ۱۱۰، گرماژ ۴۰، برای محافظت از پزشک.' ),
    array( 'مایع ضدعفونی‌کننده', 'morvarid-disinfectant-liquid', 'مایع ضدعفونی‌کننده', 'مایع ضدعفونی‌کننده مروارید طب برای ضدعفونی سطوح و ابزار.' ),
);

foreach ( $morvarid_items as $p ) {
    $products[] = array(
        'name' => $p[0],
        'sku' => $p[1],
        'brand' => 'morvarid-teb',
        'categories' => array('consumables'),
        'short_description' => $p[2],
        'description' => '<h3>معرفی</h3>
<p>' . $p[3] . '</p>
<h3>مزایا</h3>
<ul>
<li>کیفیت بالای ساخت</li>
<li>مناسب برای استفاده روزانه در مطب</li>
<li>قیمت اقتصادی</li>
</ul>',
        'specs' => array('برند' => 'مروارید طب اصفهان','نوع' => $p[2],'کاربرد' => 'لوازم مصرفی کلینیک'),
    );
}

// ==================== بدون برند ====================
$nobrand_items = array(
    array( 'سرنگ 2cc دو تکه G23 داخلی', 'syringe-2cc-2piece-g23', 'سرنگ ۲ سی‌سی دو تکه با سوزن G23 داخلی', 'syringes', array('حجم'=>'۲ سی‌سی','نوع'=>'دو تکه','گیج'=>'G23','اتصال'=>'داخلی') ),
    array( 'سرنگ 3CC سه تکه G23 اسلیپ داخلی', 'syringe-3cc-3piece-g23-slip', 'سرنگ ۳ سی‌سی سه تکه G23 اسلیپ داخلی', 'syringes', array('حجم'=>'۳ سی‌سی','نوع'=>'سه تکه','گیج'=>'G23','اتصال'=>'اسلیپ داخلی') ),
    array( 'سرنگ 3CC سه تکه G23 لوئرلاک داخلی', 'syringe-3cc-3piece-g23-luerlock', 'سرنگ ۳ سی‌سی سه تکه G23 لوئرلاک داخلی', 'syringes', array('حجم'=>'۳ سی‌سی','نوع'=>'سه تکه','گیج'=>'G23','اتصال'=>'لوئرلاک داخلی') ),
    array( 'سرنگ 5CC دو تکه G22 داخلی', 'syringe-5cc-2piece-g22', 'سرنگ ۵ سی‌سی دو تکه G22 داخلی', 'syringes', array('حجم'=>'۵ سی‌سی','نوع'=>'دو تکه','گیج'=>'G22','اتصال'=>'داخلی') ),
    array( 'سرنگ 10CC دو تکه G21 داخلی', 'syringe-10cc-2piece-g21', 'سرنگ ۱۰ سی‌سی دو تکه G21 داخلی', 'syringes', array('حجم'=>'۱۰ سی‌سی','نوع'=>'دو تکه','گیج'=>'G21','اتصال'=>'داخلی') ),
    array( 'سرنگ 10CC سه تکه G21 داخلی', 'syringe-10cc-3piece-g21', 'سرنگ ۱۰ سی‌سی سه تکه G21 داخلی', 'syringes', array('حجم'=>'۱۰ سی‌سی','نوع'=>'سه تکه','گیج'=>'G21','اتصال'=>'داخلی') ),
    array( 'سرنگ 10CC سه تکه G21 لوئرلاک مخصوص شستشو', 'syringe-10cc-3piece-g21-luerlock', 'سرنگ ۱۰ سی‌سی سه تکه G21 لوئرلاک مخصوص شستشو', 'syringes', array('حجم'=>'۱۰ سی‌سی','نوع'=>'سه تکه','گیج'=>'G21','اتصال'=>'لوئرلاک','کاربرد'=>'شستشو') ),
    array( 'سرنگ 20CC سه تکه لوئرلاک G21 داخلی', 'syringe-20cc-3piece-g21-luerlock', 'سرنگ ۲۰ سی‌سی سه تکه لوئرلاک G21 داخلی', 'syringes', array('حجم'=>'۲۰ سی‌سی','نوع'=>'سه تکه','گیج'=>'G21','اتصال'=>'لوئرلاک') ),
    array( 'سرنگ 20CC سه تکه G21 داخلی', 'syringe-20cc-3piece-g21', 'سرنگ ۲۰ سی‌سی سه تکه G21 داخلی', 'syringes', array('حجم'=>'۲۰ سی‌سی','نوع'=>'سه تکه','گیج'=>'G21','اتصال'=>'داخلی') ),
    array( 'سرنگ 50CC سه تکه G21 داخلی', 'syringe-50cc-3piece-g21', 'سرنگ ۵۰ سی‌سی سه تکه G21 داخلی', 'syringes', array('حجم'=>'۵۰ سی‌سی','نوع'=>'سه تکه','گیج'=>'G21','اتصال'=>'داخلی') ),
    array( 'سرنگ 50CC سه تکه لوئرلاک G21 داخلی', 'syringe-50cc-3piece-g21-luerlock', 'سرنگ ۵۰ سی‌سی سه تکه لوئرلاک G21 داخلی', 'syringes', array('حجم'=>'۵۰ سی‌سی','نوع'=>'سه تکه','گیج'=>'G21','اتصال'=>'لوئرلاک') ),
    array( 'سرنگ 5CC سه تکه G21 لوئرلاک مخصوص شستشو', 'syringe-5cc-3piece-g21-luerlock', 'سرنگ ۵ سی‌سی سه تکه G21 لوئرلاک مخصوص شستشو', 'syringes', array('حجم'=>'۵ سی‌سی','نوع'=>'سه تکه','گیج'=>'G21','اتصال'=>'لوئرلاک','کاربرد'=>'شستشو') ),
    array( 'سرنگ 5CC سه تکه G22 لوئرلاک داخلی', 'syringe-5cc-3piece-g22-luerlock', 'سرنگ ۵ سی‌سی سه تکه G22 لوئرلاک داخلی', 'syringes', array('حجم'=>'۵ سی‌سی','نوع'=>'سه تکه','گیج'=>'G22','اتصال'=>'لوئرلاک') ),
    array( 'سرنگ 5CC سه تکه G22 لوئر اسلیپ داخلی', 'syringe-5cc-3piece-g22-slip', 'سرنگ ۵ سی‌سی سه تکه G22 لوئر اسلیپ داخلی', 'syringes', array('حجم'=>'۵ سی‌سی','نوع'=>'سه تکه','گیج'=>'G22','اتصال'=>'لوئر اسلیپ') ),
    array( 'سرنگ انسولین', 'insulin-syringe', 'سرنگ انسولین', 'syringes', array('نوع'=>'انسولین') ),
    array( 'سرنگ انسولین G29-G30', 'insulin-syringe-g29-g30', 'سرنگ انسولین G29-G30', 'syringes', array('نوع'=>'انسولین','گیج'=>'G29-G30') ),
    array( 'سرنگ داخلی 5CC G23 AD', 'syringe-5cc-g23-ad', 'سرنگ داخلی ۵ سی‌سی G23 AD', 'syringes', array('حجم'=>'۵ سی‌سی','گیج'=>'G23','نوع'=>'AD') ),
    array( 'سرنگ دندانپزشکی سه قطعه‌ای', 'dental-syringe-3piece', 'سرنگ دندانپزشکی سه قطعه‌ای', 'syringes', array('نوع'=>'دندانپزشکی','تعداد قطعات'=>'سه قطعه') ),
    array( 'سرنگ گاواژ سه تکه ۵۰/۶۰', 'gavage-syringe-50-60', 'سرنگ گاواژ سه تکه ۵۰/۶۰', 'syringes', array('حجم'=>'۵۰/۶۰','نوع'=>'گاواژ') ),
    array( 'سوند نلاتون ۱۲ ورید', 'nelaton-catheter-12', 'سوند نلاتون ۱۲ ورید', 'catheters', array('نوع'=>'نلاتون','سایز'=>'۱۲') ),
    array( 'سوند فولی ۲ راه', 'foley-catheter-2way', 'سوند فولی ۲ راه', 'catheters', array('نوع'=>'فولی','راه'=>'۲ راه') ),
    array( 'سوند فولی ۳ راه', 'foley-catheter-3way', 'سوند فولی ۳ راه', 'catheters', array('نوع'=>'فولی','راه'=>'۳ راه') ),
    array( 'سوند معده سایز ۱۴', 'stomach-tube-14', 'سوند معده سایز ۱۴', 'catheters', array('نوع'=>'معده','سایز'=>'۱۴') ),
    array( 'سوند معده سایز ۱۶', 'stomach-tube-16', 'سوند معده سایز ۱۶', 'catheters', array('نوع'=>'معده','سایز'=>'۱۶') ),
    array( 'سوند نلاتون', 'nelaton-catheter', 'سوند نلاتون', 'catheters', array('نوع'=>'نلاتون') ),
    array( 'انسولین جعبه‌ای G27', 'insulin-syringe-box-g27', 'سرنگ انسولین جعبه‌ای G27', 'syringes', array('نوع'=>'انسولین','گیج'=>'G27') ),
    array( 'دهانی آندوسکوپی', 'endoscopic-mouthpiece', 'دهانی آندوسکوپی', 'other-injection', array('نوع'=>'دهانی آندوسکوپی') ),
    array( 'ست سرم با سرسوزن', 'iv-set-with-needle', 'ست سرم با سرسوزن', 'other-injection', array('نوع'=>'ست سرم') ),
    array( 'سرسوزن بلند', 'needle-long', 'سرسوزن بلند', 'needles', array('نوع'=>'بلند') ),
    array( 'سرسوزن کوتاه', 'needle-short', 'سرسوزن کوتاه', 'needles', array('نوع'=>'کوتاه') ),
    array( 'لوله ساکشن سایز ۲۵', 'suction-tube-25', 'لوله ساکشن سایز ۲۵', 'other-injection', array('نوع'=>'لوله ساکشن','سایز'=>'۲۵') ),
    array( 'لوله ساکشن سایز ۳۰', 'suction-tube-30', 'لوله ساکشن سایز ۳۰', 'other-injection', array('نوع'=>'لوله ساکشن','سایز'=>'۳۰') ),
    array( 'لوله ساکشن سایز ۳۵', 'suction-tube-35', 'لوله ساکشن سایز ۳۵', 'other-injection', array('نوع'=>'لوله ساکشن','سایز'=>'۳۵') ),
    array( 'ماسک یک‌بار مصرف پزشکی سه لایه ۵۰ عددی', 'medical-mask-3layer-50', 'ماسک یک‌بار مصرف سه لایه ۵۰ عددی', 'other-injection', array('نوع'=>'ماسک','تعداد لایه'=>'۳','تعداد'=>'۵۰ عددی') ),
    array( 'میکروست کامل - ۱۰۰ میلی‌متر', 'microset-complete-100', 'میکروست کامل ۱۰۰ میلی‌متر', 'other-injection', array('نوع'=>'میکروست','حجم'=>'۱۰۰ میلی‌متر') ),
    array( 'هپارین لاک', 'heparin-lock', 'هپارین لاک', 'other-injection', array('نوع'=>'هپارین لاک') ),
);

foreach ( $nobrand_items as $p ) {
    $specs = array_merge( array( 'برند' => 'بدون برند' ), $p[4] );
    $products[] = array(
        'name' => $p[0],
        'sku' => $p[1],
        'brand' => 'no-brand',
        'categories' => array('syringe-suction', $p[3]),
        'short_description' => $p[2],
        'description' => '<h3>معرفی</h3>
<p>' . $p[2] . ' برای مصارف دندانپزشکی، تزریق و درمانی. این محصول با کیفیت مناسب و قیمت اقتصادی عرضه می‌شود.</p>
<h3>مزایا</h3>
<ul>
<li>کیفیت مناسب</li>
<li>قیمت اقتصادی</li>
<li>مناسب برای استفاده روزانه</li>
<li>بسته‌بندی بهداشتی</li>
</ul>
<h3>کاربرد</h3>
<p>مناسب برای کلینیک‌های دندانپزشکی، بیمارستان‌ها و مراکز درمانی.</p>',
        'specs' => $specs,
    );
}

// ============================================================
// ۴. اجرا
// ============================================================

echo "<h2>در حال ساخت محصولات...</h2><hr>";

$count = 0;
$errors = 0;

foreach ( $products as $product ) {
    $result = mm_insert_product( $product );
    if ( $result ) $count++;
    else $errors++;
}

// ============================================================
// ۵. پاکسازی کش
// ============================================================

delete_transient( 'wc_term_counts' );
delete_transient( 'wc_category_lists_cache' );
delete_transient( 'wc_attribute_taxonomies' );

// بازسازی شمارنده ترم‌ها
global $wpdb;
$terms = $wpdb->get_results( "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ('product_cat', 'product_brand')" );
foreach ( $terms as $term ) {
    $count_products = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
         WHERE tr.term_taxonomy_id = %d AND p.post_type = 'product' AND p.post_status = 'publish'",
        $term->term_taxonomy_id
    ) );
    $wpdb->update(
        $wpdb->term_taxonomy,
        array( 'count' => $count_products ),
        array( 'term_taxonomy_id' => $term->term_taxonomy_id )
    );
}

wp_cache_flush();

// ============================================================
// ۶. گزارش نهایی
// ============================================================

echo "<hr>";
echo "<h2>عملیات کامل شد!</h2>";
echo "<ul>";
echo "<li>محصولات ساخته/به‌روزرسانی شده: <strong>{$count}</strong></li>";
echo "<li>خطاها: <strong>{$errors}</strong></li>";
echo "<li>برندها (product_brand): <strong>" . count( $brand_ids ) . "</strong></li>";
echo "<li>دسته‌بندی‌ها (product_cat): <strong>" . count( $cat_ids ) . "</strong></li>";
echo "</ul>";
echo "<p><strong>هشدار امنیتی: همین حالا این فایل را از سرور حذف کنید.</strong></p>";
echo '<p><a href="' . admin_url( 'edit.php?post_type=product' ) . '">مشاهده محصولات</a> | ';
echo '<a href="' . admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ) . '">مشاهده دسته‌بندی‌ها</a> | ';
echo '<a href="' . admin_url( 'edit-tags.php?taxonomy=product_brand&post_type=product' ) . '">مشاهده برندها</a></p>';