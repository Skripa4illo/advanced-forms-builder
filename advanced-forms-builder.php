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

/**
 * Простейший и надежный автозагрузчик для Linux/Hostinger
 */
spl_autoload_register( function ( $class ) {
	// Проверяем префикс нашего пространства имен
	$prefix = 'AFB\\';
	$len = strlen( $prefix );
	
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	// Получаем относительное имя класса (например, Frontend\Class_Form_Render)
	$relative_class = substr( $class, $len );
	
	// Заменяем обратные слэши на прямые для путей Linux
	$relative_class = str_replace( '\\', '/', $relative_class );
	
	// Разбиваем путь на массивы, чтобы точечно поправить имя файла
	$parts = explode( '/', $relative_class );
	$last_index = array_key_last( $parts );
	
	// Превращаем "Class_Form_Render" в "Class-Form-Render" (стандарт WordPress)
	$parts[ $last_index ] = str_replace( '_', '-', $parts[ $last_index ] );
	
	// Собираем финальный путь к файлу
	$file = AFB_PATH . 'includes/' . implode( '/', $parts ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/**
 * Хуки активации и деактивации
 */
register_activation_hook( __FILE__, function() {
	if ( class_exists( 'AFB\Class_Activator' ) ) {
		\AFB\Class_Activator::activate();
	}
} );

register_deactivation_hook( __FILE__, function() {
	if ( class_exists( 'AFB\Class_Deactivator' ) ) {
		\AFB\Class_Deactivator::deactivate();
	}
} );

/**
 * Инициализация ядра плагина
 */
add_action( 'plugins_loaded', function() {
	if ( class_exists( 'AFB\Class_Core' ) ) {
		$plugin = new \AFB\Class_Core();
		$plugin->run();
	}
} );