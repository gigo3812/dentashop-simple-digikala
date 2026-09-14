<?php
/**
 * Customizer Settings
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// ============================================
// ثبت پنل‌ها و تنظیمات
// ============================================
add_action('customize_register', 'gpds_customize_register');

function gpds_customize_register($wp_customize) {
    
    // ============================================
    // پنل: شگفت‌انگیزها
    // ============================================
    $wp_customize->add_section('gpds_special_offer', [
        'title'       => '⚡ شگفت‌انگیزها',
        'description' => 'تنظیمات بخش شگفت‌انگیزهای امروز',
        'priority'    => 30,
    ]);
    
    // فعال/غیرفعال
    $wp_customize->add_setting('gpds_special_offer_enabled', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    
    $wp_customize->add_control('gpds_special_offer_enabled', [
        'label'   => 'نمایش بخش شگفت‌انگیزها',
        'section' => 'gpds_special_offer',
        'type'    => 'checkbox',
    ]);
    
    // نامک دسته‌بندی
    $wp_customize->add_setting('gpds_special_offer_category', [
        'default'           => 'special-offer',
        'sanitize_callback' => 'sanitize_title',
        'transport'         => 'refresh',
    ]);
    
    $wp_customize->add_control('gpds_special_offer_category', [
        'label'       => 'نامک دسته‌بندی شگفت‌انگیز',
        'description' => 'نامک (slug) دسته‌ای که محصولات شگفت‌انگیز در آن هستند',
        'section'     => 'gpds_special_offer',
        'type'        => 'text',
    ]);
    
    // تعداد محصولات
    $wp_customize->add_setting('gpds_special_offer_limit', [
        'default'           => 8,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ]);
    
    $wp_customize->add_control('gpds_special_offer_limit', [
        'label'       => 'تعداد محصولات نمایش',
        'section'     => 'gpds_special_offer',
        'type'        => 'number',
        'input_attrs' => ['min' => 1, 'max' => 20],
    ]);
    
    // تاریخ پایان
    $wp_customize->add_setting('gpds_special_offer_end_date', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    
    $wp_customize->add_control('gpds_special_offer_end_date', [
        'label'       => 'تاریخ و ساعت پایان',
        'description' => 'مثال: 2026-12-31 23:59',
        'section'     => 'gpds_special_offer',
        'type'        => 'text',
        'input_attrs' => [
            'placeholder' => '2026-12-31 23:59',
            'dir'         => 'ltr',
        ],
    ]);
    
    // عنوان
    $wp_customize->add_setting('gpds_special_offer_title', [
        'default'           => 'شگفت‌انگیزهای امروز',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('gpds_special_offer_title', [
        'label'   => 'عنوان بخش',
        'section' => 'gpds_special_offer',
        'type'    => 'text',
    ]);
    
    // زیرعنوان
    $wp_customize->add_setting('gpds_special_offer_subtitle', [
        'default'           => 'تخفیف‌های محدود',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('gpds_special_offer_subtitle', [
        'label'   => 'زیرعنوان',
        'section' => 'gpds_special_offer',
        'type'    => 'text',
    ]);
    
    // لینک "مشاهده همه"
    $wp_customize->add_setting('gpds_special_offer_link', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    
    $wp_customize->add_control('gpds_special_offer_link', [
        'label'       => 'لینک مشاهده همه',
        'description' => 'اگه خالی باشه، به دسته شگفت‌انگیز می‌ره',
        'section'     => 'gpds_special_offer',
        'type'        => 'url',
    ]);
}