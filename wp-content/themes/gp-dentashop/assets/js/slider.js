/**
 * GP DentaShop - Slider Script
 */
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        
        if (typeof Swiper === 'undefined') return;
        
        // ============================================
        // اسلایدر اصلی
        // ============================================
        const mainSlider = document.querySelector('#gpds-home-slider');
        if (mainSlider) {
            new Swiper('#gpds-home-slider', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                speed: 700,
                effect: 'slide',
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
        
        // ============================================
        // استوری‌ها
        // ============================================
        const storiesSlider = document.querySelector('#gpds-stories-slider');
        if (storiesSlider) {
            new Swiper('#gpds-stories-slider', {
                slidesPerView: 4,
                spaceBetween: 12,
                breakpoints: {
                    480:  { slidesPerView: 5,  spaceBetween: 12 },
                    640:  { slidesPerView: 7,  spaceBetween: 14 },
                    768:  { slidesPerView: 8,  spaceBetween: 14 },
                    1024: { slidesPerView: 10, spaceBetween: 16 },
                },
            });
        }
        
        // ============================================
        // کاروسل محصولات
        // ============================================
        document.querySelectorAll('.gpds-products-carousel').forEach(function(el) {
            const nextEl = el.querySelector('.swiper-button-next');
            const prevEl = el.querySelector('.swiper-button-prev');
            
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
                navigation: nextEl && prevEl ? { nextEl, prevEl } : false,
            });
        });
        
        // ============================================
        // شمارش معکوس
        // ============================================
        initCountdowns();
        
    });
    
    function initCountdowns() {
        document.querySelectorAll('[data-gpds-countdown]').forEach(function(el) {
            const endTime = parseInt(el.dataset.end, 10) * 1000;
            if (!endTime) return;
            
            const hoursEl = el.querySelector('[data-gpds-cd="hours"]');
            const minutesEl = el.querySelector('[data-gpds-cd="minutes"]');
            const secondsEl = el.querySelector('[data-gpds-cd="seconds"]');
            
            function update() {
                const now = Date.now();
                const diff = Math.max(0, endTime - now);
                
                const h = Math.floor(diff / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);
                
                if (hoursEl) hoursEl.textContent = String(h).padStart(2, '0');
                if (minutesEl) minutesEl.textContent = String(m).padStart(2, '0');
                if (secondsEl) secondsEl.textContent = String(s).padStart(2, '0');
            }
            
            update();
            setInterval(update, 1000);
        });
    }
    
})();