/**
 * GP DentaShop - Footer Scripts
 * Back to Top + Newsletter + Smooth Scroll
 */
(function() {
    'use strict';
    
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    
    document.addEventListener('DOMContentLoaded', function() {
        initBackToTop();
        initNewsletter();
    });
    
    // ============================================
    // بازگشت به بالا
    // ============================================
    function initBackToTop() {
        const btn = $('[data-gpds-back-to-top]');
        if (!btn) return;
        
        // نمایش دکمه بعد از اسکرول
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
        
        // کلیک
        btn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth',
            });
        });
    }
    
    // ============================================
    // خبرنامه
    // ============================================
    function initNewsletter() {
        const form = $('[data-gpds-newsletter]');
        if (!form) return;
        
        const messageEl = $('[data-gpds-newsletter-message]', form.parentNode) 
            || $('[data-gpds-newsletter-message]');
        
        form.addEventListener('submit', async function(e) {
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
                const res = await fetch(GPDS.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                });
                
                const data = await res.json();
                
                if (data.success) {
                    if (messageEl) {
                        messageEl.textContent = data.data.message;
                        messageEl.className = 'gpds-footer-newsletter__message is-success';
                    }
                    input.value = '';
                } else {
                    if (messageEl) {
                        messageEl.textContent = data.data?.message || 'خطایی رخ داد';
                        messageEl.className = 'gpds-footer-newsletter__message is-error';
                    }
                }
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
    
})();