<?php
namespace AFB\Frontend;

class Class_Form_Block {

	public function __construct() {
		add_action( 'init', [ $this, 'register_gutenberg_block' ] );
	}

	public function register_gutenberg_block() {
		// 1. Регистрируем JS-скрипт для редактора
		wp_register_script(
			'afb-form-block-editor',
			AFB_URL . 'assets/js/block-form.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ], // Зависимости WP
			AFB_VERSION,
			true
		);

		// 2. Регистрируем сам блок и связываем его со скриптом
		register_block_type( 'afb/form-block', [
			'editor_script'   => 'afb-form-block-editor', // Говорим WP загрузить наш скрипт в админку
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

		// Вызываем наш стандартный рендерер, который мы проверили
		$renderer = new Class_Form_Render();
		return $renderer->render_form_shortcode( [ 'id' => $form_id ] );
	}
}