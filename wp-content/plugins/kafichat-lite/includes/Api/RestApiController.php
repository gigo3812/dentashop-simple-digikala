<?php
/**
 * REST API controller for KafiChat Lite.
 *
 * @package KafiChatLite\Api
 */

namespace KafiChatLite\Api;

defined( 'ABSPATH' ) || exit;

use KafiChatLite\Chat\ConversationManager;
use KafiChatLite\Chat\BadgeCounter;
use KafiChatLite\Database\ConversationRepository;
use KafiChatLite\Database\LogRepository;
use KafiChatLite\Database\MessageRepository;
use KafiChatLite\Platform\BaleApiService;
use KafiChatLite\Platform\WebhookHandler;
use KafiChatLite\Security\GuestAuth;
use KafiChatLite\Security\InputValidator;
use KafiChatLite\Security\RateLimiter;
use KafiChatLite\Security\EncryptionService;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class RestApiController
 */
final class RestApiController {

    private const NAMESPACE = 'kafichat/v1';
    private const NONCE_ACTION = 'kafichat_admin';

    public static function register_routes(): void {
        self::register_public_routes();
        self::register_admin_routes();
    }

    private static function register_public_routes(): void {
        register_rest_route(
            self::NAMESPACE,
            '/conversation/start',
            array(
                'methods'             => 'POST',
                'callback'            => array( self::class, 'handle_start_conversation' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/message/send',
            array(
                'methods'             => 'POST',
                'callback'            => array( self::class, 'handle_send_message' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/messages/(?P<conversation_ref>[a-f0-9]{32})',
            array(
                'methods'             => 'GET',
                'callback'            => array( self::class, 'handle_get_messages' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'conversation_ref' => array(
                        'required'          => true,
                        'validate_callback' => array( InputValidator::class, 'is_valid_conversation_ref' ),
                    ),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/unread-count',
            array(
                'methods'             => 'GET',
                'callback'            => array( self::class, 'handle_unread_count' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'conversation_ref' => array(
                        'required'          => true,
                        'validate_callback' => array( InputValidator::class, 'is_valid_conversation_ref' ),
                    ),
                    'access_token' => array(
                        'required' => false,
                    ),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/message/seen',
            array(
                'methods'             => 'POST',
                'callback'            => array( self::class, 'handle_message_seen' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    private static function register_admin_routes(): void {
        $admin_routes = array(
            '/admin/settings'              => array( 'GET',  'handle_admin_get_settings' ),
            '/admin/settings/save'         => array( 'POST', 'handle_admin_save_settings' ),
            '/admin/conversations'         => array( 'GET',  'handle_admin_get_conversations' ),
            '/admin/conversation/close'    => array( 'POST', 'handle_admin_close_conversation' ),
            '/admin/conversation/messages' => array( 'GET',  'handle_admin_get_conversation_messages' ),
            '/admin/webhook/set'           => array( 'POST', 'handle_admin_set_webhook' ),
            '/admin/webhook/health-check'  => array( 'POST', 'handle_admin_webhook_health_check' ),
            '/admin/cleanup/manual'        => array( 'POST', 'handle_admin_manual_cleanup' ),
            '/admin/logs'                  => array( 'GET',  'handle_admin_get_logs' ),
            '/admin/bale/test'             => array( 'POST', 'handle_admin_bale_test' ),
        );

        foreach ( $admin_routes as $route => $config ) {
            register_rest_route(
                self::NAMESPACE,
                $route,
                array(
                    'methods'             => $config[0],
                    'callback'            => array( self::class, $config[1] ),
                    'permission_callback' => array( self::class, 'check_admin_permission' ),
                )
            );
        }
    }

    public static function check_admin_permission( WP_REST_Request $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'You do not have permission to access this endpoint.', 'kafichat-lite' ),
                array( 'status' => 403 )
            );
        }

        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! $nonce ) {
            $nonce = $request->get_param( '_wpnonce' );
        }

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new WP_Error(
                'rest_invalid_nonce',
                __( 'Invalid or missing nonce.', 'kafichat-lite' ),
                array( 'status' => 403 )
            );
        }

        return true;
    }

    public static function handle_start_conversation( WP_REST_Request $request ): WP_REST_Response {
        $params = $request->get_json_params();

        $data = array(
            'name'  => isset( $params['name'] ) ? sanitize_text_field( $params['name'] ) : '',
            'phone' => isset( $params['phone'] ) ? sanitize_text_field( $params['phone'] ) : '',
        );

        $result = ConversationManager::start_conversation( $data );

        return new WP_REST_Response(
            array(
                'ok'               => true,
                'conversation_ref' => $result['conversation_ref'],
                'access_token'     => $result['access_token'],
            ),
            200
        );
    }

    public static function handle_send_message( WP_REST_Request $request ): WP_REST_Response {
        $params = $request->get_json_params();

        $conversation_ref = isset( $params['conversation_ref'] ) ? sanitize_text_field( $params['conversation_ref'] ) : '';
        $content          = isset( $params['content'] ) ? $params['content'] : '';
        $access_token     = isset( $params['access_token'] ) ? sanitize_text_field( $params['access_token'] ) : null;

        if ( ! InputValidator::is_valid_conversation_ref( $conversation_ref ) ) {
            return self::error_response( 'Invalid conversation reference', 400 );
        }

        if ( empty( $content ) ) {
            return self::error_response( 'Message content is required', 400 );
        }

        $result = ConversationManager::send_message( $conversation_ref, $content, $access_token );

        if ( ! $result['success'] ) {
            $status = 'Rate limit' === substr( $result['message'] ?? '', 0, 10 ) ? 429 : 400;
            return self::error_response( $result['message'] ?? 'Failed to send message', $status );
        }

        return new WP_REST_Response(
            array(
                'ok'         => true,
                'message_id' => $result['message_id'],
                'status'     => 'sent',
            ),
            200
        );
    }

    public static function handle_get_messages( WP_REST_Request $request ): WP_REST_Response {
        $conversation_ref = $request->get_param( 'conversation_ref' );
        $access_token     = $request->get_param( 'access_token' );
        $limit            = (int) ( $request->get_param( 'limit' ) ?? 50 );
        $offset           = (int) ( $request->get_param( 'offset' ) ?? 0 );

        $limit  = min( max( $limit, 1 ), 100 );
        $offset = max( $offset, 0 );

        $messages = ConversationManager::get_messages( $conversation_ref, $access_token, $limit, $offset );

        if ( empty( $messages ) && ! self::verify_public_ownership( $conversation_ref, $access_token ) ) {
            return self::error_response( 'Conversation not found or access denied', 403 );
        }

        $formatted = array_map( function ( $msg ) {
            return array(
                'id'          => (int) $msg->id,
                'sender_type' => $msg->sender_type,
                'content'     => $msg->content,
                'status'      => $msg->status,
                'is_seen'     => (bool) $msg->is_seen,
                'created_at'  => $msg->created_at,
            );
        }, $messages );

        return new WP_REST_Response(
            array(
                'ok'       => true,
                'messages' => $formatted,
                'has_more' => count( $messages ) === $limit,
            ),
            200
        );
    }

    public static function handle_unread_count( WP_REST_Request $request ): WP_REST_Response {
        $conversation_ref = $request->get_param( 'conversation_ref' );
        $access_token     = $request->get_param( 'access_token' );

        $count = BadgeCounter::get_unread_count( $conversation_ref, $access_token );

        return new WP_REST_Response(
            array(
                'ok'      => true,
                'count'   => $count,
                'has_new' => $count > 0,
            ),
            200
        );
    }

    public static function handle_message_seen( WP_REST_Request $request ): WP_REST_Response {
        $params = $request->get_json_params();

        $conversation_ref = isset( $params['conversation_ref'] ) ? sanitize_text_field( $params['conversation_ref'] ) : '';
        $access_token     = isset( $params['access_token'] ) ? sanitize_text_field( $params['access_token'] ) : null;

        if ( ! InputValidator::is_valid_conversation_ref( $conversation_ref ) ) {
            return self::error_response( 'Invalid conversation reference', 400 );
        }

        $success = BadgeCounter::mark_all_seen( $conversation_ref, $access_token );

        return new WP_REST_Response(
            array( 'ok' => $success ),
            $success ? 200 : 403
        );
    }

    public static function handle_admin_get_settings(): WP_REST_Response {
        $settings = get_option( 'kafichat_settings', array() );

        $safe_settings = $settings;
        $safe_settings['bot_token_configured']     = ! empty( $settings['bot_token'] );
        $safe_settings['admin_chat_id_configured'] = ! empty( $settings['admin_chat_id'] );
        unset( $safe_settings['bot_token'] );

        return new WP_REST_Response(
            array(
                'ok'       => true,
                'settings' => $safe_settings,
                'nonce'    => wp_create_nonce( 'wp_rest' ),
            ),
            200
        );
    }

    public static function handle_admin_save_settings( WP_REST_Request $request ): WP_REST_Response {
        $params   = $request->get_json_params();
        $settings = get_option( 'kafichat_settings', array() );

        $allowed_keys = array(
            'enabled', 'position', 'bot_token', 'admin_chat_id',
            'webhook_enabled', 'polling_enabled', 'retention_guest_days',
            'retention_logs_days', 'primary_color', 'header_title',
            'welcome_message', 'tooltip_text', 'guest_name_required',
            'guest_phone_required', 'badge_polling_interval_sec',
            'full_polling_interval_sec', 'debug_mode','header_subtitle',
        );

        foreach ( $allowed_keys as $key ) {
            if ( ! isset( $params[ $key ] ) ) {
                continue;
            }

            $value = $params[ $key ];

            switch ( $key ) {
                case 'bot_token':
                    $settings[ $key ] = sanitize_text_field( $value );
                    break;
                case 'admin_chat_id':
                    $settings[ $key ] = (int) $value;
                    break;
                case 'primary_color':
                    $sanitized = sanitize_hex_color( $value );
                    $settings[ $key ] = $sanitized ?: '#5B6CE7';
                    break;
                case 'position':
                    $valid = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
                    $settings[ $key ] = in_array( $value, $valid, true ) ? $value : 'bottom-right';
                    break;
                case 'header_title':
                case 'header_subtitle':
                case 'welcome_message':
                case 'tooltip_text':
                    $settings[ $key ] = sanitize_textarea_field( $value );
                    break;
                case 'retention_guest_days':
                case 'retention_logs_days':
                case 'badge_polling_interval_sec':
                case 'full_polling_interval_sec':
                    $settings[ $key ] = max( 1, (int) $value );
                    break;
                case 'enabled':
                case 'webhook_enabled':
                case 'polling_enabled':
                case 'guest_name_required':
                case 'guest_phone_required':
                case 'debug_mode':
                    $settings[ $key ] = (bool) $value;
                    break;
            }
        }

        update_option( 'kafichat_settings', $settings );
        LogRepository::info( 'Settings updated', 'admin' );

        return new WP_REST_Response(
            array(
                'ok'      => true,
                'message' => __( 'Settings saved successfully.', 'kafichat-lite' ),
            ),
            200
        );
    }

    /**
     * GET /admin/conversations
     */
    public static function handle_admin_get_conversations( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $status   = $request->get_param( 'status' );
        $page     = max( 1, (int) ( $request->get_param( 'page' ) ?? 1 ) );
        $per_page = min( 100, max( 1, (int) ( $request->get_param( 'per_page' ) ?? 20 ) ) );
        $offset   = ( $page - 1 ) * $per_page;
        
        $table = esc_sql( $wpdb->prefix . 'kafichat_conversations' );

        $where = '1=1';
        $where_params = array();
        if ( $status && 'all' !== $status ) {
            $where = 'status = %s';
            $where_params[] = $status;
        }

        // Count query
        if ( empty( $where_params ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where}" );
        } else {
            $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $where_params ) );
        }

        // Select query with LEFT JOIN to avoid N+1 queries
        $users_table = esc_sql( $wpdb->users );
        $select_sql = "SELECT c.id, c.conversation_ref, c.user_id, c.guest_name, c.guest_phone, c.status, c.created_at, c.updated_at, u.display_name
                       FROM {$table} c
                       LEFT JOIN {$users_table} u ON c.user_id = u.ID
                       WHERE {$where}
                       ORDER BY c.updated_at DESC
                       LIMIT %d OFFSET %d";
        
        $query_params = array_merge( $where_params, array( $per_page, $offset ) );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results( $wpdb->prepare( $select_sql, $query_params ), ARRAY_A );

        $conversations = array_map(
            function ( $row ) {
                $guest_name  = $row['guest_name'] ?? '';
                $guest_phone = $row['guest_phone'] ?? '';

                if ( class_exists( '\KafiChatLite\Security\EncryptionService' ) ) {
                    try {
                        if ( ! empty( $guest_name ) ) {
                            $dec = EncryptionService::decrypt( $guest_name );
                            if ( null !== $dec && '' !== $dec ) {
                                $guest_name = $dec;
                            }
                        }
                        if ( ! empty( $guest_phone ) ) {
                            $dec = EncryptionService::decrypt( $guest_phone );
                            if ( null !== $dec && '' !== $dec ) {
                                $guest_phone = $dec;
                            }
                        }
                    } catch ( \Throwable $e ) {
                        LogRepository::error( 'KafiChat Decrypt error: ' . $e->getMessage(), 'encryption' );
                    }
                }

                $display_name = '';

                // استفاده از نام کاربر وردپرس که در کوئری JOIN گرفتیم (بدون کوئری اضافه)
                if ( ! empty( $row['user_id'] ) && (int) $row['user_id'] > 0 && ! empty( $row['display_name'] ) ) {
                    $display_name = $row['display_name'];
                }

                if ( empty( $display_name ) && ! empty( $guest_name ) && ! self::looks_encrypted( $guest_name ) ) {
                    $display_name = $guest_name;
                }

                if ( empty( $display_name ) && ! empty( $guest_phone ) && ! self::looks_encrypted( $guest_phone ) ) {
                    $display_name = $guest_phone;
                }

                if ( empty( $display_name ) ) {
                    $display_name = 'مهمان ناشناس';
                }

                if ( self::looks_encrypted( $guest_phone ) ) {
                    $guest_phone = '';
                }

                return array(
                    'id'               => (int) $row['id'],
                    'conversation_ref' => $row['conversation_ref'],
                    'user_id'          => (int) $row['user_id'],
                    'guest_name'       => $display_name,
                    'guest_phone'      => $guest_phone,
                    'status'           => $row['status'],
                    'created_at'       => $row['created_at'],
                    'updated_at'       => $row['updated_at'],
                    'last_activity_at' => $row['updated_at'],
                );
            },
            $results
        );

        return new WP_REST_Response(
            array(
                'ok'            => true,
                'conversations' => $conversations,
                'total'         => $total,
                'page'          => $page,
                'per_page'      => $per_page,
                'total_pages'   => (int) ceil( $total / $per_page ),
            ),
            200
        );
    }

    public static function handle_admin_close_conversation( WP_REST_Request $request ): WP_REST_Response {
        $params          = $request->get_json_params();
        $conversation_id = isset( $params['conversation_id'] ) ? (int) $params['conversation_id'] : 0;

        if ( $conversation_id <= 0 ) {
            return self::error_response( 'Invalid conversation ID', 400 );
        }

        $success = ConversationManager::close_conversation( $conversation_id );

        if ( $success ) {
            LogRepository::info( "Conversation {$conversation_id} closed by admin", 'admin' );
        }

        return new WP_REST_Response(
            array( 'ok' => $success ),
            $success ? 200 : 400
        );
    }

    /**
     * GET /admin/conversation/messages
     */
    public static function handle_admin_get_conversation_messages( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $conv_ref = $request->get_param( 'conversation_ref' );
        if ( ! $conv_ref ) {
            return self::error_response( 'Missing conversation_ref', 400 );
        }

        $conv_table = esc_sql( $wpdb->prefix . 'kafichat_conversations' );
        $msg_table  = esc_sql( $wpdb->prefix . 'kafichat_messages' );

        // ایجاد کوئری با استفاده از placeholders و نادیده گرفتن هشدار برای نام جدول
        $sql_conv = "SELECT id, conversation_ref, user_id, guest_name, guest_phone, status, created_at, updated_at
                     FROM {$conv_table}
                     WHERE conversation_ref = %s
                     LIMIT 1";
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $conversation = $wpdb->get_row( $wpdb->prepare( $sql_conv, $conv_ref ), ARRAY_A );

        if ( ! $conversation ) {
            return self::error_response( 'Conversation not found', 404 );
        }

        // رمزگشایی (بدون تغییر)
        if ( class_exists( '\KafiChatLite\Security\EncryptionService' ) ) {
            try {
                if ( ! empty( $conversation['guest_name'] ) ) {
                    $d = EncryptionService::decrypt( $conversation['guest_name'] );
                    if ( null !== $d && '' !== $d ) {
                        $conversation['guest_name'] = $d;
                    }
                }
                if ( ! empty( $conversation['guest_phone'] ) ) {
                    $d = EncryptionService::decrypt( $conversation['guest_phone'] );
                    if ( null !== $d && '' !== $d ) {
                        $conversation['guest_phone'] = $d;
                    }
                }
            } catch ( \Throwable $e ) {
    LogRepository::error( 'KafiChat Decrypt error in messages: ' . $e->getMessage(), 'encryption' );
}
        }

        if ( self::looks_encrypted( $conversation['guest_name'] ?? '' ) ) {
            $conversation['guest_name'] = '';
        }
        if ( self::looks_encrypted( $conversation['guest_phone'] ?? '' ) ) {
            $conversation['guest_phone'] = '';
        }

        if ( ! empty( $conversation['user_id'] ) && (int) $conversation['user_id'] > 0 ) {
            $user = get_user_by( 'id', (int) $conversation['user_id'] );
            if ( $user ) {
                $conversation['guest_name'] = $user->display_name;
            }
        }

        // کوئری پیام‌ها با همان روش
        $sql_msgs = "SELECT id, sender_type, content, status, created_at
                     FROM {$msg_table}
                     WHERE conversation_id = %d
                     ORDER BY created_at ASC";
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $messages = $wpdb->get_results( $wpdb->prepare( $sql_msgs, $conversation['id'] ), ARRAY_A );

        return new WP_REST_Response(
            array(
                'ok'           => true,
                'conversation' => $conversation,
                'messages'     => $messages,
            ),
            200
        );
    }

    public static function handle_admin_set_webhook(): WP_REST_Response {
        $settings = get_option( 'kafichat_settings', array() );
        if ( empty( $settings['bot_token'] ) ) {
            return self::error_response( 'Bot token not configured', 400 );
        }

        $webhook_url = WebhookHandler::get_webhook_url_with_secret();
        $result      = BaleApiService::setWebhook( $webhook_url );

        if ( $result && isset( $result['ok'] ) && $result['ok'] ) {
            $settings['webhook_enabled'] = true;
            update_option( 'kafichat_settings', $settings );
            LogRepository::info( 'Webhook set successfully (with secret in URL)', 'webhook' );
            return new WP_REST_Response(
                array(
                    'ok'      => true,
                    'message' => __( 'Webhook set successfully.', 'kafichat-lite' ),
                    'url'     => $webhook_url,
                ),
                200
            );
        }

        $error_msg = isset( $result['description'] ) ? $result['description'] : 'Unknown error';
        LogRepository::error( 'Failed to set webhook: ' . $error_msg, 'webhook' );
        return self::error_response( 'Failed to set webhook: ' . $error_msg, 500 );
    }

    public static function handle_admin_webhook_health_check(): WP_REST_Response {
        $health = WebhookHandler::health_check();

        return new WP_REST_Response(
            array(
                'ok'      => true,
                'healthy' => $health['healthy'],
                'status'  => $health['status'],
                'info'    => $health['webhook_info'],
            ),
            200
        );
    }

    public static function handle_admin_manual_cleanup(): WP_REST_Response {
        $settings = get_option( 'kafichat_settings', array() );

        $guest_days = (int) ( $settings['retention_guest_days'] ?? 7 );
        $logs_days  = (int) ( $settings['retention_logs_days'] ?? 30 );

        $deleted_conversations = ConversationRepository::delete_old_guest_conversations( $guest_days );
        $deleted_logs          = LogRepository::cleanup( $logs_days );

        LogRepository::info(
            'Manual cleanup executed',
            'admin',
            array(
                'deleted_conversations' => $deleted_conversations,
                'deleted_logs'          => $deleted_logs,
            )
        );

        return new WP_REST_Response(
            array(
                'ok'                    => true,
                'deleted_conversations' => $deleted_conversations,
                'deleted_logs'          => $deleted_logs,
            ),
            200
        );
    }

    public static function handle_admin_get_logs( WP_REST_Request $request ): WP_REST_Response {
        $level   = sanitize_text_field( $request->get_param( 'level' ) ?? '' );
        $context = sanitize_text_field( $request->get_param( 'context' ) ?? '' );
        $limit   = (int) ( $request->get_param( 'limit' ) ?? 100 );
        $offset  = (int) ( $request->get_param( 'offset' ) ?? 0 );

        $args = array(
            'limit'  => min( max( $limit, 1 ), 500 ),
            'offset' => max( $offset, 0 ),
        );

        if ( $level && in_array( $level, array( 'info', 'warning', 'error' ), true ) ) {
            $args['level'] = $level;
        }

        if ( $context ) {
            $args['context'] = $context;
        }

        $logs = LogRepository::get_all( $args );

        $formatted = array_map( function ( $log ) {
            return array(
                'id'         => (int) $log->id,
                'level'      => $log->level,
                'context'    => $log->context,
                'message'    => $log->message,
                'data'       => $log->data ? json_decode( $log->data, true ) : null,
                'created_at' => $log->created_at,
            );
        }, $logs );

        return new WP_REST_Response(
            array(
                'ok'    => true,
                'logs'  => $formatted,
                'total' => count( $formatted ),
            ),
            200
        );
    }

    public static function handle_admin_bale_test(): WP_REST_Response {
        $settings = get_option( 'kafichat_settings', array() );
        if ( empty( $settings['bot_token'] ) ) {
            return self::error_response( 'Bot token not configured', 400 );
        }

        $me = BaleApiService::getMe();
        if ( ! $me || ! isset( $me['ok'] ) || ! $me['ok'] ) {
            return self::error_response( 'Failed to connect to Bale API', 500 );
        }

        $bot_info = $me['result'] ?? array();

        $message_sent  = false;
        $bale_response = null;
        if ( ! empty( $settings['admin_chat_id'] ) ) {
            $test_message  = "✅ KafiChat Lite test successful!\nBot: @" . ( $bot_info['username'] ?? 'unknown' );
            $bale_response = BaleApiService::sendMessage( (int) $settings['admin_chat_id'], $test_message );
            $message_sent  = $bale_response && isset( $bale_response['ok'] ) && $bale_response['ok'];

            LogRepository::info(
                'Bale test sendMessage response',
                'api',
                array(
                    'response_ok' => $bale_response['ok'] ?? null,
                    'message_id'  => $bale_response['result']['message_id'] ?? null,
                    'chat_type'   => $bale_response['result']['chat']['type'] ?? null,
                )
            );
        }

        return new WP_REST_Response(
            array(
                'ok'           => true,
                'bot_username' => $bot_info['username'] ?? '',
                'bot_name'     => $bot_info['first_name'] ?? '',
                'message_sent' => $message_sent,
            ),
            200
        );
    }

    private static function looks_encrypted( string $value ): bool {
        if ( '' === $value ) {
            return false;
        }
        return preg_match( '/^[A-Za-z0-9+\/=]{10,}\.[A-Za-z0-9+\/=]{10,}/', $value ) === 1;
    }

    private static function verify_public_ownership( string $conversation_ref, ?string $access_token ): bool {
        $conv = ConversationRepository::find_by_ref( $conversation_ref );
        if ( ! $conv ) {
            return false;
        }

        if ( is_user_logged_in() && $conv->user_id && (int) $conv->user_id === get_current_user_id() ) {
            return true;
        }

        $token = $access_token ?: GuestAuth::get_current_token();
        if ( ! $token || ! GuestAuth::is_valid_token_format( $token ) ) {
            return false;
        }

        return $conv->guest_token_hash === GuestAuth::hash_token( $token );
    }

    private static function error_response( string $message, int $status ): WP_REST_Response {
        return new WP_REST_Response(
            array(
                'ok'    => false,
                'error' => $message,
            ),
            $status
        );
    }
}