/**
 * GP DentaShop - Offices Scripts
 * Lazy Load Leaflet Map + Copy Address
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initLazyMap();
        initCopy();
    });

    /**
     * نقشه با Lazy Loading
     * Leaflet فقط وقتی لود می‌شه که کاربر به نقشه برسه
     */
    function initLazyMap() {
        var mapEl = document.getElementById('gpds-office-map');
        if (!mapEl) return;

        // اگه مرورگر IntersectionObserver نداره، مستقیم لود کن
        if (!('IntersectionObserver' in window)) {
            loadLeafletAndInitMap(mapEl);
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    observer.disconnect();
                    loadLeafletAndInitMap(mapEl);
                }
            });
        }, {
            rootMargin: '200px 0px',  // ۲۰۰ پیکسل قبل از دیدن، شروع کن
            threshold: 0.3,
        });

        observer.observe(mapEl);
    }

    /**
     * لود Leaflet + راه‌اندازی نقشه
     */
    function loadLeafletAndInitMap(mapEl) {
        if (typeof L !== 'undefined') {
            initMap(mapEl);
            return;
        }

        var themeUri = (typeof GPDS !== 'undefined' && GPDS.themeUri)
            ? GPDS.themeUri
            : '/wp-content/themes/gp-dentashop';

        loadCSS(themeUri + '/assets/vendor/leaflet/leaflet.css');
        loadScript(themeUri + '/assets/vendor/leaflet/leaflet.js', function () {
            initMap(mapEl);
        });
    }
    /**
     * راه‌اندازی نقشه
     */
    function initMap(mapEl) {
        if (typeof L === 'undefined') return;

        var lat = parseFloat(mapEl.dataset.lat);
        var lng = parseFloat(mapEl.dataset.lng);
        var title = mapEl.dataset.title || '';

        if (!lat || !lng) return;

        var map = L.map(mapEl, {
            center: [lat, lng],
            zoom: 15,
            scrollWheelZoom: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19,
        }).addTo(map);

        var marker = L.marker([lat, lng]).addTo(map);
        if (title) {
            marker.bindPopup('<strong>' + title + '</strong>').openPopup();
        }

        // Placeholder رو حذف کن
        mapEl.classList.add('is-loaded');

        setTimeout(function () { map.invalidateSize(); }, 200);
    }

    /**
     * لود کردن Script به صورت داینامیک
     */
    function loadScript(src, callback) {
        var script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = callback;
        script.onerror = function () {
            console.warn('[GPDS Offices] Failed to load Leaflet from CDN');
        };
        document.head.appendChild(script);
    }

    /**
     * لود کردن CSS به صورت داینامیک
     */
    function loadCSS(href) {
        if (document.querySelector('link[href="' + href + '"]')) return;
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
    }

    /**
     * کپی آدرس
     */
    function initCopy() {
        var btns = document.querySelectorAll('[data-gpds-copy]');
        if (!btns.length) return;

        btns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var text = this.dataset.gpdsCopy;
                if (!text) return;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function () {
                        window.GPDS_CORE.showToast('لینک کپی شد ✓');
                    }).catch(function () {
                        window.GPDS_CORE.fallbackCopy(text, 'لینک کپی شد ✓');
                    });
                } else {
                    window.GPDS_CORE.fallbackCopy(text, 'لینک کپی شد ✓');
                }
            });
        });
    }

})();