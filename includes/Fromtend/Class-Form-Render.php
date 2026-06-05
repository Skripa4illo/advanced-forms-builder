<?php
namespace AFB\Frontend;

class Class_Form_Render {

	public function __construct() {
		// Регистрируем шорткод [afb_form id="X"]
		add_shortcode( 'afb_form', [ $this, 'render_form_shortcode' ] );
		
		// Регистрируем скрипты, чтобы подключить их только тогда, когда вызван шорткод
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
	}

	/**
	 * Предварительная регистрация JS файлов
	 */
	public function register_assets() {
		wp_register_script(
			'afb-frontend-script',
			AFB_URL . 'assets/js/frontend-form.js',
			[],
			AFB_VERSION,
			true
		);

		// Передаем динамические переменные из PHP в JS (URL нашего API и Nonce)
		wp_localize_script( 'afb-frontend-script', 'afb_vars', [
			'rest_url' => esc_url_raw( rest_url( 'afb/v1/submit' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ) // Важно: используем 'wp_rest' для встроенной проверки в REST API
		] );
	}

	/**
	 * Рендеринг HTML формы по шорткоду
	 */
	public function render_form_shortcode( $atts ) {
		// Парсим атрибуты шорткода (дефолтный ID = 1)
		$args = shortcode_atts( [
			'id' => 1,
		], $atts );

		$form_id = absint( $args['id'] );

		// Подключаем наши зарегистрированные JS скрипты на страницу с шорткодом
		wp_enqueue_script( 'afb-frontend-script' );

		// Начинаем буферизацию вывода HTML
		ob_start();
		?>
		<div class="afb-form-wrapper" id="afb-form-container-<?php echo $form_id; ?>">
			<form data-form-id="<?php echo $form_id; ?>" method="POST" novalidate>
				
				<div class="afb-form-group" style="margin-bottom: 15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;"><?php _e( 'Ваше имя', 'advanced-forms-builder' ); ?></label>
					<input type="text" name="user_name" required style="width:100%; padding:8px; box-sizing:border-box;">
				</div>

				<div class="afb-form-group" style="margin-bottom: 15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;"><?php _e( 'Email адрес', 'advanced-forms-builder' ); ?></label>
					<input type="email" name="user_email" required style="width:100%; padding:8px; box-sizing:border-box;">
				</div>

				<div class="afb-form-group" style="margin-bottom: 15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;"><?php _e( 'Сообщение', 'advanced-forms-builder' ); ?></label>
					<textarea name="user_message" rows="4" style="width:100%; padding:8px; box-sizing:border-box;"></textarea>
				</div>

				<div class="afb-message-box" style="margin-bottom: 15px; font-weight: bold;"></div>

				<div class="afb-form-submit">
					<button type="submit" class="button button-primary" style="padding: 10px 20px; background: #0073aa; color: #fff; border: none; cursor: pointer;">
						<?php _e( 'Отправить форму', 'advanced-forms-builder' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
}