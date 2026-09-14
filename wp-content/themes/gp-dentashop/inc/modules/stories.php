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
    register_post_type('gpds_story', [
        'labels' => [
            'name'               => 'استوری‌ها',
            'singular_name'      => 'استوری',
            'menu_name'          => 'استوری‌ها',
            'add_new'            => 'افزودن استوری',
            'add_new_item'       => 'افزودن استوری جدید',
            'edit_item'          => 'ویرایش استوری',
            'all_items'          => 'همه استوری‌ها',
            'not_found'          => 'استوری یافت نشد',
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_position'   => 26,
        'menu_icon'       => 'dashicons-format-gallery',
        'supports'        => ['title', 'thumbnail'],
        'has_archive'     => false,
        'rewrite'         => false,
        'capability_type' => 'post',
        'show_in_rest'    => false,
    ]);
}

// ============================================
// 2. لود Media Library در ادمین
// ============================================
add_action('admin_enqueue_scripts', function() {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'gpds_story') return;
    
    wp_enqueue_media();
});

// ============================================
// 3. Meta Box
// ============================================
add_action('add_meta_boxes', 'gpds_story_meta_boxes');

function gpds_story_meta_boxes() {
    add_meta_box(
        'gpds_story_settings',
        '⚙️ تنظیمات استوری',
        'gpds_story_meta_box_html',
        'gpds_story',
        'normal',
        'high'
    );
}

