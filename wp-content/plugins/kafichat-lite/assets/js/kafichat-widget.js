/**
 * KafiChat Lite - Frontend Widget JavaScript
 * Production-ready: No console logs, all syntax errors fixed.
 */
(function () {
	'use strict';

	if (typeof kafichatWidget === 'undefined') {
		return;
	}

	const API_URL = kafichatWidget.apiUrl;
	const NONCE = kafichatWidget.nonce;
	const IS_LOGGED_IN = kafichatWidget.isLoggedIn;
	const LS_KEY_CONV_REF = 'kafichat_conv_ref';
	const LS_KEY_ACCESS_TOKEN = 'kafichat_access_token';
	const LS_KEY_BADGE = 'kafichat_badge';

	let floatingBtn = null;
	let chatWindow = null;
	let iconOpen = null;
	let iconClose = null;
	let badge = null;
	let guestForm = null;
	let startForm = null;
	let composerForm = null;
	let messageInput = null;
	let sendBtn = null;
	let messagesArea = null;
	let messagesList = null;
	let loadingSpinner = null;
	let isOpen = false;
	let convRef = '';
	let accessToken = '';
	let unreadCount = 0;
	let pollTimer = null;
	let isInitializing = false;
	let lastMessageId = 0;
	let pendingMessageIds = new Set();
	let overlay = null;

	function bindEvents() {
		floatingBtn.addEventListener('click', toggleChatWindow);

		const headerClose = chatWindow.querySelector('.kafichat-header-close');
		if (headerClose) {
			headerClose.addEventListener('click', closeChatWindow);
		}

		if (startForm) {
			startForm.addEventListener('submit', handleGuestFormSubmit);
		}

		if (composerForm) {
			composerForm.addEventListener('submit', function (e) {
				e.preventDefault();
				sendMessage();
			});
		}

		if (overlay) {
			overlay.addEventListener('click', closeChatWindow);
		}

		if (messageInput) {
			messageInput.addEventListener('input', function () {
				messageInput.style.height = '40px';
				const newHeight = Math.min(messageInput.scrollHeight, 120);
				messageInput.style.height = newHeight + 'px';
				
				if (sendBtn) {
					sendBtn.disabled = messageInput.value.trim() === '';
				}
			});

			messageInput.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' && !e.shiftKey) {
					e.preventDefault();
					if (!sendBtn.disabled) {
						sendMessage();
					}
				}
			});
		}
	}

    function decideInitialState() {
        if (IS_LOGGED_IN) {
            // کاربر لاگین کرده است: تحت هیچ شرایطی فرم مهمان نمایش داده نمی‌شود
            initializeConversationForLoggedInUser();
        } else if (convRef && accessToken) {
            // کاربر مهمان که قبلاً اطلاعات داده و توکن دارد
            showChatMode();
        } else {
            // کاربر مهمان جدید
            showGuestFormMode();
        }
    }

    async function initializeConversationForLoggedInUser() {
        if (isInitializing) return;

        // پاکسازی توکن‌های مهمان اگر کاربر لاگین کرده باشد
        if (convRef && !accessToken) {
            convRef = '';
            localStorage.removeItem(LS_KEY_CONV_REF);
            localStorage.removeItem(LS_KEY_ACCESS_TOKEN);
        }

        // اگر قبلاً مکالمه برایش ساخته شده، مستقیم به چت برو
        if (convRef) {
            showChatMode();
            return;
        }

        isInitializing = true;
        // نمایش مستقیم محیط چت به جای فرم مهمان
        showChatMode(); 

        try {
            const response = await apiCall('/conversation/start', 'POST', {
                name: '',
                phone: ''
            });

            if (response && response.ok && response.conversation_ref) {
                convRef = response.conversation_ref;
                accessToken = response.access_token || '';

                localStorage.setItem(LS_KEY_CONV_REF, convRef);
                if (accessToken) {
                    localStorage.setItem(LS_KEY_ACCESS_TOKEN, accessToken);
                }

                // دریافت پیام‌های مکالمه
                fetchMessages(true);
            } else {
                // نمایش خطا داخل خود چت (نه فرم مهمان)
                if (messagesList) {
                    messagesList.innerHTML = '<div class="kafichat-message system">خطا در برقراری ارتباط با سرور. لطفاً صفحه را رفرش کنید.</div>';
                }
            }
        } catch (error) {
            if (messagesList) {
                messagesList.innerHTML = '<div class="kafichat-message system">خطای شبکه: ' + escapeHtml(error.message) + '</div>';
            }
        } finally {
            isInitializing = false;
        }
    }

	function showGuestFormMode() {
		if (guestForm) guestForm.style.display = 'block';
		if (messagesArea) messagesArea.style.display = 'none';
		if (composerForm) composerForm.style.display = 'none';

		const container = document.querySelector('.kafichat-widget-container');
		if (container) {
			container.classList.add('kafichat-guest-mode');
		}
	}

	function showChatMode() {
		if (guestForm) guestForm.style.display = 'none';
		if (messagesArea) messagesArea.style.display = 'flex';
		if (composerForm) composerForm.style.display = 'flex';

		const container = document.querySelector('.kafichat-widget-container');
		if (container) {
			container.classList.remove('kafichat-guest-mode');
		}

		if (convRef && messagesList && messagesList.children.length === 0) {
			fetchMessages(true);
		} else if (convRef) {
			scrollToBottom();
		}
	}

	function toggleChatWindow() {
		if (isOpen) {
			closeChatWindow();
		} else {
			openChatWindow();
		}
	}

	function openChatWindow() {
		isOpen = true;
		
		if (overlay) {
			overlay.classList.add('kafichat-overlay-active');
		}

		chatWindow.classList.remove('kafichat-closing');
		chatWindow.classList.add('kafichat-open');
		floatingBtn.classList.add('kafichat-is-open');

		clearBadge();

		if (convRef) {
			if (messagesList && messagesList.children.length === 0) {
				fetchMessages(true);
			} else {
				fetchMessages(false);
			}
			markAsSeen();
		}
	}

	function closeChatWindow() {
		isOpen = false;
		
		if (overlay) {
			overlay.classList.remove('kafichat-overlay-active');
		}

		chatWindow.classList.remove('kafichat-open');
		chatWindow.classList.add('kafichat-closing');
		floatingBtn.classList.remove('kafichat-is-open');

		setTimeout(function () {
			if (!isOpen) {
				chatWindow.classList.remove('kafichat-closing');
			}
		}, 300);
	}

	function checkFormValidity() {
		const nameInput = document.getElementById('kafichat-guest-name');
		const phoneInput = document.getElementById('kafichat-guest-phone');
		const submitBtn = startForm ? startForm.querySelector('button[type="submit"]') : null;
		
		if (!nameInput || !phoneInput || !submitBtn) return;

		const nameReq = kafichatWidget.guestNameReq;
		const phoneReq = kafichatWidget.guestPhoneReq;
		const name = nameInput.value.trim();
		const phone = phoneInput.value.trim();
		const phoneRegex = /^09[0-9]{9}$/;

		const nameValid = !nameReq || (nameReq && name.length > 0);
		const phoneValid = !phoneReq || (phoneReq && phoneRegex.test(phone));

		if (nameValid && phoneValid) {
			submitBtn.classList.add('kafichat-ready');
			submitBtn.disabled = false;
		} else {
			submitBtn.classList.remove('kafichat-ready');
			submitBtn.disabled = true;
		}
	}

	async function handleGuestFormSubmit(e) {
		e.preventDefault();
		
		const nameInput = document.getElementById('kafichat-guest-name');
		const phoneInput = document.getElementById('kafichat-guest-phone');
		const phoneError = document.getElementById('kafichat-phone-error');
		const submitBtn = startForm.querySelector('button[type="submit"]');

		const name = nameInput ? nameInput.value.trim() : '';
		const phone = phoneInput ? phoneInput.value.trim() : '';
		const phoneRegex = /^09[0-9]{9}$/;

		if (phone && !phoneRegex.test(phone)) {
			if (phoneError) {
				phoneError.textContent = 'شماره موبایل معتبر نیست (مثال: 09123456789)';
			}
			if (phoneInput) {
				phoneInput.style.borderColor = '#ef4444';
			}
			return;
		}

		if (phoneError) phoneError.textContent = '';
		if (phoneInput) phoneInput.style.borderColor = '';

		try {
			const response = await apiCall('/conversation/start', 'POST', {
				name: name,
				phone: phone
			});

			if (response && response.ok) {
				convRef = response.conversation_ref;
				accessToken = response.access_token || '';
				localStorage.setItem(LS_KEY_CONV_REF, convRef); 
				localStorage.setItem(LS_KEY_ACCESS_TOKEN, accessToken);

				showChatMode();
			} else {
				alert('خطا در برقراری ارتباط');
			}
		} catch (error) {
			alert('خطای شبکه: ' + error.message);
		} finally {
			if (submitBtn) {
				submitBtn.disabled = false;
				submitBtn.textContent = 'شروع چت';
			}
		}
	}

	function sendMessage() {
		if (!messageInput) return;

		const content = messageInput.value.trim();
		if (!content || !convRef) return;

		messageInput.value = '';
		messageInput.style.height = '40px';
		if (sendBtn) sendBtn.disabled = true;

		const tempId = 'temp_' + Date.now();
		appendMessage('user', content, 'pending', tempId);
		pendingMessageIds.add(tempId);
		scrollToBottom();

		apiCall('/message/send', 'POST', {
			conversation_ref: convRef,
			content: content,
			access_token: accessToken
		})
		.then(function (response) {
			if (response && response.ok) {
				pendingMessageIds.delete(tempId);
				updateLastMessageStatus('sent', tempId, response.message_id);
			} else {
				updateLastMessageStatus('failed', tempId);
			}
		})
		.catch(function () {
			updateLastMessageStatus('failed', tempId);
		});
	}

	function fetchMessages(isInitialLoad) {
		if (!convRef) return;

		if (isInitialLoad && loadingSpinner) {
			loadingSpinner.style.display = 'block';
		}

		const url = '/messages/' + convRef + '?access_token=' + encodeURIComponent(accessToken);

		apiCall(url, 'GET')
		.then(function (response) {
			if (response && response.ok && response.messages) {
				if (isInitialLoad) {
					if (messagesList) messagesList.innerHTML = ''; 
					pendingMessageIds.clear();

					response.messages.forEach(function (msg) {
						appendMessage(msg.sender_type, msg.content, msg.status, msg.id);
						if (msg.id > lastMessageId) {
							lastMessageId = msg.id;
						}
					});

					scrollToBottom();
				} else {
					const newMessages = response.messages.filter(function (msg) {
						return msg.id > lastMessageId;
					});

					if (newMessages.length > 0) {
						newMessages.forEach(function (msg) {
							const existingMsg = messagesList.querySelector('[data-temp-id]');
							if (existingMsg && existingMsg.dataset.content === msg.content && msg.sender_type === 'user') {
								existingMsg.dataset.status = msg.status;
								existingMsg.dataset.messageId = msg.id;
								
								const timeDiv = existingMsg.querySelector('.kafichat-message-time');
								if (timeDiv) {
									timeDiv.innerHTML = timeDiv.innerHTML.replace('⏳', '✓');
								}
								
								pendingMessageIds.forEach(function (id) {
									if (existingMsg.dataset.tempId === id) {
										pendingMessageIds.delete(id);
									}
								});
							} else {
								appendMessage(msg.sender_type, msg.content, msg.status, msg.id);
							}

							if (msg.id > lastMessageId) {
								lastMessageId = msg.id;
							}
						});

						scrollToBottom();
					}
				}

				if (isOpen) {
					markAsSeen();
				}
			}
		})
		.finally(function () {
			if (isInitialLoad && loadingSpinner) {
				loadingSpinner.style.display = 'none';
			}
		});
	}

	function appendMessage(senderType, content, status, messageId) {
		if (!messagesList) return;

		if (messageId && messagesList.querySelector('[data-message-id="' + messageId + '"]')) {
			return;
		}

		const msgDiv = document.createElement('div');
		msgDiv.className = 'kafichat-message ' + senderType;
		msgDiv.dataset.status = status || 'sent';

		if (messageId) {
			if (typeof messageId === 'string' && messageId.startsWith('temp_')) {
				msgDiv.dataset.tempId = messageId;
			} else {
				msgDiv.dataset.messageId = messageId;
			}
			msgDiv.dataset.content = content;
		}

		const now = new Date();
		const timeStr = now.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });

		let statusIcon = '';
		if (senderType === 'user') {
			if (status === 'pending') statusIcon = ' ⏳';
			else if (status === 'sent') statusIcon = ' ✓';
			else if (status === 'failed') statusIcon = ' ❌';
		}

		const safeContent = escapeHtml(content);

		msgDiv.innerHTML =
			'<div>' + safeContent + '</div>' +
			'<div class="kafichat-message-time">' + timeStr + statusIcon + '</div>';

		messagesList.appendChild(msgDiv);
	}

	function updateLastMessageStatus(newStatus, tempId, realId) {
		const msg = messagesList.querySelector('[data-temp-id="' + tempId + '"]');
		if (!msg) return;

		msg.dataset.status = newStatus;
		if (realId) {
			msg.dataset.messageId = realId;
			delete msg.dataset.tempId;
		}

		const timeDiv = msg.querySelector('.kafichat-message-time');
		if (timeDiv) {
			let text = timeDiv.textContent;
			if (newStatus === 'sent') text = text.replace('⏳', '✓');
			else if (newStatus === 'failed') text = text.replace('⏳', '❌');
			timeDiv.textContent = text;
		}
	}

	function scrollToBottom() {
		if (!messagesArea) return;

		requestAnimationFrame(function () {
			messagesArea.scrollTop = messagesArea.scrollHeight;
		});

		setTimeout(function () {
			if (messagesArea) {
				messagesArea.scrollTop = messagesArea.scrollHeight;
			}
		}, 100);
	}

	function markAsSeen() {
		if (!convRef) return;
		apiCall('/message/seen', 'POST', {
			conversation_ref: convRef,
			access_token: accessToken
		});
	}

	function clearBadge() {
		unreadCount = 0;
		localStorage.setItem(LS_KEY_BADGE, '0');
		if (badge) badge.style.display = 'none';
	}

    function startPolling() {
        // هوشمندسازی: اگر کاربر تب مرورگر را عوض کرد یا مینیمایز کرد،_polling متوقف شود
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (pollTimer) clearTimeout(pollTimer);
            } else {
                // وقتی دوباره به تب برگشت، فوراً چک کند و سپس ادامه دهد
                scheduleNextPoll(true);
            }
        });

        scheduleNextPoll();
    }

    function scheduleNextPoll(isImmediate = false) {
        if (pollTimer) clearTimeout(pollTimer);

        // اگر تب مخفی است، هیچ درخواستی نفرست
        if (document.hidden) {
            return;
        }

        let interval = 30000; // پیش‌فرض وقتی چت بسته است

        if (isOpen) {
            // اگر چت باز است، از 3 ثانیه شروع کن
            interval = 3000; 
            // (اختیاری) می‌توانیم اینجا منطقی بنویسیم که اگر پیام جدیدی نیامد، فاصله را بیشتر کند
        }

        if (isImmediate) {
            interval = 500; // بررسی سریع پس از برگشتن به تب
        }

        pollTimer = setTimeout(function () {
            if (convRef) {
                if (isOpen) {
                    fetchMessages(false);
                } else {
                    checkUnreadCount();
                }
            }
            scheduleNextPoll();
        }, interval);
    }

	function checkUnreadCount() {
		if (!convRef) return;

		const url = '/unread-count?conversation_ref=' + convRef + '&access_token=' + encodeURIComponent(accessToken);

		apiCall(url, 'GET')
		.then(function (response) {
			if (response && response.ok) {
				unreadCount = response.count || 0;
				localStorage.setItem(LS_KEY_BADGE, unreadCount.toString());

				if (badge) {
					if (unreadCount > 0) {
						badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
						badge.style.display = 'flex';
					} else {
						badge.style.display = 'none';
					}
				}
			}
		});
	}

	async function apiCall(endpoint, method, body) {
		const baseUrl = API_URL.endsWith('/') ? API_URL.slice(0, -1) : API_URL;
		const cleanEndpoint = endpoint.startsWith('/') ? endpoint : '/' + endpoint;
		const url = baseUrl + cleanEndpoint;

		if (!NONCE) {
			throw new Error('Nonce not found');
		}

		const options = {
			method: method,
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': NONCE,
			},
			credentials: 'same-origin'
		};

		if (body && method !== 'GET') {
			options.body = JSON.stringify(body);
		}

		try {
			const response = await fetch(url, options);

			if (!response.ok) {
				const errorText = await response.text();
				throw new Error('HTTP ' + response.status + ': ' + errorText.substring(0, 200));
			}

			const data = await response.json();
			return data;
		} catch (error) {
			if (error.message.includes('Failed to fetch')) {
				throw new Error('Unable to connect to server');
			}
			throw error;
		}
	}

	function escapeHtml(text) {
		if (!text) return '';
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	document.addEventListener('DOMContentLoaded', function () {
		floatingBtn = document.getElementById('kafichat-floating-btn');
		chatWindow = document.getElementById('kafichat-chat-window');
		overlay = document.getElementById('kafichat-overlay');
		badge = document.getElementById('kafichat-badge');

		if (!floatingBtn || !chatWindow) {
			return;
		}

		iconOpen = floatingBtn.querySelector('.kafichat-icon-open');
		iconClose = floatingBtn.querySelector('.kafichat-icon-close');

		guestForm = document.getElementById('kafichat-guest-form');
		startForm = document.getElementById('kafichat-start-form');
		composerForm = document.getElementById('kafichat-composer-form');
		messageInput = document.getElementById('kafichat-message-input');
		sendBtn = composerForm ? composerForm.querySelector('.kafichat-send-btn') : null;

		messagesArea = document.getElementById('kafichat-messages-area');
		messagesList = document.getElementById('kafichat-messages-list');
		loadingSpinner = document.getElementById('kafichat-loading');

		convRef = localStorage.getItem(LS_KEY_CONV_REF) || '';
		accessToken = localStorage.getItem(LS_KEY_ACCESS_TOKEN) || '';

		bindEvents();
		decideInitialState();
		startPolling();

		if (startForm) {
			const nameInput = document.getElementById('kafichat-guest-name');
			const phoneInput = document.getElementById('kafichat-guest-phone');
			
			if (nameInput) {
				nameInput.addEventListener('input', checkFormValidity);
			}
			if (phoneInput) {
				phoneInput.addEventListener('input', checkFormValidity);
			}

			checkFormValidity();
		}
	});
})();