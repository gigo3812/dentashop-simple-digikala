<?php
/**
 * Header - Mega Menu Panel
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$top_categories = gpds_get_top_product_categories(12);

if (empty($top_categories)) {
    return;
}
?>

<aside 
    class="gpds-mega-menu" 
    data-gpds-mega-menu 
    hidden
    aria-hidden="true"
>
    <div class="gpds-mega-menu__inner">
        
        <!-- ستون سمت راست: دسته‌بندی‌ها -->
        <div class="gpds-mega-menu__sidebar">
            <ul class="gpds-mega-menu__list" role="menu">
                <?php foreach ($top_categories as $cat) : 
                    $children = gpds_get_child_categories($cat->term_id, 6);
                    $has_children = !empty($children);
                ?>
                    <li class="gpds-mega-menu__item <?php echo $has_children ? 'has-children' : ''; ?>" 
                        data-gpds-mm-item="<?php echo esc_attr($cat->term_id); ?>">
                        
                        <a 
                            href="<?php echo esc_url(get_term_link($cat)); ?>"
                            class="gpds-mega-menu__link"
                        >
                            <?php gpds_icon('category', 18); ?>
                            <span><?php echo esc_html($cat->name); ?></span>
                            <?php if ($has_children) : ?>
                                <?php gpds_icon('chevron-left', 14, 'gpds-mega-menu__arrow'); ?>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <!-- ستون چپ: محتوای دسته انتخاب‌شده -->
        <div class="gpds-mega-menu__content" data-gpds-mm-content>
            <?php foreach ($top_categories as $cat) : 
                $children = gpds_get_child_categories($cat->term_id, 12);
                if (empty($children)) continue;
            ?>
                <div 
                    class="gpds-mega-menu__panel" 
                    data-gpds-mm-panel="<?php echo esc_attr($cat->term_id); ?>"
                    hidden
                >
                    <div class="gpds-mega-menu__panel-title">
                        <?php echo esc_html($cat->name); ?>
                    </div>
                    <ul class="gpds-mega-menu__sub-list">
                        <?php foreach ($children as $child) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_term_link($child)); ?>">
                                    <?php echo esc_html($child->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a 
                        href="<?php echo esc_url(get_term_link($cat)); ?>" 
                        class="gpds-mega-menu__all"
                    >
                        مشاهده همه <?php gpds_icon('arrow-left', 14); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
    </div>
</aside>