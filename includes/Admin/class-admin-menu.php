<?php
namespace AFB\Admin;

class Class_Admin_Menu {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );
	}

	public function add_menu_pages() {
		add_menu_page(
			__( 'AFB Forms', 'advanced-forms-builder' ),
			__( 'AFB Forms', 'advanced-forms-builder' ),
			'manage_options',
			'afb-forms',
			[ $this, 'render_admin_page' ],
			'dashicons-feedback',
			30
		);
	}

	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Advanced Forms Builder', 'advanced-forms-builder' ); ?></h1>
			<p><?php _e( 'Добро пожаловать. Здесь будет Vue.js/React конструктор форм.', 'advanced-forms-builder' ); ?></p>
		</div>
		<?php
	}
}