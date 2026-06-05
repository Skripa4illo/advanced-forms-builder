<?php
/**
 * Plugin Name:       Advanced Forms Builder
 * Description:       Легковесный и расширяемый конструктор форм для WordPress. Создавайте любые формы с помощью удобного интерфейса и отображайте их через блоки Gutenberg.
 * Version:           1.0.0
 * Author:            DevScripty
 * Text Domain:       advanced-forms-builder
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFB_VERSION', '1.0.0' );
define( 'AFB_PATH', plugin_dir_path( __FILE__ ) );
define( 'AFB_URL', plugin_dir_url( __FILE__ ) );

// НАДЕЖНЫЙ АВТОЗАГРУЗЧИК ДЛЯ ЛИНУКС
spl_autoload_register( function ( $class ) {
	$prefix = 'AFB\\';
	$base_dir = AFB_PATH . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	
	// Переводим обратные слэши в прямые
	$relative_class = str_replace( '\\', '/', $relative_class );
	
	$parts = explode( '/', $relative_class );
	$last_key = array_key_last( $parts );
	
	// Меняем нижние подчеркивания на дефисы в имени файла (Class_Form_Render -> Class-Form-Render)
	$parts[ $last_key ] = str_replace( '_', '-', $parts[ $last_key ] );
	
	// Чтобы застраховаться от регистра папок (Frontend vs frontend),
	// мы можем явно проверить файл. Но самый надежный способ, раз у тебя папка называется "Frontend",
	// это писать вызов класса строго с большой буквы: new Frontend\Class_Form_Render();
	
	$file = $base_dir . implode( '/', $parts ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// ЖЕЛЕЗОБЕТОННАЯ ИНИЦИАЛИЗАЦИЯ ЯДРА (БЕЗ ОБЕРТКИ В ХУКИ)
try {
	if ( class_exists( 'AFB\Class_Core' ) ) {
		$plugin = new AFB\Class_Core();
		$plugin->run();
	} else {
		error_log( 'Advanced Forms Builder Error: Class_Core not found.' );
	}
} catch ( \Throwable $e ) {
	error_log( 'Advanced Forms Builder Critical Crash: ' . $e->getMessage() );
}

// add_action( 'admin_notices', function() {
//     echo '<div class="notice notice-success"><p style="color:red; font-size:20px; font-weight:bold;">!!! ПЛАГИН AFB ФИЗИЧЕСКИ ЗАПУЩЕН !!!</p></div>';
// });