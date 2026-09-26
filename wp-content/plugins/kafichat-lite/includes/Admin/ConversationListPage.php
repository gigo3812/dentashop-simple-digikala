<?php
/**
 * Conversation list page for the admin panel.
 *
 * Displays all conversations with filtering, searching, and the ability to
 * view message history or close a conversation. Uses the REST API (phase 8)
 * for all data fetching and mutations.
 *
 * @package KafiChatLite\Admin
 */
namespace KafiChatLite\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class ConversationListPage
 */
final class ConversationListPage {

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	public const PAGE_SLUG = 'kafichat-conversations';

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_filter = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : 'all';
		
		$allowed = array( 'all', 'open', 'closed', 'archived' );
		if ( ! in_array( $current_filter, $allowed, true ) ) {
			$current_filter = 'all';
		}
		?>
		<div class="wrap kafichat-admin-wrap">
			<h1 class="kafichat-admin-title">
				<span class="dashicons dashicons-format-chat"></span>
				<?php echo esc_html__( 'مکالمات', 'kafichat-lite' ); ?>
			</h1>

			<div id="kafichat-notification" class="kafichat-notification" style="display: none;"></div>

			<div class="kafichat-filters">
				<div class="kafichat-filter-tabs">
					<?php
					$filters = array(
						'all'    => __( 'همه', 'kafichat-lite' ),
						'open'   => __( 'باز', 'kafichat-lite' ),
						'closed' => __( 'بسته', 'kafichat-lite' ),
					);
					$base_url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );

					foreach ( $filters as $key => $label ) {
						$url    = 'all' === $key ? $base_url : add_query_arg( 'status', $key, $base_url );
						$active = ( $current_filter === $key ) ? 'active' : '';
						printf(
							'<a href="%s" class="kafichat-filter-tab %s">%s</a>',
							esc_url( $url ),
							esc_attr( $active ),
							esc_html( $label )
						);
					}
					?>
				</div>

				<div class="kafichat-filter-search">
					<input type="text" id="kafichat-search"
						placeholder="<?php echo esc_attr__( 'جستجو بر اساس نام یا شماره...', 'kafichat-lite' ); ?>">
				</div>

				<button type="button" class="button" id="kafichat-refresh-list">
					<span class="dashicons dashicons-update"></span>
					<?php echo esc_html__( 'بروزرسانی', 'kafichat-lite' ); ?>
				</button>
			</div>

			<div id="kafichat-conversations-loading" class="kafichat-loading">
				<span class="spinner is-active"></span>
				<?php echo esc_html__( 'در حال بارگذاری...', 'kafichat-lite' ); ?>
			</div>

			<div id="kafichat-conversations-list" class="kafichat-conversations-list" style="display: none;"></div>

			<div id="kafichat-conversations-empty" class="kafichat-empty" style="display: none;">
				<span class="dashicons dashicons-format-chat"></span>
				<p><?php echo esc_html__( 'مکالمه‌ای یافت نشد.', 'kafichat-lite' ); ?></p>
			</div>
		</div>

		<!-- Modal for viewing messages -->
		<div id="kafichat-messages-modal" class="kafichat-modal" style="display: none;">
			<div class="kafichat-modal-backdrop"></div>
			<div class="kafichat-modal-content">
				<div class="kafichat-modal-header">
					<h2 id="kafichat-modal-title"><?php echo esc_html__( 'پیام‌های مکالمه', 'kafichat-lite' ); ?></h2>
					<button type="button" class="kafichat-modal-close">&times;</button>
				</div>
				<div id="kafichat-modal-body" class="kafichat-modal-body"></div>
			</div>
		</div>

		<?php
		wp_add_inline_script(
   		 'kafichat-admin',
   		'window.kafichatAdminPage = { currentFilter: ' . wp_json_encode( $current_filter ) . ' };',
   		 'before'
		);
		?>
		<?php
	}
}