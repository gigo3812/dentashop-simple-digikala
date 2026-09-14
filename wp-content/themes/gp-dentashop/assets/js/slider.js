/**
 * GP DentaShop - Slider + Countdown + Stories
 * بهینه + کامل
 */
(function () {
    'use strict';

    // ============================================
    // 1. Swiper Sliders
    // ============================================
    function initSliders() {
        if (typeof Swiper === 'undefined') return;

        const mainSlider = document.querySelector('#gpds-home-slider');
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

        document.querySelectorAll('.gpds-products-carousel').forEach(function (el) {
            const nextEl = el.querySelector('.swiper-button-next');
            const prevEl = el.querySelector('.swiper-button-prev');

            new Swiper(el, {
                slidesPerView: 2,
                spaceBetween: 12,
                breakpoints: {
                    480: { slidesPerView: 2, spaceBetween: 12 },
                    640: { slidesPerView: 3, spaceBetween: 12 },
                    768: { slidesPerView: 4, spaceBetween: 12 },
                    1024: { slidesPerView: 5, spaceBetween: 14 },
                    1280: { slidesPerView: 6, spaceBetween: 14 },
                },
                navigation: nextEl && prevEl ? { nextEl, prevEl } : false,
            });
        });
    }

    // ============================================
    // 2. Countdown (شگفت‌انگیزها)
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
                        setTimeout(function () {
                            section.style.display = 'none';
                        }, 300);
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
    // 3. Stories System (بهینه)
    // ============================================
    function initStories() {
        const storyButtons = document.querySelectorAll('[data-gpds-story]');
        if (!storyButtons.length) return;

        // 🎯 State
        const IMAGE_DURATION = 5000; // 5 ثانیه برای تصویر
        const DEFAULT_VIDEO_DURATION = 0; // 0 = کل ویدیو

        const stories = Array.from(storyButtons).map(btn => {
            // 🎯 رفع مشکل: 0 باید حفظ بشه، نه 5
            const rawDuration = btn.dataset.storyVideoDuration;
            const parsedDuration = (rawDuration === '' || rawDuration === undefined || rawDuration === null) 
                ? DEFAULT_VIDEO_DURATION 
                : parseInt(rawDuration, 10);

            return {
                index: parseInt(btn.dataset.storyIndex, 10),
                image: btn.dataset.storyImage,
                thumb: btn.dataset.storyThumb,
                title: btn.dataset.storyTitle,
                url: btn.dataset.storyUrl,
                newTab: btn.dataset.storyNewTab === '1',
                video: btn.dataset.storyVideo || '',
                videoMime: btn.dataset.storyVideoMime || '',
                videoDuration: isNaN(parsedDuration) ? DEFAULT_VIDEO_DURATION : parsedDuration,
                button: btn,
            };
        });

        // 🎯 State Management
        const state = {
            modal: null,
            currentIndex: 0,
            timer: null,
            isLoading: false,
            isMuted: true,
            isPaused: false,
            videoEndedHandler: null,
        };

        // ============================================
        // ساخت Modal
        // ============================================
        function buildModal() {
            if (state.modal) return state.modal;

            const modal = document.createElement('div');
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
                        <video data-gpds-story-video preload="metadata" playsinline muted style="display:none;"></video>
                    </div>
                    
                    <div class="gpds-story-modal__actions">
                        <button type="button" class="gpds-story-modal__action-btn" data-gpds-story-mute aria-label="صدا" hidden>
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                <g data-gpds-icon-muted>
                                    <path d="M11 5L6 9H2v6h4l5 4V5z"/>
                                    <line x1="23" y1="9" x2="17" y2="15"/>
                                    <line x1="17" y1="9" x2="23" y2="15"/>
                                </g>
                                <g data-gpds-icon-unmuted style="display:none">
                                    <path d="M11 5L6 9H2v6h4l5 4V5z"/>
                                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
                                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>
                                </g>
                            </svg>
                        </button>
                        
                        <button type="button" class="gpds-story-modal__action-btn" data-gpds-story-pause aria-label="توقف/پخش" hidden>
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                <g data-gpds-icon-pause>
                                    <rect x="6" y="4" width="4" height="16"/>
                                    <rect x="14" y="4" width="4" height="16"/>
                                </g>
                                <g data-gpds-icon-play style="display:none">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </g>
                            </svg>
                        </button>
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
            state.modal = modal;

            // Event Listeners (یک بار)
            modal.querySelectorAll('[data-gpds-story-close]').forEach(btn => 
                btn.addEventListener('click', closeStory)
            );

            modal.querySelector('[data-gpds-story-prev]')?.addEventListener('click', e => {
                e.stopPropagation();
                prevStory();
            });

            modal.querySelector('[data-gpds-story-next]')?.addEventListener('click', e => {
                e.stopPropagation();
                nextStory();
            });

            modal.querySelector('[data-gpds-story-mute]')?.addEventListener('click', e => {
                e.stopPropagation();
                toggleMute();
            });

            modal.querySelector('[data-gpds-story-pause]')?.addEventListener('click', e => {
                e.stopPropagation();
                togglePause();
            });

            // Keyboard
            document.addEventListener('keydown', function (e) {
                if (!document.body.classList.contains('gpds-story-open')) return;

                switch (e.key) {
                    case 'Escape': closeStory(); break;
                    case 'ArrowLeft': prevStory(); break;
                    case 'ArrowRight': nextStory(); break;
                    case 'm': case 'M': toggleMute(); break;
                    case ' ': e.preventDefault(); togglePause(); break;
                }
            });

            // Touch swipe
            let touchStartX = 0;
            modal.addEventListener('touchstart', e => {
                touchStartX = e.touches[0].clientX;
            }, { passive: true });

            modal.addEventListener('touchend', e => {
                const diff = touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) prevStory();
                    else nextStory();
                }
            }, { passive: true });

            return modal;
        }

        // ============================================
        // 🎯 Mute Toggle
        // ============================================
        function toggleMute() {
            if (!state.modal) return;

            const video = state.modal.querySelector('[data-gpds-story-video]');
            if (!video || video.style.display === 'none') return;

            state.isMuted = !state.isMuted;
            video.muted = state.isMuted;

            const iconMuted = state.modal.querySelector('[data-gpds-icon-muted]');
            const iconUnmuted = state.modal.querySelector('[data-gpds-icon-unmuted]');

            if (iconMuted && iconUnmuted) {
                iconMuted.style.display = state.isMuted ? '' : 'none';
                iconUnmuted.style.display = state.isMuted ? 'none' : '';
            }
        }

        // ============================================
        // 🎯 Pause/Play
        // ============================================
        function togglePause() {
            if (!state.modal) return;

            const video = state.modal.querySelector('[data-gpds-story-video]');
            const isVideo = video && video.style.display !== 'none';

            if (isVideo) {
                if (video.paused) {
                    video.play();
                    state.isPaused = false;
                    // ادامه تایمر فقط برای ویدیوی مدت‌دار
                    if (stories[state.currentIndex].videoDuration > 0) {
                        startTimer(stories[state.currentIndex]);
                    }
                } else {
                    video.pause();
                    state.isPaused = true;
                    clearTimeout(state.timer);
                }
            }

            updatePauseIcon();
        }

        function updatePauseIcon() {
            if (!state.modal) return;

            const video = state.modal.querySelector('[data-gpds-story-video]');
            const isVideo = video && video.style.display !== 'none';
            const isPausedNow = isVideo ? video.paused : state.isPaused;

            const iconPause = state.modal.querySelector('[data-gpds-icon-pause]');
            const iconPlay = state.modal.querySelector('[data-gpds-icon-play]');

            if (iconPause && iconPlay) {
                iconPause.style.display = isPausedNow ? 'none' : '';
                iconPlay.style.display = isPausedNow ? 'none' : '';
                iconPause.style.display = isPausedNow ? 'none' : '';
                iconPlay.style.display = isPausedNow ? '' : 'none';
            }
        }

        // ============================================
        // 🎯 Timer برای تصویر یا ویدیوی مدت‌دار
        // ============================================
        function startTimer(story) {
            clearTimeout(state.timer);
            state.timer = null;

            if (!story) return;

            let duration = IMAGE_DURATION; // پیش‌فرض برای تصویر

            // اگه ویدیو داره و مدت مشخصی تنظیم شده
            if (story.video && story.videoDuration > 0) {
                duration = story.videoDuration * 1000;
            }
            // اگه ویدیو داره ولی مدت 0 (کل ویدیو) → تایمر نذار
            else if (story.video && story.videoDuration === 0) {
                return; // `ended` event مدیریت می‌کنه
            }

            // آپدیت progress bar
            const activeBar = state.modal.querySelector('.gpds-story-modal__progress-bar.is-active');
            if (activeBar) {
                activeBar.style.setProperty('--story-duration', (duration / 1000) + 's');
            }

            state.timer = setTimeout(function () {
                state.timer = null;
                nextStory();
            }, duration);
        }

        // ============================================
        // 🎯 ناوبری
        // ============================================
        function nextStory() {
            clearTimeout(state.timer);
            state.timer = null;
            stopVideo();

            if (state.currentIndex < stories.length - 1) {
                showModal(state.currentIndex + 1);
            } else {
                closeStory();
            }
        }

        function prevStory() {
            clearTimeout(state.timer);
            state.timer = null;
            stopVideo();

            if (state.currentIndex > 0) {
                showModal(state.currentIndex - 1);
            }
        }

        function stopVideo() {
            if (!state.modal) return;
            const video = state.modal.querySelector('[data-gpds-story-video]');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        }

        // ============================================
        // 🎯 بستن
        // ============================================
        function closeStory() {
            clearTimeout(state.timer);
            state.timer = null;
            state.isPaused = false;

            if (state.modal) {
                state.modal.style.display = 'none';

                const img = state.modal.querySelector('[data-gpds-story-image]');
                if (img) img.src = '';

                const video = state.modal.querySelector('[data-gpds-story-video]');
                if (video) {
                    video.pause();
                    video.innerHTML = '';
                    video.removeAttribute('src');
                    video.load();
                }
            }

            document.body.classList.remove('gpds-story-open');
        }

        // ============================================
        // 🎯 نمایش Modal
        // ============================================
        function showModal(index) {
            const story = stories[index];
            if (!story) return;

            buildModal();

            const modal = state.modal;
            const img = modal.querySelector('[data-gpds-story-image]');
            const video = modal.querySelector('[data-gpds-story-video]');
            const avatar = modal.querySelector('[data-gpds-story-avatar]');
            const username = modal.querySelector('[data-gpds-story-username]');
            const cta = modal.querySelector('[data-gpds-story-cta]');
            const progress = modal.querySelector('[data-gpds-story-progress]');
            const prevBtn = modal.querySelector('[data-gpds-story-prev]');
            const nextBtn = modal.querySelector('[data-gpds-story-next]');
            const muteBtn = modal.querySelector('[data-gpds-story-mute]');
            const pauseBtn = modal.querySelector('[data-gpds-story-pause]');

            state.isPaused = false;
            state.currentIndex = index;

            // 🎯 Progress bars
            progress.innerHTML = '';
            stories.forEach((_, i) => {
                const bar = document.createElement('div');
                bar.className = 'gpds-story-modal__progress-bar';
                if (i < index) bar.classList.add('is-done');
                if (i === index) bar.classList.add('is-active');
                progress.appendChild(bar);
            });

            // 🎯 تصویر یا ویدیو
            if (story.video) {
                showVideo(story, video, img, muteBtn, pauseBtn);
            } else {
                showImage(story, video, img, muteBtn, pauseBtn);
            }

            // Avatar + Username
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

            // نمایش
            modal.style.display = 'flex';
            document.body.classList.add('gpds-story-open');
        }

        // ============================================
        // 🎯 نمایش ویدیو
        // ============================================
        function showVideo(story, video, img, muteBtn, pauseBtn) {
            img.style.display = 'none';
            img.src = '';

            video.style.display = '';
            video.muted = state.isMuted;

            // Reset
            video.pause();
            video.innerHTML = '';
            video.removeAttribute('src');
            video.load();

            // Source
            const source = document.createElement('source');
            source.src = story.video;
            source.type = story.videoMime || 'video/mp4';
            video.appendChild(source);
            video.load();
            video.currentTime = 0;

            // دکمه‌ها
            muteBtn.hidden = false;
            pauseBtn.hidden = false;
            updatePauseIcon();

            // Mute icon
            const iconMuted = state.modal.querySelector('[data-gpds-icon-muted]');
            const iconUnmuted = state.modal.querySelector('[data-gpds-icon-unmuted]');
            if (iconMuted && iconUnmuted) {
                iconMuted.style.display = state.isMuted ? '' : 'none';
                iconUnmuted.style.display = state.isMuted ? 'none' : '';
            }

            // 🎯 وقتی آماده شد
            video.addEventListener('canplay', function onCanPlay() {
                video.removeEventListener('canplay', onCanPlay);

                video.play().catch(err => console.warn('Autoplay blocked:', err));

                const realDuration = (video.duration && !isNaN(video.duration)) ? video.duration : 0;
                const activeBar = state.modal.querySelector('.gpds-story-modal__progress-bar.is-active');

                console.log('[GPDS] videoDuration =', story.videoDuration, '| realDuration =', realDuration);

                if (story.videoDuration === 0) {
                    // 🎬 کل ویدیو
                    if (realDuration > 0) {
                        // 🎯 progress bar با مدت واقعی
                        if (activeBar) {
                            // مستقیم از animation استفاده کن
                            activeBar.style.animation = 'none';
                            void activeBar.offsetWidth;
                            activeBar.style.setProperty('--story-duration', realDuration + 's');
                            activeBar.style.animation = '';
                        }

                        // 🎯 `ended` event
                        state.videoEndedHandler = function () {
                            video.removeEventListener('ended', state.videoEndedHandler);
                            state.videoEndedHandler = null;
                            if (!state.isPaused) nextStory();
                        };
                        video.addEventListener('ended', state.videoEndedHandler, { once: true });
                    } else {
                        // fallback
                        if (activeBar) {
                            activeBar.style.animation = 'gpds-story-progress 30s linear forwards';
                        }
                        state.timer = setTimeout(function () {
                            state.timer = null;
                            nextStory();
                        }, 30000);
                    }
                } else {
                    // ⏱️ مدت مشخص
                    const durationMs = story.videoDuration * 1000;

                    // 🎯 progress bar
                    if (activeBar) {
                        activeBar.style.animation = 'none';
                        void activeBar.offsetWidth;
                        activeBar.style.setProperty('--story-duration', story.videoDuration + 's');
                        activeBar.style.animation = '';
                    }

                    // تایمر
                    clearTimeout(state.timer);
                    state.timer = setTimeout(function () {
                        state.timer = null;
                        nextStory();
                    }, durationMs);
                }
            }, { once: true });
        }
        // ============================================
        // 🎯 نمایش تصویر
        // ============================================
        function showImage(story, video, img, muteBtn, pauseBtn) {
            video.style.display = 'none';
            video.pause();
            video.innerHTML = '';
            video.removeAttribute('src');

            img.style.display = '';
            img.src = story.image;
            img.alt = story.title;

            muteBtn.hidden = true;
            pauseBtn.hidden = true;

            startTimer(story);
        }

        // ============================================
        // 🎯 باز کردن استوری
        // ============================================
        function openStory(index) {
            if (state.isLoading) return;

            const story = stories[index];
            if (!story) return;

            state.isLoading = true;
            story.button.classList.add('is-loading');

            if (story.video) {
                // 🎬 پیش‌لود ویدیو (metadata)
                const vid = document.createElement('video');
                vid.preload = 'metadata';
                vid.muted = true;

                vid.addEventListener('loadedmetadata', function () {
                    story.button.classList.remove('is-loading');
                    state.isLoading = false;
                    showModal(index);
                }, { once: true });

                vid.addEventListener('error', function () {
                    story.button.classList.remove('is-loading');
                    state.isLoading = false;
                    console.error('[GPDS Story] Video failed:', story.video);
                }, { once: true });

                vid.src = story.video;
            } else {
                // 🖼️ پیش‌لود تصویر
                const img = new Image();
                img.onload = function () {
                    setTimeout(function () {
                        story.button.classList.remove('is-loading');
                        state.isLoading = false;
                        showModal(index);
                    }, 200);
                };
                img.onerror = function () {
                    story.button.classList.remove('is-loading');
                    state.isLoading = false;
                    console.error('[GPDS Story] Image failed:', story.image);
                };
                img.src = story.image;
            }
        }

        // ============================================
        // 🎯 رویداد کلیک
        // ============================================
        storyButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openStory(parseInt(this.dataset.storyIndex, 10));
            });
        });
    }

    // ============================================
    // اجرا
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