function gpds_story_meta_box_html($post) {
    wp_nonce_field('gpds_story_save', 'gpds_story_nonce');
    
    // متادیتا
    $url     = get_post_meta($post->ID, '_gpds_url', true);
    $new_tab = get_post_meta($post->ID, '_gpds_new_tab', true);
    $color   = get_post_meta($post->ID, '_gpds_color', true) ?: 'default';
    $order   = get_post_meta($post->ID, '_gpds_order', true);
    $expires = get_post_meta($post->ID, '_gpds_expires', true);
    $active  = get_post_meta($post->ID, '_gpds_active', true);
    
    // 🎯 ویدیو — مقدار صحیح 0 حفظ بشه
    $video_id       = (int) get_post_meta($post->ID, '_gpds_video_id', true);
    $video_duration = get_post_meta($post->ID, '_gpds_video_duration', true);
    
    if ($video_duration === '' || $video_duration === false) {
        $video_duration = 0; // پیش‌فرض: کل ویدیو
    } else {
        $video_duration = (int) $video_duration;
    }
    
    if ($active === '') $active = '1';
    if ($order === '') $order = 0;
    ?>
    
    <style>
    .gpds-mb-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 10px 0; }
    .gpds-mb-field { display: flex; flex-direction: column; gap: 8px; }
    .gpds-mb-field--full { grid-column: 1 / -1; }
    .gpds-mb-field > label { font-weight: 600; font-size: 13px; color: #1d2327; }
    .gpds-mb-field input[type="text"],
    .gpds-mb-field input[type="url"],
    .gpds-mb-field input[type="number"],
    .gpds-mb-field input[type="datetime-local"] {
        padding: 10px 12px; border: 1px solid #dcdcde; border-radius: 6px;
        font-size: 14px; width: 100%;
    }
    .gpds-mb-field input:focus { outline: none; border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; }
    .gpds-mb-help { font-size: 12px; color: #757575; line-height: 1.5; }
    .gpds-mb-help code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; }
    .gpds-video-preview { padding: 12px; background: #f6f7f7; border-radius: 6px; border: 1px solid #dcdcde; }
    .gpds-video-preview video { max-width: 200px; border-radius: 8px; display: block; }
    .gpds-video-preview p { margin: 8px 0 0; font-size: 12px; color: #757575; }
    .gpds-mb-toggle {
        display: inline-flex; align-items: center; gap: 10px; padding: 10px 14px;
        background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; cursor: pointer;
    }
    .gpds-mb-toggle:hover { background: #f0f0f1; }
    .gpds-mb-colors { display: flex; flex-wrap: wrap; gap: 8px; }
    .gpds-mb-color {
        display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;
        background: #fff; border: 2px solid #dcdcde; border-radius: 8px;
        cursor: pointer; font-size: 13px; transition: all 0.2s;
    }
    .gpds-mb-color input { display: none; }
    .gpds-mb-color:has(input:checked) { border-color: #2271b1; background: #f0f6fc; box-shadow: 0 0 0 1px #2271b1; }
    .gpds-mb-color-dot {
        width: 18px; height: 18px; border-radius: 50%;
        border: 2px solid #fff; box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
    }
    .gpds-video-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
    .gpds-duration-hint { background: #fffbeb; border: 1px solid #fde68a; padding: 8px 12px; border-radius: 6px; font-size: 12px; color: #92400e; }
    </style>
    
    <div class="gpds-mb-wrap">
        
        <!-- 🎬 ویدیو -->
        <div class="gpds-mb-field gpds-mb-field--full">
            <label>🎬 ویدیو استوری (اختیاری)</label>
            <div class="gpds-video-buttons">
                <button type="button" class="button button-primary" id="gpds-select-video">
                    <?php echo $video_id ? 'تغییر ویدیو' : 'انتخاب ویدیو'; ?>
                </button>
                <?php if ($video_id) : ?>
                    <button type="button" class="button" id="gpds-remove-video">حذف ویدیو</button>
                <?php endif; ?>
            </div>
            
            <input type="hidden" id="gpds_video_id" name="gpds_video_id" value="<?php echo esc_attr($video_id); ?>">
            
            <?php if ($video_id) : 
                $video_src = wp_get_attachment_url($video_id);
                $video_meta = wp_get_attachment_metadata($video_id);
                $video_len = isset($video_meta['length']) ? round($video_meta['length'], 1) : 0;
                $file_path = get_attached_file($video_id);
                $file_size = ($file_path && file_exists($file_path)) ? size_format(filesize($file_path)) : '—';
            ?>
                <div class="gpds-video-preview">
                    <video src="<?php echo esc_url($video_src); ?>" preload="metadata" muted playsinline></video>
                    <p>
                        ✅ ویدیو انتخاب شده<br>
                        حجم: <?php echo esc_html($file_size); ?><br>
                        مدت: <?php echo $video_len ? esc_html($video_len) . ' ثانیه' : 'نامعلوم'; ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <span class="gpds-mb-help">
                فرمت پیشنهادی: <code>MP4 (H.264 + AAC)</code> — حجم پیشنهادی: زیر ۲ مگابایت
            </span>
        </div>
        
        <!-- ⏱️ مدت ویدیو -->
        <?php if ($video_id) : ?>
        <div class="gpds-mb-field gpds-mb-field--full">
            <label for="gpds_video_duration">⏱️ مدت پخش ویدیو (ثانیه)</label>
            <input 
                type="number" 
                id="gpds_video_duration" 
                name="gpds_video_duration" 
                value="<?php echo esc_attr($video_duration); ?>"
                min="0"
                max="120"
                step="1"
            >
            <div class="gpds-duration-hint">
                💡 <strong>0</strong> = پخش کل ویدیو (تا آخر) | <strong>بیشتر از 0</strong> = فقط X ثانیه پخش بشه و بره بعدی
            </div>
        </div>
        <?php endif; ?>
        
        <!-- URL -->
        <div class="gpds-mb-field gpds-mb-field--full">
            <label for="gpds_url">لینک مقصد</label>
            <input 
                type="url" 
                id="gpds_url" 
                name="gpds_url" 
                value="<?php echo esc_attr($url); ?>"
                placeholder="https://dentashop.ir/shop/"
                dir="ltr"
            >
            <span class="gpds-mb-help">وقتی کاربر روی استوری کلیک کرد، به این آدرس بره</span>
        </div>
        
        <!-- New Tab -->
        <div class="gpds-mb-field">
            <label>رفتار لینک</label>
            <label class="gpds-mb-toggle">
                <input type="checkbox" name="gpds_new_tab" value="1" <?php checked($new_tab, '1'); ?>>
                <span>در تب جدید باز شود</span>
            </label>
        </div>
        
        <!-- Active -->
        <div class="gpds-mb-field">
            <label>وضعیت</label>
            <label class="gpds-mb-toggle">
                <input type="checkbox" name="gpds_active" value="1" <?php checked($active, '1'); ?>>
                <span>فعال در سایت</span>
            </label>
        </div>
        
        <!-- Color -->
        <div class="gpds-mb-field gpds-mb-field--full">
            <label>🎨 رنگ حلقه استوری</label>
            <div class="gpds-mb-colors">
                <?php
                $colors = [
                    'default' => ['label' => 'گرادیان', 'bg' => 'linear-gradient(135deg, #ef4056, #f9a825, #00bfd0)'],
                    'red'     => ['label' => 'قرمز',   'bg' => '#ef4056'],
                    'blue'    => ['label' => 'آبی',    'bg' => '#3b82f6'],
                    'green'   => ['label' => 'سبز',    'bg' => '#10b981'],
                    'purple'  => ['label' => 'بنفش',   'bg' => '#8b5cf6'],
                    'gold'    => ['label' => 'طلایی',  'bg' => '#f9a825'],
                    'pink'    => ['label' => 'صورتی',  'bg' => '#ec4899'],
                    'dark'    => ['label' => 'مشکی',   'bg' => '#1f2937'],
                ];
                foreach ($colors as $key => $data) :
                ?>
                    <label class="gpds-mb-color">
                        <input type="radio" name="gpds_color" value="<?php echo esc_attr($key); ?>" <?php checked($color, $key); ?>>
                        <span class="gpds-mb-color-dot" style="background: <?php echo esc_attr($data['bg']); ?>;"></span>
                        <span><?php echo esc_html($data['label']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Order -->
        <div class="gpds-mb-field">
            <label for="gpds_order">ترتیب نمایش</label>
            <input type="number" id="gpds_order" name="gpds_order" value="<?php echo esc_attr($order); ?>" min="0">
            <span class="gpds-mb-help">عدد کمتر = بالاتر</span>
        </div>
        
        <!-- Expires -->
        <div class="gpds-mb-field">
            <label for="gpds_expires">تاریخ انقضا</label>
            <input type="datetime-local" id="gpds_expires" name="gpds_expires" value="<?php echo esc_attr($expires); ?>">
            <span class="gpds-mb-help">خالی = بدون انقضا</span>
        </div>
        
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var mediaFrame;
        
        // انتخاب ویدیو
        $('#gpds-select-video').on('click', function(e) {
            e.preventDefault();
            
            if (mediaFrame) {
                mediaFrame.open();
                return;
            }
            
            mediaFrame = wp.media({
                title: 'انتخاب ویدیو استوری',
                button: { text: 'استفاده از این ویدیو' },
                library: { type: 'video' },
                multiple: false
            });
            
            mediaFrame.on('select', function() {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                $('#gpds_video_id').val(attachment.id);
                
                var preview = '<div class="gpds-video-preview">';
                preview += '<video src="' + attachment.url + '" preload="metadata" muted playsinline></video>';
                preview += '<p>✅ ویدیو: ' + attachment.filename + '</p>';
                preview += '</div>';
                
                $('#gpds-select-video').closest('.gpds-mb-field').find('.gpds-video-preview').remove();
                $('#gpds-select-video').closest('.gpds-mb-field').find('span.gpds-mb-help').first().before(preview);
                
                $('#gpds-select-video').text('تغییر ویدیو');
            });
            
            mediaFrame.open();
        });
        
        // حذف ویدیو
        $('#gpds-remove-video').on('click', function(e) {
            e.preventDefault();
            if (!confirm('ویدیو حذف شود؟')) return;
            
            $('#gpds_video_id').val('');
            $('.gpds-video-preview').remove();
            $(this).remove();
            $('#gpds-select-video').text('انتخاب ویدیو');
        });
    });
    </script>
    
    <?php
}

// ============================================
// 4. ذخیره متادیتا
// ============================================
add_action('save_post_gpds_story', 'gpds_save_story_meta');

function gpds_save_story_meta($post_id) {
    if (!isset($_POST['gpds_story_nonce'])) return;
    if (!wp_verify_nonce($_POST['gpds_story_nonce'], 'gpds_story_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    // URL
    update_post_meta($post_id, '_gpds_url', 
        isset($_POST['gpds_url']) ? esc_url_raw($_POST['gpds_url']) : ''
    );
    
    // New tab
    update_post_meta($post_id, '_gpds_new_tab', isset($_POST['gpds_new_tab']) ? '1' : '0');
    
    // Active
    update_post_meta($post_id, '_gpds_active', isset($_POST['gpds_active']) ? '1' : '0');
    
    // Color
    $color = isset($_POST['gpds_color']) ? sanitize_key($_POST['gpds_color']) : 'default';
    $valid_colors = ['default', 'red', 'blue', 'green', 'purple', 'gold', 'pink', 'dark'];
    if (!in_array($color, $valid_colors, true)) $color = 'default';
    update_post_meta($post_id, '_gpds_color', $color);
    
    // Order
    update_post_meta($post_id, '_gpds_order', 
        isset($_POST['gpds_order']) ? intval($_POST['gpds_order']) : 0
    );
    
    // Expires
    update_post_meta($post_id, '_gpds_expires', 
        isset($_POST['gpds_expires']) ? sanitize_text_field($_POST['gpds_expires']) : ''
    );
    
    // 🎯 ویدیو ID
    update_post_meta($post_id, '_gpds_video_id', 
        isset($_POST['gpds_video_id']) ? absint($_POST['gpds_video_id']) : 0
    );
    
    // 🎯 مدت ویدیو — مقدار 0 باید حفظ بشه
    $duration = 0; // پیش‌فرض: کل ویدیو
    if (isset($_POST['gpds_video_duration']) && $_POST['gpds_video_duration'] !== '') {
        $duration = absint($_POST['gpds_video_duration']);
    }
    update_post_meta($post_id, '_gpds_video_duration', $duration);
    
    // پاک کردن Cache
    gpds_clear_stories_cache();
}

// ============================================
// 5. Helper: پاک کردن Cache
// ============================================
function gpds_clear_stories_cache() {
    delete_transient('gpds_active_stories');
}

add_action('deleted_post', function($post_id) {
    if (get_post_type($post_id) === 'gpds_story') {
        gpds_clear_stories_cache();
    }
});

// ============================================
// 6. ستون‌های ادمین
// ============================================
add_filter('manage_gpds_story_posts_columns', function($columns) {
    return [
        'cb'      => $columns['cb'],
        'thumb'   => 'تصویر',
        'title'   => 'عنوان',
        'type'    => 'نوع',
        'url'     => 'لینک',
        'color'   => 'رنگ',
        'active'  => 'وضعیت',
        'order'   => 'ترتیب',
        'date'    => $columns['date'],
    ];
});

add_action('manage_gpds_story_posts_custom_column', function($column, $post_id) {
    switch ($column) {
        case 'thumb':
            $thumb_id = get_post_thumbnail_id($post_id);
            if ($thumb_id) {
                echo wp_get_attachment_image($thumb_id, [60, 60], false, [
                    'style' => 'width:60px;height:60px;border-radius:50%;object-fit:cover;',
                ]);
            } else {
                echo '<span style="color:#999">—</span>';
            }
            break;
            
        case 'type':
            $video_id = (int) get_post_meta($post_id, '_gpds_video_id', true);
            if ($video_id) {
                $duration = (int) get_post_meta($post_id, '_gpds_video_duration', true);
                $label = $duration > 0 ? "🎬 ویدیو ({$duration}s)" : '🎬 ویدیو (کامل)';
                echo '<span style="color:#2271b1;font-weight:600;">' . esc_html($label) . '</span>';
            } else {
                echo '<span style="color:#757575;">🖼️ تصویر</span>';
            }
            break;
            
        case 'url':
            $url = get_post_meta($post_id, '_gpds_url', true);
            if ($url) {
                printf(
                    '<a href="%s" target="_blank" rel="noopener" dir="ltr" title="%s">%s</a>',
                    esc_url($url),
                    esc_attr($url),
                    esc_html(wp_parse_url($url, PHP_URL_HOST) ?: $url)
                );
            } else {
                echo '<span style="color:#999">—</span>';
            }
            break;
            
        case 'color':
            $color = get_post_meta($post_id, '_gpds_color', true) ?: 'default';
            $map = [
                'default' => ['label' => 'گرادیان', 'bg' => 'linear-gradient(135deg, #ef4056, #00bfd0)'],
                'red'     => ['label' => 'قرمز',   'bg' => '#ef4056'],
                'blue'    => ['label' => 'آبی',    'bg' => '#3b82f6'],
                'green'   => ['label' => 'سبز',    'bg' => '#10b981'],
                'purple'  => ['label' => 'بنفش',   'bg' => '#8b5cf6'],
                'gold'    => ['label' => 'طلایی',  'bg' => '#f9a825'],
                'pink'    => ['label' => 'صورتی',  'bg' => '#ec4899'],
                'dark'    => ['label' => 'مشکی',   'bg' => '#1f2937'],
            ];
            $item = $map[$color] ?? $map['default'];
            printf(
                '<span style="display:inline-block;width:14px;height:14px;border-radius:50%%;background:%s;vertical-align:middle;margin-left:6px;"></span>%s',
                esc_attr($item['bg']),
                esc_html($item['label'])
            );
            break;
            
        case 'active':
            $active = get_post_meta($post_id, '_gpds_active', true);
            $expires = get_post_meta($post_id, '_gpds_expires', true);
            
            if ($active !== '1') {
                echo '<span style="color:#d63638">● غیرفعال</span>';
            } elseif ($expires && strtotime($expires) < current_time('timestamp')) {
                echo '<span style="color:#d63638">● منقضی</span>';
            } else {
                echo '<span style="color:#00a32a">● فعال</span>';
            }
            break;
            
        case 'order':
            echo intval(get_post_meta($post_id, '_gpds_order', true));
            break;
    }
}, 10, 2);

// ============================================
// 7. مرتب‌سازی ادمین
// ============================================
add_action('pre_get_posts', function($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('post_type') !== 'gpds_story') return;
    
    $query->set('meta_key', '_gpds_order');
    $query->set('orderby', 'meta_value_num');
    $query->set('order', 'ASC');
});

// ============================================
// 8. گرفتن استوری‌های فعال (Frontend)
// ============================================
function gpds_get_active_stories($limit = 20) {
    $cache_key = 'gpds_active_stories';
    $cached = get_transient($cache_key);
    
    if ($cached !== false && is_array($cached)) {
        return array_slice($cached, 0, $limit);
    }
    
    $query = new WP_Query([
        'post_type'              => 'gpds_story',
        'post_status'            => 'publish',
        'posts_per_page'         => 30,
        'meta_key'               => '_gpds_order',
        'orderby'                => 'meta_value_num',
        'order'                  => 'ASC',
        'no_found_rows'          => true,
        'update_post_term_cache' => false,
    ]);
    
    $stories = [];
    $now = current_time('timestamp');
    
    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            // چک فعال
            $active = get_post_meta($post->ID, '_gpds_active', true);
            if ($active === '0') continue;
            
            // چک انقضا
            $expires = get_post_meta($post->ID, '_gpds_expires', true);
            if ($expires && strtotime($expires) < $now) continue;
            
            // چک تصویر
            $thumb_id = get_post_thumbnail_id($post->ID);
            if (!$thumb_id) continue;
            
            $thumb = wp_get_attachment_image_src($thumb_id, 'gpds-story');
            $full  = wp_get_attachment_image_src($thumb_id, 'large');
            
            if (!$thumb || !$full) continue;
            
            // 🎯 ویدیو
            $video_id       = (int) get_post_meta($post->ID, '_gpds_video_id', true);
            $video_url      = '';
            $video_mime     = '';
            
            // 🎯 مدت — مقدار 0 حفظ بشه
            $duration_raw = get_post_meta($post->ID, '_gpds_video_duration', true);
            $video_duration = ($duration_raw === '' || $duration_raw === false) 
                ? 0 
                : (int) $duration_raw;
            
            if ($video_id > 0) {
                $video_src = wp_get_attachment_url($video_id);
                if ($video_src) {
                    $video_url = $video_src;
                    $video_mime = get_post_mime_type($video_id);
                }
            }
            
            $stories[] = [
                'id'             => $post->ID,
                'title'          => $post->post_title,
                'image'          => $thumb[0],
                'image_full'     => $full[0],
                'url'            => get_post_meta($post->ID, '_gpds_url', true),
                'new_tab'        => get_post_meta($post->ID, '_gpds_new_tab', true) === '1',
                'color'          => get_post_meta($post->ID, '_gpds_color', true) ?: 'default',
                'video'          => $video_url,
                'video_mime'     => $video_mime,
                'video_duration' => $video_duration,
            ];
        }
    }
    
    wp_reset_postdata();
    
    // Cache به مدت 1 ساعت
    set_transient($cache_key, $stories, HOUR_IN_SECONDS);
    
    return array_slice($stories, 0, $limit);
}

// ============================================
// 9. اضافه کردن سایز تصویر
// ============================================
add_action('after_setup_theme', function() {
    add_image_size('gpds-story', 120, 120, true);
}, 25);