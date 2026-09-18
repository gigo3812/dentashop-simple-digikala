/**
 * GP DentaShop - Cart Script
 * Mini Cart + Add to Cart + Cart Page Enhancements
 *
 * @package GP_DentaShop
 */
(function () {
    'use strict';

    // ============================================
    // HELPERS
    // ============================================
    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    const escapeEl = document.createElement('div');
    const escapeHTML = (str) => {
        if (str === null || str === undefined) return '';
        escapeEl.textContent = String(str);
        return escapeEl.innerHTML;
    };

    const readQtyConfig = (input) => ({
        val:  parseFloat(input.value) || 0,
        min:  parseFloat(input.getAttribute('min')),
        max:  parseFloat(input.getAttribute('max')),
        step: parseFloat(input.getAttribute('step')) || 1,
    });

    const withLoading = async (el, task) => {
        if (el) el.classList.add('is-loading');
        try {
            return await task();
        } finally {
            if (el) el.classList.remove('is-loading');
        }
    };

    // ============================================
    // BOOT
    // ============================================
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof GPDS === 'undefined') return;

        initMiniCart();
        initAddToCart();
        initCartPage();
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

        const isOpen = () => !panel.hasAttribute('hidden');
        const open   = () => panel.removeAttribute('hidden');
        const close  = () => panel.setAttribute('hidden', '');

        trigger.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (isOpen()) {
                close();
            } else {
                open();
                await loadCart();
            }
        });

        document.addEventListener('click', (e) => {
            if (isOpen() && !miniCart.contains(e.target)) close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });

        async function loadCart() {
            if (loading) loading.style.display = 'block';
            content.innerHTML = '';

            const body = new FormData();
            body.append('action', 'gpds_get_cart');
            body.append('nonce', GPDS.nonce);

            try {
                const res  = await fetch(GPDS.ajaxUrl, { method: 'POST', body });
                const data = await res.json();

                if (data.success) {
                    renderCart(data.data, content);
                }
            } catch (err) {
                console.error('[GPDS] Mini cart error:', err);
                content.innerHTML = `<div class="gpds-search-empty">${GPDS.i18n.error}</div>`;
            } finally {
                if (loading) loading.style.display = 'none';
            }
        }

        function renderCart(data, container) {
            const { count, items = [], subtotal, cart_url, checkout_url } = data;

            if (!count || items.length === 0) {
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

            const itemsHTML = items.map((item) => `
                <div class="gpds-cart-item">
                    <img src="${escapeHTML(item.image)}" alt="${escapeHTML(item.title)}" loading="lazy">
                    <div class="gpds-cart-item__info">
                        <div class="gpds-cart-item__title">${escapeHTML(item.title)}</div>
                        <div class="gpds-cart-item__qty">تعداد: ${item.qty}</div>
                        <div class="gpds-cart-item__price">${item.price}</div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = `
                <div class="gpds-cart-items">${itemsHTML}</div>
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
        }
    }

    // ============================================
    // ADD TO CART
    // ============================================
    function initAddToCart() {
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-gpds-add-to-cart]');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            if (btn.classList.contains('is-loading')) return;

            const productId = btn.dataset.productId;
            if (!productId) return;

            await withLoading(btn, async () => {
                try {
                    const url = new URL(GPDS.homeUrl);
                    url.searchParams.set('add-to-cart', productId);
                    url.searchParams.set('quantity', '1');

                    await fetch(url.toString(), {
                        method: 'GET',
                        credentials: 'same-origin',
                    });

                    await refreshCartCount();
                    showToast(GPDS.i18n.addedToCart);
                } catch (err) {
                    console.error('[GPDS] Add to cart error:', err);
                    showToast(GPDS.i18n.error, 'error');
                }
            });
        });
    }

    // ============================================
    // CART PAGE
    // ============================================
    function initCartPage() {
        const cartPage = $('.gpds-cart-page');
        if (!cartPage) return;

        injectQtyButtons(cartPage);
        cleanEmptyNotices(cartPage);

        // Delegated listener
        cartPage.addEventListener('click', (e) => {
            const qtyBtn = e.target.closest('.gpds-qty-btn');
            if (qtyBtn) {
                e.preventDefault();
                handleQtyClick(cartPage, qtyBtn);
                return;
            }

            const removeLink = e.target.closest('.product-remove a.remove');
            if (removeLink) {
                e.preventDefault();
                handleRemoveClick(cartPage, removeLink);
            }
        });

        // فقط عدد در input
        cartPage.addEventListener('input', (e) => {
            const input = e.target;
            if (!input.matches('.gpds-qty-wrapper input.qty')) return;
            
            input.value = input.value.replace(/[^0-9]/g, '');
            updateQtyButtons(input.closest('.gpds-qty-wrapper'));
        });
    }

    // ============================================
    // INJECT QTY BUTTONS (+ / −)
    // ============================================
    function injectQtyButtons(scope) {
        $$('.woocommerce-cart-form .quantity', scope).forEach((qty) => {
            // اگه قبلاً wrapper ساخته شده، رد کن
            if (qty.querySelector('.gpds-qty-wrapper')) return;

            const input = $('input.qty', qty);
            if (!input) return;

            // ساخت wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'gpds-qty-wrapper';

            // دکمه کاهش
            const minus = document.createElement('button');
            minus.type = 'button';
            minus.className = 'gpds-qty-btn minus';
            minus.setAttribute('aria-label', 'کاهش تعداد');
            minus.textContent = '−';

            // دکمه افزایش
            const plus = document.createElement('button');
            plus.type = 'button';
            plus.className = 'gpds-qty-btn plus';
            plus.setAttribute('aria-label', 'افزایش تعداد');
            plus.textContent = '+';

            // انتقال input به wrapper
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(minus);
            wrapper.appendChild(input);
            wrapper.appendChild(plus);

            // وضعیت اولیه
            updateQtyButtons(wrapper);
        });
    }

    /**
     * آپدیت disabled state دکمه‌ها
     */
    function updateQtyButtons(wrapper) {
        if (!wrapper) return;

        const input = $('input.qty', wrapper);
        if (!input) return;

        const minusBtn = $('.gpds-qty-btn.minus', wrapper);
        const plusBtn  = $('.gpds-qty-btn.plus', wrapper);

        const { val, min, max } = readQtyConfig(input);

        if (minusBtn) {
            minusBtn.disabled = !isNaN(min) && val <= min;
        }
        if (plusBtn) {
            plusBtn.disabled = !isNaN(max) && max > 0 && val >= max;
        }
    }

    /**
     * حذف notices خالی
     */
    function cleanEmptyNotices(scope) {
        const notices = $('.woocommerce > .woocommerce-notices-wrapper', scope);
        if (!notices) return;

        if (notices.children.length === 0 && notices.textContent.trim() === '') {
            notices.remove();
        }
    }

    // ============================================
    // QTY HANDLER
    // ============================================
    function handleQtyClick(scope, btn) {
        const wrapper = btn.closest('.gpds-qty-wrapper');
        if (!wrapper) return;

        if (wrapper.classList.contains('is-loading')) return;

        const input = $('input.qty', wrapper);
        if (!input) return;

        const { val, min, max, step } = readQtyConfig(input);
        const isPlus = btn.classList.contains('plus');
        const effectiveMin = isNaN(min) ? 0 : min;

        let newVal = val;

        if (isPlus) {
            if (!isNaN(max) && val + step > max) return;
            newVal = val + step;
        } else {
            const next = val - step;

            if (next < effectiveMin) {
                // رسیدن به زیر حد مجاز
                if (effectiveMin > 0 && val <= effectiveMin) {
                    // حذف محصول
                    removeCartItem(scope, input, wrapper);
                    return;
                }
                if (effectiveMin === 0 && next <= 0) {
                    // تعداد به صفر → حذف
                    input.value = 0;
                    removeCartItem(scope, input, wrapper);
                    return;
                }
                newVal = effectiveMin;
            } else {
                newVal = next;
            }
        }

        if (newVal === val) return;

        input.value = newVal;
        updateQtyButtons(wrapper);
        updateCart(scope, wrapper);
    }

    // ============================================
    // REMOVE HANDLER
    // ============================================
    function handleRemoveClick(scope, link) {
        const removeUrl = link.getAttribute('href');
        if (!removeUrl) return;
        if (removeUrl === '#' || removeUrl.startsWith('javascript:')) return;

        // loading روی کل row
        const row = link.closest('.woocommerce-cart-form__cart-item');
        if (row) row.classList.add('is-loading');

        // ۱. حذف
        fetch(removeUrl, {
            method: 'GET',
            credentials: 'same-origin',
        })
        .then(() => {
            // ۲. گرفتن HTML جدید
            return fetch(window.location.href, { credentials: 'same-origin' });
        })
        .then((r) => r.text())
        .then((html) => {
            replaceCartDOM(scope, html);
        })
        .catch((err) => {
            console.error('[GPDS] Remove item error:', err);
            showToast(GPDS.i18n.error, 'error');
            if (row) row.classList.remove('is-loading');
        });
    }

    /**
     * حذف محصول (وقتی تعداد به صفر می‌رسه)
     */
    function removeCartItem(scope, input, wrapper) {
        const row = input.closest('.woocommerce-cart-form__cart-item');
        const removeLink = row ? row.querySelector('.product-remove a.remove') : null;

        if (removeLink && removeLink.getAttribute('href')) {
            handleRemoveClick(scope, removeLink);
            return;
        }

        // fallback
        input.value = 0;
        updateCart(scope, wrapper);
    }

    // ============================================
    // AJAX UPDATE
    // ============================================
    function updateCart(scope, wrapper) {
        const form = $('.woocommerce-cart-form', scope);
        if (!form) return;

        // loading روی wrapper
        if (wrapper) wrapper.classList.add('is-loading');

        const formData = new FormData(form);
        formData.append('update_cart', '1');

        // فعال کردن دکمه بروزرسانی
        const updateBtn = $('button[name="update_cart"]', form);
        if (updateBtn) updateBtn.disabled = false;

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
        .then((r) => r.text())
        .then((html) => {
            replaceCartDOM(scope, html);
        })
        .catch((err) => {
            console.error('[GPDS] Cart update error:', err);
            showToast(GPDS.i18n.error, 'error');
            if (wrapper) wrapper.classList.remove('is-loading');
        });
    }

    // ============================================
    // DOM REPLACEMENT
    // ============================================
    function replaceCartDOM(scope, html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        // ذخیره focus (اگه روی input بوده)
        const activeEl = document.activeElement;
        const activeName = activeEl && activeEl.matches('.gpds-qty-wrapper input.qty')
            ? activeEl.name
            : null;

        swapElement(scope, '.woocommerce-cart-form', doc);
        swapElement(scope, '.cart_totals', doc);
        swapElement(scope, '.woocommerce-notices-wrapper', doc, {
            parentSelector: '.woocommerce',
            insertAsFirst: true,
        });

        // Re-init
        injectQtyButtons(scope);
        cleanEmptyNotices(scope);

        // بازگرداندن focus
        if (activeName) {
            const newInput = $(`input[name="${activeName}"]`, scope);
            if (newInput) newInput.focus();
        }

        // Sync mini cart
        refreshCartCount();

        // رویداد
        document.body.dispatchEvent(new Event('updated_cart_totals', { bubbles: true }));
    }

    /**
     * جایگزینی یک المان با نسخه جدید
     */
    function swapElement(scope, selector, doc, opts = {}) {
        const { parentSelector = null, insertAsFirst = false } = opts;

        const newEl = doc.querySelector(selector);
        if (!newEl) return;

        const searchCtx = parentSelector ? $(parentSelector, scope) : scope;
        if (!searchCtx) return;

        const oldEl = searchCtx.querySelector(selector);

        if (oldEl) {
            oldEl.replaceWith(newEl);
        } else if (insertAsFirst) {
            searchCtx.insertBefore(newEl, searchCtx.firstChild);
        }
    }

    // ============================================
    // REFRESH CART COUNT
    // ============================================
    async function refreshCartCount() {
        const body = new FormData();
        body.append('action', 'gpds_get_cart');
        body.append('nonce', GPDS.nonce);

        try {
            const res  = await fetch(GPDS.ajaxUrl, { method: 'POST', body });
            const data = await res.json();

            if (!data.success) return;

            const count = data.data.count;
            $$('[data-gpds-cart-count]').forEach((el) => {
                el.textContent = count;
                el.toggleAttribute('hidden', count <= 0);
            });
        } catch (err) {
            console.error('[GPDS] Cart refresh error:', err);
        }
    }

    // ============================================
    // TOAST
    // ============================================
    function showToast(message, type = 'success') {
        const existing = $('.gpds-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `gpds-toast gpds-toast--${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('is-visible'));
        });

        setTimeout(() => {
            toast.classList.remove('is-visible');
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    // ============================================
    // PUBLIC API
    // ============================================
    window.GPDSCart = {
        refresh: refreshCartCount,
        toast:   showToast,
    };

})();