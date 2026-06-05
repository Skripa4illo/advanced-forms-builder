<?php
namespace AFB;

class Class_Core {

	public function run() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	private function load_dependencies() {
		// Здесь мы инициализируем компоненты
		if ( is_admin() ) {
			new Admin\Class_Admin_Menu();
		} else {
			new Frontend\Class_Form_Render();
		}
	}

	private function init_hooks() {
		// Общие хуки (например, локализация или API)
		add_action( 'init', [ $this, 'init_plugin' ] );
	}

	public function init_plugin() {
		load_plugin_textdomain( 'advanced-forms-builder', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
}