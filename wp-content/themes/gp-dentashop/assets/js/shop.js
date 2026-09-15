/**
 * GP DentaShop - Shop / Archive Scripts
 * 
 * فقط توی صفحات آرشیو (shop, category, brand) لود می‌شه
 * 
 * مسئولیت‌ها:
 *   - فیلترها (باز/بست accordion)
 *   - سایدبار موبایل
 *   - تغییر حالت نمایش (grid/list)
 *
 * @package GP_DentaShop
 */

(function() {
    'use strict';
    
    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
    
    document.addEventListener('DOMContentLoaded', function() {
        initFilterGroups();
        initShopView();
        initShopSidebar();
    });
    
    // ============================================
    // فیلتر - باز/بسته (accordion)
    // ============================================
    function initFilterGroups() {
        const btns = $$('[data-gpds-filter-toggle]');
        if (!btns.length) return;
        
        btns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const group = this.closest('.gpds-filter-group');
                if (!group) return;
                group.classList.toggle('is-open');
            });
        });
        
        // باز کردن گروه اول به صورت پیش‌فرض
        const firstGroup = $('.gpds-filter-group');
        if (firstGroup && !firstGroup.classList.contains('is-open')) {
            firstGroup.classList.add('is-open');
        }
    }
    
    // ============================================
    // تغییر نمایش (grid/list)
    // ============================================
    function initShopView() {
        const btns = $$('[data-gpds-view]');
        if (!btns.length) return;
        
        const saved = localStorage.getItem('gpds_view') || 'grid';
        applyView(saved);
        
        btns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const view = this.dataset.gpdsView;
                applyView(view);
                localStorage.setItem('gpds_view', view);
            });
        });
        
        function applyView(view) {
            const shop = $('#gpds-shop');
            if (!shop) return;
            
            shop.classList.toggle('is-list', view === 'list');
            shop.classList.toggle('is-grid', view === 'grid');
            
            btns.forEach(b => b.classList.toggle('is-active', b.dataset.gpdsView === view));
        }
    }
    
    // ============================================
    // سایدبار موبایل
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
        
        // کلیک بیرون
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('is-open') && 
                !sidebar.contains(e.target) && 
                !openBtn.contains(e.target)) {
                close();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('is-open')) close();
        });
    }
    
})();