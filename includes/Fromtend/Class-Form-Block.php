<?php
namespace AFB\Frontend;

class Class_Form_Block {

	public function __construct() {
		// Само объявление типа блока оставляем на init
		add_action( 'init', [ $this, 'register_gutenberg_block' ] );

		// А подключение JS-скрипта переносим на специальный хук редактора!
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_script' ] );
	}

	public function register_gutenberg_block() {
		// Регистрируем ТОЛЬКО блок. Связь по имени скрипта в FSE-темах часто отваливается на ранних хуках.
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

	public function enqueue_block_editor_script() {
		// Этот хук гарантирует, что 'wp-blocks', 'wp-element' и др. уже существуют в системе!
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