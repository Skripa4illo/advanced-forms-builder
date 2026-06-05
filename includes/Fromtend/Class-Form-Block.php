<?php
namespace AFB\Frontend;

class Class_Form_Block {

	public function __construct() {
		// Регистрацию самого типа блока оставляем на init
		add_action( 'init', [ $this, 'register_gutenberg_block' ] );

		// Подключение скрипта переносим на специальный хук редактора блоков
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_assets' ] );
	}

	public function register_gutenberg_block() {
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

	public function enqueue_block_assets() {
		// Этот хук гарантирует, что все зависимости (wp.blocks, wp.components) уже созданы в глобальном окне браузера
		wp_enqueue_script(
			'afb-form-block-editor',
			AFB_URL . 'assets/js/block-form.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ],
			AFB_VERSION,
			true
		);
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