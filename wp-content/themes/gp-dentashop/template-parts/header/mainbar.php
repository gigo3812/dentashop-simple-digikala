<?php
/**
 * Header - Mainbar (ردیف دوم)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-mainbar">
    <div class="gpds-container">
        <div class="gpds-mainbar__inner">
            
            <!-- دکمه دسته‌بندی‌ها -->
            <button 
                type="button" 
                class="gpds-mainbar__categories-btn"
                data-gpds-mega-menu-trigger
                aria-expanded="false"
            >
                <?php gpds_icon('menu', 20); ?>
                <span>دسته‌بندی محصولات</span>
                <?php gpds_icon('chevron-down', 14, 'gpds-mainbar__categories-arrow'); ?>
            </button>
            
            <!-- منوی اصلی -->
            <nav class="gpds-mainbar__nav" role="navigation" aria-label="منوی اصلی">
                <?php gpds_menu('gpds_main_menu', [
                    'menu_class' => 'gpds-main-menu',
                ]); ?>
            </nav>
            
            <!-- اکشن‌های اضافی (سمت چپ) -->
            <div class="gpds-mainbar__extra">
                <a href="#" class="gpds-mainbar__extra-link">
                    <?php gpds_icon('map-pin', 16); ?>
                    <span>شعبه‌ها</span>
                </a>
            </div>
            
        </div>
    </div>
</div>