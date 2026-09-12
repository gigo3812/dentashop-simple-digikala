/**
 * GP DentaShop - Main Script
 * مدیریت عمومی: Dropdown, Mega Menu, Mobile Menu
 */
(function() {
    'use strict';
    
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
    
    document.addEventListener('DOMContentLoaded', function() {
        document.documentElement.classList.add('gpds-js-ready');
        
        initDropdowns();
        initMegaMenu();
        initMobileMenu();
        initSearchOverlay();
    });
    
    // ============================================
    // Dropdown (کاربر و ...)
    // ============================================
    function initDropdowns() {
        $$('[data-gpds-dropdown]').forEach(dropdown => {
            const trigger = $('[data-gpds-dropdown-trigger]', dropdown);
            if (!trigger) return;
            
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropdown.classList.toggle('is-open');
            });
        });
        
        document.addEventListener('click', function(e) {
            $$('.gpds-dropdown.is-open').forEach(dd => {
                if (!dd.contains(e.target)) dd.classList.remove('is-open');
            });
        });
    }
    
    // ============================================
    // Mega Menu
    // ============================================
    function initMegaMenu() {
        const trigger = $('[data-gpds-mega-menu-trigger]');
        const menu = $('[data-gpds-mega-menu]');
        if (!trigger || !menu) return;
        
        const items = $$('[data-gpds-mm-item]', menu);
        const panels = $$('[data-gpds-mm-panel]', menu);
        
        // باز/بسته کردن
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = !menu.hasAttribute('hidden');
            
            if (isOpen) {
                closeMegaMenu();
            } else {
                openMegaMenu();
            }
        });
        
        function openMegaMenu() {
            menu.removeAttribute('hidden');
            menu.setAttribute('aria-hidden', 'false');
            trigger.setAttribute('aria-expanded', 'true');
            
            // فعال‌سازی اولین آیتم
            if (items.length > 0 && panels.length > 0) {
                activateItem(items[0].dataset.gpdsMmItem);
            }
        }
        
        function closeMegaMenu() {
            menu.setAttribute('hidden', '');
            menu.setAttribute('aria-hidden', 'true');
            trigger.setAttribute('aria-expanded', 'false');
        }
        
        // تغییر بین دسته‌ها
        items.forEach(item => {
            const id = item.dataset.gpdsMmItem;
            
            item.addEventListener('mouseenter', () => activateItem(id));
            item.addEventListener('focus', () => activateItem(id));
        });
        
        function activateItem(id) {
            items.forEach(it => it.classList.toggle('is-active', it.dataset.gpdsMmItem === id));
            panels.forEach(p => {
                if (p.dataset.gpdsMmPanel === id) {
                    p.removeAttribute('hidden');
                } else {
                    p.setAttribute('hidden', '');
                }
            });
        }
        
        // بستن با کلیک بیرون
        document.addEventListener('click', function(e) {
            if (!menu.hasAttribute('hidden') && 
                !menu.contains(e.target) && 
                !trigger.contains(e.target)) {
                closeMegaMenu();
            }
        });
        
        // بستن با Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !menu.hasAttribute('hidden')) {
                closeMegaMenu();
            }
        });
    }
    
    // ============================================
    // Mobile Menu
    // ============================================
    function initMobileMenu() {
        const trigger = $('[data-gpds-mobile-menu-trigger]');
        const menu = $('[data-gpds-mobile-menu]');
        const overlay = $('[data-gpds-mobile-menu-overlay]');
        const closeBtn = $('[data-gpds-mobile-menu-close]');
        
        if (!trigger || !menu) return;
        
        function open() {
            menu.removeAttribute('hidden');
            menu.setAttribute('aria-hidden', 'false');
            if (overlay) overlay.removeAttribute('hidden');
            document.body.style.overflow = 'hidden';
            
            requestAnimationFrame(() => menu.classList.add('is-open'));
        }
        
        function close() {
            menu.classList.remove('is-open');
            if (overlay) overlay.setAttribute('hidden', '');
            document.body.style.overflow = '';
            
            setTimeout(() => {
                menu.setAttribute('hidden', '');
                menu.setAttribute('aria-hidden', 'true');
            }, 250);
        }
        
        trigger.addEventListener('click', open);
        if (closeBtn) closeBtn.addEventListener('click', close);
        if (overlay) overlay.addEventListener('click', close);
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !menu.hasAttribute('hidden')) close();
        });
    }
    
    // ============================================
    // Search Overlay (Mobile)
    // ============================================
    function initSearchOverlay() {
        const trigger = $('[data-gpds-search-overlay-trigger]');
        const overlay = $('[data-gpds-search-overlay]');
        const closeBtn = $('[data-gpds-search-overlay-close]');
        
        if (!trigger || !overlay) return;
        
        trigger.addEventListener('click', function() {
            overlay.removeAttribute('hidden');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            
            setTimeout(() => {
                const input = $('input[data-gpds-search]', overlay);
                if (input) input.focus();
            }, 100);
        });
        
        function close() {
            overlay.setAttribute('hidden', '');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
        
        if (closeBtn) closeBtn.addEventListener('click', close);
        
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) close();
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !overlay.hasAttribute('hidden')) close();
        });
    }
    
})();