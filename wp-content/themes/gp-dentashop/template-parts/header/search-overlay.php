<?php
/**
 * Header - Search Overlay (Mobile)
 *
 * @package GP_DentaShop
 */

if (!defined('ABSPATH')) exit;
?>

<div class="gpds-search-overlay" data-gpds-search-overlay hidden aria-hidden="true">
    <div class="gpds-search-overlay__inner">
        
        <form 
            role="search" 
            method="get" 
            action="<?php echo esc_url(home_url('/')); ?>" 
            class="gpds-search-form gpds-search-form--mobile"
            autocomplete="off"
        >
            <div class="gpds-search-form__input-wrap">
                <?php gpds_icon('search', 20, 'gpds-search-form__icon'); ?>
                <input 
                    type="search" 
                    name="s" 
                    class="gpds-input gpds-input--search gpds-search-form__input"
                    placeholder="جستجو..."
                    aria-label="جستجو"
                    data-gpds-search
                >
                <input type="hidden" name="post_type" value="product">
                
                <button 
                    type="button" 
                    class="gpds-search-overlay__close"
                    data-gpds-search-overlay-close
                    aria-label="بستن"
                >
                    <?php gpds_icon('close', 20); ?>
                </button>
            </div>
            
            <div class="gpds-search-results" data-gpds-search-results hidden>
                <div class="gpds-search-results__loading">
                    <span class="gpds-spin"><?php gpds_icon('loader', 20); ?></span>
                </div>
                <div class="gpds-search-results__body" data-gpds-search-body></div>
            </div>
        </form>
        
    </div>
</div>