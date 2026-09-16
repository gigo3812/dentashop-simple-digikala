<?php
/**
 * Offices Module
 * 
 * سیستم دفاتر و مراکز
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;


// ============================================
// 1. ثبت CPT
// ============================================

add_action('init', 'gpds_register_office_cpt');

function gpds_register_office_cpt() {
    register_post_type('office', [
        'labels' => [
            'name'               => 'دفاتر',
            'singular_name'      => 'دفتر',
            'menu_name'          => 'دفاتر',
            'add_new'            => 'افزودن دفتر',
            'add_new_item'       => 'افزودن دفتر جدید',
            'edit_item'          => 'ویرایش دفتر',
            'new_item'           => 'دفتر جدید',
            'view_item'          => 'مشاهده دفتر',
            'search_items'       => 'جستجوی دفتر',
            'not_found'          => 'دفتری یافت نشد',
            'all_items'          => 'همه دفاتر',
        ],
        'public'          => true,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'show_in_rest'    => true,
        'has_archive'     => 'offices',
        'menu_icon'       => 'dashicons-location',
        'menu_position'   => 25,
        'supports'        => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
        'rewrite'         => [
            'slug'       => 'office',
            'with_front' => false,
        ],
    ]);
}


// ============================================
// 2. متاباکس‌ها
// ============================================

add_action('add_meta_boxes', 'gpds_office_meta_boxes');

function gpds_office_meta_boxes() {
    add_meta_box('gpds_office_manager',  'مسئول دفتر',    'gpds_office_manager_box',  'office', 'normal', 'high');
    add_meta_box('gpds_office_contact',  'راه‌های تماس',   'gpds_office_contact_box',  'office', 'normal', 'high');
    add_meta_box('gpds_office_social',   'شبکه‌های اجتماعی', 'gpds_office_social_box',   'office', 'normal', 'default');
    add_meta_box('gpds_office_address',  'آدرس و موقعیت',  'gpds_office_address_box',  'office', 'normal', 'high');
    add_meta_box('gpds_office_hours',    'ساعت کاری',      'gpds_office_hours_box',    'office', 'normal', 'default');
}


/**
 * متاباکس: مسئول
 */
function gpds_office_manager_box($post) {
    wp_nonce_field('gpds_office_meta', 'gpds_office_nonce');

    $fields = [
        'manager_name'  => ['label' => 'نام مسئول',    'placeholder' => 'مثلاً: علی محمدی'],
        'manager_role'  => ['label' => 'سمت',          'placeholder' => 'مثلاً: مدیر فروش'],
        'manager_mobile'=> ['label' => 'موبایل مسئول', 'placeholder' => '09121234567'],
    ];
    ?>
    <table class="form-table">
        <?php foreach ($fields as $key => $field) : 
            $value = get_post_meta($post->ID, '_office_' . $key, true);
        ?>
            <tr>
                <th><label for="office_<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
                <td>
                    <input type="text" 
                           id="office_<?php echo esc_attr($key); ?>" 
                           name="office_<?php echo esc_attr($key); ?>" 
                           value="<?php echo esc_attr($value); ?>" 
                           class="regular-text" 
                           placeholder="<?php echo esc_attr($field['placeholder']); ?>">
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}


/**
 * متاباکس: تماس
 */
function gpds_office_contact_box($post) {
    $fields = [
        'phone'    => ['label' => 'تلفن ثابت', 'placeholder' => '056-1234567', 'hint' => 'چند خط رو با کاما جدا کن'],
        'mobile'   => ['label' => 'موبایل',    'placeholder' => '09121234567'],
        'whatsapp' => ['label' => 'واتساپ',    'placeholder' => '989121234567', 'hint' => 'بدون + و صفر'],
        'email'    => ['label' => 'ایمیل',      'placeholder' => 'info@example.com', 'type' => 'email'],
        'website'  => ['label' => 'وب‌سایت',    'placeholder' => 'https://example.com', 'type' => 'url'],
    ];
    ?>
    <table class="form-table">
        <?php foreach ($fields as $key => $field) : 
            $value = get_post_meta($post->ID, '_office_' . $key, true);
            $type  = $field['type'] ?? 'text';
        ?>
            <tr>
                <th><label for="office_<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
                <td>
                    <input type="<?php echo esc_attr($type); ?>" 
                           id="office_<?php echo esc_attr($key); ?>" 
                           name="office_<?php echo esc_attr($key); ?>" 
                           value="<?php echo esc_attr($value); ?>" 
                           class="regular-text" 
                           placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                           dir="ltr">
                    <?php if (!empty($field['hint'])) : ?>
                        <p class="description"><?php echo esc_html($field['hint']); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}


