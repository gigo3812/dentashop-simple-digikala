<?php
/**
 * Stories Module
 * 
 * سیستم استوری با Custom Post Type
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// ============================================
// 1. ثبت Custom Post Type
// ============================================
add_action('init', 'gpds_register_story_cpt');

function gpds_register_story_cpt() {
    $labels = [
        'name'                  => 'استوری‌ها',
        'singular_name'         => 'استوری',
        'menu_name'             => 'استوری‌ها',
        'name_admin_bar'        => 'استوری',
        'add_new'               => 'افزودن استوری',
        'add_new_item'          => 'افزودن استوری جدید',
        'new_item'              => 'استوری جدید',
        'edit_item'             => 'ویرایش استوری',
        'view_item'             => 'مشاهده استوری',
        'all_items'             => 'همه استوری‌ها',
        'search_items'          => 'جستجوی استوری',
        'not_found'             => 'استوری یافت نشد',
        'not_found_in_trash'    => 'استوری در زباله‌دان یافت نشد',
    ];
    
    register_post_type('gpds_story', [
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 26,
        'menu_icon'           => 'dashicons-format-gallery',
        'supports'            => ['title', 'thumbnail'],
        'has_archive'         => false,
        'rewrite'             => false,
        'capability_type'     => 'post',
        'show_in_rest'        => true,
        'rest_base'           => 'stories',
    ]);
}

// ============================================
// 2. متاباکس‌های اختصاصی
// ============================================
add_action('add_meta_boxes', 'gpds_story_meta_boxes');

function gpds_story_meta_boxes() {
    add_meta_box(
        'gpds_story_options',
        'تنظیمات استوری',
        'gpds_story_meta_box_render',
        'gpds_story',
        'normal',
        'high'
    );
}

function gpds_story_meta_box_render($post) {
    wp_nonce_field('gpds_story_meta', 'gpds_story_nonce');
    
    // گرفتن مقادیر فعلی
    $target_url   = get_post_meta($post->ID, '_gpds_story_url', true);
    $open_new     = get_post_meta($post->ID, '_gpds_story_new_tab', true);
    $ring_color   = get_post_meta($post->ID, '_gpds_story_ring_color', true) ?: 'default';
    $order        = get_post_meta($post->ID, '_gpds_story_order', true) ?: 0;
    $expires      = get_post_meta($post->ID, '_gpds_story_expires', true);
    $is_active    = get_post_meta($post->ID, '_gpds_story_active', true);
    
    if ($is_active === '') $is_active = '1';
    ?>
    
    <style>
    .gpds-mb-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        padding: 8px 0;
    }
    .gpds-mb-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .gpds-mb-field.full {
        grid-column: 1 / -1;
    }
    .gpds-mb-field label {
        font-weight: 600;
        font-size: 13px;
        color: #1d2327;
    }
    .gpds-mb-field input[type="text"],
    .gpds-mb-field input[type="url"],
    .gpds-mb-field input[type="datetime-local"],
    .gpds-mb-field input[type="number"] {
        padding: 8px 10px;
        border: 1px solid #dcdcde;
        border-radius: 4px;
        font-size: 13px;
    }
    .gpds-mb-field input:focus {
        outline: none;
        border-color: #2271b1;
        box-shadow: 0 0 0 1px #2271b1;
    }
    .gpds-mb-help {
        font-size: 12px;
        color: #757575;
        font-style: italic;
    }
    .gpds-mb-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: #f6f7f7;
        border-radius: 4px;
        border: 1px solid #dcdcde;
    }
    .gpds-mb-toggle input {
        margin: 0;
    }
    .gpds-mb-color-options {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .gpds-mb-color-options label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        background: #f6f7f7;
        border-radius: 4px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
        font-weight: normal;
    }
    .gpds-mb-color-options input:checked + span {
        font-weight: 600;
    }
    .gpds-mb-color-options label:has(input:checked) {
        border-color: #2271b1;
        background: #f0f6fc;
    }
    .gpds-mb-color-preview {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: inline-block;
    }
    .gpds-mb-color-preview.default { background: linear-gradient(135deg, #ef4056, #f9a825, #00bfd0); }
    .gpds-mb-color-preview.red { background: #ef4056; }
    .gpds-mb-color-preview.blue { background: #3b82f6; }
    .gpds-mb-color-preview.green { background: #10b981; }
    .gpds-mb-color-preview.purple { background: #8b5cf6; }
    .gpds-mb-color-preview.gold { background: #f9a825; }
    </style>
    
    <div class="gpds-mb-grid">
        
        <!-- لینک مقصد -->
        <div class="gpds-mb-field full">
            <label for="gpds_story_url">لینک مقصد</label>
            <input 
                type="url" 
                id="gpds_story_url" 
                name="gpds_story_url" 
                value="<?php echo esc_attr($target_url); ?>"
                placeholder="https://example.com/product/..."
            >
            <span class="gpds-mb-help">
                وقتی کاربر روی استوری کلیک کرد، به این آدرس بره. اگه خالی باشه، لینک غیرفعاله.
            </span>
        </div>
        
        <!-- باز شدن در تب جدید -->
        <div class="gpds-mb-field">
            <label>باز شدن لینک</label>
            <label class="gpds-mb-toggle">
                <input 
                    type="checkbox" 
                    name="gpds_story_new_tab" 
                    value="1"
                    <?php checked($open_new, '1'); ?>
                >
                <span>در تب جدید باز شود</span>
            </label>
        </div>
        
        <!-- فعال بودن -->
        <div class="gpds-mb-field">
            <label>وضعیت</label>
            <label class="gpds-mb-toggle">
                <input 
                    type="checkbox" 
                    name="gpds_story_active" 
                    value="1"
                    <?php checked($is_active, '1'); ?>
                >
                <span>فعال (نمایش در سایت)</span>
            </label>
        </div>
        
        <!-- رنگ حلقه -->
        <div class="gpds-mb-field full">
            <label>رنگ حلقه استوری</label>
            <div class="gpds-mb-color-options">
                <?php
                $colors = [
                    'default' => ['title' => 'پیش‌فرض (گرادیان)', 'class' => 'default'],
                    'red'     => ['title' => 'قرمز',  'class' => 'red'],
                    'blue'    => ['title' => 'آبی',   'class' => 'blue'],
                    'green'   => ['title' => 'سبز',   'class' => 'green'],
                    'purple'  => ['title' => 'بنفش',  'class' => 'purple'],
                    'gold'    => ['title' => 'طلایی', 'class' => 'gold'],
                ];
                
                foreach ($colors as $value => $data) :
                ?>
                    <label>
                        <input 
                            type="radio" 
                            name="gpds_story_ring_color" 
                            value="<?php echo esc_attr($value); ?>"
                            <?php checked($ring_color, $value); ?>
                        >
                        <span class="gpds-mb-color-preview <?php echo esc_attr($data['class']); ?>"></span>
                        <span><?php echo esc_html($data['title']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- ترتیب -->
        <div class="gpds-mb-field">
            <label for="gpds_story_order">ترتیب نمایش</label>
            <input 
                type="number" 
                id="gpds_story_order" 
                name="gpds_story_order" 
                value="<?php echo esc_attr($order); ?>"
                min="0"
                step="1"
            >
            <span class="gpds-mb-help">عدد کمتر = بالاتر</span>
        </div>
        
        <!-- تاریخ انقضا -->
        <div class="gpds-mb-field">
            <label for="gpds_story_expires">تاریخ انقضا</label>
            <input 
                type="datetime-local" 
                id="gpds_story_expires" 
                name="gpds_story_expires" 
                value="<?php echo esc_attr($expires); ?>"
            >
            <span class="gpds-mb-help">اگه خالی باشه، همیشه نمایش داده می‌شه</span>
        </div>
        
    </div>
    
    <?php
}

// ============================================
// 3. ذخیره متادیتا
// ============================================
add_action('save_post_gpds_story', 'gpds_save_story_meta');

function gpds_save_story_meta($post_id) {
    if (!isset($_POST['gpds_story_nonce'])) return;
    if (!wp_verify_nonce($_POST['gpds_story_nonce'], 'gpds_story_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    // URL
    $url = isset($_POST['gpds_story_url']) ? esc_url_raw($_POST['gpds_story_url']) : '';
    update_post_meta($post_id, '_gpds_story_url', $url);
    
    // New tab
    $new_tab = isset($_POST['gpds_story_new_tab']) ? '1' : '0';
    update_post_meta($post_id, '_gpds_story_new_tab', $new_tab);
    
    // Active
    $active = isset($_POST['gpds_story_active']) ? '1' : '0';
    update_post_meta($post_id, '_gpds_story_active', $active);
    
    // Ring color
    $color = isset($_POST['gpds_story_ring_color']) ? sanitize_key($_POST['gpds_story_ring_color']) : 'default';
    update_post_meta($post_id, '_gpds_story_ring_color', $color);
    
    // Order
    $order = isset($_POST['gpds_story_order']) ? intval($_POST['gpds_story_order']) : 0;
    update_post_meta($post_id, '_gpds_story_order', $order);
    
    // Expires
    $expires = isset($_POST['gpds_story_expires']) ? sanitize_text_field($_POST['gpds_story_expires']) : '';
    update_post_meta($post_id, '_gpds_story_expires', $expires);
}

// ============================================
// 4. ستون‌های ادمین
// ============================================
add_filter('manage_gpds_story_posts_columns', 'gpds_story_admin_columns');

function gpds_story_admin_columns($columns) {
    $new = [];
    $new['cb'] = $columns['cb'];
    $new['story_thumb'] = 'تصویر';
    $new['title'] = $columns['title'];
    $new['story_url'] = 'لینک مقصد';
    $new['story_color'] = 'رنگ حلقه';
    $new['story_active'] = 'وضعیت';
    $new['story_order'] = 'ترتیب';
    $new['date'] = $columns['date'];
    
    return $new;
}

add_action('manage_gpds_story_posts_custom_column', 'gpds_story_admin_column_content', 10, 2);

function gpds_story_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'story_thumb':
            $thumb = get_the_post_thumbnail($post_id, [60, 60], ['style' => 'border-radius:50%;object-fit:cover;']);
            echo $thumb ?: '—';
            break;
            
        case 'story_url':
            $url = get_post_meta($post_id, '_gpds_story_url', true);
            if ($url) {
                echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html(wp_parse_url($url, PHP_URL_HOST)) . '</a>';
            } else {
                echo '<span style="color:#999">—</span>';
            }
            break;
            
        case 'story_color':
            $color = get_post_meta($post_id, '_gpds_story_ring_color', true) ?: 'default';
            $labels = [
                'default' => 'گرادیان',
                'red'     => 'قرمز',
                'blue'    => 'آبی',
                'green'   => 'سبز',
                'purple'  => 'بنفش',
                'gold'    => 'طلایی',
            ];
            echo esc_html($labels[$color] ?? '—');
            break;
            
        case 'story_active':
            $active = get_post_meta($post_id, '_gpds_story_active', true);
            $expires = get_post_meta($post_id, '_gpds_story_expires', true);
            
            if ($active === '1') {
                if ($expires && strtotime($expires) < time()) {
                    echo '<span style="color:#d63638">⏰ منقضی</span>';
                } else {
                    echo '<span style="color:#00a32a">● فعال</span>';
                }
            } else {
                echo '<span style="color:#d63638">● غیرفعال</span>';
            }
            break;
            
        case 'story_order':
            echo intval(get_post_meta($post_id, '_gpds_story_order', true));
            break;
    }
}

// ============================================
// 5. مرتب‌سازی ادمین
// ============================================
add_action('pre_get_posts', 'gpds_story_admin_order');

function gpds_story_admin_order($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('post_type') !== 'gpds_story') return;
    
    $query->set('meta_key', '_gpds_story_order');
    $query->set('orderby', 'meta_value_num');
    $query->set('order', 'ASC');
}

// ============================================
// 6. گرفتن استوری‌های فعال (Frontend)
// ============================================
function gpds_get_active_stories($limit = 20) {
    // 🎯 Cache با Transient
    $cache_key = 'gpds_active_stories_' . $limit;
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    $query = new WP_Query([
        'post_type'      => 'gpds_story',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'meta_query'     => [
            [
                'key'     => '_gpds_story_active',
                'value'   => '1',
                'compare' => '=',
            ],
        ],
        'meta_key'       => '_gpds_story_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'no_found_rows'  => true,           // 🎯 بهینه
        'update_post_term_cache' => false,  // 🎯 بهینه
    ]);
    
    $stories = [];
    $now = current_time('timestamp');
    
    foreach ($query->posts as $post) {
        $expires = get_post_meta($post->ID, '_gpds_story_expires', true);
        
        if ($expires && strtotime($expires) < $now) {
            continue;
        }
        
        $thumb_id = get_post_thumbnail_id($post->ID);
        if (!$thumb_id) continue;
        
        // 🎯 فقط سایزهای لازم
        $thumb = wp_get_attachment_image_src($thumb_id, 'gpds-story');
        $full  = wp_get_attachment_image_src($thumb_id, 'large');
        
        if (!$thumb || !$full) continue;
        
        $stories[] = [
            'id'         => $post->ID,
            'title'      => $post->post_title,
            'image'      => $thumb[0],
            'image_w'    => $thumb[1],
            'image_h'    => $thumb[2],
            'image_full' => $full[0],
            'url'        => get_post_meta($post->ID, '_gpds_story_url', true),
            'new_tab'    => get_post_meta($post->ID, '_gpds_story_new_tab', true) === '1',
            'color'      => get_post_meta($post->ID, '_gpds_story_ring_color', true) ?: 'default',
        ];
    }
    
    wp_reset_postdata();
    
    // 🎯 ذخیره در Cache به مدت 1 ساعت
    set_transient($cache_key, $stories, HOUR_IN_SECONDS);
    
    return $stories;
}

// 🎯 پاک کردن Cache وقتی استوری ذخیره شد
add_action('save_post_gpds_story', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    delete_transient('gpds_active_stories_20');
    delete_transient('gpds_active_stories_10');
    delete_transient('gpds_active_stories_30');
});

// 🎯 پاک کردن Cache وقتی استوری حذف شد
add_action('deleted_post', function($post_id) {
    if (get_post_type($post_id) === 'gpds_story') {
        delete_transient('gpds_active_stories_20');
        delete_transient('gpds_active_stories_10');
        delete_transient('gpds_active_stories_30');
    }
});