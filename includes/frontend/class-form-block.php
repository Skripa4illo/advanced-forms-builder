<?php
namespace AFB\frontend;

class Class_Form_Block {

	public function __construct() {
		// Регистрируем всё СТРОГО на хук init, когда ядро WP готово принимать блоки
		add_action( 'init', [ $this, 'register_gutenberg_block' ] );
	}

	public function register_gutenberg_block() {
		// Регистрируем скрипт редактора
		wp_register_script(
			'afb-form-block-editor',
			AFB_URL . 'assets/js/block-form.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ],
			AFB_VERSION,
			true
		);

		// Регистрируем тип блока и связываем его со скриптом через 'editor_script'
		register_block_type( 'afb/form-block', [
			'editor_script'   => 'afb-form-block-editor',
			'render_callback' => [ $this, 'render_block_html' ],
			'attributes'      => [
				'formId' => [
					'type'    => 'number',
					'default' => 1,
				],
			],
		] );
	}

	/**
	 * Колбэк для рендеринга блока на фронтенде
	 */
	public function render_block_html( $attributes ) {
		$form_id = isset( $attributes['formId'] ) ? absint( $attributes['formId'] ) : 1;

		$renderer = new Class_Form_Render();
		return $renderer->render_form_shortcode( [ 'id' => $form_id ] );
	}
}