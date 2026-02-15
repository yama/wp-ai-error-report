<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPAIErrorReport_ErrorHandler {
	private $log_file_path;
	private $report_sender;
	private $target_error_types = array(
		E_ERROR,
		E_PARSE,
		E_CORE_ERROR,
	);

	public function __construct($log_file_path, $report_sender) {
		$this->log_file_path = $log_file_path;
		$this->report_sender = $report_sender;
	}

	public function register() {
		add_action('init', array($this, 'maybe_send_report'));
		register_shutdown_function(array($this, 'capture_fatal_error'));
	}

	public function maybe_send_report() {
		$this->report_sender->send_report_if_due();
	}

	public function capture_fatal_error() {
		$error = error_get_last();
		if (!$this->is_target_error($error)) {
			return;
		}

		$entry = array(
			'timestamp'      => gmdate('c'),
			'type'           => $this->map_error_type($error['type']),
			'message_masked' => WPAIErrorReport_Masking::mask_text((string) $error['message']),
			'file_masked'    => WPAIErrorReport_Masking::mask_text((string) $error['file']),
			'line'           => isset($error['line']) ? (int) $error['line'] : 0,
			'site_url'       => site_url(),
		);

		$this->append_log_entry($entry);
	}

	private function append_log_entry($entry) {
		$dir = dirname($this->log_file_path);
		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		$line = wp_json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		if ($line === false) {
			return;
		}

		file_put_contents($this->log_file_path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
	}

	private function is_target_error($error) {
		if (!is_array($error) || !isset($error['type'])) {
			return false;
		}

		return in_array((int) $error['type'], $this->target_error_types, true);
	}

	private function map_error_type($type) {
		$map = array(
			E_ERROR      => 'E_ERROR',
			E_PARSE      => 'E_PARSE',
			E_CORE_ERROR => 'E_CORE_ERROR',
		);

		return isset($map[$type]) ? $map[$type] : (string) $type;
	}
}
