/**
 * GP DentaShop - Product Page Scripts
 * گالری، تب‌ها، تعداد
 */
(function() {
    'use strict';
    
    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
    
    document.addEventListener('DOMContentLoaded', function() {
        initGallery();
        initProductTabs();
        initQuantity();
        initFilterGroups();
        initShopView();
        initShopSidebar();
    });
    
    // ============================================
    // گالری محصول
    // ============================================
    function initGallery() {
        const mainImg = $('[data-gpds-gallery-main]');
        const thumbs  = $$('[data-gpds-gallery-thumb]');
        
        if (!mainImg || !thumbs.length) return;
        
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const newSrc = this.dataset.image;
                if (!newSrc) return;
                
                // Fade out → تغییر → fade in
                mainImg.style.opacity = '0.3';
                
                setTimeout(() => {
                    mainImg.src = newSrc;
                    mainImg.onload = () => {
                        mainImg.style.opacity = '1';
                    };
                }, 100);
                
                // Active class
                thumbs.forEach(t => t.classList.remove('is-active'));
                this.classList.add('is-active');
            });
        });
    }
    
    // ============================================
    // تب‌های محصول
    // ============================================
    function initProductTabs() {
        const tabs   = $$('[data-gpds-tab]');
        const panels = $$('[data-gpds-tab-panel]');
        
        if (!tabs.length || !panels.length) return;
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const key = this.dataset.gpdsTab;
                
                tabs.forEach(t => t.classList.toggle('is-active', t === this));
                panels.forEach(p => {
                    p.classList.toggle('is-active', p.dataset.gpdsTabPanel === key);
                });
                
                // آپدیت URL Hash
                history.replaceState(null, '', '#tab-' + key);
            });
        });
        
        // باز کردن تب از Hash
        const hash = window.location.hash.replace('#tab-', '');
        if (hash) {
            const targetTab = $(`[data-gpds-tab="${hash}"]`);
            if (targetTab) targetTab.click();
        }
    }
    
    // ============================================
    // تعداد محصول (+ / -)
    // ============================================
    function initQuantity() {
        $$('[data-gpds-qty]').forEach(qty => {
            const input  = $('[data-gpds-qty-input]', qty);
            const minus  = $('[data-gpds-qty-minus]', qty);
            const plus   = $('[data-gpds-qty-plus]', qty);
            
            if (!input) return;
            
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 9999;
            
            if (minus) {
                minus.addEventListener('click', function() {
                    const val = parseInt(input.value) || min;
                    if (val > min) input.value = val - 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
            
            if (plus) {
                plus.addEventListener('click', function() {
                    const val = parseInt(input.value) || min;
                    if (val < max) input.value = val + 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
            
            // فقط عدد
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            input.addEventListener('blur', function() {
                let val = parseInt(this.value);
                if (isNaN(val) || val < min) val = min;
                if (val > max) val = max;
                this.value = val;
            });
        });
    }
    
    // ============================================
    // فیلتر - باز/بسته
    // ============================================
    function initFilterGroups() {
        $$('[data-gpds-filter-toggle]').forEach(btn => {
            btn.addEventListener('click', function() {
                const group = this.closest('.gpds-filter-group');
                if (!group) return;
                group.classList.toggle('is-open');
            });
        });
        
        // باز کردن گروه اول به صورت پیش‌فرض
        const firstGroup = $('.gpds-filter-group');
        if (firstGroup) firstGroup.classList.add('is-open');
    }
    
    // ============================================
    // تغییر نمایش (grid/list)
    // ============================================
    function initShopView() {
        const btns = $$('[data-gpds-view]');
        if (!btns.length) return;
        
        // خواندن از localStorage
        const saved = localStorage.getItem('gpds_view') || 'grid';
        applyView(saved);
        
        btns.forEach(btn => {
            btn.addEventListener('click', function() {
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
        
        openBtn.addEventListener('click', () => {
            sidebar.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        });
        
        if (closeBtn) {
            closeBtn.addEventListener('click', close);
        }
        
        // کلیک بیرون
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('is-open') && 
                !sidebar.contains(e.target) && 
                !openBtn.contains(e.target)) {
                close();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') close();
        });
        
        function close() {
            sidebar.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }
    
})();