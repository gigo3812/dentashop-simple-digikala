/**
 * KafiChat Lite - Admin Panel JavaScript
 * Production-ready: No console logs, branding upsell removed for WP.org compliance.
 */
(function () {
	'use strict';

	if (typeof kafichatAdmin === 'undefined') {
		return;
	}

	const API = kafichatAdmin.restUrl;
	const NONCE = kafichatAdmin.nonce;
	const I18N = kafichatAdmin.i18n;

	function apiFetch(path, options) {
		const url = API + path;
		const defaults = {
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': NONCE,
			},
		};
		return fetch(url, Object.assign(defaults, options || {}))
			.then(function (r) { return r.json().then(function (data) { return { status: r.status, data: data }; }); });
	}

	function notify(message, type) {
		type = type || 'info';
		const box = document.getElementById('kafichat-notification');
		if (!box) return;

		box.className = 'kafichat-notification ' + type;
		box.innerHTML = '<span class="dashicons dashicons-' +
			(type === 'success' ? 'yes-alt' : type === 'error' ? 'warning' : 'info') +
			'"></span> ' + message;
		box.style.display = 'flex';

		clearTimeout(box._timer);
		box._timer = setTimeout(function () {
			box.style.display = 'none';
		}, 5000);
	}

	function escapeHtml(str) {
		if (str == null) return '';
		const div = document.createElement('div');
		div.textContent = String(str);
		return div.innerHTML;
	}

	function formatDate(dateStr) {
		if (!dateStr) return '';
		const d = new Date(dateStr);
		return d.toLocaleString('fa-IR', {
			year: 'numeric',
			month: '2-digit',
			day: '2-digit',
			hour: '2-digit',
			minute: '2-digit',
			hour12: false
		});
	}

	function initSettingsForms() {
		const forms = document.querySelectorAll('.kafichat-form[data-form="settings"]');
		forms.forEach(function (form) {
			form.addEventListener('submit', async function (e) {
				e.preventDefault();

				const submitBtn = form.querySelector('button[type="submit"]');
				const originalText = submitBtn.innerHTML;
				submitBtn.innerHTML = '<span class="spinner is-active" style="float:none;margin:0;"></span> ' + I18N.saving;
				submitBtn.disabled = true;

				try {
					const data = {};

					form.querySelectorAll('input, select, textarea').forEach(function (field) {
						if (field.type === 'checkbox') {
							data[field.name] = field.checked;
						} else if (field.type === 'number') {
							data[field.name] = parseInt(field.value, 10) || 0;
						} else if (field.type === 'password' && field.value === '') {
							// Don't send empty password (keep existing)
						} else {
							data[field.name] = field.value;
						}
					});

					const result = await apiFetch('admin/settings/save', {
						method: 'POST',
						body: JSON.stringify(data),
					});

					if (result.data && result.data.ok) {
						notify(I18N.saved, 'success');
						form.querySelectorAll('input[type="password"]').forEach(function (f) { f.value = ''; });
					} else {
						notify((result.data && result.data.error) || I18N.error, 'error');
					}
				} catch (err) {
					notify(I18N.error + ' ' + err.message, 'error');
				} finally {
					submitBtn.innerHTML = originalText;
					submitBtn.disabled = false;
				}
			});
		});
	}

	function initBaleTest() {
		const btn = document.getElementById('kafichat-test-bale');
		const result = document.getElementById('kafichat-test-result');
		if (!btn) return;

		btn.addEventListener('click', async function () {
			btn.disabled = true;
			btn.innerHTML = '<span class="spinner is-active" style="float:none;margin:0;"></span> ' + I18N.testing;
			result.innerHTML = '';

			try {
				const res = await apiFetch('admin/bale/test', { method: 'POST' });

				if (res.data && res.data.ok) {
					result.innerHTML =
						'<div class="kafichat-notification success" style="display:flex;">' +
						'<span class="dashicons dashicons-yes-alt"></span> ' +
						'<div>' +
						'<strong>' + I18N.testSuccess + '</strong><br>' +
						'Bot: @' + escapeHtml(res.data.bot_username) + ' (' + escapeHtml(res.data.bot_name) + ')<br>' +
						'ارسال پیام آزمایشی: ' + (res.data.message_sent ? 'بله ✓' : 'خیر (Admin Chat ID را بررسی کنید)') +
						'</div></div>';
				} else {
					result.innerHTML =
						'<div class="kafichat-notification error" style="display:flex;">' +
						'<span class="dashicons dashicons-warning"></span> ' +
						'<strong>' + I18N.testFailed + '</strong> ' + escapeHtml((res.data && res.data.error) || '') +
						'</div>';
				}
			} catch (err) {
				result.innerHTML =
					'<div class="kafichat-notification error" style="display:flex;">' +
					'<span class="dashicons dashicons-warning"></span> ' + escapeHtml(err.message) +
					'</div>';
			} finally {
				btn.disabled = false;
				btn.innerHTML = '<span class="dashicons dashicons-yes-alt"></span> ' +
					(btn.dataset.originalText || 'تست اتصال');
			}
		});

		btn.dataset.originalText = btn.textContent.trim();
	}

	function initWebhookButtons() {
		const setBtn = document.getElementById('kafichat-set-webhook');
		const healthBtn = document.getElementById('kafichat-webhook-health');
		const statusBox = document.getElementById('kafichat-webhook-status');

		if (setBtn) {
			setBtn.addEventListener('click', async function () {
				setBtn.disabled = true;
				statusBox.innerHTML = '<span class="spinner is-active" style="float:none;margin:0;"></span> در حال تنظیم وبهوک...';

				try {
					const res = await apiFetch('admin/webhook/set', { method: 'POST' });
					if (res.data && res.data.ok) {
						statusBox.innerHTML = '<span class="kafichat-status-ok">✓ ' + escapeHtml(res.data.message) + '</span>';
					} else {
						statusBox.innerHTML = '<span class="kafichat-status-warn">✗ ' + escapeHtml((res.data && res.data.error) || 'ناموفق') + '</span>';
					}
				} catch (err) {
					statusBox.innerHTML = '<span class="kafichat-status-warn">✗ ' + escapeHtml(err.message) + '</span>';
				} finally {
					setBtn.disabled = false;
				}
			});
		}

		if (healthBtn) {
			healthBtn.addEventListener('click', async function () {
				healthBtn.disabled = true;
				statusBox.innerHTML = '<span class="spinner is-active" style="float:none;margin:0;"></span> در حال بررسی...';

				try {
					const res = await apiFetch('admin/webhook/health-check', { method: 'POST' });
					if (res.data) {
						const statusClass = res.data.healthy ? 'kafichat-status-ok' : 'kafichat-status-warn';
						const icon = res.data.healthy ? '✓' : '⚠';
						statusBox.innerHTML =
							'<span class="' + statusClass + '">' + icon + ' ' + escapeHtml(res.data.status) + '</span>';
					}
				} catch (err) {
					statusBox.innerHTML = '<span class="kafichat-status-warn">✗ ' + escapeHtml(err.message) + '</span>';
				} finally {
					healthBtn.disabled = false;
				}
			});
		}
	}

	function initCleanup() {
		const btn = document.getElementById('kafichat-manual-cleanup');
		const result = document.getElementById('kafichat-cleanup-result');
		if (!btn) return;

		btn.addEventListener('click', async function () {
			if (!confirm(I18N.confirmCleanup)) return;

			btn.disabled = true;
			try {
				const res = await apiFetch('admin/cleanup/manual', { method: 'POST' });
				if (res.data && res.data.ok) {
					const msg = 'پاکسازی انجام شد: ' + res.data.deleted_conversations + ' مکالمه و ' +
						res.data.deleted_logs + ' لاگ حذف شدند';
					if (result) {
						result.innerHTML = '<div class="kafichat-notification success" style="display:flex;">' +
							'<span class="dashicons dashicons-yes-alt"></span> ' + escapeHtml(msg) + '</div>';
					}
					notify('پاکسازی با موفقیت انجام شد', 'success');
				}
			} catch (err) {
				notify(I18N.error + ' ' + err.message, 'error');
			} finally {
				btn.disabled = false;
			}
		});
	}

	function initConversationsList() {
		const listEl = document.getElementById('kafichat-conversations-list');
		if (!listEl) return;

		const loadingEl = document.getElementById('kafichat-conversations-loading');
		const emptyEl = document.getElementById('kafichat-conversations-empty');
		const searchEl = document.getElementById('kafichat-search');
		const refreshBtn = document.getElementById('kafichat-refresh-list');

		let allConversations = [];
		let currentPage = 1;
		const perPage = 20;
		let totalPages = 1;
		let totalConversations = 0;
		const currentFilter = (window.kafichatAdminPage && window.kafichatAdminPage.currentFilter) || 'all';

		async function loadConversations(page) {
			page = page || 1;
			loadingEl.style.display = 'block';
			listEl.style.display = 'none';
			emptyEl.style.display = 'none';

			const existingPagination = document.getElementById('kafichat-pagination');
			if (existingPagination) {
				existingPagination.remove();
			}

			try {
				const query = currentFilter !== 'all' ? '?status=' + currentFilter + '&' : '?';
				const res = await apiFetch('admin/conversations' + query + 'page=' + page + '&per_page=' + perPage);

				if (res.data && res.data.conversations) {
					allConversations = res.data.conversations;
					currentPage = res.data.page || 1;
					totalPages = res.data.total_pages || 1;
					totalConversations = res.data.total || 0;
					renderConversations(allConversations);
					renderPagination();
				} else {
					renderConversations([]);
					renderPagination();
				}
			} catch (err) {
				notify(I18N.error + ' ' + err.message, 'error');
				loadingEl.style.display = 'none';
			}
		}

		function renderConversations(conversations) {
			loadingEl.style.display = 'none';

			if (!conversations.length) {
				listEl.style.display = 'none';
				emptyEl.style.display = 'block';
				return;
			}

			listEl.style.display = 'block';
			emptyEl.style.display = 'none';

			listEl.innerHTML = conversations.map(function (conv) {
				const name = conv.guest_name || 'مهمان ناشناس';
				const phone = conv.guest_phone || '';
				const lastActivity = conv.last_activity_at || conv.created_at;
				const statusLabel = conv.status === 'open' ? 'باز' : 'بسته';

				return '<div class="kafichat-conversation-item" data-id="' + conv.id + '" data-ref="' + escapeHtml(conv.conversation_ref) + '">' +
					'<div class="kafichat-conv-info">' +
					'<div class="kafichat-conv-name">' + escapeHtml(name) + '</div>' +
					'<div class="kafichat-conv-meta">' +
					(phone ? '<span>📱 <span dir="ltr">' + escapeHtml(phone) + '</span></span>' : '') +
					'<span>🕒 ' + formatDate(lastActivity) + '</span>' +
					'</div>' +
					'</div>' +
					'<span class="kafichat-status-badge ' + escapeHtml(conv.status) + '">' + escapeHtml(statusLabel) + '</span>' +
					'<div class="kafichat-conv-actions">' +
					'<button type="button" class="button kafichat-view-messages" data-ref="' + escapeHtml(conv.conversation_ref) + '" data-name="' + escapeHtml(name) + '" title="مشاهده پیام‌ها">' +
					'<span class="dashicons dashicons-visibility"></span></button>' +
					(conv.status === 'open' ? '<button type="button" class="button kafichat-close-conv" data-id="' + conv.id + '" title="بستن مکالمه">' +
						'<span class="dashicons dashicons-no-alt"></span></button>' : '') +
					'</div>' +
					'</div>';
			}).join('');

			listEl.querySelectorAll('.kafichat-view-messages').forEach(function (btn) {
				btn.addEventListener('click', function () { viewMessages(btn.dataset.ref, btn.dataset.name); });
			});
			listEl.querySelectorAll('.kafichat-close-conv').forEach(function (btn) {
				btn.addEventListener('click', function () { closeConversation(btn.dataset.id); });
			});
		}

		function renderPagination() {
			const existingPagination = document.getElementById('kafichat-pagination');
			if (existingPagination) {
				existingPagination.remove();
			}

			if (totalPages <= 1) {
				return;
			}

			const pagination = document.createElement('div');
			pagination.id = 'kafichat-pagination';
			pagination.className = 'kafichat-pagination';

			let html = '';

			html += '<div class="kafichat-pagination-info">';
			html += 'صفحه ' + currentPage + ' از ' + totalPages + ' (مجموع: ' + totalConversations + ' مکالمه)';
			html += '</div>';

			html += '<div class="kafichat-pagination-buttons">';

			if (currentPage > 1) {
				html += '<button type="button" class="kafichat-page-btn" data-page="' + (currentPage - 1) + '">';
				html += '<span class="dashicons dashicons-arrow-right-alt2"></span> قبلی';
				html += '</button>';
			}

			const startPage = Math.max(1, currentPage - 2);
			const endPage = Math.min(totalPages, currentPage + 2);

			if (startPage > 1) {
				html += '<button type="button" class="kafichat-page-btn" data-page="1">1</button>';
				if (startPage > 2) {
					html += '<span class="kafichat-page-dots">...</span>';
				}
			}

			for (let i = startPage; i <= endPage; i++) {
				const activeClass = i === currentPage ? ' active' : '';
				html += '<button type="button" class="kafichat-page-btn' + activeClass + '" data-page="' + i + '">' + i + '</button>';
			}

			if (endPage < totalPages) {
				if (endPage < totalPages - 1) {
					html += '<span class="kafichat-page-dots">...</span>';
				}
				html += '<button type="button" class="kafichat-page-btn" data-page="' + totalPages + '">' + totalPages + '</button>';
			}

			if (currentPage < totalPages) {
				html += '<button type="button" class="kafichat-page-btn" data-page="' + (currentPage + 1) + '">';
				html += 'بعدی <span class="dashicons dashicons-arrow-right-alt2"></span>';
				html += '</button>';
			}

			html += '</div>';

			pagination.innerHTML = html;

			listEl.parentNode.insertBefore(pagination, listEl.nextSibling);

			pagination.querySelectorAll('.kafichat-page-btn').forEach(function (btn) {
				btn.addEventListener('click', function () {
					const page = parseInt(btn.dataset.page, 10);
					if (page && page !== currentPage) {
						loadConversations(page);
						listEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
				});
			});
		}

		async function closeConversation(id) {
			if (!confirm(I18N.confirmClose)) return;

			try {
				const res = await apiFetch('admin/conversation/close', {
					method: 'POST',
					body: JSON.stringify({ conversation_id: parseInt(id, 10) }),
				});
				if (res.data && res.data.ok) {
					notify('مکالمه بسته شد', 'success');
					loadConversations(currentPage);
				} else {
					notify((res.data && res.data.error) || I18N.error, 'error');
				}
			} catch (err) {
				notify(I18N.error + ' ' + err.message, 'error');
			}
		}

		async function viewMessages(convRef, name) {
			const modal = document.getElementById('kafichat-messages-modal');
			const modalTitle = document.getElementById('kafichat-modal-title');
			const modalBody = document.getElementById('kafichat-modal-body');

			modalTitle.textContent = name;
			modalBody.innerHTML = '<div style="text-align:center;padding:30px;"><span class="spinner is-active" style="float:none;"></span> در حال بارگذاری...</div>';
			modal.style.display = 'flex';

			try {
				const res = await apiFetch('admin/conversation/messages?conversation_ref=' + encodeURIComponent(convRef));

				if (!res.data || !res.data.ok) {
					throw new Error((res.data && res.data.error) || 'Failed to load messages');
				}

				const messages = res.data.messages || [];
				const convInfo = res.data.conversation || {};

				let html = '<div style="padding: 10px; background: #f0f0f1; border-radius: 4px; margin-bottom: 15px;">';
				html += '<strong>' + escapeHtml(convInfo.guest_name || 'مهمان') + '</strong>';
				if (convInfo.guest_phone) {
					html += ' &middot; 📱 <span dir="ltr">' + escapeHtml(convInfo.guest_phone) + '</span>';
				}
				const statusLabel = convInfo.status === 'open' ? 'باز' : 'بسته';
				html += ' &middot; <span class="kafichat-status-badge ' + escapeHtml(convInfo.status) + '">' + escapeHtml(statusLabel) + '</span>';
				html += '</div>';

				if (!messages.length) {
					html += '<p style="text-align:center;color:#666;padding:20px;">هنوز پیامی ارسال نشده است.</p>';
				} else {
					messages.forEach(function (msg) {
						const allowed = ['user', 'admin', 'system'];
						const typeClass = allowed.indexOf(msg.sender_type) !== -1 ? msg.sender_type : 'system';

						let senderLabel = msg.sender_type;
						if (msg.sender_type === 'user') senderLabel = 'کاربر';
						else if (msg.sender_type === 'admin') senderLabel = 'پشتیبان';
						else if (msg.sender_type === 'system') senderLabel = 'سیستم';

						let statusText = msg.status || '';
						if (msg.status === 'sent') statusText = 'ارسال شده';
						else if (msg.status === 'seen') statusText = 'دیده شده';
						else if (msg.status === 'pending') statusText = 'در انتظار';
						else if (msg.status === 'failed') statusText = 'ناموفق';

						html += '<div class="kafichat-modal-message ' + typeClass + '">';
						html += '<div>' + escapeHtml(msg.content || '') + '</div>';
						html += '<div class="kafichat-modal-message-time">' +
							escapeHtml(senderLabel) + ' &middot; ' + formatDate(msg.created_at);
						if (msg.sender_type !== 'system' && statusText) {
							html += ' &middot; ' + escapeHtml(statusText);
						}
						html += '</div></div>';
					});
				}

				modalBody.innerHTML = html;
			} catch (err) {
				modalBody.innerHTML = '<p style="color:#f44336;text-align:center;">' + escapeHtml(err.message) + '</p>';
			}
		}

		if (searchEl) {
			let searchTimer;
			searchEl.addEventListener('input', function (e) {
				clearTimeout(searchTimer);
				searchTimer = setTimeout(function () {
					const query = e.target.value.toLowerCase().trim();
					if (!query) {
						loadConversations(1);
						return;
					}
					const filtered = allConversations.filter(function (c) {
						return (c.guest_name || '').toLowerCase().indexOf(query) !== -1 ||
							(c.guest_phone || '').toLowerCase().indexOf(query) !== -1 ||
							c.conversation_ref.toLowerCase().indexOf(query) !== -1;
					});
					renderConversations(filtered);
				}, 300);
			});
		}

		if (refreshBtn) {
			refreshBtn.addEventListener('click', function () { loadConversations(1); });
		}

		document.querySelectorAll('.kafichat-modal-close, .kafichat-modal-backdrop').forEach(function (el) {
			el.addEventListener('click', function () {
				const modal = document.getElementById('kafichat-messages-modal');
				if (modal) modal.style.display = 'none';
			});
		});

		loadConversations(1);
	}

	function initTabs() {
		const tabs = document.querySelectorAll('.kafichat-tab-item');
		const panes = document.querySelectorAll('.kafichat-tab-pane');

		if (!tabs.length || !panes.length) return;

		function switchTab(tabName) {
			tabs.forEach(function (tab) {
				tab.classList.toggle('active', tab.dataset.tab === tabName);
			});

			panes.forEach(function (pane) {
				if (pane.dataset.tab === tabName) {
					pane.classList.add('active');
					pane.style.display = 'block';
				} else {
					pane.classList.remove('active');
					pane.style.display = 'none';
				}
			});

			const url = new URL(window.location);
			url.searchParams.set('tab', tabName);
			window.history.replaceState({}, '', url);
		}

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function (e) {
				e.preventDefault();
				switchTab(tab.dataset.tab);
			});
		});

		const initialTab = window.kafichatInitialTab || 'general';
		switchTab(initialTab);

		window.addEventListener('popstate', function () {
			const params = new URLSearchParams(window.location.search);
			const tab = params.get('tab') || 'general';
			switchTab(tab);
		});
	}

	function initLogsViewer() {
		const loadBtn = document.getElementById('kafichat-load-logs');
		const refreshBtn = document.getElementById('kafichat-refresh-logs');
		const container = document.getElementById('kafichat-logs-container');

		if (!loadBtn || !container) return;

		const levelLabels = {
			'info': 'ℹ️',
			'warning': '⚠️',
			'error': '❌'
		};

		const levelColors = {
			'info': '#3b82f6',
			'warning': '#f59e0b',
			'error': '#ef4444'
		};

		async function fetchLogs() {
			container.innerHTML = '<div style="text-align: center; padding: 30px;"><span class="spinner is-active"></span> در حال بارگذاری...</div>';

			try {
				const res = await apiFetch('admin/logs?limit=50');

				if (res.data && res.data.logs) {
					renderLogs(res.data.logs);
					if (refreshBtn) refreshBtn.style.display = 'inline-flex';
				} else {
					container.innerHTML = '<div class="kafichat-logs-empty" style="text-align: center; padding: 40px; color: var(--kafichat-gray-500);"><p>' + I18N.error + '</p></div>';
				}
			} catch (err) {
				container.innerHTML = '<div class="kafichat-notification error" style="display: flex;"><span class="dashicons dashicons-warning"></span> ' + escapeHtml(err.message) + '</div>';
			}
		}

		function renderLogs(logs) {
			if (logs.length === 0) {
				container.innerHTML = '<div class="kafichat-logs-empty" style="text-align: center; padding: 40px; color: var(--kafichat-gray-500);"><p>لاگی یافت نشد</p></div>';
				return;
			}

			let html = '<div class="kafichat-logs-table-wrapper">';
			html += '<table class="kafichat-logs-table">';
			html += '<thead><tr>';
			html += '<th style="width: 80px;">سطح</th>';
			html += '<th style="width: 100px;">زمینه</th>';
			html += '<th>پیام</th>';
			html += '<th style="width: 160px;">زمان</th>';
			html += '</tr></thead>';
			html += '<tbody>';

			logs.forEach(function (log) {
				const icon = levelLabels[log.level] || '📝';
				const color = levelColors[log.level] || '#6b7280';
				const time = log.created_at ? formatDate(log.created_at) : '';

				html += '<tr>';
				html += '<td><span style="color: ' + color + '; font-weight: 700;">' + icon + ' ' + escapeHtml(log.level) + '</span></td>';
				html += '<td><code>' + escapeHtml(log.context || '-') + '</code></td>';
				html += '<td>' + escapeHtml(log.message || '');

				if (log.data && typeof log.data === 'object') {
					html += '<details style="margin-top: 8px;"><summary style="cursor: pointer; color: var(--kafichat-gray-500); font-size: 12px;">جزئیات</summary>';
					html += '<pre style="background: var(--kafichat-gray-100); padding: 10px; border-radius: 6px; font-size: 11px; margin-top: 6px; overflow-x: auto; direction: ltr; text-align: left;">' + escapeHtml(JSON.stringify(log.data, null, 2)) + '</pre>';
					html += '</details>';
				}

				html += '</td>';
				html += '<td style="color: var(--kafichat-gray-500); font-size: 12px;">' + time + '</td>';
				html += '</tr>';
			});

			html += '</tbody></table></div>';
			container.innerHTML = html;
		}

		loadBtn.addEventListener('click', fetchLogs);

		if (refreshBtn) {
			refreshBtn.addEventListener('click', fetchLogs);
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		initTabs();
		initSettingsForms();
		initBaleTest();
		initWebhookButtons();
		initCleanup();
		initConversationsList();
		initLogsViewer();
	});
})();