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

$plugin_config = wp_ai_error_report_load_config();

$error_log_path = wp_ai_error_report_upload_log_path();

$report_sender_service = new WPAIErrorReport_ReportSender(
	$error_log_path,
	dirname($error_log_path) . '/last_report_attempted_at.touch',
	$plugin_config
);

$fatal_error_handler = new WPAIErrorReport_ErrorHandler(
    $error_log_path, $report_sender_service
);

$fatal_error_handler->register();
