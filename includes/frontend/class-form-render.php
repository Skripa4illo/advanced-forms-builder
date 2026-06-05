<?php
namespace AFB\frontend;

class Class_Form_Render {

	public function __construct() {
		add_shortcode( 'afb_form', [ $this, 'render_form_shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
	}

	public function register_assets() {
		wp_register_script(
			'afb-frontend-script',
			AFB_URL . 'assets/js/frontend-form.js',
			[],
			AFB_VERSION,
			true
		);

		// Передаем переменные. Внутри REST API используем относительный путь /wp-json/, 
		// чтобы избежать проблем с кириллическими доменами и SSL на Hostinger
		wp_localize_script( 'afb-frontend-script', 'afb_vars', [
			'rest_url' => site_url( '/wp-json/afb/v1/submit' ),
			'nonce'    => wp_create_nonce( 'wp_rest' )
		] );
	}

	public function render_form_shortcode( $atts ) {
		$args = shortcode_atts( [ 'id' => 1 ], $atts );
		$form_id = absint( $args['id'] );

		wp_enqueue_script( 'afb-frontend-script' );

		ob_start();
		?>
		<div class="afb-form-wrapper" id="afb-form-container-<?php echo $form_id; ?>">
			<form data-form-id="<?php echo $form_id; ?>" method="POST" novalidate>
				
				<div class="afb-form-group" style="margin-bottom: 15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Ваше имя</label>
					<input type="text" name="user_name" required style="width:100%; padding:8px; box-sizing:border-box;">
				</div>

				<div class="afb-form-group" style="margin-bottom: 15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Email адрес</label>
					<input type="email" name="user_email" required style="width:100%; padding:8px; box-sizing:border-box;">
				</div>

				<div class="afb-form-group" style="margin-bottom: 15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Сообщение</label>
					<textarea name="user_message" rows="4" style="width:100%; padding:8px; box-sizing:border-box;"></textarea>
				</div>

				<div class="afb-message-box" style="margin-bottom: 15px; font-weight: bold;"></div>

				<div class="afb-form-submit">
					<button type="submit" style="padding: 10px 20px; background: #0073aa; color: #fff; border: none; cursor: pointer;">
						Отправить форму
					</button>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
}