<?php
/**
 * Plugin Name:       Advanced Forms Builder
 * Description:       Легковесный и расширяемый конструктор форм для WordPress.
 * Version:           1.0.0
 * Author:            DevScripty
 * Text Domain:       advanced-forms-builder
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/Skripa4illo/advanced-forms-builder.git
 */

// Если файл вызван напрямую, прерываем выполнение
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Константы для удобства
define( 'AFB_VERSION', '1.0.0' );
define( 'AFB_PATH', plugin_dir_path( __FILE__ ) );
define( 'AFB_URL', plugin_dir_url( __FILE__ ) );

// Простейший автозагрузчик (для старта)
spl_autoload_register( function ( $class ) {
	$prefix = 'AFB\\';
	$base_dir = AFB_PATH . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	// Изменяем имя файла под стандарт Class-Name.php, если используешь его
	$file_parts = explode('/', $file);
	$last_key = array_key_last($file_parts);
	$file_parts[$last_key] = 'Class-' . $file_parts[$last_key];
	$file = implode('/', $file_parts);

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// Активация и деактивация
register_activation_hook( __FILE__, function() {
	AFB\Class_Activator::activate();
} );

// Запуск плагина
function run_advanced_forms_builder() {
	$plugin = new AFB\Class_Core();
	$plugin->run();
}
run_advanced_forms_builder();