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
    $relative_class = str_replace( '\\', '/', $relative_class );
    
    $parts = explode( '/', $relative_class );
    $last_key = array_key_last( $parts );
    
    // 1. Приводим все промежуточные папки (все элементы, КРОМЕ имени файла) к нижнему регистру
    for ( $i = 0; $i < $last_key; $i++ ) {
        $parts[$i] = strtolower( $parts[$i] );
    }
    
    // 2. Меняем нижние подчеркивания на дефисы в имени файла (Class_Form_Block -> Class-Form-Block)
    $parts[ $last_key ] = str_replace( '_', '-', $parts[ $last_key ] );
    
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

// ТЕСТ: Принудительное подключение скрипта в редактор
add_action( 'enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'afb-test-block-script',
        plugin_dir_url( __FILE__ ) . 'assets/js/block-form.js',
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
        time(), // сброс кэша версии при каждой перезагрузке
        true
    );
});