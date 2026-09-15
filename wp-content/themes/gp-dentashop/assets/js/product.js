/**
 * GP DentaShop - Product Page Scripts
 * گالری، تب‌ها، تعداد
 */
(function () {
    'use strict';

    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    document.addEventListener('DOMContentLoaded', function () {
        initGallery();
        initProductTabs();
        initQuantity();
    });

    // ============================================
    // گالری محصول
    // ============================================
    function initGallery() {
        const mainImg = $('[data-gpds-gallery-main]');
        const thumbs = $$('[data-gpds-gallery-thumb]');

        if (!mainImg || !thumbs.length) return;

        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function () {
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

            // فقط عدد
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


})();