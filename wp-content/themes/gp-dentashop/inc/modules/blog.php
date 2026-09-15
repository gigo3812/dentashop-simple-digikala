<?php
/**
 * Blog Module
 * 
 * مسئولیت‌ها:
 *   - کوئری مقالات (با کش)
 *   - مقالات مرتبط
 *   - زمان مطالعه
 *   - بردکرامب بلاگ
 *   - بازدید پست
 *   - Schema Article (SEO)
 *   - Open Graph (SEO)
 *   - لود تمپلیت‌ها از inc/templates/blog/
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;


// ============================================
// 1. کوئری مقالات
// ============================================

function gpds_get_blog_posts($args = []) {
    $args = wp_parse_args($args, [
        'limit'    => 9,
        'page'     => 1,
        'orderby'  => 'date',
        'order'    => 'DESC',
        'category' => 0,
        'exclude'  => [],
        'paginate' => false,
    ]);

    $query_args = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => (int) $args['limit'],
        'paged'               => max(1, (int) $args['page']),
        'orderby'             => $args['orderby'],
        'order'               => $args['order'],
        'ignore_sticky_posts' => true,
        'no_found_rows'       => !$args['paginate'],
    ];

    if ($args['category']) {
        $query_args['cat'] = (int) $args['category'];
    }

    if (!empty($args['exclude'])) {
        $query_args['post__not_in'] = array_map('intval', (array) $args['exclude']);
    }

    return new WP_Query($query_args);
}


// ============================================
// 2. مقالات مرتبط
// ============================================

function gpds_get_related_posts($post_id, $limit = 3) {
    $post_id = (int) $post_id;
    if (!$post_id) {
        return new WP_Query(['post__in' => [0]]);
    }

    return new WP_Query([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => (int) $limit,
        'post__not_in'        => [$post_id],
        'category__in'        => wp_get_post_categories($post_id),
        'orderby'             => 'rand',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ]);
}


// ============================================
// 3. زمان مطالعه (سازگار با فارسی)
// ============================================

function gpds_get_reading_time($content, $wpm = 200) {
    $content = trim(wp_strip_all_tags($content));

    if (empty($content)) {
        return 1;
    }

    $word_count = count(preg_split('/\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY));

    return max(1, (int) ceil($word_count / $wpm));
}


// ============================================
// 4. دسته‌های بلاگ
// ============================================

function gpds_get_blog_categories($limit = 10) {
    $cache_key = 'gpds_blog_cats_' . $limit;
    $cached    = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    $cats = get_categories([
        'hide_empty' => true,
        'number'     => (int) $limit,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ]);

    set_transient($cache_key, $cats, HOUR_IN_SECONDS);

    return $cats;
}


// ============================================
// 5. بازدید پست
// ============================================

function gpds_increment_post_views($post_id) {
    if (is_admin() || !is_singular('post')) return;

    $count = (int) get_post_meta($post_id, '_gpds_views', true);
    update_post_meta($post_id, '_gpds_views', $count + 1);
}

function gpds_get_post_views($post_id) {
    return (int) get_post_meta($post_id, '_gpds_views', true);
}


// ============================================
// 6. پاک کردن کش
// ============================================

add_action('save_post_post', 'gpds_clear_blog_cache');
add_action('deleted_post',   'gpds_clear_blog_cache');

function gpds_clear_blog_cache() {
    delete_transient('gpds_blog_cats_10');
    delete_transient('gpds_blog_cats_20');
}


// ============================================
// 7. بردکرامب بلاگ
// ============================================

function gpds_blog_breadcrumb() {
    if (is_front_page()) return;

    $sep = gpds_get_icon('chevron-left', 14);

    echo '<nav class="gpds-breadcrumb" aria-label="مسیر">';
    echo '<a href="' . esc_url(home_url('/')) . '">خانه</a>';

    if (is_singular('post')) {
        $blog_page_id = get_option('page_for_posts');
        if ($blog_page_id) {
            echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
            echo '<a href="' . esc_url(get_permalink($blog_page_id)) . '">وبلاگ</a>';
        }

        $categories = get_the_category();
        if (!empty($categories)) {
            $cat = $categories[0];
            echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
            echo '<a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a>';
        }

        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<span>' . esc_html(get_the_title()) . '</span>';
    } elseif (is_category() || is_tag() || is_author() || is_date()) {
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<span>' . esc_html(wp_strip_all_tags(get_the_archive_title())) . '</span>';
    } elseif (is_search()) {
        echo '<span class="gpds-breadcrumb-sep">' . $sep . '</span>';
        echo '<span>جستجو: ' . esc_html(get_search_query()) . '</span>';
    }

    echo '</nav>';
}


// ============================================
// 8. Schema Article (SEO)
// ============================================

add_action('wp_head', 'gpds_render_article_schema', 5);

function gpds_render_article_schema() {
    if (!is_singular('post')) return;

    $post_id = get_the_ID();
    $logo_id = get_theme_mod('custom_logo');
    $logo    = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    $image   = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'full') : '';

    $schema = [
        '@context'         => 'https://schema.org',
        '@type'            => 'Article',
        'headline'         => get_the_title(),
        'description'      => get_the_excerpt(),
        'url'              => get_permalink(),
        'datePublished'    => get_the_date('c'),
        'dateModified'     => get_the_modified_date('c'),
        'author'           => ['@type' => 'Person', 'name' => get_the_author()],
        'publisher'        => ['@type' => 'Organization', 'name' => get_bloginfo('name')],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => get_permalink()],
    ];

    if ($logo) {
        $schema['publisher']['logo'] = ['@type' => 'ImageObject', 'url' => $logo];
    }
    if ($image) {
        $schema['image'] = ['@type' => 'ImageObject', 'url' => $image];
    }

    echo '<script type="application/ld+json">' 
        . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) 
        . '</script>' . "\n";
}


// ============================================
// 9. Open Graph (SEO)
// ============================================

add_action('wp_head', 'gpds_render_og_tags', 6);

function gpds_render_og_tags() {
    if (!is_singular('post')) return;

    $post_id = get_the_ID();
    $image   = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'full') : '';

    echo '<meta property="og:type" content="article">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr(get_the_excerpt()) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c')) . '">' . "\n";

    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    }
}


// ============================================
// 10. لود تمپلیت‌ها از inc/templates/blog/
// ============================================

add_filter('template_include', 'gpds_blog_template_include', 98);

function gpds_blog_template_include($template) {
    $base = GPDS_INC . '/templates/blog';

    // مقاله تکی
    if (is_singular('post')) {
        $custom = $base . '/single.php';
        if (file_exists($custom)) return $custom;
    }

    // لیست مقالات (home, category, tag, author, date, search)
    if (is_home() || is_category() || is_tag() || is_author() || is_date() || is_search()) {
        $custom = $base . '/archive.php';
        if (file_exists($custom)) return $custom;
    }

    return $template;
}