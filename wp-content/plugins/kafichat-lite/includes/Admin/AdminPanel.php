<?php
/**
 * Admin panel for KafiChat Lite.
 *
 * Registers the main admin menu, submenu pages, and renders the settings
 * interface with vertical tabs layout and SPA-style switching.
 *
 * @package KafiChatLite\Admin
 */
namespace KafiChatLite\Admin;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Platform\WebhookHandler;

/**
 * Class AdminPanel
 */
final class AdminPanel {

	public const MENU_SLUG = 'kafichat-lite';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
	}

	public static function add_menu(): void {
		add_menu_page(
			__( 'کافی‌چت لایت', 'kafichat-lite' ),
			__( 'کافی‌چت لایت', 'kafichat-lite' ),
			'manage_options',
			self::MENU_SLUG,
			array( self::class, 'render_main_page' ),
			'dashicons-format-chat',
			30
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'تنظیمات', 'kafichat-lite' ),
			__( 'تنظیمات', 'kafichat-lite' ),
			'manage_options',
			self::MENU_SLUG,
			array( self::class, 'render_main_page' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'مکالمات', 'kafichat-lite' ),
			__( 'مکالمات', 'kafichat-lite' ),
			'manage_options',
			'kafichat-conversations',
			array( ConversationListPage::class, 'render' )
		);
	}

	public static function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, 'kafichat' ) === false ) {
			return;
		}

        // لود فونت وزیر برای پنل ادمین
        wp_enqueue_style(
            'kafichat-vazir-font',
            'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css',
            array(),
            null
        );

        wp_enqueue_style(
            'kafichat-admin',
            KAFICHAT_LITE_URL . 'assets/css/kafichat-admin.css',
            array(),
            KAFICHAT_LITE_VERSION
        );

		wp_enqueue_script(
			'kafichat-admin',
			KAFICHAT_LITE_URL . 'assets/js/kafichat-admin.js',
			array(),
			KAFICHAT_LITE_VERSION,
			true
		);

		wp_localize_script(
			'kafichat-admin',
			'kafichatAdmin',
			array(
				'restUrl'    => esc_url_raw( rest_url( 'kafichat/v1/' ) ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'i18n'       => array(
					'saving'         => __( 'در حال ذخیره...', 'kafichat-lite' ),
					'saved'          => __( '✓ ذخیره شد!', 'kafichat-lite' ),
					'error'          => __( '✗ خطا!', 'kafichat-lite' ),
					'testing'        => __( 'در حال تست...', 'kafichat-lite' ),
					'testSuccess'    => __( '✓ اتصال موفق!', 'kafichat-lite' ),
					'testFailed'     => __( '✗ اتصال ناموفق!', 'kafichat-lite' ),
					'confirmCleanup' => __( 'آیا مطمئن هستید؟ این عملیات داده‌های قدیمی را حذف می‌کند.', 'kafichat-lite' ),
					'confirmClose'   => __( 'آیا مطمئن هستید که می‌خواهید این مکالمه را ببندید؟', 'kafichat-lite' ),
					'copied'         => __( 'کپی شد!', 'kafichat-lite' ),
				),
				'webhookUrl' => esc_url( WebhookHandler::get_webhook_url() ),
			)
		);
	}

	public static function render_main_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings     = get_option( 'kafichat_settings', array() );
		// Nonce verification is not required here because this is only UI tab display,
		// not a data-modifying action.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_tab  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		$allowed_tabs = array( 'general', 'bale', 'appearance', 'messages', 'tools', 'pro' );

		if ( ! in_array( $current_tab, $allowed_tabs, true ) ) {
			$current_tab = 'general';
		}
		?>
		<div class="wrap kafichat-admin-wrap">
			<h1 class="kafichat-admin-title">
				<span class="dashicons dashicons-format-chat"></span>
				<?php echo esc_html__( 'تنظیمات کافی‌چت لایت', 'kafichat-lite' ); ?>
				<span class="kafichat-version">v<?php echo esc_html( KAFICHAT_LITE_VERSION ); ?></span>
			</h1>
			<div id="kafichat-notification" class="kafichat-notification" style="display: none;"></div>
			
			<div class="kafichat-admin-layout">
				<aside class="kafichat-sidebar">
					<nav class="kafichat-tabs-vertical">
						<?php self::render_tab_nav( $current_tab ); ?>
					</nav>
				</aside>
				<main class="kafichat-main-content">
					<div class="kafichat-tab-pane" data-tab="general" <?php echo $current_tab === 'general' ? 'style="display: block;"' : ''; ?>>
						<?php self::render_tab_general( $settings ); ?>
					</div>
					<div class="kafichat-tab-pane" data-tab="bale" <?php echo $current_tab === 'bale' ? 'style="display: block;"' : ''; ?>>
						<?php self::render_tab_bale( $settings ); ?>
					</div>
					<div class="kafichat-tab-pane" data-tab="appearance" <?php echo $current_tab === 'appearance' ? 'style="display: block;"' : ''; ?>>
						<?php self::render_tab_appearance( $settings ); ?>
					</div>
					<div class="kafichat-tab-pane" data-tab="messages" <?php echo $current_tab === 'messages' ? 'style="display: block;"' : ''; ?>>
						<?php self::render_tab_messages( $settings ); ?>
					</div>
					<div class="kafichat-tab-pane" data-tab="tools" <?php echo $current_tab === 'tools' ? 'style="display: block;"' : ''; ?>>
						<?php self::render_tab_tools( $settings ); ?>
					</div>
					<div class="kafichat-tab-pane" data-tab="pro" <?php echo $current_tab === 'pro' ? 'style="display: block;"' : ''; ?>>
						<?php self::render_tab_pro(); ?>
					</div>
				</main>
			</div>
		</div>

		<?php
		wp_add_inline_script(
   			 'kafichat-admin',
   			 'window.kafichatInitialTab = ' . wp_json_encode( $current_tab ) . ';',
   				 'before'
		);
		?>
		<?php
	}