/**
 * متاباکس: شبکه‌های اجتماعی
 */
function gpds_office_social_box($post) {
    $fields = [
        'instagram' => ['label' => 'اینستاگرام', 'placeholder' => 'username (بدون @)'],
        'telegram'  => ['label' => 'تلگرام',      'placeholder' => 'username (بدون @)'],
    ];
    ?>
    <table class="form-table">
        <?php foreach ($fields as $key => $field) : 
            $value = get_post_meta($post->ID, '_office_' . $key, true);
        ?>
            <tr>
                <th><label for="office_<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
                <td>
                    <input type="text" 
                           id="office_<?php echo esc_attr($key); ?>" 
                           name="office_<?php echo esc_attr($key); ?>" 
                           value="<?php echo esc_attr($value); ?>" 
                           class="regular-text" 
                           placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                           dir="ltr">
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}


/**
 * متاباکس: آدرس و موقعیت
 */
function gpds_office_address_box($post) {
    $fields = [
        'province' => ['label' => 'استان',       'placeholder' => 'خراسان جنوبی'],
        'city'     => ['label' => 'شهر',         'placeholder' => 'بیرجند'],
        'address'  => ['label' => 'آدرس کامل',   'placeholder' => 'آدرس دقیق', 'type' => 'textarea'],
        'postal'   => ['label' => 'کد پستی',     'placeholder' => '9714713976'],
        'lat'      => ['label' => 'عرض جغرافیایی', 'placeholder' => '32.8663', 'dir' => 'ltr'],
        'lng'      => ['label' => 'طول جغرافیایی', 'placeholder' => '59.2211', 'dir' => 'ltr'],
    ];
    ?>
    <table class="form-table">
        <?php foreach ($fields as $key => $field) : 
            $value = get_post_meta($post->ID, '_office_' . $key, true);
            $type  = $field['type'] ?? 'text';
            $dir   = $field['dir'] ?? 'rtl';
        ?>
            <tr>
                <th><label for="office_<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
                <td>
                    <?php if ($type === 'textarea') : ?>
                        <textarea id="office_<?php echo esc_attr($key); ?>" 
                                  name="office_<?php echo esc_attr($key); ?>" 
                                  rows="3" 
                                  class="large-text" 
                                  placeholder="<?php echo esc_attr($field['placeholder']); ?>"><?php echo esc_textarea($value); ?></textarea>
                    <?php else : ?>
                        <input type="text" 
                               id="office_<?php echo esc_attr($key); ?>" 
                               name="office_<?php echo esc_attr($key); ?>" 
                               value="<?php echo esc_attr($value); ?>" 
                               class="regular-text" 
                               placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                               dir="<?php echo esc_attr($dir); ?>">
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p class="description" style="background:#f0f6fc;padding:10px;border-radius:6px;">
        💡 مختصات رو از 
        <a href="https://neshan.org/maps" target="_blank" rel="noopener">نشان</a> 
        یا 
        <a href="https://www.google.com/maps" target="_blank" rel="noopener">Google Maps</a> 
        بگیر. روی نقطه موردنظر راست‌کلیک کن و مختصات رو کپی کن.
    </p>
    <?php
}


/**
 * متاباکس: ساعت کاری
 */
