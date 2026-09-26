<?php
/**
 * Frontend widget template.
 *
 * Renders the floating chat button and the chat window container.
 *
 * @package KafiChatLite
 */
defined( 'ABSPATH' ) || exit;

$kafichat_settings = get_option( 'kafichat_settings', array() );

if ( empty( $kafichat_settings['enabled'] ) ) {
	return;
}

$kafichat_position        = $kafichat_settings['position'] ?? 'bottom-right';
$kafichat_brand_color     = $kafichat_settings['primary_color'] ?? '#00B894';
$kafichat_header_title    = $kafichat_settings['header_title'] ?? 'پشتیبانی آنلاین';
$kafichat_header_subtitle = $kafichat_settings['header_subtitle'] ?? 'سعی میکنیم در سریعترین زمان ممکن پاسخگوی شما باشیم...';
$kafichat_tooltip_text    = $kafichat_settings['tooltip_text'] ?? 'پشتیبانی آنلاین';

// Pass settings to JS.
wp_localize_script(
	'kafichat-widget',
	'kafichatWidget',
	array(
		'apiUrl'         => esc_url_raw( rest_url( 'kafichat/v1/' ) ),
		'nonce'          => wp_create_nonce( 'wp_rest' ),
		'isLoggedIn'     => is_user_logged_in(),
		'position'       => esc_attr( $kafichat_position ),
		'brandColor'     => esc_attr( $kafichat_brand_color ),
		'headerTitle'    => esc_html( $kafichat_header_title ),
		'headerSubtitle' => esc_html( $kafichat_header_subtitle ),
		'tooltipText'    => esc_html( $kafichat_tooltip_text ),
		'welcomeMessage' => esc_html( $kafichat_settings['welcome_message'] ?? 'سلام! چطور می‌تونم کمکتون کنم؟' ),
		'proUrl'         => esc_url( KAFICHAT_LITE_PRO_URL ),
		'guestNameReq'   => ! empty( $kafichat_settings['guest_name_required'] ),
		'guestPhoneReq'  => ! empty( $kafichat_settings['guest_phone_required'] ),
		'badgeInterval'  => (int) ( $kafichat_settings['badge_polling_interval_sec'] ?? 30 ),
		'fullInterval'   => (int) ( $kafichat_settings['full_polling_interval_sec'] ?? 5 ),
	)
);
?>
<div id="kafichat-widget-container" class="kafichat-widget-container kafichat-pos-<?php echo esc_attr( $kafichat_position ); ?>" style="--kafichat-brand: <?php echo esc_attr( $kafichat_brand_color ); ?>;">

	<!-- Floating Button -->
	<button type="button" id="kafichat-floating-btn" class="kafichat-floating-btn" aria-label="<?php echo esc_attr( $kafichat_tooltip_text ); ?>">
		<span class="kafichat-tooltip"><?php echo esc_html( $kafichat_tooltip_text ); ?></span>
		
		<!-- کانتینر آیکن‌ها -->
		<div class="kafichat-icon-container">
			<svg class="kafichat-icon-open kafichat-icon-active" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
			</svg>
			<svg class="kafichat-icon-close" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<line x1="18" y1="6" x2="6" y2="18"></line>
				<line x1="6" y1="6" x2="18" y2="18"></line>
			</svg>
		</div>
		
		<span id="kafichat-badge" class="kafichat-badge" style="display: none;">0</span>
	</button>

	<!-- Overlay (برای موبایل) -->
	<div id="kafichat-overlay" class="kafichat-overlay"></div>

	<!-- Chat Window -->
	<div id="kafichat-chat-window" class="kafichat-chat-window">
		
		<!-- Header -->
		<div class="kafichat-chat-header">
			<!-- اطلاعات هدر (آیکن + عنوان + اسلاگ) -->
			<div class="kafichat-header-info">
				<div class="kafichat-avatar">
					<!-- آیکن Check Mark -->
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="20 6 9 17 4 12"></polyline>
					</svg>
				</div>
				<div class="kafichat-header-text">
					<div class="kafichat-header-title"><?php echo esc_html( $kafichat_header_title ); ?></div>
					<div class="kafichat-header-subtitle"><?php echo esc_html( $kafichat_header_subtitle ); ?></div>
				</div>
			</div>
			
			<!-- دکمه بستن (سمت چپ در RTL) -->
			<button type="button" class="kafichat-header-close" aria-label="بستن">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<line x1="18" y1="6" x2="6" y2="18"></line>
					<line x1="6" y1="6" x2="18" y2="18"></line>
				</svg>
			</button>
		</div>

		<!-- Body -->
		<div class="kafichat-chat-body">
			
			<!-- Guest Form -->
			<div id="kafichat-guest-form" class="kafichat-guest-form" style="display: none;">
				<h3>شروع گفتگو</h3>
				<p>لطفاً برای ادامه، اطلاعات خود را وارد کنید.</p>
				<form id="kafichat-start-form">
					<div class="kafichat-form-group">
						<label for="kafichat-guest-name">نام شما</label>
						<input type="text" id="kafichat-guest-name" name="name" placeholder="مثال: علی احمدی" autocomplete="name">
					</div>
					<div class="kafichat-form-group">
						<label for="kafichat-guest-phone">شماره موبایل</label>
						<input type="tel" id="kafichat-guest-phone" name="phone" placeholder="09123456789" dir="ltr" autocomplete="tel">
						<span class="kafichat-form-hint">فرمت صحیح: 09123456789 (یازده رقم با 09)</span>
						<span class="kafichat-form-error" id="kafichat-phone-error"></span>
					</div>
					<button type="submit" class="kafichat-btn-primary">شروع چت</button>
				</form>
			</div>

			<!-- Messages Area -->
			<div id="kafichat-messages-area" class="kafichat-messages-area">
				<div id="kafichat-messages-list" class="kafichat-messages-list"></div>
				<div id="kafichat-loading" class="kafichat-loading" style="display: none;">
					<div class="kafichat-spinner"></div>
				</div>
			</div>
		</div>

				<!-- Footer -->
		<div class="kafichat-chat-footer">
			<!-- Composer -->
			<form id="kafichat-composer-form" class="kafichat-composer-form" style="display: none;">
				<textarea id="kafichat-message-input" class="kafichat-message-input" placeholder="پیام خود را بنویسید..." rows="1"></textarea>
				<button type="submit" class="kafichat-send-btn" disabled aria-label="ارسال پیام">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="22" y1="2" x2="11" y2="13"></line>
						<polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
					</svg>
				</button>
			</form>
			
			<!-- Copyright / Branding -->
			<div class="kafichat-copyright">
				<a href="https://chat.kafgram.com" target="_blank" rel="noopener nofollow">
					کافی چت - پشتیبانی آنلاین بله
				</a>
			</div>
		</div>
	</div>
</div>