private static function render_tab_nav( string $current ): void {
    $tabs = array(
        'general'    => array( 'dashicons-admin-generic', __( 'عمومی', 'kafichat-lite' ) ),
        'bale'       => array( 'dashicons-cloud', __( 'اتصال بله', 'kafichat-lite' ) ),
        'appearance' => array( 'dashicons-art', __( 'ظاهر', 'kafichat-lite' ) ),
        'messages'   => array( 'dashicons-email', __( 'پیام‌ها', 'kafichat-lite' ) ),
        'tools'      => array( 'dashicons-admin-tools', __( 'ابزارها', 'kafichat-lite' ) ),
        'pro'        => array( 'dashicons-star-filled', __( 'ارتقا به Pro', 'kafichat-lite' ) ),
    );

    $allowed_badge_html = array(
        'span' => array(
            'class' => array(),
        ),
    );

    foreach ( $tabs as $slug => $info ) {
        $active = ( $current === $slug ) ? 'active' : '';
        $badge  = ( 'pro' === $slug ) ? '<span class="kafichat-pro-badge-vertical">PRO</span>' : '';
        
        printf(
            '<button type="button" class="kafichat-tab-item %s" data-tab="%s">
                <span class="dashicons %s"></span>
                <span>%s</span>
                %s
            </button>',
            esc_attr( $active ),
            esc_attr( $slug ),
            esc_attr( $info[0] ),
            esc_html( $info[1] ),
            wp_kses( $badge, $allowed_badge_html ) // مستقیماً در خروجی
        );
    }
}

	private static function render_tab_general( array $settings ): void {
		$enabled              = ! empty( $settings['enabled'] );
		$position             = $settings['position'] ?? 'bottom-right';
		$guest_name_required  = $settings['guest_name_required'] ?? true;
		$guest_phone_required = $settings['guest_phone_required'] ?? true;
		?>
		<h2><?php echo esc_html__( 'تنظیمات عمومی', 'kafichat-lite' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'پیکربندی اصلی ویجت چت', 'kafichat-lite' ); ?></p>
		<form class="kafichat-form" data-form="settings">
			<table class="form-table">
				<tr>
					<th scope="row"><?php echo esc_html__( 'فعال‌سازی ویجت', 'kafichat-lite' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="enabled" value="1" <?php checked( $enabled ); ?>>
							<?php echo esc_html__( 'نمایش ویجت چت در وب‌سایت', 'kafichat-lite' ); ?>
						</label>
						<p class="description"><?php echo esc_html__( 'وقتی غیرفعال باشد، ویجت در هیچ صفحه‌ای نمایش داده نمی‌شود.', 'kafichat-lite' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__( 'موقعیت ویجت', 'kafichat-lite' ); ?></th>
					<td>
						<select name="position">
							<option value="bottom-right" <?php selected( $position, 'bottom-right' ); ?>><?php echo esc_html__( 'پایین سمت راست', 'kafichat-lite' ); ?></option>
							<option value="bottom-left" <?php selected( $position, 'bottom-left' ); ?>><?php echo esc_html__( 'پایین سمت چپ', 'kafichat-lite' ); ?></option>
							<option value="top-right" <?php selected( $position, 'top-right' ); ?>><?php echo esc_html__( 'بالا سمت راست', 'kafichat-lite' ); ?></option>
							<option value="top-left" <?php selected( $position, 'top-left' ); ?>><?php echo esc_html__( 'بالا سمت چپ', 'kafichat-lite' ); ?></option>
						</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'فیلدهای فرم مهمان', 'kafichat-lite' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="guest_name_required" value="1" <?php checked( $guest_name_required ); ?>>
								<?php echo esc_html__( 'نام مهمان الزامی باشد', 'kafichat-lite' ); ?>
							</label>
							<br>
							<label>
								<input type="checkbox" name="guest_phone_required" value="1" <?php checked( $guest_phone_required ); ?>>
								<?php echo esc_html__( 'شماره موبایل مهمان الزامی باشد', 'kafichat-lite' ); ?>
							</label>
						</td>
					</tr>
				</table>
			<?php self::render_submit_button(); ?>
		</form>

		<!-- Privacy section -->
		<div class="kafichat-help-box" style="margin-top: 40px; border-right: 4px solid var(--kafichat-info);">
			<h3>
				<span class="dashicons dashicons-shield"></span> 
				<?php echo esc_html__( 'حریم خصوصی و داده‌ها', 'kafichat-lite' ); ?>
			</h3>
			<p><?php echo esc_html__( 'این افزونه داده‌های زیر را در دیتابیس سایت شما ذخیره می‌کند:', 'kafichat-lite' ); ?></p>
			<ul style="margin: 10px 20px 10px 0; line-height: 1.8;">
				<li><?php echo esc_html__( 'نام و شماره موبایل مهمانان (به‌صورت رمزنگاری‌شده و امن)', 'kafichat-lite' ); ?></li>
				<li><?php echo esc_html__( 'متن پیام‌های ارسالی و دریافتی', 'kafichat-lite' ); ?></li>
				<li><?php echo esc_html__( 'لاگ‌های سیستمی برای عیب‌یابی فنی', 'kafichat-lite' ); ?></li>
			</ul>
			<p>
				<?php echo esc_html__( 'این داده‌ها فقط برای ارائه خدمات چت استفاده می‌شوند و به هیچ شخص ثالثی (به‌جز API پیام‌رسان بله برای ارسال پیام) ارسال نمی‌شوند. شما می‌توانید مدت زمان نگهداری این داده‌ها را در تب "پیام‌ها و داده‌ها" تنظیم و به‌صورت خودکار پاک‌سازی کنید.', 'kafichat-lite' ); ?>
			</p>
			<p style="margin-top: 15px; font-size: 13px; color: var(--kafichat-gray-600);">
				<?php echo esc_html__( 'برای اطلاعات بیشتر، به', 'kafichat-lite' ); ?> 
				<a href="https://chat.kafgram.com/privacy" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html__( 'سیاست حریم خصوصی ما', 'kafichat-lite' ); ?>
				</a> 
				<?php echo esc_html__( 'مراجعه کنید.', 'kafichat-lite' ); ?>
			</p>
		</div>
		<?php
	}

	private static function render_tab_bale( array $settings ): void {
		$bot_token       = ! empty( $settings['bot_token'] );
		$admin_chat_id   = $settings['admin_chat_id'] ?? '';
		$webhook_url     = WebhookHandler::get_webhook_url();
		$webhook_url_sec = WebhookHandler::get_webhook_url_with_secret();
		?>
		<h2><?php echo esc_html__( 'اتصال به پیام‌رسان بله', 'kafichat-lite' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'ربات بله خود را برای دریافت پیام‌ها متصل کنید.', 'kafichat-lite' ); ?></p>
		<form class="kafichat-form" data-form="settings">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="bot_token"><?php echo esc_html__( 'توکن ربات', 'kafichat-lite' ); ?></label>
					</th>
					<td>
						<input type="password" id="bot_token" name="bot_token" class="regular-text"
							placeholder="<?php echo esc_attr( $bot_token ? '••••••••••••••••' : '123456789:ABC...' ); ?>"
							autocomplete="off">
						<p class="description">
							<?php echo esc_html__( 'این توکن را از @BotFather در بله دریافت کنید.', 'kafichat-lite' ); ?>
							<?php if ( $bot_token ) : ?>
								<br><strong style="color: #10b981;">✓ <?php echo esc_html__( 'توکن تنظیم شده است. برای حفظ توکن فعلی، این فیلد را خالی بگذارید.', 'kafichat-lite' ); ?></strong>
							<?php endif; ?>
						</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="admin_chat_id"><?php echo esc_html__( 'شناسه چت ادمین', 'kafichat-lite' ); ?></label>
						</th>
						<td>
							<input type="text" id="admin_chat_id" name="admin_chat_id" class="regular-text"
								value="<?php echo esc_attr( $admin_chat_id ); ?>"
								placeholder="123456789" pattern="[0-9]+">
							<p class="description"><?php echo esc_html__( 'شناسه چتی که پیام‌های ربات به آن ارسال می‌شود.', 'kafichat-lite' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'وب‌هوک', 'kafichat-lite' ); ?></th>
						<td>
							<p>
								<strong><?php echo esc_html__( 'آدرس وب‌هوک اصلی:', 'kafichat-lite' ); ?></strong><br>
								<code style="display: inline-block; padding: 8px 12px; background: var(--kafichat-gray-100); word-break: break-all; border-radius: 6px; margin-top: 8px;"><?php echo esc_html( $webhook_url ); ?></code>
							</p>
							<p style="margin-top: 15px;">
								<strong><?php echo esc_html__( 'آدرس وب‌هوک جایگزین (مخصوص هاست‌های محدود):', 'kafichat-lite' ); ?></strong><br>
								<code style="display: inline-block; padding: 8px 12px; background: var(--kafichat-gray-100); word-break: break-all; border-radius: 6px; margin-top: 8px;"><?php echo esc_html( $webhook_url_sec ); ?></code>
								<span class="description" style="display:block; margin-top:5px;"><?php echo esc_html__( 'اگر هاست شما هدرهای امنیتی را حذف می‌کند، از این آدرس استفاده کنید.', 'kafichat-lite' ); ?></span>
							</p>
							<div class="kafichat-actions-row" style="margin-top: 20px;">
								<button type="button" class="button button-primary" id="kafichat-set-webhook" style="background: var(--kafichat-primary); color: white; border-color: var(--kafichat-primary);">
									<span class="dashicons dashicons-cloud-upload"></span>
									<?php echo esc_html__( 'تنظیم وب‌هوک در بله', 'kafichat-lite' ); ?>
								</button>
								<button type="button" class="button button-secondary" id="kafichat-webhook-health" style="border-color: var(--kafichat-gray-300);">
									<span class="dashicons dashicons-visibility"></span>
									<?php echo esc_html__( 'بررسی وضعیت', 'kafichat-lite' ); ?>
								</button>
							</div>
							<div id="kafichat-webhook-status" style="margin-top: 15px;"></div>
						</td>
					</tr>
				</table>
			<div class="kafichat-actions-row">
				<?php self::render_submit_button(); ?>
				<button type="button" class="button kafichat-btn-test-connection" id="kafichat-test-bale">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php echo esc_html__( 'تست اتصال', 'kafichat-lite' ); ?>
				</button>
			</div>
			<div id="kafichat-test-result" style="margin-top: 15px;"></div>
		</form>
		<div class="kafichat-help-box">
   			 <h3><span class="dashicons dashicons-info"></span> <?php echo esc_html__( 'راهنمای دریافت توکن و شناسه چت', 'kafichat-lite' ); ?></h3>
   		 <ol>
        <li><?php echo esc_html__( 'در بله به دنبال @BotFather بگردید', 'kafichat-lite' ); ?></li>
        <li><?php echo esc_html__( 'دستور /newbot را ارسال کنید و دستورالعمل‌ها را دنبال کنید', 'kafichat-lite' ); ?></li>
        <li><?php echo esc_html__( 'توکن ربات را کپی کرده و در فیلد بالا وارد کنید', 'kafichat-lite' ); ?></li>
        <li><?php echo esc_html__( 'برای دریافت شناسه چت ادمین، به ربات @userinfo_idbot در بله پیام دهید', 'kafichat-lite' ); ?></li>
        <li><a href="<?php echo esc_url( 'https://chat.kafgram.com/#setup' ); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html__( 'مشاهده آموزش‌ها و نکات استفاده', 'kafichat-lite' ); ?></a>
        </li>
    </ol>
</div>
		<?php
	}

	private static function render_tab_appearance( array $settings ): void {
		$header_title    = $settings['header_title'] ?? 'پشتیبانی آنلاین';
		$header_subtitle = $settings['header_subtitle'] ?? 'سعی میکنیم در سریعترین زمان ممکن پاسخگوی شما باشیم...';
		$tooltip_text    = $settings['tooltip_text'] ?? 'پشتیبانی آنلاین';
		$welcome_message = $settings['welcome_message'] ?? 'سلام! چطور می‌تونم کمکتون کنم؟';
		?>
		<h2><?php echo esc_html__( 'ظاهر ویجت', 'kafichat-lite' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'سفارشی‌سازی ظاهر ویجت چت', 'kafichat-lite' ); ?></p>
		<form class="kafichat-form" data-form="settings">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="header_title"><?php echo esc_html__( 'عنوان هدر', 'kafichat-lite' ); ?></label>
					</th>
					<td>
						<input type="text" id="header_title" name="header_title" class="regular-text"
							value="<?php echo esc_attr( $header_title ); ?>" maxlength="50">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="header_subtitle"><?php echo esc_html__( 'اسلاگ زیر عنوان', 'kafichat-lite' ); ?></label>
					</th>
					<td>
						<input type="text" id="header_subtitle" name="header_subtitle" class="regular-text"
							value="<?php echo esc_attr( $header_subtitle ); ?>" maxlength="120">
						<p class="description"><?php echo esc_html__( 'متن کوتاهی که زیر عنوان هدر نمایش داده می‌شود.', 'kafichat-lite' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="tooltip_text"><?php echo esc_html__( 'متن Tooltip', 'kafichat-lite' ); ?></label>
					</th>
					<td>
						<input type="text" id="tooltip_text" name="tooltip_text" class="regular-text"
							value="<?php echo esc_attr( $tooltip_text ); ?>" maxlength="50">
						<p class="description"><?php echo esc_html__( 'بالای دکمه ویجت نمایش داده می‌شود.', 'kafichat-lite' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="welcome_message"><?php echo esc_html__( 'پیام خوش‌آمدگویی', 'kafichat-lite' ); ?></label>
					</th>
					<td>
						<textarea id="welcome_message" name="welcome_message" rows="3" class="large-text"
							maxlength="500"><?php echo esc_textarea( $welcome_message ); ?></textarea>
						<p class="description"><?php echo esc_html__( 'اولین پیامی که هنگام باز کردن چت نمایش داده می‌شود.', 'kafichat-lite' ); ?></p>
					</td>
				</tr>
			</table>
			<?php self::render_submit_button(); ?>
		</form>
		<?php
	}

	private static function render_tab_messages( array $settings ): void {
		$retention_guest = $settings['retention_guest_days'] ?? 7;
		$retention_logs  = $settings['retention_logs_days'] ?? 30;
		$badge_interval  = $settings['badge_polling_interval_sec'] ?? 30;
		$full_interval   = $settings['full_polling_interval_sec'] ?? 5;
		?>
		<h2><?php echo esc_html__( 'پیام‌ها و داده‌ها', 'kafichat-lite' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'پیکربندی نگهداری داده‌ها و فواصل polling', 'kafichat-lite' ); ?></p>
		<form class="kafichat-form" data-form="settings">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="retention_guest_days"><?php echo esc_html__( 'نگهداری پیام‌های مهمان', 'kafichat-lite' ); ?></label>
					</th>
					<td>
						<input type="number" id="retention_guest_days" name="retention_guest_days"
							value="<?php echo esc_attr( $retention_guest ); ?>" min="1" max="365" style="width: 100px;">
						<?php echo esc_html__( 'روز', 'kafichat-lite' ); ?>
						<p class="description"><?php echo esc_html__( 'مدت زمان نگهداری پیام‌های مهمان قبل از حذف خودکار.', 'kafichat-lite' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="retention_logs_days"><?php echo esc_html__( 'نگهداری لاگ‌ها', 'kafichat-lite' ); ?></label>
						</th>
						<td>
							<input type="number" id="retention_logs_days" name="retention_logs_days"
								value="<?php echo esc_attr( $retention_logs ); ?>" min="1" max="365" style="width: 100px;">
							<?php echo esc_html__( 'روز', 'kafichat-lite' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="badge_polling_interval_sec"><?php echo esc_html__( 'فاصله Polling برای Badge', 'kafichat-lite' ); ?></label>
						</th>
						<td>
							<input type="number" id="badge_polling_interval_sec" name="badge_polling_interval_sec"
								value="<?php echo esc_attr( $badge_interval ); ?>" min="10" max="300" style="width: 100px;">
							<?php echo esc_html__( 'ثانیه', 'kafichat-lite' ); ?>
							<p class="description"><?php echo esc_html__( 'هر چند وقت یکبار برای پیام‌های جدید بررسی شود (فقط badge). مقدار بیشتر = فشار کمتر روی سرور.', 'kafichat-lite' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="full_polling_interval_sec"><?php echo esc_html__( 'فاصله Polling کامل', 'kafichat-lite' ); ?></label>
						</th>
						<td>
							<input type="number" id="full_polling_interval_sec" name="full_polling_interval_sec"
								value="<?php echo esc_attr( $full_interval ); ?>" min="3" max="60" style="width: 100px;">
							<?php echo esc_html__( 'ثانیه', 'kafichat-lite' ); ?>
							<p class="description"><?php echo esc_html__( 'هر چند وقت یکبار پیام‌ها را وقتی پنجره چت باز است دریافت کند.', 'kafichat-lite' ); ?></p>
						</td>
					</tr>
				</table>
			<?php self::render_submit_button(); ?>
			<div class="kafichat-info-box">
				<strong>💡 <?php echo esc_html__( 'نکته برای هاست اشتراکی:', 'kafichat-lite' ); ?></strong>
				<?php echo esc_html__( 'در هاست اشتراکی، از فواصل طولانی‌تر (30-60 ثانیه) استفاده کنید تا فشار کمتری روی سرور باشد. در VPS می‌توانید تا 5 ثانیه پایین بیایید.', 'kafichat-lite' ); ?>
			</div>
		</form>
		<?php
	}

	private static function render_tab_tools( array $settings ): void {
		$debug_mode  = ! empty( $settings['debug_mode'] );
		$has_sodium  = (int) get_option( 'kafichat_has_sodium', 0 );
		$php_version = PHP_VERSION;
		$wp_version  = get_bloginfo( 'version' );
		?>
		<h2><?php echo esc_html__( 'ابزارها و عیب‌یابی', 'kafichat-lite' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'ابزارهای تشخیصی و اطلاعات سیستم', 'kafichat-lite' ); ?></p>
		<form class="kafichat-form" data-form="settings">
			<table class="form-table">
				<tr>
					<th scope="row"><?php echo esc_html__( 'حالت Debug', 'kafichat-lite' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="debug_mode" value="1" <?php checked( $debug_mode ); ?>>
							<?php echo esc_html__( 'فعال‌سازی حالت debug (ثبت جزئیات بیشتر)', 'kafichat-lite' ); ?>
						</label>
						<p class="description"><?php echo esc_html__( 'فقط هنگام عیب‌یابی فعال کنید. بعد از اتمام کار غیرفعال کنید.', 'kafichat-lite' ); ?></p>
					</td>
				</tr>
			</table>
			<?php self::render_submit_button(); ?>
		</form>

		<h2 style="margin-top: 40px;"><?php echo esc_html__( 'اطلاعات سیستم', 'kafichat-lite' ); ?></h2>
		<table class="kafichat-system-info">
			<tr>
				<th><?php echo esc_html__( 'نسخه افزونه', 'kafichat-lite' ); ?></th>
				<td><code><?php echo esc_html( KAFICHAT_LITE_VERSION ); ?></code></td>
			</tr>
			<tr>
				<th><?php echo esc_html__( 'وردپرس', 'kafichat-lite' ); ?></th>
				<td><code><?php echo esc_html( $wp_version ); ?></code></td>
			</tr>
			<tr>
				<th><?php echo esc_html__( 'PHP', 'kafichat-lite' ); ?></th>
				<td><code><?php echo esc_html( $php_version ); ?></code></td>
			</tr>
			<tr>
				<th><?php echo esc_html__( 'Libsodium', 'kafichat-lite' ); ?></th>
				<td>
					<?php if ( $has_sodium ) : ?>
						<span class="kafichat-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'موجود', 'kafichat-lite' ); ?></span>
					<?php else : ?>
						<span class="kafichat-status-warn"><span class="dashicons dashicons-warning"></span> <?php echo esc_html__( 'موجود نیست (از OpenSSL استفاده می‌شود)', 'kafichat-lite' ); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><?php echo esc_html__( 'REST API', 'kafichat-lite' ); ?></th>
					<td><code><?php echo esc_html( rest_url( 'kafichat/v1/' ) ); ?></code></td>
				</tr>
			</table>

		<h2 style="margin-top: 40px;"><?php echo esc_html__( 'عملیات', 'kafichat-lite' ); ?></h2>
		<div class="kafichat-actions-row">
			<button type="button" class="button kafichat-btn-action" id="kafichat-manual-cleanup">
				<span class="dashicons dashicons-trash"></span>
				<?php echo esc_html__( 'اجرای پاک‌سازی', 'kafichat-lite' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=kafichat-conversations' ) ); ?>" class="button kafichat-btn-action">
				<span class="dashicons dashicons-format-chat"></span>
				<?php echo esc_html__( 'مشاهده مکالمات', 'kafichat-lite' ); ?>
			</a>
		</div>
		<div id="kafichat-cleanup-result" style="margin-top: 15px;"></div>

		<h2 style="margin-top: 40px;"><?php echo esc_html__( 'لاگ‌های سیستم', 'kafichat-lite' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'لاگ‌های اخیر افزونه برای عیب‌یابی و نظارت', 'kafichat-lite' ); ?></p>
		<div class="kafichat-actions-row">
			<button type="button" class="button" id="kafichat-load-logs">
				<span class="dashicons dashicons-list-view"></span>
				<?php echo esc_html__( 'بارگذاری لاگ‌های اخیر', 'kafichat-lite' ); ?>
			</button>
			<button type="button" class="button button-secondary" id="kafichat-refresh-logs" style="display: none;">
				<span class="dashicons dashicons-update"></span>
				<?php echo esc_html__( 'بروزرسانی', 'kafichat-lite' ); ?>
			</button>
		</div>
		<div id="kafichat-logs-container" style="margin-top: 20px;">
			<div class="kafichat-logs-empty" style="text-align: center; padding: 40px; color: var(--kafichat-gray-500);">
				<span class="dashicons dashicons-media-text" style="font-size: 48px; width: 48px; height: 48px; color: var(--kafichat-gray-300); margin-bottom: 10px;"></span>
				<p><?php echo esc_html__( 'برای مشاهده لاگ‌ها، روی دکمه بالا کلیک کنید', 'kafichat-lite' ); ?></p>
			</div>
		</div>
		<?php
	}

	private static function render_tab_pro(): void {
		?>
		<div class="kafichat-pro-tab-wrapper">
			<div class="kafichat-pro-header">
				<h2>💎 <?php echo esc_html__( 'ارتقا به کافی‌چت Pro', 'kafichat-lite' ); ?></h2>
				<p class="kafichat-pro-subtitle">
					<?php echo esc_html__( 'ویژگی‌های پیشرفته را باز کنید و تجربه پشتیبانی را متحول نمایید.', 'kafichat-lite' ); ?>
				</p>
			</div>
			<div class="kafichat-compare-table-wrapper">
				<table class="kafichat-compare-table">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'ویژگی‌ها', 'kafichat-lite' ); ?></th>
							<th class="kafichat-col-lite"><?php echo esc_html__( 'نسخه رایگان', 'kafichat-lite' ); ?></th>
							<th class="kafichat-col-pro"><?php echo esc_html__( 'نسخه Pro', 'kafichat-lite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php echo esc_html__( 'پلتفرم', 'kafichat-lite' ); ?></td>
							<td><?php echo esc_html__( 'فقط بله', 'kafichat-lite' ); ?></td>
							<td><strong><?php echo esc_html__( 'بله + تلگرام + سایت', 'kafichat-lite' ); ?></strong></td>
						</tr>
						<tr>
							<td><?php echo esc_html__( 'ارسال فایل', 'kafichat-lite' ); ?></td>
							<td><span class="kafichat-icon-cross">❌</span></td>
							<td><span class="kafichat-icon-check">✅</span></td>
						</tr>
						<tr>
							<td><?php echo esc_html__( 'چند ادمین', 'kafichat-lite' ); ?></td>
							<td><span class="kafichat-icon-cross">❌</span></td>
							<td><span class="kafichat-icon-check">✅</span></td>
						</tr>
						<tr>
							<td>Admin Lock</td>
							<td><span class="kafichat-icon-cross">❌</span></td>
							<td><span class="kafichat-icon-check">✅</span></td>
							</tr>
							<tr>
								<td><?php echo esc_html__( 'ساعت کاری', 'kafichat-lite' ); ?></td>
								<td><span class="kafichat-icon-cross">❌</span></td>
								<td><span class="kafichat-icon-check">✅</span></td>
							</tr>
							<tr>
								<td><?php echo esc_html__( 'پاسخگویی خودکار', 'kafichat-lite' ); ?></td>
								<td><span class="kafichat-icon-cross">❌</span></td>
								<td><span class="kafichat-icon-check">✅</span></td>
							</tr>
							<tr>
								<td><?php echo esc_html__( 'امتیازدهی', 'kafichat-lite' ); ?></td>
								<td><span class="kafichat-icon-cross">❌</span></td>
								<td><span class="kafichat-icon-check">✅</span></td>
							</tr>
							<tr>
								<td><?php echo esc_html__( 'پشتیبانی ووکامرس', 'kafichat-lite' ); ?></td>
								<td><span class="kafichat-icon-cross">❌</span></td>
								<td><span class="kafichat-icon-check">✅</span></td>
							</tr>
							<tr>
								<td><?php echo esc_html__( 'خروجی مکالمات', 'kafichat-lite' ); ?></td>
								<td><span class="kafichat-icon-cross">❌</span></td>
								<td><span class="kafichat-icon-check">✅</span></td>
							</tr>
							<tr>
								<td><?php echo esc_html__( 'نشانگر پیام جدید', 'kafichat-lite' ); ?></td>
								<td><span class="kafichat-icon-check">✅</span></td>
								<td><span class="kafichat-icon-check">✅</span></td>
							</tr>
					</tbody>
				</table>
			</div>
			<div class="kafichat-pro-cta">
				<a href="<?php echo esc_url( KAFICHAT_LITE_PRO_URL ); ?>" class="button button-primary button-hero" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html__( 'مشخصات کامل نسخه PRO را از اینجا ببینید', 'kafichat-lite' ); ?>
				</a>
				<p class="kafichat-pro-migration-notice">
					🔄 <?php echo esc_html__( 'مهاجرت خودکار داده‌ها — هیچ چیز از دست نمی‌رود', 'kafichat-lite' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	private static function render_submit_button(): void {
		?>
		<p class="submit">
			<button type="submit" class="button button-primary kafichat-btn-save">
				<span class="dashicons dashicons-saved"></span>
				<?php echo esc_html__( 'ذخیره تغییرات', 'kafichat-lite' ); ?>
			</button>
		</p>
		<?php
	}
}