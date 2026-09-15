<?php
/**
 * Single Post - Table of Contents
 * 
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// استخراج H2 ها از محتوا
$content = get_the_content();
if (empty($content)) return;

preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches);
if (empty($matches[1])) return;
?>

<nav class="gpds-post__toc" aria-label="فهرست مطالب">
    <h3 class="gpds-post__toc-title">
        <?php gpds_icon('list', 18); ?>
        فهرست مطالب
    </h3>
    <ol class="gpds-post__toc-list">
        <?php foreach ($matches[1] as $index => $title) : ?>
            <li class="gpds-post__toc-item">
                <a href="#heading-<?php echo esc_attr($index + 1); ?>" data-gpds-toc-link>
                    <?php echo esc_html(wp_strip_all_tags($title)); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>