function gpds_office_hours_box($post) {
    $hours = get_post_meta($post->ID, '_office_hours', true);
    if (!is_array($hours)) $hours = [];

    $days = [
        'sat' => 'شنبه',
        'sun' => 'یکشنبه',
        'mon' => 'دوشنبه',
        'tue' => 'سه‌شنبه',
        'wed' => 'چهارشنبه',
        'thu' => 'پنجشنبه',
        'fri' => 'جمعه',
    ];
    ?>
    <table class="form-table">
        <?php foreach ($days as $key => $label) : 
            $open  = $hours[$key]['open']  ?? '';
            $close = $hours[$key]['close'] ?? '';
            $off   = $hours[$key]['off']   ?? false;
        ?>
            <tr>
                <th><?php echo esc_html($label); ?></th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="office_hours[<?php echo esc_attr($key); ?>][off]" 
                               value="1" 
                               <?php checked($off); ?>>
                        تعطیل
                    </label>
                    &nbsp;&nbsp;
                    <input type="time" 
                           name="office_hours[<?php echo esc_attr($key); ?>][open]" 
                           value="<?php echo esc_attr($open); ?>" 
                           dir="ltr">
                    تا
                    <input type="time" 
                           name="office_hours[<?php echo esc_attr($key); ?>][close]" 
                           value="<?php echo esc_attr($close); ?>" 
                           dir="ltr">
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}


// ============================================
// 3. ذخیره
// ============================================

add_action('save_post_office', 'gpds_save_office_meta');

