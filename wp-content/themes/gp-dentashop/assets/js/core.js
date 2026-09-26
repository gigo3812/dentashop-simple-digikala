/**
 * GP DentaShop - Core Scripts
 * 
 * ادغام‌شده:
 *   - main.js   (Loader, Dropdown, MegaMenu, MobileMenu, SearchOverlay)
 *   - footer.js (BackToTop, Newsletter)
 *   - cart.js   (MiniCart, AddToCart, CartPage)
 *   - search.js (Live Search)
 *
 * @package GP_DentaShop
 */
(function () {
    'use strict';

    // ============================================
    // HELPERS مشترک (یک‌بار تعریف)
    // ============================================
    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    const escapeEl = document.createElement('div');
    const escapeHTML = (str) => {
        if (str === null || str === undefined) return '';
        escapeEl.textContent = String(str);
        return escapeEl.innerHTML;
    };

    const withLoading = async (el, task) => {
        if (el) el.classList.add('is-loading');
        try {
            return await task();
        } finally {
            if (el) el.classList.remove('is-loading');
        }
    };

    const showToast = (message, type = 'success') => {
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
    };

    const fallbackCopy = (text, successMsg = 'کپی شد ✓') => {
        const input = document.createElement('textarea');
        input.value = text;
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        try {
            document.execCommand('copy');
            showToast(successMsg);
        } catch (e) {
            showToast('کپی نشد', 'error');
        }
        document.body.removeChild(input);
    };

    // ============================================
    // 🎯 Loading Screen
    // ============================================
    (function initLoader() {
        const loader = document.getElementById('gpds-loader');
        if (!loader) return;

        loader._gpdsStart = performance.now();
        loader.classList.add('is-loading');

        function hideLoader() {
            if (!loader || loader._gpdsHidden) return;
            loader._gpdsHidden = true;

            const elapsed = performance.now() - (loader._gpdsStart || 0);
            const remaining = Math.max(0, 300 - elapsed);

            setTimeout(() => {
                loader.classList.remove('is-loading');
                loader.classList.add('is-hidden');

                setTimeout(() => {
                    if (loader.parentNode) loader.parentNode.removeChild(loader);
                }, 500);
            }, remaining);
        }

        function setupStrategy() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    requestAnimationFrame(() => setTimeout(hideLoader, 100));
                });
            } else if (document.readyState === 'interactive') {
                setTimeout(hideLoader, 100);
            } else {
                hideLoader();
            }

            window.addEventListener('load', () => {
                if (!loader._gpdsHidden) setTimeout(hideLoader, 200);
            });

            setTimeout(() => {
                if (!loader._gpdsHidden) hideLoader();
            }, 3000);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupStrategy);
        } else {
            setupStrategy();
        }
    })();

    // ============================================
    // BOOT
    // ============================================
    document.addEventListener('DOMContentLoaded', function () {
        document.documentElement.classList.add('gpds-js-ready');

        initDropdowns();
        initMegaMenu();
        initMobileMenu();
        initSearchOverlay();
        initBackToTop();
        initNewsletter();

        if (typeof GPDS !== 'undefined') {
            initMiniCart();
            initAddToCart();
            initCartPage();
            initLiveSearch();
        }
    });

    // ============================================
    // DROPDOWN
    // ============================================
    function initDropdowns() {
        $$('[data-gpds-dropdown]').forEach(dropdown => {
            const trigger = $('[data-gpds-dropdown-trigger]', dropdown);
            if (!trigger) return;

            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropdown.classList.toggle('is-open');
            });
        });

        document.addEventListener('click', function (e) {
            $$('.gpds-dropdown.is-open').forEach(dd => {
                if (!dd.contains(e.target)) dd.classList.remove('is-open');
            });
        });
    }

    // ============================================
    // MEGA MENU
    // ============================================
    function initMegaMenu() {
        const trigger = $('[data-gpds-mega-menu-trigger]');
        const menu = $('[data-gpds-mega-menu]');
        if (!trigger || !menu) return;

        const items = $$('[data-gpds-mm-item]', menu);
        const panels = $$('[data-gpds-mm-panel]', menu);

        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (!menu.hasAttribute('hidden')) {
                closeMegaMenu();
            } else {
                openMegaMenu();
            }
        });

        function openMegaMenu() {
            menu.removeAttribute('hidden');
            menu.setAttribute('aria-hidden', 'false');
            trigger.setAttribute('aria-expanded', 'true');
            if (items.length > 0 && panels.length > 0) {
                activateItem(items[0].dataset.gpdsMmItem);
            }
        }

        function closeMegaMenu() {
            menu.setAttribute('hidden', '');
            menu.setAttribute('aria-hidden', 'true');
            trigger.setAttribute('aria-expanded', 'false');
        }

        items.forEach(item => {
            const id = item.dataset.gpdsMmItem;
            item.addEventListener('mouseenter', () => activateItem(id));
            item.addEventListener('focus', () => activateItem(id));
        });

        function activateItem(id) {
            items.forEach(it => it.classList.toggle('is-active', it.dataset.gpdsMmItem === id));
            panels.forEach(p => { p.hidden = p.dataset.gpdsMmPanel !== id; });
        }

        document.addEventListener('click', function (e) {
            if (!menu.hidden && !menu.contains(e.target) && !trigger.contains(e.target)) {
                closeMegaMenu();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !menu.hidden) closeMegaMenu();
        });
    }

    // ============================================
    // MOBILE MENU
    // ============================================
    function initMobileMenu() {
        const trigger = $('[data-gpds-mobile-menu-trigger]');
        const menu = $('[data-gpds-mobile-menu]');
        const overlay = $('[data-gpds-mobile-menu-overlay]');
        const closeBtn = $('[data-gpds-mobile-menu-close]');

        if (!trigger || !menu) return;

        let lastFocusedElement = null;

        const rootLevel = $('[data-gpds-mobile-level="root"]');
        const categoriesLevel = $('[data-gpds-mobile-level="categories"]');
        const categoriesTrigger = $('[data-gpds-mobile-categories-trigger]');
        const categoryTriggers = $$('[data-gpds-mobile-category-trigger]');
        const categoryBackButtons = $$('[data-gpds-mobile-category-back]');

        const closeSubLevels = () => {
            $$('[data-gpds-mobile-sublevel]').forEach((level) => {
                level.hidden = true;
                const category = level.closest('.gpds-mobile-menu__category');
                const button = category ? $('[data-gpds-mobile-category-trigger]', category) : null;
                if (button) button.setAttribute('aria-expanded', 'false');
            });
        };

        const showRootLevel = () => {
            if (categoriesLevel) categoriesLevel.hidden = true;
            if (rootLevel) rootLevel.hidden = false;
            if (categoriesTrigger) categoriesTrigger.setAttribute('aria-expanded', 'false');
            closeSubLevels();
        };

        const showCategoriesLevel = () => {
            if (!categoriesLevel) return;
            if (rootLevel) rootLevel.hidden = true;
            categoriesLevel.hidden = false;
            if (categoriesTrigger) categoriesTrigger.setAttribute('aria-expanded', 'true');
            closeSubLevels();

            const backButton = $('[data-gpds-mobile-back="root"]');
            if (backButton) setTimeout(() => backButton.focus(), 30);
        };

        const openSubLevel = (id, button) => {
            const sublevel = $('[data-gpds-mobile-sublevel="' + id + '"]');
            if (!sublevel) return;
            closeSubLevels();
            sublevel.hidden = false;
            if (button) button.setAttribute('aria-expanded', 'true');
            const backButton = $('[data-gpds-mobile-category-back]', sublevel);
            if (backButton) setTimeout(() => backButton.focus(), 30);
        };

        const openMenu = () => {
            lastFocusedElement = document.activeElement;
            menu.hidden = false;
            if (overlay) overlay.hidden = false;
            menu.setAttribute('aria-hidden', 'false');
            trigger.setAttribute('aria-expanded', 'true');
            document.documentElement.classList.add('gpds-mobile-menu-open');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                menu.classList.add('is-open');
                if (overlay) overlay.classList.add('is-visible');
            });

            showRootLevel();
            setTimeout(() => { if (closeBtn) closeBtn.focus(); }, 30);
        };

        const closeMenu = () => {
            menu.classList.remove('is-open');
            if (overlay) overlay.classList.remove('is-visible');
            menu.setAttribute('aria-hidden', 'true');
            trigger.setAttribute('aria-expanded', 'false');
            document.documentElement.classList.remove('gpds-mobile-menu-open');
            document.body.style.overflow = '';
            showRootLevel();

            setTimeout(() => {
                menu.hidden = true;
                if (overlay) overlay.hidden = true;
            }, 220);

            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                setTimeout(() => lastFocusedElement.focus(), 30);
            }
        };

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = trigger.getAttribute('aria-expanded') === 'true';
            isOpen ? closeMenu() : openMenu();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', (event) => {
                event.preventDefault();
                closeMenu();
            });
        }

        if (overlay) overlay.addEventListener('click', closeMenu);

        if (categoriesTrigger) {
            categoriesTrigger.addEventListener('click', (event) => {
                event.preventDefault();
                showCategoriesLevel();
            });
        }

        const rootBack = $('[data-gpds-mobile-back="root"]');
        if (rootBack) {
            rootBack.addEventListener('click', (event) => {
                event.preventDefault();
                showRootLevel();
                if (categoriesTrigger) setTimeout(() => categoriesTrigger.focus(), 30);
            });
        }

        categoryTriggers.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const id = button.getAttribute('data-gpds-mobile-category-trigger');
                if (!id) return;
                openSubLevel(id, button);
            });
        });

        categoryBackButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const sublevel = button.closest('[data-gpds-mobile-sublevel]');
                if (!sublevel) return;
                sublevel.hidden = true;
                const category = sublevel.closest('.gpds-mobile-menu__category');
                if (category) {
                    const trig = $('[data-gpds-mobile-category-trigger]', category);
                    if (trig) {
                        trig.setAttribute('aria-expanded', 'false');
                        setTimeout(() => trig.focus(), 30);
                    }
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            if (trigger.getAttribute('aria-expanded') === 'true') closeMenu();
        });
    }

    // ============================================
    // SEARCH OVERLAY
    // ============================================
    function initSearchOverlay() {
        const trigger = $('[data-gpds-search-overlay-trigger]');
        const overlay = $('[data-gpds-search-overlay]');
        const closeBtn = $('[data-gpds-search-overlay-close]');

        if (!trigger || !overlay) return;

        trigger.addEventListener('click', function () {
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                const input = $('input[data-gpds-search]', overlay);
                if (input) input.focus();
            }, 100);
        });

        function close() {
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        if (closeBtn) closeBtn.addEventListener('click', close);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !overlay.hidden) close();
        });
    }

    // ============================================
    // BACK TO TOP
    // ============================================
    function initBackToTop() {
        const btn = $('[data-gpds-back-to-top]');
        if (!btn) return;

        let isVisible = false;

        function checkScroll() {
            const shouldShow = window.scrollY > 500;
            if (shouldShow !== isVisible) {
                isVisible = shouldShow;
                btn.classList.toggle('is-visible', shouldShow);
            }
        }

        window.addEventListener('scroll', checkScroll, { passive: true });
        checkScroll();

        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ============================================
    // NEWSLETTER
    // ============================================
    function initNewsletter() {
        const form = $('[data-gpds-newsletter]');
        if (!form) return;

        const messageEl = $('[data-gpds-newsletter-message]', form.parentNode)
            || $('[data-gpds-newsletter-message]');

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const input = $('input[type="email"]', form);
            const button = $('button[type="submit"]', form);
            if (!input || !input.value) return;

            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'در حال ثبت...';

            const formData = new FormData();
            formData.append('action', 'gpds_newsletter');
            formData.append('nonce', GPDS.nonce);
            formData.append('email', input.value);

            try {
                const res = await fetch(GPDS.ajaxUrl, { method: 'POST', body: formData });
                const data = await res.json();

                if (messageEl) {
                    messageEl.textContent = data.success
                        ? data.data.message
                        : (data.data?.message || 'خطایی رخ داد');
                    messageEl.className = 'gpds-footer-newsletter__message ' +
                        (data.success ? 'is-success' : 'is-error');
                }

                if (data.success) input.value = '';
            } catch (err) {
                if (messageEl) {
                    messageEl.textContent = 'خطای شبکه';
                    messageEl.className = 'gpds-footer-newsletter__message is-error';
                }
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    }

    // ============================================
    // MINI CART
    // ============================================
    function initMiniCart() {
        const miniCart = $('[data-gpds-mini-cart]');
        if (!miniCart) return;

        const trigger = $('[data-gpds-cart-trigger]', miniCart);
        const panel = $('[data-gpds-cart-panel]', miniCart);
        const content = $('[data-gpds-cart-content]', miniCart);
        const loading = $('[data-gpds-cart-loading]', miniCart);

        if (!trigger || !panel || !content) return;

        const isOpen = () => !panel.hasAttribute('hidden');
        const open = () => panel.removeAttribute('hidden');
        const close = () => panel.setAttribute('hidden', '');

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
                const res = await fetch(GPDS.ajaxUrl, { method: 'POST', body });
                const data = await res.json();
                if (data.success) renderCart(data.data, content);
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

        cartPage.addEventListener('input', (e) => {
            const input = e.target;
            if (!input.matches('.gpds-qty-wrapper input.qty')) return;
            input.value = input.value.replace(/[^0-9]/g, '');
            updateQtyButtons(input.closest('.gpds-qty-wrapper'));
        });
    }

    function injectQtyButtons(scope) {
        $$('.woocommerce-cart-form .quantity', scope).forEach((qty) => {
            if (qty.querySelector('.gpds-qty-wrapper')) return;
            const input = $('input.qty', qty);
            if (!input) return;

            const wrapper = document.createElement('div');
            wrapper.className = 'gpds-qty-wrapper';

            const minus = document.createElement('button');
            minus.type = 'button';
            minus.className = 'gpds-qty-btn minus';
            minus.setAttribute('aria-label', 'کاهش تعداد');
            minus.textContent = '−';

            const plus = document.createElement('button');
            plus.type = 'button';
            plus.className = 'gpds-qty-btn plus';
            plus.setAttribute('aria-label', 'افزایش تعداد');
            plus.textContent = '+';

            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(minus);
            wrapper.appendChild(input);
            wrapper.appendChild(plus);

            updateQtyButtons(wrapper);
        });
    }

    function updateQtyButtons(wrapper) {
        if (!wrapper) return;
        const input = $('input.qty', wrapper);
        if (!input) return;

        const minusBtn = $('.gpds-qty-btn.minus', wrapper);
        const plusBtn = $('.gpds-qty-btn.plus', wrapper);

        const val = parseFloat(input.value) || 0;
        const min = parseFloat(input.getAttribute('min'));
        const max = parseFloat(input.getAttribute('max'));

        if (minusBtn) minusBtn.disabled = !isNaN(min) && val <= 1;
        if (plusBtn) plusBtn.disabled = !isNaN(max) && max > 0 && val >= max;
    }

    function cleanEmptyNotices(scope) {
        const notices = $('.woocommerce > .woocommerce-notices-wrapper', scope);
        if (!notices) return;
        if (notices.children.length === 0 && notices.textContent.trim() === '') {
            notices.remove();
        }
    }

    function handleQtyClick(scope, btn) {
        const wrapper = btn.closest('.gpds-qty-wrapper');
        if (!wrapper || wrapper.classList.contains('is-loading')) return;

        const input = $('input.qty', wrapper);
        if (!input) return;

        const val = parseFloat(input.value) || 0;
        const min = parseFloat(input.getAttribute('min'));
        const max = parseFloat(input.getAttribute('max'));
        const step = parseFloat(input.getAttribute('step')) || 1;
        const isPlus = btn.classList.contains('plus');
        const effectiveMin = isNaN(min) ? 0 : min;

        let newVal = val;

        if (isPlus) {
            if (!isNaN(max) && val + step > max) return;
            newVal = val + step;
        } else {
            const next = val - step;

            if (next < effectiveMin) {
                if (effectiveMin > 0 && val <= effectiveMin) {
                    removeCartItem(scope, input, wrapper);
                    return;
                }
                if (effectiveMin === 0 && next <= 0) {
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

    function handleRemoveClick(scope, link) {
        const removeUrl = link.getAttribute('href');
        if (!removeUrl || removeUrl === '#' || removeUrl.startsWith('javascript:')) return;

        const row = link.closest('.woocommerce-cart-form__cart-item');
        if (row) row.classList.add('is-loading');

        fetch(removeUrl, { method: 'GET', credentials: 'same-origin' })
            .then(() => fetch(window.location.href, { credentials: 'same-origin' }))
            .then((r) => r.text())
            .then((html) => replaceCartDOM(scope, html))
            .catch((err) => {
                console.error('[GPDS] Remove item error:', err);
                showToast(GPDS.i18n.error, 'error');
                if (row) row.classList.remove('is-loading');
            });
    }

    function removeCartItem(scope, input, wrapper) {
        const row = input.closest('.woocommerce-cart-form__cart-item');
        const removeLink = row ? row.querySelector('.product-remove a.remove') : null;

        if (removeLink && removeLink.getAttribute('href')) {
            handleRemoveClick(scope, removeLink);
            return;
        }

        input.value = 0;
        updateCart(scope, wrapper);
    }

    function updateCart(scope, wrapper) {
        const form = $('.woocommerce-cart-form', scope);
        if (!form) return;

        if (wrapper) wrapper.classList.add('is-loading');

        const formData = new FormData(form);
        formData.append('update_cart', '1');

        const updateBtn = $('button[name="update_cart"]', form);
        if (updateBtn) updateBtn.disabled = false;

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
            .then((r) => r.text())
            .then((html) => replaceCartDOM(scope, html))
            .catch((err) => {
                console.error('[GPDS] Cart update error:', err);
                showToast(GPDS.i18n.error, 'error');
                if (wrapper) wrapper.classList.remove('is-loading');
            });
    }

    function replaceCartDOM(scope, html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');

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

        injectQtyButtons(scope);
        cleanEmptyNotices(scope);

        if (activeName) {
            const newInput = $(`input[name="${activeName}"]`, scope);
            if (newInput) newInput.focus();
        }

        refreshCartCount();
        document.body.dispatchEvent(new Event('updated_cart_totals', { bubbles: true }));
    }

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

    async function refreshCartCount() {
        const body = new FormData();
        body.append('action', 'gpds_get_cart');
        body.append('nonce', GPDS.nonce);

        try {
            const res = await fetch(GPDS.ajaxUrl, { method: 'POST', body });
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
    // LIVE SEARCH
    // ============================================
    function initLiveSearch() {
        const inputs = $$('input[data-gpds-search]');
        if (!inputs.length) return;

        const DEBOUNCE_DELAY = 350;
        const MIN_CHARS = 2;

        inputs.forEach(input => {
            const form = input.closest('form');
            const resultsBox = form ? $('[data-gpds-search-results]', form) : null;
            const body = form ? $('[data-gpds-search-body]', form) : null;
            const loading = form ? $('.gpds-search-results__loading', form) : null;

            if (!resultsBox || !body) return;

            let timeout = null;
            let currentRequest = null;

            input.addEventListener('input', function () {
                clearTimeout(timeout);
                const term = input.value.trim();

                if (term.length < MIN_CHARS) {
                    resultsBox.setAttribute('hidden', '');
                    return;
                }

                timeout = setTimeout(() => doSearch(term), DEBOUNCE_DELAY);
            });

            input.addEventListener('focus', function () {
                if (input.value.trim().length >= MIN_CHARS && body.innerHTML) {
                    resultsBox.removeAttribute('hidden');
                }
            });

            document.addEventListener('click', function (e) {
                if (!form.contains(e.target)) {
                    resultsBox.setAttribute('hidden', '');
                }
            });

            async function doSearch(term) {
                if (currentRequest) currentRequest.abort();

                resultsBox.removeAttribute('hidden');
                if (loading) loading.style.display = 'block';
                body.innerHTML = '';

                const controller = new AbortController();
                currentRequest = controller;

                const formData = new FormData();
                formData.append('action', 'gpds_search');
                formData.append('nonce', GPDS.nonce);
                formData.append('term', term);

                try {
                    const response = await fetch(GPDS.ajaxUrl, {
                        method: 'POST',
                        body: formData,
                        signal: controller.signal,
                    });

                    const data = await response.json();
                    if (loading) loading.style.display = 'none';

                    if (data.success) {
                        renderSearchResults(data.data, body, term);
                    } else {
                        renderSearchEmpty(data.data?.message || GPDS.i18n.searchEmpty, body);
                    }
                } catch (err) {
                    if (err.name === 'AbortError') return;
                    if (loading) loading.style.display = 'none';
                    renderSearchEmpty(GPDS.i18n.error, body);
                } finally {
                    currentRequest = null;
                }
            }
        });

        function renderSearchResults(data, container, term) {
            const { products = [], categories = [], total = 0 } = data;

            if (total === 0) {
                renderSearchEmpty(GPDS.i18n.searchEmpty, container);
                return;
            }

            let html = '';

            if (categories.length) {
                html += '<div class="gpds-search-cat">';
                html += '<div class="gpds-search-cat__title">دسته‌بندی‌ها</div>';
                categories.forEach(cat => {
                    html += `
                        <a href="${escapeHTML(cat.url)}" class="gpds-search-cat__item">
                            <span>${escapeHTML(cat.title)}</span>
                            <span class="gpds-text-tertiary">${cat.count} کالا</span>
                        </a>
                    `;
                });
                html += '</div>';
            }

            if (products.length) {
                html += '<div class="gpds-search-products">';
                html += '<div class="gpds-search-products__title">محصولات</div>';
                products.forEach(p => {
                    html += `
                        <a href="${escapeHTML(p.url)}" class="gpds-search-product">
                            <img src="${escapeHTML(p.image)}" alt="${escapeHTML(p.title)}" loading="lazy">
                            <div class="gpds-search-product__info">
                                <div class="gpds-search-product__title">${escapeHTML(p.title)}</div>
                                <div class="gpds-search-product__price">${p.price}</div>
                            </div>
                        </a>
                    `;
                });
                html += '</div>';
            }

            html += `
                <a href="${GPDS.homeUrl}?s=${encodeURIComponent(term)}&post_type=product" class="gpds-search-all">
                    مشاهده همه نتایج
                </a>
            `;

            container.innerHTML = html;
        }

        function renderSearchEmpty(message, container) {
            container.innerHTML = `<div class="gpds-search-empty">${escapeHTML(message)}</div>`;
        }
    }

    // ============================================
    // PUBLIC API
    // ============================================
    window.GPDS_CORE = {
        $, $$, escapeHTML, showToast, withLoading, fallbackCopy,
        refreshCart: refreshCartCount,
    };

})();