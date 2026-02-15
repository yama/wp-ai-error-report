<?php
/**
 * Plugin Name: WP AI Error Report
 * Description: Fatal error logs are summarized by AI and sent by email in batched intervals.
 * Version: 0.1.0
 * Author: Yamamoto
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/includes/class-masking.php';
require_once __DIR__ . '/includes/class-report-sender.php';
require_once __DIR__ . '/includes/class-error-handler.php';
require_once __DIR__ . '/includes/plugin-functions.php';

$report_config = wp_ai_error_report_load_config();

$log_file_path      = wp_ai_error_report_upload_log_path();
$schedule_file_path = dirname($log_file_path) . '/last_report_attempted_at.touch';
$report_sender      = new WPAIErrorReport_ReportSender($log_file_path, $schedule_file_path, $report_config);
$handler            = new WPAIErrorReport_ErrorHandler($log_file_path, $report_sender);
$handler->register();
