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
    
    // Меняем нижние подчеркивания на дефисы в имени файла
    $parts[ $last_key ] = str_replace( '_', '-', $parts[ $last_key ] );
    
    // Вариант 1: Проверяем путь строго в нижнем регистре (как требует WP стандарт)
    $parts_lowercase = $parts;
    for ( $i = 0; $i <= $last_key; $i++ ) {
        $parts_lowercase[$i] = strtolower( $parts_lowercase[$i] );
    }
    $file_lowercase = $base_dir . implode( '/', $parts_lowercase ) . '.php';

    if ( file_exists( $file_lowercase ) ) {
        require_once $file_lowercase;
        return;
    }

    // Вариант 2: Если не нашли, проверяем путь с сохранением регистра папок (для папки Admin)
    $parts[ $last_key ] = strtolower( $parts[ $last_key ] ); // файл всё равно в нижнем регистре
    $file_as_is = $base_dir . implode( '/', $parts ) . '.php';

    if ( file_exists( $file_as_is ) ) {
        require_once $file_as_is;
        return;
    }
} );

// ЖЕЛЕЗОБЕТОННАЯ ИНИЦИАЛИЗАЦИЯ ЯДРА (БЕЗ ОБЕРТКИ В ХУКИ)
// try {
// 	if ( class_exists( 'AFB\Class_Core' ) ) {
// 		$plugin = new AFB\Class_Core();
// 		$plugin->run();
// 	} else {
// 		error_log( 'Advanced Forms Builder Error: Class_Core not found.' );
// 	}
// } catch ( \Throwable $e ) {
// 	error_log( 'Advanced Forms Builder Critical Crash: ' . $e->getMessage() );
// }

// БЕЗОПАСНАЯ ИНИЦИАЛИЗАЦИЯ И ДЕБАГ ЯДРА
try {
    if ( class_exists( 'AFB\Class_Core' ) ) {
        $plugin = new AFB\Class_Core();
        $plugin->run();
    } else {
        // Если класс не найден, выводим заметную плашку в админке
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p style="color:red; font-size:20px; font-weight:bold; padding:10px;">❌ Advanced Forms Builder Error: Class_Core NOT found. Автозагрузчик промахнулся мимо файла!</p></div>';
        });
    }
} catch ( \Throwable $e ) {
    // Если упало внутри самого ядра
    add_action( 'admin_notices', function() use ( $e ) {
        echo '<div class="notice notice-error"><p style="color:orange; font-size:20px; font-weight:bold; padding:10px;">⚠️ Advanced Forms Builder Critical Crash: ' . esc_html( $e->getMessage() ) . '</p></div>';
    });
}

// add_action( 'admin_notices', function() {
//     echo '<div class="notice notice-success"><p style="color:red; font-size:20px; font-weight:bold;">!!! ПЛАГИН AFB ФИЗИЧЕСКИ ЗАПУЩЕН !!!</p></div>';
// });

// ТЕСТ: Принудительное подключение скрипта в редактор
// add_action( 'enqueue_block_editor_assets', function() {
//     wp_enqueue_script(
//         'afb-test-block-script',
//         plugin_dir_url( __FILE__ ) . 'assets/js/block-form.js',
//         array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
//         time(), // сброс кэша версии при каждой перезагрузке
//         true
//     );
// });