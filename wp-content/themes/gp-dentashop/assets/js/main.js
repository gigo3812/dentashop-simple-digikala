/**
 * GP DentaShop - Main Script
 * بهینه‌شده - Loading Screen سبک
 */
(function () {
    'use strict';

    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    // ============================================
    // 🎯 Loading Screen - بهینه
    // ============================================

    // شروع فوری (قبل از DOMContentLoaded)
    function initLoaderEarly() {
        const loader = document.getElementById('gpds-loader');
        if (!loader) return;

        // ذخیره start time
        loader._gpdsStart = performance.now();

        // شروع انیمیشن progress با CSS (بدون JS)
        loader.classList.add('is-loading');
    }

    // اجرای فوری
    if (document.readyState === 'loading') {
        initLoaderEarly();
    } else {
        initLoaderEarly();
    }

    // ============================================
    // مخفی کردن لودر
    // ============================================
    function hideLoader() {
        const loader = document.getElementById('gpds-loader');
        if (!loader || loader._gpdsHidden) return;

        loader._gpdsHidden = true;

        const elapsed = performance.now() - (loader._gpdsStart || 0);
        const MIN_TIME = 300;
        const remaining = Math.max(0, MIN_TIME - elapsed);

        setTimeout(function () {
            loader.classList.remove('is-loading');
            loader.classList.add('is-hidden');

            setTimeout(function () {
                if (loader && loader.parentNode) {
                    loader.parentNode.removeChild(loader);
                }
            }, 500);
        }, remaining);
    }

    // ============================================
    // استراتژی مخفی کردن (بهینه)
    // ============================================
    function setupLoaderStrategy() {
        const loader = document.getElementById('gpds-loader');
        if (!loader) return;

        // 🎯 روش 1: DOMContentLoaded (سریع‌ترین)
        // - صفحه پارس شده، می‌تونه محو بشه
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                // تاخیر کوتاه برای smooth transition
                requestAnimationFrame(function () {
                    setTimeout(hideLoader, 100);
                });
            });
        } else if (document.readyState === 'interactive') {
            setTimeout(hideLoader, 100);
        } else {
            hideLoader();
        }

        // 🎯 روش 2: window.load (fallback برای تصاویر)
        // - فقط اگه هنوز مخفی نشده
        window.addEventListener('load', function () {
            if (!loader._gpdsHidden) {
                setTimeout(hideLoader, 200);
            }
        });

        // 🎯 روش 3: Fallback قطعی (ضد گیر)
        // - حداکثر 3 ثانیه (نه 8!)
        setTimeout(function () {
            if (!loader._gpdsHidden) {
                hideLoader();
            }
        }, 3000);
    }

    // راه‌اندازی استراتژی
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupLoaderStrategy);
    } else {
        setupLoaderStrategy();
    }

    // ============================================
    // بقیه اسکریپت‌ها (بدون تغییر)
    // ============================================

    document.addEventListener('DOMContentLoaded', function () {
        document.documentElement.classList.add('gpds-js-ready');

        initDropdowns();
        initMegaMenu();
        initMobileMenu();
        initSearchOverlay();
    });

    // Dropdown
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

    // Mega Menu
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
            panels.forEach(p => {
                p.hidden = p.dataset.gpdsMmPanel !== id;
            });
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
    function initMobileMenu() {

        const trigger = $('[data-gpds-mobile-menu-trigger]');
        const menu = $('[data-gpds-mobile-menu]');
        const overlay = $('[data-gpds-mobile-menu-overlay]');
        const closeBtn = $('[data-gpds-mobile-menu-close]');

        if (!trigger || !menu) return;


        /*
         * -------------------------------------------------------
         * State
         * -------------------------------------------------------
         */

        let lastFocusedElement = null;


        /*
         * -------------------------------------------------------
         * Elements
         * -------------------------------------------------------
         */

        const rootLevel = $('[data-gpds-mobile-level="root"]');
        const categoriesLevel = $('[data-gpds-mobile-level="categories"]');

        const categoriesTrigger =
            $('[data-gpds-mobile-categories-trigger]');

        const categoryTriggers =
            $$('[data-gpds-mobile-category-trigger]');

        const categoryBackButtons =
            $$('[data-gpds-mobile-category-back]');


        /*
         * -------------------------------------------------------
         * Helpers
         * -------------------------------------------------------
         */

        const showElement = (element) => {

            if (!element) return;

            element.hidden = false;

            requestAnimationFrame(() => {
                element.classList.add('is-visible');
            });
        };


        const hideElement = (element) => {

            if (!element) return;

            element.classList.remove('is-visible');

            element.hidden = true;
        };


        const closeSubLevels = () => {

            $$('[data-gpds-mobile-sublevel]').forEach((level) => {

                level.hidden = true;

                const category = level.closest(
                    '.gpds-mobile-menu__category'
                );

                const button = category
                    ? $('[data-gpds-mobile-category-trigger]', category)
                    : null;

                if (button) {
                    button.setAttribute('aria-expanded', 'false');
                }

            });
        };


        const showRootLevel = () => {

            if (categoriesLevel) {
                categoriesLevel.hidden = true;
            }

            if (rootLevel) {
                rootLevel.hidden = false;
            }

            if (categoriesTrigger) {
                categoriesTrigger.setAttribute(
                    'aria-expanded',
                    'false'
                );
            }

            closeSubLevels();
        };


        const showCategoriesLevel = () => {

            if (!categoriesLevel) return;

            if (rootLevel) {
                rootLevel.hidden = true;
            }

            categoriesLevel.hidden = false;

            if (categoriesTrigger) {
                categoriesTrigger.setAttribute(
                    'aria-expanded',
                    'true'
                );
            }

            closeSubLevels();

            const backButton =
                $('[data-gpds-mobile-back="root"]');

            if (backButton) {
                setTimeout(() => backButton.focus(), 30);
            }
        };


        const openSubLevel = (id, button) => {

            const sublevel =
                $('[data-gpds-mobile-sublevel="' + id + '"]');

            if (!sublevel) return;

            /*
             * فقط یک زیرمنو هم‌زمان باز باشد.
             */

            closeSubLevels();

            sublevel.hidden = false;

            if (button) {
                button.setAttribute(
                    'aria-expanded',
                    'true'
                );
            }

            const backButton =
                $('[data-gpds-mobile-category-back]', sublevel);

            if (backButton) {
                setTimeout(() => backButton.focus(), 30);
            }
        };


        /*
         * -------------------------------------------------------
         * Open menu
         * -------------------------------------------------------
         */

        const openMenu = () => {

            lastFocusedElement =
                document.activeElement;

            menu.hidden = false;

            if (overlay) {
                overlay.hidden = false;
            }

            menu.setAttribute(
                'aria-hidden',
                'false'
            );

            trigger.setAttribute(
                'aria-expanded',
                'true'
            );

            document.documentElement.classList.add(
                'gpds-mobile-menu-open'
            );

            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {

                menu.classList.add('is-open');

                if (overlay) {
                    overlay.classList.add('is-visible');
                }

            });

            showRootLevel();

            setTimeout(() => {

                if (closeBtn) {
                    closeBtn.focus();
                }

            }, 30);
        };


        /*
         * -------------------------------------------------------
         * Close menu
         * -------------------------------------------------------
         */

        const closeMenu = () => {

            menu.classList.remove('is-open');

            if (overlay) {
                overlay.classList.remove('is-visible');
            }

            menu.setAttribute(
                'aria-hidden',
                'true'
            );

            trigger.setAttribute(
                'aria-expanded',
                'false'
            );

            document.documentElement.classList.remove(
                'gpds-mobile-menu-open'
            );

            document.body.style.overflow = '';

            showRootLevel();

            setTimeout(() => {

                menu.hidden = true;

                if (overlay) {
                    overlay.hidden = true;
                }

            }, 220);

            if (
                lastFocusedElement &&
                typeof lastFocusedElement.focus === 'function'
            ) {
                setTimeout(() => {
                    lastFocusedElement.focus();
                }, 30);
            }
        };


        /*
         * -------------------------------------------------------
         * Main menu trigger
         * -------------------------------------------------------
         */

        trigger.addEventListener(
            'click',
            (event) => {

                event.preventDefault();

                const isOpen =
                    trigger.getAttribute('aria-expanded') === 'true';

                if (isOpen) {
                    closeMenu();
                } else {
                    openMenu();
                }

            }
        );


        /*
         * -------------------------------------------------------
         * Close button
         * -------------------------------------------------------
         */

        if (closeBtn) {

            closeBtn.addEventListener(
                'click',
                (event) => {

                    event.preventDefault();

                    closeMenu();

                }
            );

        }


        /*
         * -------------------------------------------------------
         * Overlay
         * -------------------------------------------------------
         */

        if (overlay) {

            overlay.addEventListener(
                'click',
                closeMenu
            );

        }


        /*
         * -------------------------------------------------------
         * Product categories
         * -------------------------------------------------------
         */

        if (categoriesTrigger) {

            categoriesTrigger.addEventListener(
                'click',
                (event) => {

                    event.preventDefault();

                    showCategoriesLevel();

                }
            );

        }


        /*
         * -------------------------------------------------------
         * Back to root
         * -------------------------------------------------------
         */

        const rootBack =
            $('[data-gpds-mobile-back="root"]');

        if (rootBack) {

            rootBack.addEventListener(
                'click',
                (event) => {

                    event.preventDefault();

                    showRootLevel();

                    if (categoriesTrigger) {
                        setTimeout(
                            () => categoriesTrigger.focus(),
                            30
                        );
                    }

                }
            );

        }


        /*
         * -------------------------------------------------------
         * Category -> children
         * -------------------------------------------------------
         */

        categoryTriggers.forEach((button) => {

            button.addEventListener(
                'click',
                (event) => {

                    event.preventDefault();

                    const id =
                        button.getAttribute(
                            'data-gpds-mobile-category-trigger'
                        );

                    if (!id) return;

                    openSubLevel(id, button);

                }
            );

        });


        /*
         * -------------------------------------------------------
         * Children -> category list
         * -------------------------------------------------------
         */

        categoryBackButtons.forEach((button) => {

            button.addEventListener(
                'click',
                (event) => {

                    event.preventDefault();

                    const sublevel =
                        button.closest(
                            '[data-gpds-mobile-sublevel]'
                        );

                    if (sublevel) {

                        sublevel.hidden = true;

                        const category =
                            sublevel.closest(
                                '.gpds-mobile-menu__category'
                            );

                        if (category) {

                            const trigger =
                                $('[data-gpds-mobile-category-trigger]', category);

                            if (trigger) {

                                trigger.setAttribute(
                                    'aria-expanded',
                                    'false'
                                );

                                setTimeout(
                                    () => trigger.focus(),
                                    30
                                );

                            }

                        }

                    }

                }
            );

        });


        /*
         * -------------------------------------------------------
         * Escape
         * -------------------------------------------------------
         */

        document.addEventListener(
            'keydown',
            (event) => {

                if (event.key !== 'Escape') return;

                if (
                    trigger.getAttribute('aria-expanded') === 'true'
                ) {
                    closeMenu();
                }

            }
        );


        /*
         * -------------------------------------------------------
         * Prevent menu links from accidentally bubbling
         * -------------------------------------------------------
         */

        menu.addEventListener(
            'click',
            (event) => {

                const link =
                    event.target.closest('a');

                if (!link) return;

                /*
                 * لینک‌های واقعی اجازه دارند
                 * عادی عمل کنند.
                 */

            }
        );

    }
    // Search Overlay
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
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) close();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !overlay.hidden) close();
        });
    }

})();