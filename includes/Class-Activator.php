<?php
namespace AFB;

class Class_Activator {

	public static function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		// Таблица для самих форм
		$table_forms = $wpdb->prefix . 'afb_forms';
		$sql_forms = "CREATE TABLE $table_forms (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			form_fields longtext NOT NULL, /* JSON структура полей */
			settings longtext DEFAULT NULL, /* Настройки формы (редиректы, цвета) */
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		// Таблица для записей (сабмитов)
		$table_entries = $wpdb->prefix . 'afb_entries';
		$sql_entries = "CREATE TABLE $table_entries (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			form_id bigint(20) NOT NULL,
			response longtext NOT NULL, /* JSON ответов пользователя */
			user_ip varchar(45) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_forms );
		dbDelta( $sql_entries );
	}
}