<?php
/**
 * Home - Stories Section
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

$stories = gpds_get_active_stories(10);

if (empty($stories)) {
    return;
}

// 🎯 Preload اولین تصویر استوری (برای LCP)
if (!empty($stories[0]['image'])) {
    add_action('wp_head', function() use ($stories) {
        printf(
            '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
            esc_url($stories[0]['image'])
        );
    }, 1);
}

// 🎯 رنگ‌ها برای inline style
$color_map = [
    'default' => 'linear-gradient(135deg, #ef4056 0%, #f9a825 50%, #00bfd0 100%)',
    'red'     => '#ef4056',
    'blue'    => '#3b82f6',
    'green'   => '#10b981',
    'purple'  => '#8b5cf6',
    'gold'    => '#f9a825',
    'pink'    => '#ec4899',
    'dark'    => '#1f2937',
];
?>

<section class="gpds-section gpds-stories-section" aria-label="استوری‌ها">
    <div class="gpds-container">
        <div class="gpds-card gpds-stories">
            
            <div class="gpds-stories-scroll">
                <?php foreach ($stories as $i => $story) : 
                    $color_css = $color_map[$story['color']] ?? $color_map['default'];
                ?>
                    <a 
                        href="#"
                        class="gpds-story"
                        data-gpds-story
                        data-story-index="<?php echo esc_attr($i); ?>"
                        data-story-image="<?php echo esc_url($story['image_full']); ?>"
                        data-story-thumb="<?php echo esc_url($story['image']); ?>"
                        data-story-title="<?php echo esc_attr($story['title']); ?>"
                        data-story-url="<?php echo esc_url($story['url']); ?>"
                        data-story-new-tab="<?php echo $story['new_tab'] ? '1' : '0'; ?>"
                        <?php if (!empty($story['video'])) : ?>
                            data-story-video="<?php echo esc_url($story['video']); ?>"
                            data-story-video-mime="<?php echo esc_attr($story['video_mime']); ?>"
                            data-story-video-duration="<?php echo esc_attr($story['video_duration']); ?>"
                        <?php endif; ?>
                        aria-label="مشاهده استوری: <?php echo esc_attr($story['title']); ?>"
                    >
                       


                    <span class="gpds-story__ring" style="background: <?php echo esc_attr($color_css); ?>;">
                        <svg class="gpds-story__progress" viewBox="0 0 100 100" aria-hidden="true">
                            <circle cx="50" cy="50" r="48" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="2"
                                    stroke-dasharray="301.59" stroke-dashoffset="301.59"
                                    transform="rotate(-90 50 50)" pathLength="100" />
                        </svg>
                        
                        <span class="gpds-story__image">
                            <img src="<?php echo esc_url($story['image']); ?>"
                                alt="<?php echo esc_attr($story['title']); ?>"
                                width="120" height="120"
                                loading="<?php echo $i < 3 ? 'eager' : 'lazy'; ?>"
                                decoding="async">
                            
                            <?php if (!empty($story['video'])) : ?>
                                <span class="gpds-story__video-badge" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                        <polygon points="5 3 19 12 5 21 5 3"/>
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </span>
                    </span>





                    <span class="gpds-story__title">
                        <?php echo esc_html($story['title']); ?>
                    </span>
                    </a>
                <?php endforeach; ?>
            </div>
            
        </div>
    </div>
</section>