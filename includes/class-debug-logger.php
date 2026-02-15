<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPAIErrorReport_DebugLogger {
	private $enabled;
	private $log_file_path;

	public function __construct($enabled, $log_file_path) {
		$this->enabled       = (bool) $enabled;
		$this->log_file_path = (string) $log_file_path;
	}

	public function log($event, $context = array()) {
		if (!$this->enabled || $this->log_file_path === '') {
			return;
		}

		$dir = dirname($this->log_file_path);
		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		$payload = array(
			'timestamp' => gmdate('c'),
			'event'     => (string) $event,
			'context'   => is_array($context) ? $context : array(),
		);
		$line = wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		if ($line === false) {
			return;
		}

		file_put_contents($this->log_file_path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
	}
}

