<?php
/**
 * Footer Module
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;

// Override فوتر GeneratePress
add_action('after_setup_theme', function() {
    remove_action('generate_footer', 'generate_construct_footer');
    add_action('generate_footer', 'gpds_render_footer');
}, 20);

function gpds_render_footer() {
    // اگه صفحه blank هست
    if (is_page_template('page-templates/template-blank.php')) {
        return;
    }
    ?>
    <footer id="gpds-footer" class="gpds-footer" role="contentinfo" style="position: relative; z-index: 9999999;">
        <?php
        get_template_part('template-parts/footer/top');
        get_template_part('template-parts/footer/services');
        get_template_part('template-parts/footer/links');
        get_template_part('template-parts/footer/bottom');
        ?>
    </footer>
    <?php
}