function gpds_save_office_meta($post_id) {
    if (!isset($_POST['gpds_office_nonce'])) return;
    if (!wp_verify_nonce($_POST['gpds_office_nonce'], 'gpds_office_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $text_fields = [
        'manager_name', 'manager_role', 'manager_mobile',
        'phone', 'mobile', 'whatsapp', 'email', 'website',
        'instagram', 'telegram',
        'province', 'city', 'postal',
    ];

    $url_fields   = ['website'];
    $email_fields = ['email'];
    $float_fields = ['lat', 'lng'];

    // فیلدهای ساده
    foreach ($text_fields as $field) {
        $key = 'office_' . $field;
        if (!isset($_POST[$key])) continue;

        $value = $_POST[$key];

        if (in_array($field, $email_fields, true)) {
            $value = sanitize_email($value);
        } elseif (in_array($field, $url_fields, true)) {
            $value = esc_url_raw($value);
        } else {
            $value = sanitize_text_field($value);
        }

        if ($value === '') {
            delete_post_meta($post_id, '_' . $key);
        } else {
            update_post_meta($post_id, '_' . $key, $value);
        }
    }

    // مختصات
    foreach ($float_fields as $field) {
        $key = 'office_' . $field;
        if (!isset($_POST[$key])) continue;
        $value = (string) floatval($_POST[$key]);

        if ($value === '0' || $value === '') {
            delete_post_meta($post_id, '_' . $key);
        } else {
            update_post_meta($post_id, '_' . $key, $value);
        }
    }

    // آدرس
    if (isset($_POST['office_address'])) {
        $value = sanitize_textarea_field($_POST['office_address']);
        if ($value === '') {
            delete_post_meta($post_id, '_office_address');
        } else {
            update_post_meta($post_id, '_office_address', $value);
        }
    }

    // ساعت کاری
    if (isset($_POST['office_hours']) && is_array($_POST['office_hours'])) {
        $clean = [];
        foreach ($_POST['office_hours'] as $day => $data) {
            $clean[sanitize_key($day)] = [
                'off'   => !empty($data['off']),
                'open'  => sanitize_text_field($data['open']  ?? ''),
                'close' => sanitize_text_field($data['close'] ?? ''),
            ];
        }
        update_post_meta($post_id, '_office_hours', $clean);
    }
}


// ============================================
// 4. هِلپرها
// ============================================

/**
 * گرفتن اطلاعات کامل یه دفتر
 */
function gpds_get_office_data($post_id) {
    $post_id = (int) $post_id;
    if (!$post_id) return [];

    $get = function ($key) use ($post_id) {
        return get_post_meta($post_id, '_office_' . $key, true);
    };

    return [
        'id'             => $post_id,
        'title'          => get_the_title($post_id),
        'permalink'      => get_permalink($post_id),
        'excerpt'        => get_the_excerpt($post_id),
        'content'        => get_post_field('post_content', $post_id),
        'thumbnail'      => get_the_post_thumbnail_url($post_id, 'large'),
        'thumbnail_id'   => get_post_thumbnail_id($post_id),

        // مسئول
        'manager_name'   => $get('manager_name'),
        'manager_role'   => $get('manager_role'),
        'manager_mobile' => $get('manager_mobile'),

        // تماس
        'phone'          => $get('phone'),
        'mobile'         => $get('mobile'),
        'whatsapp'       => $get('whatsapp'),
        'email'          => $get('email'),
        'website'        => $get('website'),

        // شبکه‌های اجتماعی
        'instagram'      => $get('instagram'),
        'telegram'       => $get('telegram'),

        // آدرس
        'province'       => $get('province'),
        'city'           => $get('city'),
        'address'        => $get('address'),
        'postal'         => $get('postal'),
        'lat'            => (float) $get('lat'),
        'lng'            => (float) $get('lng'),

        // ساعت کاری
        'hours'          => $get('hours') ?: [],
    ];
}

/**
 * کوئری دفاتر
 */
function gpds_get_offices($args = []) {
    $args = wp_parse_args($args, [
        'limit'    => -1,
        'paginate' => false,
    ]);

    return new WP_Query([
        'post_type'      => 'office',
        'post_status'    => 'publish',
        'posts_per_page' => (int) $args['limit'],
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'no_found_rows'  => !$args['paginate'],
    ]);
}

/**
 * نام روز به فارسی
 */
function gpds_office_day_label($key) {
    $map = [
        'sat' => 'شنبه',
        'sun' => 'یکشنبه',
        'mon' => 'دوشنبه',
        'tue' => 'سه‌شنبه',
        'wed' => 'چهارشنبه',
        'thu' => 'پنجشنبه',
        'fri' => 'جمعه',
    ];
    return $map[$key] ?? $key;
}

/**
 * چک کردن باز بودن دفتر الان
 */
function gpds_office_is_open_now($hours) {
    if (empty($hours)) return false;

    $day_map = [
        0 => 'sun',
        1 => 'mon',
        2 => 'tue',
        3 => 'wed',
        4 => 'thu',
        5 => 'fri',
        6 => 'sat',
    ];

    $today     = $day_map[date('w')] ?? 'sat';
    $current   = date('H:i');

    if (empty($hours[$today]) || !empty($hours[$today]['off'])) {
        return false;
    }

    $open  = $hours[$today]['open']  ?? '';
    $close = $hours[$today]['close'] ?? '';

    if (!$open || !$close) return false;

    return ($current >= $open && $current <= $close);
}


// ============================================
// 5. Schema (LocalBusiness)
// ============================================

add_action('wp_head', 'gpds_office_schema', 7);

function gpds_office_schema() {
    if (!is_singular('office')) return;

    $data = gpds_get_office_data(get_the_ID());
    if (empty($data)) return;

    $schema = [
        '@context'  => 'https://schema.org',
        '@type'     => 'LocalBusiness',
        'name'      => $data['title'],
        'url'       => $data['permalink'],
        'telephone' => $data['phone'] ?: $data['mobile'],
    ];

    if ($data['address']) {
        $schema['address'] = [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $data['address'],
            'addressLocality' => $data['city'],
            'addressRegion'   => $data['province'],
            'postalCode'      => $data['postal'],
            'addressCountry'  => 'IR',
        ];
    }

    if ($data['lat'] && $data['lng']) {
        $schema['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => $data['lat'],
            'longitude' => $data['lng'],
        ];
    }

    if ($data['thumbnail']) {
        $schema['image'] = $data['thumbnail'];
    }

    if ($data['email']) {
        $schema['email'] = $data['email'];
    }

    echo '<script type="application/ld+json">' 
        . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) 
        . '</script>' . "\n";
}


// ============================================
// 6. لود تمپلیت‌ها
// ============================================

add_filter('template_include', 'gpds_office_template_include', 97);

function gpds_office_template_include($template) {
    $base = GPDS_INC . '/templates/offices';

    if (is_singular('office')) {
        $custom = $base . '/single.php';
        if (file_exists($custom)) return $custom;
    }

    if (is_post_type_archive('office')) {
        $custom = $base . '/archive.php';
        if (file_exists($custom)) return $custom;
    }

    return $template;
}