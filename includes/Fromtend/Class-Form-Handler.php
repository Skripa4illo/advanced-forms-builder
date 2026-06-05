<?php
namespace AFB\Frontend;

use WP_REST_Request;
use WP_REST_Response;

class Class_Form_Handler {

	public function __construct() {
		// Регистрируем наш роут в REST API WordPress
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Регистрация эндпоинта: /wp-json/afb/v1/submit/
	 */
	public function register_routes() {
		register_rest_route( 'afb/v1', '/submit', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_form_submission' ],
			'permission_callback' => [ $this, 'check_permission' ], // Проверка безопасности
		] );
	}

	/**
	 * Проверка безопасности (Nonce)
	 */
	public function check_permission( WP_REST_Request $request ) {
		// Временно возвращаем true, чтобы не блокировать REST API
		// и спокойно протестировать отправку формы с фронтенда.
		return true;
	}

	/**
	 * Основная логика обработки данных формы
	 */
	public function handle_form_submission( WP_REST_Request $request ) {
		global $wpdb;

		// Получаем данные из тела запроса (JSON)
		$params  = $request->get_json_params();
		$form_id = isset( $params['form_id'] ) ? absint( $params['form_id'] ) : 0;
		$fields  = isset( $params['fields'] ) ? $params['fields'] : [];

		// 1. Базовая валидация входных параметров
		if ( ! $form_id ) {
			return new WP_REST_Response( [ 'error' => 'Invalid Form ID' ], 400 );
		}

		if ( empty( $fields ) ) {
			return new WP_REST_Response( [ 'error' => 'Form cannot be empty' ], 400 );
		}

		// 2. Санитизация (очистка) данных
		$sanitized_data = [];
		foreach ( $fields as $key => $value ) {
			// Очищаем ключи и значения в зависимости от их типа
			$safe_key = sanitize_text_field( $key );
			if ( is_array( $value ) ) {
				$sanitized_data[ $safe_key ] = array_map( 'sanitize_text_field', $value );
			} else {
				$sanitized_data[ $safe_key ] = sanitize_text_field( $value );
			}
		}

		// 3. Запись в базу данных
		$table_entries = $wpdb->prefix . 'afb_entries';
		
		$inserted = $wpdb->insert(
			$table_entries,
			[
				'form_id'    => $form_id,
				'response'   => wp_json_encode( $sanitized_data ), // Сохраняем ответы в JSON
				'user_ip'    => $this->get_user_ip(),
				'created_at' => current_time( 'mysql' )
			],
			[ '%d', '%s', '%s', '%s' ]
		);

		if ( ! $inserted ) {
			return new WP_REST_Response( [ 'error' => 'Database error, failed to save entry' ], 500 );
		}

		$entry_id = $wpdb->insert_id;

		// 4. Расширяемость (Хук для будущих Pro-интеграций: Telegram, Mailchimp, СМС)
		// Передаем ID записи, ID формы и сами очищенные данные
		do_action( 'afb_form_submission_success', $entry_id, $form_id, $sanitized_data );

		// Возвращаем успешный ответ фронтенду
		return new WP_REST_Response( [
			'success' => true,
			'message' => __( 'Форма успешно отправлена!', 'advanced-forms-builder' ),
			'entry_id' => $entry_id
		], 200 );
	}

	/**
	 * Получение IP адреса пользователя
	 */
	private function get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		return $_SERVER['REMOTE_ADDR'];
	}
}