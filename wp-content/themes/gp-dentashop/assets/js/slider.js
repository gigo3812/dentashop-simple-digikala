/**
 * GP DentaShop - Stories System
 * بهینه + Instagram-like loading + Modal داینامیک
 */
(function() {
    'use strict';
    
    const STORY_DURATION = 5000;
    
    function initStories() {
        const storyButtons = document.querySelectorAll('[data-gpds-story]');
        if (!storyButtons.length) return;
        
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
        // 🎯 ساخت Modal (فقط یک بار، در اولین کلیک)
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
            
            // Append به body
            document.body.appendChild(modal);
            
            // 🎯 Event Listeners
            const closeBtns = modal.querySelectorAll('[data-gpds-story-close]');
            closeBtns.forEach(btn => btn.addEventListener('click', closeStory));
            
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
                else if (e.key === 'ArrowLeft')  prevStory(); // RTL
                else if (e.key === 'ArrowRight') nextStory(); // RTL
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
        
        // ============================================
        // 🎯 باز کردن استوری
        // ============================================
        function openStory(index) {
            if (isLoading) return;
            
            const story = stories[index];
            if (!story) return;
            
            currentIndex = index;
            isLoading = true;
            
            // 1. اضافه کردن کلاس loading به دکمه
            story.button.classList.add('is-loading');
            
            // 2. شروع پیش‌لود تصویر
            const img = new Image();
            img.onload = function() {
                // ✅ تصویر لود شد
                setTimeout(function() {
                    story.button.classList.remove('is-loading');
                    isLoading = false;
                    
                    // 3. باز کردن Modal
                    showModal(index);
                }, 400); // حداقل زمان لودینگ برای دیده شدن
            };
            
            img.onerror = function() {
                // ❌ خطا در لود تصویر
                story.button.classList.remove('is-loading');
                isLoading = false;
                console.error('[GPDS Story] Failed to load:', story.image);
            };
            
            img.src = story.image;
        }
        
        // ============================================
        // 🎯 نمایش Modal
        // ============================================
        function showModal(index) {
            const story = stories[index];
            if (!story) return;
            
            // ساخت Modal اگه نبود
            buildModal();
            
            // المان‌ها
            const img         = modal.querySelector('[data-gpds-story-image]');
            const avatar      = modal.querySelector('[data-gpds-story-avatar]');
            const username    = modal.querySelector('[data-gpds-story-username]');
            const cta         = modal.querySelector('[data-gpds-story-cta]');
            const progress    = modal.querySelector('[data-gpds-story-progress]');
            const prevBtn     = modal.querySelector('[data-gpds-story-prev]');
            const nextBtn     = modal.querySelector('[data-gpds-story-next]');
            
            // 🎯 Progress bars
            progress.innerHTML = '';
            stories.forEach((_, i) => {
                const bar = document.createElement('div');
                bar.className = 'gpds-story-modal__progress-bar';
                if (i < index)  bar.classList.add('is-done');
                if (i === index) bar.classList.add('is-active');
                progress.appendChild(bar);
            });
            
            // 🎯 محتوا
            img.src = story.image;
            img.alt = story.title;
            avatar.src = story.thumb;
            username.textContent = story.title;
            
            // 🎯 CTA
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
            
            // 🎯 ناوبری
            prevBtn.style.display = index > 0 ? '' : 'none';
            nextBtn.style.display = index < stories.length - 1 ? '' : 'none';
            
            // 🎯 نمایش Modal
            modal.style.display = 'flex';
            document.body.classList.add('gpds-story-open');
            
            // 🎯 تایمر
            startTimer();
        }
        
        // ============================================
        // 🎯 تایمر
        // ============================================
        function startTimer() {
            clearTimeout(timer);
            timer = setTimeout(nextStory, STORY_DURATION);
        }
        
        // ============================================
        // 🎯 ناوبری
        // ============================================
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
        
        // ============================================
        // 🎯 بستن
        // ============================================
        function closeStory() {
            clearTimeout(timer);
            
            if (modal) {
                modal.style.display = 'none';
                
                // پاک کردن محتوا
                const img = modal.querySelector('[data-gpds-story-image]');
                if (img) img.src = '';
            }
            
            document.body.classList.remove('gpds-story-open');
        }
        
        // ============================================
        // 🎯 رویداد کلیک روی استوری
        // ============================================
        storyButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const index = parseInt(this.dataset.storyIndex, 10);
                openStory(index);
            });
        });
        
    }
    
    // ============================================
    // اجرا
    // ============================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStories);
    } else {
        initStories();
    }
    
})();