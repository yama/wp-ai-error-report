<?php
/**
 * Plugin Name: WP AI Error Report
 * Description: Fatal error logs are summarized by AI and sent by email in batched intervals.
 * Version: 0.1.0
 * Author: PoC Team
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/includes/class-masking.php';
require_once __DIR__ . '/includes/class-report-sender.php';
require_once __DIR__ . '/includes/class-error-handler.php';

function wp_ai_error_report_load_config() {
	$defaults = array(
		'api_key'             => '',
		'notification_emails' => '',
		'model'               => 'gpt-4.1-mini',
		'large_log_threshold' => '1MB',
		'max_lines'           => 100,
		'send_interval_minutes' => 60,
	);

	$config_file = __DIR__ . '/config.php';
	if (!file_exists($config_file)) {
		return $defaults;
	}

	$config = require $config_file;
	if (!is_array($config)) {
		return $defaults;
	}

	return array_merge($defaults, $config);
}

function wp_ai_error_report_upload_log_path() {
	$upload_dir = wp_upload_dir();
	$base_dir   = rtrim($upload_dir['basedir'], '/\\') . '/wp-ai-error-report/logs';

	if (!is_dir($base_dir)) {
		wp_mkdir_p($base_dir);
	}

	$index_file = $base_dir . '/index.php';
	if (!file_exists($index_file)) {
		file_put_contents($index_file, "<?php\n// Silence is golden.\n");
	}

	$htaccess_file = $base_dir . '/.htaccess';
	if (!file_exists($htaccess_file)) {
		file_put_contents($htaccess_file, "Deny from all\n");
	}

	return $base_dir . '/error.log';
}

$report_config = wp_ai_error_report_load_config();

$log_file_path      = wp_ai_error_report_upload_log_path();
$schedule_file_path = dirname($log_file_path) . '/last_report_attempted_at.touch';
$report_sender      = new WPAIErrorReport_ReportSender($log_file_path, $schedule_file_path, $report_config);
$handler            = new WPAIErrorReport_ErrorHandler($log_file_path, $report_sender);
$handler->register();
