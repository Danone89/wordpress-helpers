<?php


use WP_Queue\QueueManager;

if (class_exists('WP_Queue') && defined('DOING_CRON') && DOING_CRON) {
	wp_queue()->cron();
}
if (defined('ENV') && ENV == 'dev') {
	add_filter('wp_queue_default_connection', function () {
		return 'sync';
	});
}
//load_module('WP_Queu');
if (!function_exists('wp_queue')) {
	/**
	 * Return Queue instance.
	 *
	 * @param string $connection
	 *
	 * @return Queue
	 */
	function wp_queue($connection = '')
	{
		if (empty($connection)) {
			$connection = apply_filters('wp_queue_default_connection', 'database');
		}

		return QueueManager::resolve($connection);
	}
}

if (!function_exists('wp_queue_install_tables')) {
	/**
	 * Install database tables
	 * @return void
	 */
	function wp_queue_install_tables()
	{
		global $wpdb;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$wpdb->hide_errors();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}queue_jobs (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				job longtext NOT NULL,
				attempts tinyint(3) NOT NULL DEFAULT 0,
				reserved_at datetime DEFAULT NULL,
				available_at datetime NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id)
				) $charset_collate;";

		dbDelta($sql);

		$sql = "CREATE TABLE {$wpdb->prefix}queue_failures (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				job longtext NOT NULL,
				error text DEFAULT NULL,
				failed_at datetime NOT NULL,
				PRIMARY KEY  (id)
				) $charset_collate;";

		dbDelta($sql);
	}
}
