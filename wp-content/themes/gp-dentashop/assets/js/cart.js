/**
 * GP DentaShop - Cart Script
 * مدیریت Mini Cart + Add to Cart (Ajax)
 */
(function() {
    'use strict';
    
    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
    
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof GPDS === 'undefined') return;
        
        initMiniCart();
        initAddToCart();
    });
    
    // ============================================
    // MINI CART
    // ============================================
    function initMiniCart() {
        const miniCart = $('[data-gpds-mini-cart]');
        if (!miniCart) return;
        
        const trigger = $('[data-gpds-cart-trigger]', miniCart);
        const panel   = $('[data-gpds-cart-panel]', miniCart);
        const content = $('[data-gpds-cart-content]', miniCart);
        const loading = $('[data-gpds-cart-loading]', miniCart);
        
        if (!trigger || !panel || !content) return;
        
        let isLoaded = false;
        
        trigger.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = !panel.hasAttribute('hidden');
            
            if (isOpen) {
                closePanel();
            } else {
                openPanel();
                // همیشه دوباره لود کن تا مطمئن باشیم به‌روزه
                await loadCart();
            }
        });
        
        function openPanel() {
            panel.removeAttribute('hidden');
        }
        
        function closePanel() {
            panel.setAttribute('hidden', '');
        }
        
        document.addEventListener('click', function(e) {
            if (!panel.hasAttribute('hidden') && !miniCart.contains(e.target)) {
                closePanel();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePanel();
        });
        
        async function loadCart() {
            if (loading) loading.style.display = 'block';
            content.innerHTML = '';
            
            const formData = new FormData();
            formData.append('action', 'gpds_get_cart');
            formData.append('nonce', GPDS.nonce);
            
            try {
                const response = await fetch(GPDS.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                });
                
                const data = await response.json();
                
                if (loading) loading.style.display = 'none';
                
                if (data.success) {
                    renderCart(data.data, content);
                    isLoaded = true;
                }
            } catch (err) {
                if (loading) loading.style.display = 'none';
                content.innerHTML = `<div class="gpds-search-empty">${GPDS.i18n.error}</div>`;
            }
        }
        
        function renderCart(data, container) {
            const { count, items = [], subtotal, cart_url, checkout_url } = data;
            
            if (count === 0 || items.length === 0) {
                container.innerHTML = `
                    <div class="gpds-cart-empty">
                        <p>${GPDS.i18n.cartEmpty}</p>
                        <a href="${GPDS.homeUrl}" class="gpds-btn gpds-btn--primary gpds-btn--block">
                            شروع خرید
                        </a>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="gpds-cart-items">';
            items.forEach(item => {
                html += `
                    <div class="gpds-cart-item">
                        <img src="${escapeHTML(item.image)}" alt="${escapeHTML(item.title)}" loading="lazy">
                        <div class="gpds-cart-item__info">
                            <div class="gpds-cart-item__title">${escapeHTML(item.title)}</div>
                            <div class="gpds-cart-item__qty">تعداد: ${item.qty}</div>
                            <div class="gpds-cart-item__price">${item.price}</div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            html += `
                <div class="gpds-cart-footer">
                    <div class="gpds-cart-subtotal">
                        <span>مجموع:</span>
                        <strong>${subtotal}</strong>
                    </div>
                    <a href="${escapeHTML(cart_url)}" class="gpds-btn gpds-btn--outline gpds-btn--block">
                        مشاهده سبد خرید
                    </a>
                    <a href="${escapeHTML(checkout_url)}" class="gpds-btn gpds-btn--primary gpds-btn--block">
                        تسویه حساب
                    </a>
                </div>
            `;
            
            container.innerHTML = html;
        }
    }
    
    // ============================================
    // ADD TO CART (Ajax - بدون رفرش)
    // ============================================
    function initAddToCart() {
        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('[data-gpds-add-to-cart]');
            if (!btn) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            if (btn.classList.contains('is-loading')) return;
            
            const productId = btn.dataset.productId;
            if (!productId) return;
            
            btn.classList.add('is-loading');
            
            try {
                // روش امن: ارسال به افزودن سبد ووکامرس با کوکی سشن
                const url = new URL(GPDS.homeUrl);
                url.searchParams.set('add-to-cart', productId);
                url.searchParams.set('quantity', '1');
                
                // Fetch به ووکامرس (پیاده‌سازی سریع و سبک)
                await fetch(url.toString(), {
                    method: 'GET',
                    credentials: 'same-origin',
                });
                
                // به‌روزرسانی تعداد سبد خرید
                await refreshCartCount();
                
                // پیام موفقیت
                showToast(GPDS.i18n.addedToCart);
                
            } catch (err) {
                console.error('[GPDS] Add to cart error:', err);
                showToast(GPDS.i18n.error, 'error');
            } finally {
                btn.classList.remove('is-loading');
            }
        });
    }
    
    // ============================================
    // به‌روزرسانی تعداد سبد خرید
    // ============================================
    async function refreshCartCount() {
        const formData = new FormData();
        formData.append('action', 'gpds_get_cart');
        formData.append('nonce', GPDS.nonce);
        
        try {
            const res = await fetch(GPDS.ajaxUrl, { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.success) {
                $$('[data-gpds-cart-count]').forEach(el => {
                    el.textContent = data.data.count;
                    if (data.data.count > 0) {
                        el.removeAttribute('hidden');
                    } else {
                        el.setAttribute('hidden', '');
                    }
                });
            }
        } catch (err) {
            console.error('[GPDS] Cart refresh error:', err);
        }
    }
    
    // ============================================
    // Toast (پیام کوتاه)
    // ============================================
    function showToast(message, type = 'success') {
        // حذف Toast قبلی
        const existing = $('.gpds-toast');
        if (existing) existing.remove();
        
        const toast = document.createElement('div');
        toast.className = `gpds-toast gpds-toast--${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('is-visible'));
        });
        
        // Auto remove
        setTimeout(() => {
            toast.classList.remove('is-visible');
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
    
    // ============================================
    // HTML Escape
    // ============================================
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }
    
    // ============================================
    // Export برای استفاده در جاهای دیگر
    // ============================================
    window.GPDSCart = {
        refresh: refreshCartCount,
        toast: showToast,
    };
    
})();