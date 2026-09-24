/**
 * GP DentaShop - Main Script
 * اسلایدر + کانت‌داون + استوری
 * بدون وابستگی خارجی (به جز GPDSSlider engine)
 */
(function () {
    'use strict';

    // ============================================
    // 1. اسلایدرها
    // ============================================
    function initSliders() {
        if (typeof window.Swiper === 'undefined') {
            console.warn('[GPDS] Slider engine not loaded');
            return;
        }

        // اسلایدر اصلی
        var mainSlider = document.querySelector('#gpds-home-slider');
        if (mainSlider) {
            new Swiper('#gpds-home-slider', {
                loop: true,
                autoplay: { delay: 5000, disableOnInteraction: false },
                speed: 700,
                pagination: {
                    el: mainSlider.querySelector('.swiper-pagination'),
                    clickable: true,
                },
                navigation: {
                    nextEl: mainSlider.querySelector('.swiper-button-next'),
                    prevEl: mainSlider.querySelector('.swiper-button-prev'),
                },
            });
        }

        // کاروسل محصولات
        document.querySelectorAll('.gpds-products-carousel').forEach(function (el) {
            var nextEl = el.querySelector('.swiper-button-next');
            var prevEl = el.querySelector('.swiper-button-prev');

            new Swiper(el, {
                slidesPerView: 2,
                spaceBetween: 12,
                breakpoints: {
                    480:  { slidesPerView: 2, spaceBetween: 12 },
                    640:  { slidesPerView: 3, spaceBetween: 12 },
                    768:  { slidesPerView: 4, spaceBetween: 12 },
                    1024: { slidesPerView: 5, spaceBetween: 14 },
                    1280: { slidesPerView: 6, spaceBetween: 14 },
                },
                navigation: (nextEl && prevEl) ? { nextEl: nextEl, prevEl: prevEl } : false,
            });
        });

        // کاروسل برندها
        document.querySelectorAll('.gpds-brands-carousel').forEach(function (el) {
            var nextEl = el.querySelector('.swiper-button-next');
            var prevEl = el.querySelector('.swiper-button-prev');

            new Swiper(el, {
                slidesPerView: 3,
                spaceBetween: 10,
                grabCursor: true,
                breakpoints: {
                    480:  { slidesPerView: 3, spaceBetween: 10 },
                    640:  { slidesPerView: 3, spaceBetween: 12 },
                    768:  { slidesPerView: 5, spaceBetween: 14 },
                    1024: { slidesPerView: 6, spaceBetween: 14 },
                    1280: { slidesPerView: 7, spaceBetween: 16 },
                },
                navigation: (nextEl && prevEl) ? { nextEl: nextEl, prevEl: prevEl } : false,
            });
        });
    }

    // ============================================
    // 2. کانت‌داون
    // ============================================
    function initCountdowns() {
        document.querySelectorAll('[data-gpds-countdown]').forEach(function (el) {
            var endTime = parseInt(el.dataset.end, 10) * 1000;
            if (!endTime || isNaN(endTime)) return;

            var hoursEl = el.querySelector('[data-gpds-cd="hours"]');
            var minutesEl = el.querySelector('[data-gpds-cd="minutes"]');
            var secondsEl = el.querySelector('[data-gpds-cd="seconds"]');

            var serverNow = parseInt(el.dataset.serverNow || 0, 10) * 1000;
            var clientNow = Date.now();
            var timeOffset = serverNow > 0 ? (clientNow - serverNow) : 0;
            var intervalId = null;

            function update() {
                var now = Date.now() - timeOffset;
                var diff = Math.max(0, endTime - now);

                if (diff <= 0) {
                    var section = el.closest('.gpds-section');
                    if (section) {
                        section.style.transition = 'opacity 0.3s';
                        section.style.opacity = '0';
                        setTimeout(function () { section.style.display = 'none'; }, 300);
                    }
                    if (intervalId) clearInterval(intervalId);
                    return;
                }

                var h = Math.floor(diff / 3600000);
                var m = Math.floor((diff % 3600000) / 60000);
                var s = Math.floor((diff % 60000) / 1000);

                if (hoursEl) hoursEl.textContent = String(h).padStart(2, '0');
                if (minutesEl) minutesEl.textContent = String(m).padStart(2, '0');
                if (secondsEl) secondsEl.textContent = String(s).padStart(2, '0');
            }

            update();
            intervalId = setInterval(update, 1000);
        });
    }

    // ============================================
    // 3. استوری
    // ============================================
    // ⚠️ کل کد initStories خودت رو دقیقاً از اینجا به بعد paste کن
    // (بدون هیچ تغییری)
    
    function initStories() {
        // ← اینجا کل تابع initStories خودت رو paste کن
    }

    // ============================================
    // Init
    // ============================================
    function initAll() {
        initSliders();
        initCountdowns();
        initStories();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();