/**
 * GP DentaShop - Products Scripts
 * ادغام‌شده: product.js + shop.js
 * 
 * لود شرطی در: is_product() || is_shop() || is_product_category() ...
 *
 * @package GP_DentaShop
 */
(function () {
    'use strict';

    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    document.addEventListener('DOMContentLoaded', function () {
        initGallery();
        initProductTabs();
        initQuantity();
        initFilterGroups();
        initShopView();
        initShopSidebar();
    });

    // ============================================
    // PRODUCT - گالری
    // ============================================
    function initGallery() {
        const mainImg = $('[data-gpds-gallery-main]');
        const thumbs = $$('[data-gpds-gallery-thumb]');
        if (!mainImg || !thumbs.length) return;

        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function () {
                const newSrc = this.dataset.image;
                if (!newSrc) return;

                mainImg.style.opacity = '0.3';
                setTimeout(() => {
                    mainImg.src = newSrc;
                    mainImg.onload = () => { mainImg.style.opacity = '1'; };
                }, 100);

                thumbs.forEach(t => t.classList.remove('is-active'));
                this.classList.add('is-active');
            });
        });
    }

    // ============================================
    // PRODUCT - تب‌ها
    // ============================================
    function initProductTabs() {
        const tabs = $$('[data-gpds-tab]');
        const panels = $$('[data-gpds-tab-panel]');
        if (!tabs.length || !panels.length) return;

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                const key = this.dataset.gpdsTab;
                tabs.forEach(t => t.classList.toggle('is-active', t === this));
                panels.forEach(p => {
                    p.classList.toggle('is-active', p.dataset.gpdsTabPanel === key);
                });
                history.replaceState(null, '', '#tab-' + key);
            });
        });

        const hash = window.location.hash.replace('#tab-', '');
        if (hash) {
            const targetTab = $(`[data-gpds-tab="${hash}"]`);
            if (targetTab) targetTab.click();
        }
    }

    // ============================================
    // PRODUCT - تعداد
    // ============================================
    function initQuantity() {
        $$('[data-gpds-qty]').forEach(qty => {
            const input = $('[data-gpds-qty-input]', qty);
            const minus = $('[data-gpds-qty-minus]', qty);
            const plus = $('[data-gpds-qty-plus]', qty);
            if (!input) return;

            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 9999;

            if (minus) {
                minus.addEventListener('click', function () {
                    const val = parseInt(input.value) || min;
                    if (val > min) input.value = val - 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }

            if (plus) {
                plus.addEventListener('click', function () {
                    const val = parseInt(input.value) || min;
                    if (val < max) input.value = val + 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }

            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            input.addEventListener('blur', function () {
                let val = parseInt(this.value);
                if (isNaN(val) || val < min) val = min;
                if (val > max) val = max;
                this.value = val;
            });
        });
    }

    // ============================================
    // SHOP - فیلتر accordion
    // ============================================
    function initFilterGroups() {
        const btns = $$('[data-gpds-filter-toggle]');
        if (!btns.length) return;

        btns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const group = this.closest('.gpds-filter-group');
                if (group) group.classList.toggle('is-open');
            });
        });

        const firstGroup = $('.gpds-filter-group');
        if (firstGroup && !firstGroup.classList.contains('is-open')) {
            firstGroup.classList.add('is-open');
        }
    }

    // ============================================
    // SHOP - grid/list view
    // ============================================
    function initShopView() {
        const btns = $$('[data-gpds-view]');
        if (!btns.length) return;

        const shop = $('#gpds-shop');
        if (!shop) return;

        const saved = localStorage.getItem('gpds_view') || 'grid';

        function applyView(view) {
            shop.classList.toggle('is-list', view === 'list');
            shop.classList.toggle('is-grid', view === 'grid');
            btns.forEach(b => b.classList.toggle('is-active', b.dataset.gpdsView === view));
        }

        applyView(saved);

        btns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const view = this.dataset.gpdsView;
                applyView(view);
                localStorage.setItem('gpds_view', view);
            });
        });
    }

    // ============================================
    // SHOP - سایدبار موبایل
    // ============================================
    function initShopSidebar() {
        const sidebar = $('[data-gpds-shop-sidebar]');
        const openBtn = $('[data-gpds-shop-sidebar-open]');
        const closeBtn = $('[data-gpds-shop-sidebar-close]');
        if (!sidebar || !openBtn) return;

        function open() {
            sidebar.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function close() {
            sidebar.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        openBtn.addEventListener('click', open);
        if (closeBtn) closeBtn.addEventListener('click', close);

        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('is-open') &&
                !sidebar.contains(e.target) &&
                !openBtn.contains(e.target)) {
                close();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('is-open')) close();
        });
    }

})();