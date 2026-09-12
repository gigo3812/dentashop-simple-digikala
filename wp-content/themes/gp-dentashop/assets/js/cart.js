/**
 * GP DentaShop - Cart Script
 * مدیریت Mini Cart
 */
(function() {
    'use strict';
    
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof GPDS === 'undefined') return;
        
        const miniCart = $('[data-gpds-mini-cart]');
        if (!miniCart) return;
        
        const trigger = $('[data-gpds-cart-trigger]', miniCart);
        const panel = $('[data-gpds-cart-panel]', miniCart);
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
                if (!isLoaded) await loadCart();
            }
        });
        
        function openPanel() {
            panel.removeAttribute('hidden');
        }
        
        function closePanel() {
            panel.setAttribute('hidden', '');
        }
        
        document.addEventListener('click', function(e) {
            if (!panel.hasAttribute('hidden') && 
                !miniCart.contains(e.target)) {
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
        
        // ============================================
        // به‌روزرسانی خودکار بعد از افزودن به سبد
        // ============================================
        if (typeof jQuery !== 'undefined') {
            jQuery(document.body).on('added_to_cart', function() {
                isLoaded = false;
                updateCartCount();
            });
        }
        
        async function updateCartCount() {
            const formData = new FormData();
            formData.append('action', 'gpds_get_cart');
            formData.append('nonce', GPDS.nonce);
            
            try {
                const response = await fetch(GPDS.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                });
                const data = await response.json();
                
                if (data.success) {
                    const countEl = $('[data-gpds-cart-count]');
                    if (countEl) {
                        countEl.textContent = data.data.count;
                        if (data.data.count > 0) {
                            countEl.removeAttribute('hidden');
                        } else {
                            countEl.setAttribute('hidden', '');
                        }
                    }
                }
            } catch (err) {}
        }
        
    });
    
    function escapeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    
})();