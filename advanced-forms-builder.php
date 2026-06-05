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
	
	// Унифицируем слэши для Linux: строго переводим всё в /
	$relative_class = str_replace( '\\', '/', $relative_class );
	
	$file_parts = explode( '/', $relative_class );
	$last_key = array_key_last( $file_parts );
	
	// Превращаем имя класса из "Class_Activator" в "Class-Activator"
	$file_parts[ $last_key ] = str_replace( '_', '-', $file_parts[ $last_key ] );
	
	// Собираем полный путь
	$file = $base_dir . implode( '/', $file_parts ) . '.php';

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