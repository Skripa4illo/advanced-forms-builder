<?php
namespace AFB\frontend;

class Class_Form_Block {

	public function __construct() {
		// Возвращаем железобетонный хук подключения ассетов редактора
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_gutenberg_block' ] );
	}

	public function register_gutenberg_block() {
		// Подключаем скрипт напрямую через enqueue, как в нашем успешном тесте
		wp_enqueue_script(
			'afb-form-block-editor',
			AFB_URL . 'assets/js/block-form.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ],
			time(), // Сбрасываем кэш при каждом пуше
			true
		);

		// Регистрируем сам блок
		register_block_type( 'afb/form-block', [
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