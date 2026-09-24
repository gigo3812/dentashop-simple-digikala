/**
 * GPDS Slider Engine
 * جایگزین سبک Swiper - سازگار با کلاس‌های Swiper
 * ~3KB minified
 */
(function (window, document) {
    'use strict';

    function GPDSSlider(el, options) {
        if (!el || el.gpdsSlider) return el ? el.gpdsSlider : null;

        // ============================================
        // تنظیمات پیش‌فرض
        // ============================================
        var params = {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: false,
            speed: 300,
            autoplay: false,
            pagination: false,
            navigation: false,
            breakpoints: null,
            watchSlidesProgress: false,
            grabCursor: false,
        };

        // ادغام
        if (options) {
            for (var k in options) {
                if (Object.prototype.hasOwnProperty.call(options, k) && options[k] !== undefined) {
                    params[k] = options[k];
                }
            }
        }

        // ============================================
        // State
        // ============================================
        var wrapper = el.querySelector('.swiper-wrapper');
        if (!wrapper) return null;

        var slides = [];
        var currentIndex = 0;
        var translate = 0;
        var slideWidth = 0;         // عرض اسلاید + فاصله
        var maxTranslate = 0;
        var spv = params.slidesPerView;
        var gap = params.spaceBetween;
        var autoplayTimer = null;
        var resizeTimer = null;
        var isDragging = false;
        var startX = 0;
        var startTranslate = 0;
        var currentX = 0;
        var isRTL = document.documentElement.dir === 'rtl' ||
                    getComputedStyle(el).direction === 'rtl';

        var bullets = [];
        var nextEl = null, prevEl = null;
        var maxIndex = 0;

        // ============================================
        // refresh
        // ============================================
        function refresh() {
            slides = Array.prototype.slice.call(wrapper.children);
        }

        // ============================================
        // breakpoint
        // ============================================
        function applyBreakpoint() {
            if (!params.breakpoints) return;
            var w = window.innerWidth;
            var keys = Object.keys(params.breakpoints).map(Number).sort(function(a,b){return a-b;});
            var active = null;
            for (var i = 0; i < keys.length; i++) {
                if (w >= keys[i]) active = params.breakpoints[keys[i]];
            }
            if (active) {
                if (active.slidesPerView !== undefined) spv = active.slidesPerView;
                if (active.spaceBetween !== undefined) gap = active.spaceBetween;
            } else {
                spv = params.slidesPerView;
                gap = params.spaceBetween;
            }
        }

        // ============================================
        // اندازه‌ها
        // ============================================
        function updateSize() {
            applyBreakpoint();
            var elWidth = el.clientWidth;
            var slideW = (elWidth - gap * (spv - 1)) / spv;

            slideWidth = slideW + gap;

            slides.forEach(function (s, i) {
                s.style.width = slideW + 'px';
                if (i < slides.length - 1) {
                    s.style.marginInlineEnd = gap + 'px';
                } else {
                    s.style.marginInlineEnd = '0px';
                }
            });

            maxIndex = Math.max(0, slides.length - Math.ceil(spv));
            maxTranslate = maxIndex * slideWidth;
        }

        // ============================================
        // translate
        // ============================================
        function applyTranslate(animated) {
            wrapper.style.transition = animated === false
                ? 'none'
                : 'transform ' + params.speed + 'ms cubic-bezier(0.4, 0, 0.2, 1)';
            var x = isRTL ? translate : -translate;
            wrapper.style.transform = 'translate3d(' + x + 'px, 0, 0)';
        }

        // ============================================
        // slideTo
        // ============================================
        function slideTo(index) {
            if (index < 0) index = 0;
            if (index > maxIndex) index = maxIndex;

            currentIndex = index;
            translate = index * slideWidth;
            applyTranslate();
            updatePagination();
            updateNav();
        }

        function slideNext() {
            if (currentIndex >= maxIndex) {
                if (params.loop) slideTo(0);
                else return;
            } else {
                slideTo(currentIndex + 1);
            }
        }

        function slidePrev() {
            if (currentIndex <= 0) {
                if (params.loop) slideTo(maxIndex);
                else return;
            } else {
                slideTo(currentIndex - 1);
            }
        }

        // ============================================
        // Pagination
        // ============================================
        function initPagination() {
            if (!params.pagination) return;
            var pagEl = typeof params.pagination.el === 'string'
                ? el.querySelector(params.pagination.el)
                : params.pagination.el;
            if (!pagEl) return;

            var count = maxIndex + 1;
            if (bullets.length === count && pagEl.children.length === count) {
                return; // نیازی به بازسازی نیست
            }

            pagEl.innerHTML = '';
            bullets = [];
            for (var i = 0; i < count; i++) {
                var b = document.createElement('span');
                b.className = 'swiper-pagination-bullet';
                if (params.pagination.clickable) {
                    b.style.cursor = 'pointer';
                    b.addEventListener('click', (function (idx) {
                        return function () { slideTo(idx); };
                    })(i));
                }
                pagEl.appendChild(b);
                bullets.push(b);
            }
            updatePagination();
        }

        function updatePagination() {
            for (var i = 0; i < bullets.length; i++) {
                if (i === currentIndex) {
                    bullets[i].classList.add('swiper-pagination-bullet-active');
                } else {
                    bullets[i].classList.remove('swiper-pagination-bullet-active');
                }
            }
        }

        // ============================================
        // Navigation
        // ============================================
        function initNavigation() {
            if (!params.navigation) return;
            nextEl = typeof params.navigation.nextEl === 'string'
                ? el.querySelector(params.navigation.nextEl)
                : params.navigation.nextEl;
            prevEl = typeof params.navigation.prevEl === 'string'
                ? el.querySelector(params.navigation.prevEl)
                : params.navigation.prevEl;

            if (nextEl) nextEl.addEventListener('click', function (e) { e.preventDefault(); slideNext(); });
            if (prevEl) prevEl.addEventListener('click', function (e) { e.preventDefault(); slidePrev(); });
        }

        function updateNav() {
            if (!nextEl && !prevEl) return;
            if (params.loop) {
                if (nextEl) nextEl.classList.remove('swiper-button-disabled');
                if (prevEl) prevEl.classList.remove('swiper-button-disabled');
                return;
            }
            if (prevEl) prevEl.classList.toggle('swiper-button-disabled', currentIndex <= 0);
            if (nextEl) nextEl.classList.toggle('swiper-button-disabled', currentIndex >= maxIndex);
        }

        // ============================================
        // Autoplay
        // ============================================
        function startAutoplay() {
            if (!params.autoplay) return;
            stopAutoplay();
            var delay = params.autoplay.delay || 5000;
            autoplayTimer = setInterval(slideNext, delay);
        }

        function stopAutoplay() {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        }

        // ============================================
        // Drag
        // ============================================
        function onStart(e) {
            if (maxIndex === 0) return;
            isDragging = true;
            startX = e.type.charAt(0) === 't' ? e.touches[0].pageX : e.pageX;
            currentX = startX;
            startTranslate = translate;
            wrapper.style.transition = 'none';
            stopAutoplay();
            el.style.cursor = params.grabCursor ? 'grabbing' : '';
        }

        function onMove(e) {
            if (!isDragging) return;
            var x = e.type.charAt(0) === 't' ? e.touches[0].pageX : e.pageX;
            currentX = x;
            var diff = x - startX;
            var newT = startTranslate - diff;

            if (newT < 0) newT = newT * 0.3;
            if (newT > maxTranslate) newT = maxTranslate + (newT - maxTranslate) * 0.3;

            translate = newT;
            applyTranslate(false);
        }

        function onEnd() {
            if (!isDragging) return;
            isDragging = false;
            el.style.cursor = params.grabCursor ? 'grab' : '';
            var diff = currentX - startX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) slidePrev();
                else slideNext();
            } else {
                slideTo(currentIndex);
            }
            startAutoplay();
        }

        // ============================================
        // Resize
        // ============================================
        function onResize() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                refresh();
                updateSize();
                initPagination();
                slideTo(Math.min(currentIndex, maxIndex));
            }, 150);
        }

        // ============================================
        // Init
        // ============================================
        function init() {
            refresh();
            updateSize();
            initPagination();
            initNavigation();
            updateNav();
            startAutoplay();

            if (params.grabCursor) el.style.cursor = 'grab';

            el.addEventListener('mousedown', onStart);
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onEnd);
            el.addEventListener('touchstart', onStart, { passive: true });
            el.addEventListener('touchmove', onMove, { passive: true });
            el.addEventListener('touchend', onEnd);
            window.addEventListener('resize', onResize);

            if (params.autoplay && params.autoplay.disableOnInteraction !== false) {
                el.addEventListener('mouseenter', stopAutoplay);
                el.addEventListener('mouseleave', startAutoplay);
            }
        }

        // ============================================
        // API
        // ============================================
        var instance = {
            el: el,
            params: params,
            slideTo: slideTo,
            slideNext: slideNext,
            slidePrev: slidePrev,
            get activeIndex() { return currentIndex; },
            update: function () {
                refresh();
                updateSize();
                initPagination();
                slideTo(Math.min(currentIndex, maxIndex));
            },
            destroy: function () {
                stopAutoplay();
                el.removeEventListener('mousedown', onStart);
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onEnd);
                el.removeEventListener('touchstart', onStart);
                el.removeEventListener('touchmove', onMove);
                el.removeEventListener('touchend', onEnd);
                window.removeEventListener('resize', onResize);
                el.gpdsSlider = null;
            },
        };

        el.gpdsSlider = instance;
        init();
        return instance;
    }

    // ============================================
    // Adapter سازگار با new Swiper()
    // ============================================
    function SwiperAdapter(selector, options) {
        var els;
        if (typeof selector === 'string') {
            els = document.querySelectorAll(selector);
        } else if (selector instanceof HTMLElement) {
            els = [selector];
        } else if (selector && selector.length !== undefined) {
            els = selector;
        } else {
            return null;
        }

        var instances = [];
        Array.prototype.forEach.call(els, function (el) {
            var inst = GPDSSlider(el, options);
            if (inst) instances.push(inst);
        });

        return instances.length === 1 ? instances[0] : instances;
    }

    window.GPDSSlider = GPDSSlider;
    window.Swiper = SwiperAdapter;

})(window, document);