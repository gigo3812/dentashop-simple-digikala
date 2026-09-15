<?php
/**
 * Single Post - Tags + Author
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$tags = get_the_tags();
?>

<?php if (!empty($tags)) : ?>
    <div class="gpds-post__tags">
        <span class="gpds-post__tags-label">برچسب‌ها:</span>
        <?php foreach ($tags as $tag) : ?>
            <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="gpds-post__tag">
                #<?php echo esc_html($tag->name); ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$author_id = get_the_author_meta('ID');
$bio       = get_the_author_meta('description');
if ($bio) :
    ?>
    <div class="gpds-post__author">
        <div class="gpds-post__author-avatar">
            <?php echo get_avatar($author_id, 64, '', get_the_author(), ['class' => 'gpds-post__author-img']); ?>
        </div>
        <div class="gpds-post__author-info">
            <span class="gpds-post__author-label">نویسنده</span>
            <h4 class="gpds-post__author-name"><?php the_author(); ?></h4>
            <p class="gpds-post__author-bio"><?php echo esc_html($bio); ?></p>
        </div>
    </div>
<?php endif; ?>