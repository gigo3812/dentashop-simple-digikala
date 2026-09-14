/**
 * GP DentaShop - Slider + Countdown + Stories
 * کامل + بهینه
 */
(function() {
    'use strict';
    
    // ============================================
    // 1. Swiper Sliders
    // ============================================
    function initSliders() {
        if (typeof Swiper === 'undefined') return;
        
        // اسلایدر اصلی صفحه
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
        
        // کاروسل محصولات (پرفروش‌ها، شگفت‌انگیزها، ...)
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
    }
    
    // ============================================
    // 2. Countdown (شگفت‌انگیزها) - از سرور
    // ============================================
    function initCountdowns() {
        document.querySelectorAll('[data-gpds-countdown]').forEach(function(el) {
            var endTime = parseInt(el.dataset.end, 10) * 1000; // به میلی‌ثانیه
            if (!endTime || isNaN(endTime)) return;
            
            var hoursEl   = el.querySelector('[data-gpds-cd="hours"]');
            var minutesEl = el.querySelector('[data-gpds-cd="minutes"]');
            var secondsEl = el.querySelector('[data-gpds-cd="seconds"]');
            
            // 🎯 هماهنگی زمان سرور و کلاینت
            var serverNow = parseInt(el.dataset.serverNow || 0, 10) * 1000;
            var clientNow = Date.now();
            var timeOffset = serverNow > 0 ? (clientNow - serverNow) : 0;
            
            var intervalId = null;
            
            function update() {
                var now = Date.now() - timeOffset; // زمان سرور
                var diff = Math.max(0, endTime - now);
                
                if (diff <= 0) {
                    // ⏰ زمان تموم شد → مخفی کردن بخش
                    var section = el.closest('.gpds-section');
                    if (section) {
                        section.style.transition = 'opacity 0.3s';
                        section.style.opacity = '0';
                        setTimeout(function() {
                            section.style.display = 'none';
                        }, 300);
                    }
                    
                    if (intervalId) clearInterval(intervalId);
                    return;
                }
                
                var h = Math.floor(diff / (1000 * 60 * 60));
                var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                var s = Math.floor((diff % (1000 * 60)) / 1000);
                
                if (hoursEl)   hoursEl.textContent   = String(h).padStart(2, '0');
                if (minutesEl) minutesEl.textContent = String(m).padStart(2, '0');
                if (secondsEl) secondsEl.textContent = String(s).padStart(2, '0');
            }
            
            update();
            intervalId = setInterval(update, 1000);
        });
    }
    
    // ============================================
    // 3. Stories System
    // ============================================
    function initStories() {
        const storyButtons = document.querySelectorAll('[data-gpds-story]');
        if (!storyButtons.length) return;
        
        const STORY_DURATION = 5000;
        
        // 🎯 State
        let stories = Array.from(storyButtons).map(btn => ({
            index:    parseInt(btn.dataset.storyIndex, 10),
            image:    btn.dataset.storyImage,
            thumb:    btn.dataset.storyThumb,
            title:    btn.dataset.storyTitle,
            url:      btn.dataset.storyUrl,
            newTab:   btn.dataset.storyNewTab === '1',
            button:   btn,
        }));
        
        let modal = null;
        let currentIndex = 0;
        let timer = null;
        let isLoading = false;
        
        // ============================================
        // ساخت Modal (فقط یک بار، در اولین کلیک)
        // ============================================
        function buildModal() {
            if (modal) return modal;
            
            modal = document.createElement('div');
            modal.className = 'gpds-story-modal';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            
            modal.innerHTML = `
                <div class="gpds-story-modal__backdrop" data-gpds-story-close></div>
                <div class="gpds-story-modal__container">
                    <div class="gpds-story-modal__progress" data-gpds-story-progress></div>
                    
                    <div class="gpds-story-modal__header">
                        <div class="gpds-story-modal__user">
                            <div class="gpds-story-modal__avatar">
                                <img src="" alt="" data-gpds-story-avatar>
                            </div>
                            <div class="gpds-story-modal__user-info">
                                <div class="gpds-story-modal__username" data-gpds-story-username></div>
                                <div class="gpds-story-modal__time">الان</div>
                            </div>
                        </div>
                        
                        <button type="button" class="gpds-story-modal__close" data-gpds-story-close aria-label="بستن">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="gpds-story-modal__content" data-gpds-story-content>
                        <img src="" alt="" data-gpds-story-image>
                    </div>
                    
                    <a href="#" class="gpds-story-modal__cta" data-gpds-story-cta target="_self" rel="noopener">
                        مشاهده بیشتر
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </a>
                    
                    <button type="button" class="gpds-story-modal__nav gpds-story-modal__nav--prev" data-gpds-story-prev aria-label="قبلی">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                    
                    <button type="button" class="gpds-story-modal__nav gpds-story-modal__nav--next" data-gpds-story-next aria-label="بعدی">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Event Listeners
            modal.querySelectorAll('[data-gpds-story-close]').forEach(btn => {
                btn.addEventListener('click', closeStory);
            });
            
            const prevBtn = modal.querySelector('[data-gpds-story-prev]');
            const nextBtn = modal.querySelector('[data-gpds-story-next]');
            
            if (prevBtn) prevBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                prevStory();
            });
            
            if (nextBtn) nextBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                nextStory();
            });
            
            // Keyboard
            document.addEventListener('keydown', function(e) {
                if (!modal || !document.body.classList.contains('gpds-story-open')) return;
                
                if (e.key === 'Escape') closeStory();
                else if (e.key === 'ArrowLeft')  prevStory();
                else if (e.key === 'ArrowRight') nextStory();
            });
            
            // Touch swipe
            let touchStartX = 0;
            modal.addEventListener('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
            }, { passive: true });
            
            modal.addEventListener('touchend', function(e) {
                const diff = touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) prevStory();
                    else nextStory();
                }
            }, { passive: true });
            
            return modal;
        }
        
        // باز کردن استوری
        function openStory(index) {
            if (isLoading) return;
            
            const story = stories[index];
            if (!story) return;
            
            currentIndex = index;
            isLoading = true;
            
            story.button.classList.add('is-loading');
            
            const img = new Image();
            img.onload = function() {
                setTimeout(function() {
                    story.button.classList.remove('is-loading');
                    isLoading = false;
                    showModal(index);
                }, 400);
            };
            
            img.onerror = function() {
                story.button.classList.remove('is-loading');
                isLoading = false;
                console.error('[GPDS Story] Failed to load:', story.image);
            };
            
            img.src = story.image;
        }
        
        // نمایش Modal
        function showModal(index) {
            const story = stories[index];
            if (!story) return;
            
            buildModal();
            
            const img         = modal.querySelector('[data-gpds-story-image]');
            const avatar      = modal.querySelector('[data-gpds-story-avatar]');
            const username    = modal.querySelector('[data-gpds-story-username]');
            const cta         = modal.querySelector('[data-gpds-story-cta]');
            const progress    = modal.querySelector('[data-gpds-story-progress]');
            const prevBtn     = modal.querySelector('[data-gpds-story-prev]');
            const nextBtn     = modal.querySelector('[data-gpds-story-next]');
            
            // Progress bars
            progress.innerHTML = '';
            stories.forEach((_, i) => {
                const bar = document.createElement('div');
                bar.className = 'gpds-story-modal__progress-bar';
                if (i < index)  bar.classList.add('is-done');
                if (i === index) bar.classList.add('is-active');
                progress.appendChild(bar);
            });
            
            // محتوا
            img.src = story.image;
            img.alt = story.title;
            avatar.src = story.thumb;
            username.textContent = story.title;
            
            // CTA
            if (story.url) {
                cta.href = story.url;
                cta.style.display = '';
                
                if (story.newTab) {
                    cta.target = '_blank';
                    cta.setAttribute('rel', 'noopener noreferrer');
                } else {
                    cta.target = '_self';
                    cta.setAttribute('rel', 'noopener');
                }
            } else {
                cta.style.display = 'none';
            }
            
            // ناوبری
            prevBtn.style.display = index > 0 ? '' : 'none';
            nextBtn.style.display = index < stories.length - 1 ? '' : 'none';
            
            // نمایش Modal
            modal.style.display = 'flex';
            document.body.classList.add('gpds-story-open');
            
            // تایمر
            startTimer();
        }
        
        function startTimer() {
            clearTimeout(timer);
            timer = setTimeout(nextStory, STORY_DURATION);
        }
        
        function nextStory() {
            if (currentIndex < stories.length - 1) {
                showModal(currentIndex + 1);
            } else {
                closeStory();
            }
        }
        
        function prevStory() {
            if (currentIndex > 0) {
                showModal(currentIndex - 1);
            }
        }
        
        function closeStory() {
            clearTimeout(timer);
            
            if (modal) {
                modal.style.display = 'none';
                const img = modal.querySelector('[data-gpds-story-image]');
                if (img) img.src = '';
            }
            
            document.body.classList.remove('gpds-story-open');
        }
        
        // رویداد کلیک
        storyButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const index = parseInt(this.dataset.storyIndex, 10);
                openStory(index);
            });
        });
    }
    
    // ============================================
    // اجرا (همه با هم)
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