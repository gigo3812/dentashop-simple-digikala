<?php
/**
 * 404 Template - Fallback
 * 
 * این فایل الزاماً باید توی ریشه باشه (الزام وردپرس)
 * فقط redirect می‌کنه به تمپلیت اصلی توی inc/
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// لود تمپلیت اصلی
$template = GPDS_INC . '/templates/404.php';

if (file_exists($template)) {
    include $template;
    return;
}

// fallback نهایی
get_header();
?>

<main id="gpds-main" class="gpds-main">
    <div class="gpds-container">
        <h1>صفحه یافت نشد</h1>
        <p>متأسفانه صفحه‌ای که دنبالش بودید وجود ندارد.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="gpds-btn gpds-btn--primary">
            بازگشت به خانه
        </a>
    </div>
</main>

<?php get_footer();