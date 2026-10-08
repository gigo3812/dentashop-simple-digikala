(function (window, document) {
    'use strict';

    /*
     * Lightweight Swiper-compatible slider
     * Designed for gp-dentashop
     * Supports:
     * - RTL
     * - slidesPerView
     * - spaceBetween
     * - breakpoints
     * - loop
     * - autoplay
     * - navigation
     * - pagination
     * - speed
     * - grabCursor
     * - mouse/touch dragging
     */

    function getNumber(value, fallback) {
        var number = parseFloat(value);
        return isNaN(number) ? fallback : number;
    }

    function getElement(element, container) {
        if (!element) {
            return null;
        }

        if (typeof element === 'string') {
            return (container || document).querySelector(element);
        }

        return element;
    }

    function getDirection(container, params) {
        if (params && params.direction === 'rtl') {
            return 'rtl';
        }

        if (
            container &&
            container.getAttribute &&
            container.getAttribute('dir') === 'rtl'
        ) {
            return 'rtl';
        }

        if (
            document.documentElement &&
            document.documentElement.getAttribute('dir') === 'rtl'
        ) {
            return 'rtl';
        }

        if (
            document.body &&
            document.body.getAttribute('dir') === 'rtl'
        ) {
            return 'rtl';
        }

        return 'ltr';
    }

    function getBreakpointParams(params) {
        var width = window.innerWidth || document.documentElement.clientWidth;
        var result = {};

        Object.keys(params || {}).forEach(function (key) {
            if (key === 'breakpoints') {
                return;
            }

            result[key] = params[key];
        });

        if (params.breakpoints) {
            var matched = 0;

            Object.keys(params.breakpoints).forEach(function (breakpoint) {
                var minWidth = parseInt(breakpoint, 10);

                if (
                    !isNaN(minWidth) &&
                    width >= minWidth &&
                    minWidth >= matched
                ) {
                    matched = minWidth;

                    Object.keys(params.breakpoints[breakpoint]).forEach(function (key) {
                        result[key] = params.breakpoints[breakpoint][key];
                    });
                }
            });
        }

        return result;
    }

    function Swiper(container, options) {
        if (!(this instanceof Swiper)) {
            return new Swiper(container, options);
        }

        this.el = getElement(container);

        if (!this.el) {
            return;
        }

        this.wrapper = this.el.querySelector('.swiper-wrapper');

        if (!this.wrapper) {
            return;
        }

        this.originalSlides = Array.prototype.slice.call(
            this.wrapper.children
        );

        this.slides = [];
        this.params = options || {};
        this.currentParams = {};
        this.currentIndex = 0;
        this.realIndex = 0;
        this.translate = 0;
        this.timer = null;
        this.dragging = false;
        this.startX = 0;
        this.startTranslate = 0;
        this.lastX = 0;
        this.pointerId = null;

        this.direction = getDirection(this.el, this.params);

        this.init();
    }

    Swiper.prototype.init = function () {
        this.el.classList.add('swiper-initialized');

        this.el.style.overflow = 'hidden';

        this.wrapper.style.display = 'flex';
        this.wrapper.style.width = '100%';
        this.wrapper.style.boxSizing = 'border-box';
        this.wrapper.style.willChange = 'transform';

        this.setupSlides();
        this.setupNavigation();
        this.setupPagination();
        this.setupDragging();
        this.setupResize();

        this.update();

        if (this.params.autoplay) {
            this.startAutoplay();
        }
    };

    Swiper.prototype.setupSlides = function () {
        var self = this;

        this.slides = Array.prototype.slice.call(
            this.wrapper.children
        );

        /*
         * Loop support
         */
        if (this.params.loop && !this.el.dataset.loopReady) {
            this.el.dataset.loopReady = '1';

            var visible = parseInt(
                this.currentParams.slidesPerView || this.params.slidesPerView || 1,
                10
            );

            if (visible > 1 && this.slides.length > visible) {
                var firstSlides = this.slides.slice(0, visible);
                var lastSlides = this.slides.slice(-visible);

                lastSlides.forEach(function (slide) {
                    var clone = slide.cloneNode(true);
                    clone.setAttribute('data-swiper-clone', 'true');
                    self.wrapper.insertBefore(clone, self.wrapper.firstChild);
                });

                firstSlides.forEach(function (slide) {
                    var clone = slide.cloneNode(true);
                    clone.setAttribute('data-swiper-clone', 'true');
                    self.wrapper.appendChild(clone);
                });

                this.slides = Array.prototype.slice.call(
                    this.wrapper.children
                );

                this.loopOffset = visible;
            }
        }
    };

    Swiper.prototype.getParams = function () {
        this.currentParams = getBreakpointParams(this.params);

        this.direction = getDirection(
            this.el,
            this.currentParams
        );

        return this.currentParams;
    };

    Swiper.prototype.update = function () {
        var params = this.getParams();

        var slidesPerView = params.slidesPerView || 1;
        var spaceBetween = getNumber(params.spaceBetween, 0);

        var totalSlides = this.slides.length;

        if (!totalSlides) {
            return;
        }

        var containerWidth = this.el.clientWidth;

        if (!containerWidth) {
            return;
        }

        var slideWidth;

        if (slidesPerView === 'auto') {
            slideWidth = 'auto';
        } else {
            slideWidth =
                (
                    containerWidth -
                    (slidesPerView - 1) * spaceBetween
                ) / slidesPerView;
        }

        this.slides.forEach(function (slide) {
            slide.style.boxSizing = 'border-box';

            if (slideWidth !== 'auto') {
                slide.style.width = slideWidth + 'px';
                slide.style.flex = '0 0 ' + slideWidth + 'px';
            } else {
                slide.style.flex = '0 0 auto';
            }

            slide.style.marginRight = '0px';
            slide.style.marginLeft = '0px';

            if (spaceBetween) {
                if (this.direction === 'rtl') {
                    slide.style.marginLeft = spaceBetween + 'px';
                } else {
                    slide.style.marginRight = spaceBetween + 'px';
                }
            }
        }, this);

        this.wrapper.style.transform =
            'translate3d(' + this.getTranslateForIndex(this.currentIndex) + 'px,0,0)';

        this.updatePagination();
    };

    Swiper.prototype.getSlideSize = function () {
        var params = this.currentParams;
        var slidesPerView = params.slidesPerView || 1;
        var spaceBetween = getNumber(params.spaceBetween, 0);

        var width = this.el.clientWidth;

        if (slidesPerView === 'auto') {
            var first = this.slides[0];

            return first ? first.offsetWidth + spaceBetween : width;
        }

        return (
            (
                width -
                (slidesPerView - 1) * spaceBetween
            ) / slidesPerView
        ) + spaceBetween;
    };

    Swiper.prototype.getTranslateForIndex = function (index) {
        var slideSize = this.getSlideSize();

        if (this.direction === 'rtl') {
            return index * slideSize;
        }

        return -(index * slideSize);
    };

    Swiper.prototype.applyTranslate = function (translate, speed) {
        var self = this;

        this.wrapper.style.transition =
            'transform ' + (speed || 0) + 'ms ease';

        this.wrapper.style.transform =
            'translate3d(' + translate + 'px,0,0)';

        this.translate = translate;

        if (speed) {
            clearTimeout(this.transitionTimer);

            this.transitionTimer = setTimeout(function () {
                self.wrapper.style.transition = '';
            }, speed);
        }
    };

    Swiper.prototype.slideTo = function (index, speed) {
        if (!this.slides.length) {
            return;
        }

        var params = this.currentParams;

        speed =
            typeof speed === 'number'
                ? speed
                : getNumber(params.speed, 300);

        var totalOriginal =
            this.originalSlides.length || this.slides.length;

        var loopOffset = this.loopOffset || 0;

        if (params.loop && loopOffset) {
            if (index < loopOffset) {
                index = totalOriginal + index;
            }

            if (index >= totalOriginal + loopOffset) {
                index = loopOffset;
            }
        } else {
            index = Math.max(
                0,
                Math.min(index, this.slides.length - 1)
            );
        }

        this.currentIndex = index;

        this.realIndex =
            params.loop && loopOffset
                ? (
                    (index - loopOffset) %
                    totalOriginal +
                    totalOriginal
                ) % totalOriginal
                : index;

        var translate =
            this.getTranslateForIndex(index);

        this.applyTranslate(translate, speed);
        this.updatePagination();

        if (
            params.loop &&
            loopOffset &&
            index >= totalOriginal + loopOffset
        ) {
            var self = this;

            setTimeout(function () {
                self.currentIndex = loopOffset;
                self.applyTranslate(
                    self.getTranslateForIndex(loopOffset),
                    0
                );
            }, speed + 5);
        }

        if (
            params.loop &&
            loopOffset &&
            index < loopOffset
        ) {
            var self2 = this;

            setTimeout(function () {
                self2.currentIndex =
                    totalOriginal + loopOffset - 1;

                self2.applyTranslate(
                    self2.getTranslateForIndex(
                        self2.currentIndex
                    ),
                    0
                );
            }, speed + 5);
        }
    };

    Swiper.prototype.slideNext = function () {
        this.slideTo(
            this.currentIndex + 1,
            this.currentParams.speed || 300
        );

        if (
            this.currentParams.autoplay &&
            this.currentParams.autoplay.disableOnInteraction
        ) {
            this.stopAutoplay();
        }
    };

    Swiper.prototype.slidePrev = function () {
        this.slideTo(
            this.currentIndex - 1,
            this.currentParams.speed || 300
        );

        if (
            this.currentParams.autoplay &&
            this.currentParams.autoplay.disableOnInteraction
        ) {
            this.stopAutoplay();
        }
    };

    Swiper.prototype.setupNavigation = function () {
        var self = this;
        var navigation = this.params.navigation;

        if (!navigation) {
            return;
        }

        var next = getElement(
            navigation.nextEl,
            this.el
        );

        var prev = getElement(
            navigation.prevEl,
            this.el
        );

        if (next) {
            next.addEventListener('click', function (event) {
                event.preventDefault();
                self.slideNext();
            });
        }

        if (prev) {
            prev.addEventListener('click', function (event) {
                event.preventDefault();
                self.slidePrev();
            });
        }

        this.nextEl = next;
        this.prevEl = prev;
    };

    Swiper.prototype.setupPagination = function () {
        var pagination = this.params.pagination;

        if (!pagination) {
            return;
        }

        this.paginationEl = getElement(
            pagination.el,
            this.el
        );

        if (!this.paginationEl) {
            return;
        }

        this.paginationEl.innerHTML = '';

        var count =
            this.originalSlides.length ||
            this.slides.length;

        for (var i = 0; i < count; i++) {
            var bullet = document.createElement('span');

            bullet.className =
                'swiper-pagination-bullet';

            bullet.dataset.index = i;

            if (pagination.clickable) {
                bullet.style.cursor = 'pointer';

                bullet.addEventListener(
                    'click',
                    this.createPaginationHandler(i)
                );
            }

            this.paginationEl.appendChild(bullet);
        }

        this.updatePagination();
    };

    Swiper.prototype.createPaginationHandler = function (index) {
        var self = this;

        return function (event) {
            event.preventDefault();

            var target = index;

            if (self.loopOffset) {
                target += self.loopOffset;
            }

            self.slideTo(
                target,
                self.currentParams.speed || 300
            );
        };
    };

    Swiper.prototype.updatePagination = function () {
        if (!this.paginationEl) {
            return;
        }

        var bullets =
            this.paginationEl.querySelectorAll(
                '.swiper-pagination-bullet'
            );

        var active =
            this.realIndex || 0;

        Array.prototype.forEach.call(
            bullets,
            function (bullet, index) {
                bullet.classList.toggle(
                    'swiper-pagination-bullet-active',
                    index === active
                );
            }
        );
    };

    Swiper.prototype.setupDragging = function () {
        var self = this;

        if (this.params.grabCursor) {
            this.el.style.cursor = 'grab';
        }

        this.el.addEventListener(
            'pointerdown',
            function (event) {
                if (
                    event.pointerType === 'mouse' &&
                    event.button !== 0
                ) {
                    return;
                }

                self.dragging = true;
                self.pointerId = event.pointerId;
                self.startX = event.clientX;
                self.lastX = event.clientX;
                self.startTranslate = self.translate;

                self.wrapper.style.transition = 'none';

                if (self.params.grabCursor) {
                    self.el.style.cursor = 'grabbing';
                }

                try {
                    self.el.setPointerCapture(
                        event.pointerId
                    );
                } catch (e) {}
            }
        );

        this.el.addEventListener(
            'pointermove',
            function (event) {
                if (!self.dragging) {
                    return;
                }

                var diff =
                    event.clientX -
                    self.startX;

                self.lastX = event.clientX;

                var newTranslate =
                    self.startTranslate + diff;

                self.wrapper.style.transform =
                    'translate3d(' +
                    newTranslate +
                    'px,0,0)';

                self.translate = newTranslate;
            }
        );

        var endDrag = function () {
            if (!self.dragging) {
                return;
            }

            self.dragging = false;

            if (self.params.grabCursor) {
                self.el.style.cursor = 'grab';
            }

            var diff =
                self.lastX -
                self.startX;

            var threshold =
                Math.max(
                    30,
                    self.el.clientWidth * 0.08
                );

            if (Math.abs(diff) >= threshold) {
                if (diff < 0) {
                    self.slideNext();
                } else {
                    self.slidePrev();
                }
            } else {
                self.applyTranslate(
                    self.getTranslateForIndex(
                        self.currentIndex
                    ),
                    self.currentParams.speed || 300
                );
            }
        };

        this.el.addEventListener(
            'pointerup',
            endDrag
        );

        this.el.addEventListener(
            'pointercancel',
            endDrag
        );

        this.el.addEventListener(
            'pointerleave',
            function () {
                if (self.dragging) {
                    endDrag();
                }
            }
        );
    };

    Swiper.prototype.setupResize = function () {
        var self = this;

        var resizeTimer;

        window.addEventListener(
            'resize',
            function () {
                clearTimeout(resizeTimer);

                resizeTimer = setTimeout(function () {
                    self.update();
                }, 100);
            }
        );
    };

    Swiper.prototype.startAutoplay = function () {
        var self = this;

        this.stopAutoplay();

        var autoplay = this.params.autoplay;

        if (!autoplay) {
            return;
        }

        var delay =
            typeof autoplay === 'object'
                ? getNumber(autoplay.delay, 3000)
                : 3000;

        this.timer = setInterval(
            function () {
                self.slideNext();
            },
            delay
        );
    };

    Swiper.prototype.stopAutoplay = function () {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    };

    Swiper.prototype.destroy = function () {
        this.stopAutoplay();

        if (this.transitionTimer) {
            clearTimeout(this.transitionTimer);
        }

        this.el.classList.remove(
            'swiper-initialized'
        );

        this.wrapper.style.transform = '';
        this.wrapper.style.transition = '';

        this.el.style.overflow = '';

        this.slides.forEach(function (slide) {
            slide.style.width = '';
            slide.style.flex = '';
            slide.style.marginRight = '';
            slide.style.marginLeft = '';
        });
    };

    /*
     * Compatibility helpers
     */
    Swiper.prototype.autoplay = {
        start: function () {
            this.startAutoplay();
        },

        stop: function () {
            this.stopAutoplay();
        }
    };

    /*
     * Global Swiper
     */
    window.Swiper = Swiper;

})(window, document);