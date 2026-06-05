<?php
/**
 * Plugin Name:       Advanced Forms Builder
 * Description:       Легковесный и расширяемый конструктор форм для WordPress.
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
	$parts[ $last_key ] = str_replace( '_', '-', $parts[ $last_index ] );
	
	// Чтобы застраховаться от регистра папок (Frontend vs frontend),
	// мы можем явно проверить файл. Но самый надежный способ, раз у тебя папка называется "Frontend",
	// это писать вызов класса строго с большой буквы: new Frontend\Class_Form_Render();
	
	$file = $base_dir . implode( '/', $parts ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// БЕЗОПАСНАЯ ИНИЦИАЛИЗАЦИЯ ЯДРА
add_action( 'plugins_loaded', function() {
	try {
		// Проверяем физическое существование класса перед созданием объекта
		if ( class_exists( 'AFB\Class_Core' ) ) {
			$plugin = new AFB\Class_Core();
			$plugin->run();
		} else {
			// Если класс не найден, пишем ошибку в лог WP, но не вешаем сайт
			error_log( 'Advanced Forms Builder Error: Class_Core not found.' );
		}
	} catch ( \Throwable $e ) {
		// Перехватываем любые критические ошибки (даже Fatal в PHP 7+)
		error_log( 'Advanced Forms Builder Critical Crash: ' . $e->getMessage() );
	}
} );