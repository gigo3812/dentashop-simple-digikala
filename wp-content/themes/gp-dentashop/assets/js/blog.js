/**
 * GP DentaShop - Blog Scripts
 * TOC + Copy URL + Smooth Scroll
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initTOC();
        initCopyURL();
    });

    /**
     * فهرست مطالب (TOC)
     */
    function initTOC() {
        var content = document.querySelector('.gpds-post__content');
        var tocList = document.querySelector('.gpds-post__toc-list');

        if (!content || !tocList) return;

        var headings = content.querySelectorAll('h2');
        var toc = document.querySelector('.gpds-post__toc');

        if (!headings.length) {
            if (toc) toc.style.display = 'none';
            return;
        }

        // ID دهی به هدینگ‌ها
        headings.forEach(function (h, i) {
            h.id = 'heading-' + (i + 1);
        });

        var links = tocList.querySelectorAll('a');

        // Smooth scroll
        links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.getAttribute('href').substring(1);
                var target = document.getElementById(id);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.pushState(null, '', '#' + id);
                }
            });
        });

        // Active link on scroll
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        links.forEach(function (l) { l.classList.remove('is-active'); });
                        var active = tocList.querySelector('a[href="#' + entry.target.id + '"]');
                        if (active) active.classList.add('is-active');
                    }
                });
            }, {
                rootMargin: '-100px 0px -70% 0px',
                threshold: 0,
            });

            headings.forEach(function (h) { observer.observe(h); });
        }
    }

    /**
     * کپی لینک مقاله
     */
    function initCopyURL() {
        var btn = document.querySelector('[data-gpds-copy-url]');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var url = this.dataset.gpdsCopyUrl;
            if (!url) return;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () {
                    showToast('لینک کپی شد ✓');
                }).catch(function () {
                    fallbackCopy(url);
                });
            } else {
                fallbackCopy(url);
            }
        });

        function fallbackCopy(text) {
            var input = document.createElement('textarea');
            input.value = text;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            try {
                document.execCommand('copy');
                showToast('لینک کپی شد ✓');
            } catch (e) {
                showToast('کپی نشد', 'error');
            }
            document.body.removeChild(input);
        }

        function showToast(message, type) {
            var toast = document.createElement('div');
            toast.className = 'gpds-toast' + (type === 'error' ? ' gpds-toast--error' : '');
            toast.textContent = message;
            document.body.appendChild(toast);

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    toast.classList.add('is-visible');
                });
            });

            setTimeout(function () {
                toast.classList.remove('is-visible');
                setTimeout(function () { toast.remove(); }, 300);
            }, 2000);
        }
    }

})();