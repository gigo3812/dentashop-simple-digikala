/**
 * GP DentaShop - Search Script
 * جستجوی Ajax زنده با debounce
 */
(function() {
    'use strict';
    
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const DEBOUNCE_DELAY = 350;
    const MIN_CHARS = 2;
    
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof GPDS === 'undefined') return;
        
        const inputs = document.querySelectorAll('input[data-gpds-search]');
        if (!inputs.length) return;
        
        inputs.forEach(input => initSearch(input));
    });
    
    function initSearch(input) {
        const form = input.closest('form');
        const resultsBox = $('[data-gpds-search-results]', form);
        const body = $('[data-gpds-search-body]', form);
        const loading = $('.gpds-search-results__loading', form);
        
        if (!resultsBox || !body) return;
        
        let timeout = null;
        let currentRequest = null;
        
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            
            const term = input.value.trim();
            
            if (term.length < MIN_CHARS) {
                resultsBox.setAttribute('hidden', '');
                return;
            }
            
            timeout = setTimeout(() => doSearch(term), DEBOUNCE_DELAY);
        });
        
        input.addEventListener('focus', function() {
            if (input.value.trim().length >= MIN_CHARS && body.innerHTML) {
                resultsBox.removeAttribute('hidden');
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!form.contains(e.target)) {
                resultsBox.setAttribute('hidden', '');
            }
        });
        
        async function doSearch(term) {
            // لغو درخواست قبلی
            if (currentRequest) currentRequest.abort();
            
            // نمایش لودینگ
            resultsBox.removeAttribute('hidden');
            if (loading) loading.style.display = 'block';
            body.innerHTML = '';
            
            const controller = new AbortController();
            currentRequest = controller;
            
            const formData = new FormData();
            formData.append('action', 'gpds_search');
            formData.append('nonce', GPDS.nonce);
            formData.append('term', term);
            
            try {
                const response = await fetch(GPDS.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal,
                });
                
                const data = await response.json();
                
                if (loading) loading.style.display = 'none';
                
                if (data.success) {
                    renderResults(data.data, body, term);
                } else {
                    renderEmpty(data.data?.message || GPDS.i18n.searchEmpty, body);
                }
            } catch (err) {
                if (err.name === 'AbortError') return;
                if (loading) loading.style.display = 'none';
                renderEmpty(GPDS.i18n.error, body);
            } finally {
                currentRequest = null;
            }
        }
    }
    
    function renderResults(data, container, term) {
        const { products = [], categories = [], total = 0 } = data;
        
        if (total === 0) {
            renderEmpty(GPDS.i18n.searchEmpty, container);
            return;
        }
        
        let html = '';
        
        // دسته‌بندی‌ها
        if (categories.length) {
            html += '<div class="gpds-search-cat">';
            html += '<div class="gpds-search-cat__title">دسته‌بندی‌ها</div>';
            categories.forEach(cat => {
                html += `
                    <a href="${escapeHTML(cat.url)}" class="gpds-search-cat__item">
                        <span>${escapeHTML(cat.title)}</span>
                        <span class="gpds-text-tertiary">${cat.count} کالا</span>
                    </a>
                `;
            });
            html += '</div>';
        }
        
        // محصولات
        if (products.length) {
            html += '<div class="gpds-search-products">';
            html += '<div class="gpds-search-products__title">محصولات</div>';
            products.forEach(p => {
                html += `
                    <a href="${escapeHTML(p.url)}" class="gpds-search-product">
                        <img src="${escapeHTML(p.image)}" alt="${escapeHTML(p.title)}" loading="lazy">
                        <div class="gpds-search-product__info">
                            <div class="gpds-search-product__title">${escapeHTML(p.title)}</div>
                            <div class="gpds-search-product__price">${p.price}</div>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
        }
        
        // دکمه مشاهده همه
        html += `
            <a href="${GPDS.homeUrl}?s=${encodeURIComponent(term)}&post_type=product" class="gpds-search-all">
                مشاهده همه نتایج
            </a>
        `;
        
        container.innerHTML = html;
    }
    
    function renderEmpty(message, container) {
        container.innerHTML = `<div class="gpds-search-empty">${escapeHTML(message)}</div>`;
    }
    
    function escapeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    
})();
