<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPAIErrorReport_ReportSender {
	private $log_file_path;
	private $schedule_file_path;
	private $api_key;
	private $recipients;
	private $model;
	private $large_log_threshold_bytes;
	private $max_lines;
	private $send_interval_seconds;

	public function __construct($log_file_path, $schedule_file_path, $config) {
		$this->log_file_path              = $log_file_path;
		$this->schedule_file_path         = $schedule_file_path;
		$this->api_key                    = isset($config['api_key']) ? (string) $config['api_key'] : '';
		$this->recipients                 = $this->parse_recipients(isset($config['notification_emails']) ? $config['notification_emails'] : '');
		$this->model                      = isset($config['model']) && $config['model'] ? (string) $config['model'] : 'gpt-4.1-mini';
		$this->large_log_threshold_bytes  = $this->parse_size_to_bytes(isset($config['large_log_threshold']) ? $config['large_log_threshold'] : '1MB');
		$this->max_lines                  = isset($config['max_lines']) ? max(1, (int) $config['max_lines']) : 100;
		$this->send_interval_seconds      = $this->parse_interval_seconds(isset($config['send_interval_minutes']) ? $config['send_interval_minutes'] : 60);
	}

	public function should_send_report() {
		if (!file_exists($this->log_file_path)) {
			return false;
		}

		if (!file_exists($this->schedule_file_path)) {
			return true;
		}

		$mtime = filemtime($this->schedule_file_path);
		if (!$mtime) {
			return true;
		}

		return (time() - $mtime) >= $this->send_interval_seconds;
	}

	public function send_report_if_due() {
		if (!$this->should_send_report()) {
			return false;
		}

		$this->touch_schedule_file();
		return $this->send_report();
	}

	public function send_report() {
		if (empty($this->recipients)) {
			error_log('WP AI Error Report: notification_emails are empty. Skip sending.');
			return false;
		}

		if ($this->api_key === '') {
			error_log('WP AI Error Report: API key is empty. Skip sending.');
			return false;
		}

		$report_data = $this->read_log_excerpt();
		if ($report_data === false) {
			error_log('WP AI Error Report: failed to read log file.');
			return false;
		}

		$summary = $this->request_summary($report_data);
		if ($summary === false) {
			error_log('WP AI Error Report: OpenAI request failed.');
			return false;
		}

		if (!$this->send_email($summary)) {
			error_log('WP AI Error Report: wp_mail failed.');
			return false;
		}

		if (!@unlink($this->log_file_path)) {
			error_log('WP AI Error Report: report sent, but failed to delete error.log.');
		}
		if (file_exists($this->schedule_file_path) && !@unlink($this->schedule_file_path)) {
			error_log('WP AI Error Report: report sent, but failed to delete schedule marker.');
		}

		return true;
	}

	private function touch_schedule_file() {
		$dir = dirname($this->schedule_file_path);
		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		return @touch($this->schedule_file_path);
	}

	private function read_log_excerpt() {
		if (!is_readable($this->log_file_path)) {
			return false;
		}

		$file_size = filesize($this->log_file_path);
		if ($file_size === false) {
			return false;
		}

		$lines = $this->tail_lines($this->log_file_path, $this->max_lines);
		if ($lines === false) {
			return false;
		}

		return array(
			'lines'      => $lines,
			'is_large'   => $file_size >= $this->large_log_threshold_bytes,
			'file_size'  => $file_size,
			'max_lines'  => $this->max_lines,
		);
	}

	private function request_summary($report_data) {
		$system_prompt = 'あなたはWordPress運用アシスタントです。非エンジニアにも理解できる平易な日本語で要約してください。';
		$user_prompt   = $this->build_user_prompt($report_data);

		$payload = array(
			'model'             => $this->model,
			'max_output_tokens' => 800,
			'input'             => array(
				array(
					'role'    => 'system',
					'content' => array(
						array(
							'type' => 'input_text',
							'text' => $system_prompt,
						),
					),
				),
				array(
					'role'    => 'user',
					'content' => array(
						array(
							'type' => 'input_text',
							'text' => $user_prompt,
						),
					),
				),
			),
		);

		$response = wp_remote_post(
			'https://api.openai.com/v1/responses',
			array(
				'timeout' => 25,
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode($payload),
			)
		);

		if (is_wp_error($response)) {
			error_log('WP AI Error Report: ' . $response->get_error_message());
			return false;
		}

		$status = (int) wp_remote_retrieve_response_code($response);
		$body   = (string) wp_remote_retrieve_body($response);
		if ($status < 200 || $status >= 300) {
			error_log('WP AI Error Report: OpenAI HTTP error ' . $status . ' body=' . $body);
			return false;
		}

		$data = json_decode($body, true);
		if (!is_array($data)) {
			return false;
		}

		$text = $this->extract_output_text($data);
		if ($text === '') {
			return false;
		}

		return $text;
	}

	private function send_email($summary) {
		$subject = sprintf('[WordPress] エラーレポート - %s', get_bloginfo('name'));
		return wp_mail($this->recipients, $subject, $summary);
	}

	private function build_user_prompt($report_data) {
		$meta_lines = array(
			'以下はWordPressのFatal系エラーログです。',
			'非エンジニア向けに、何が起きているか・想定原因・最初に確認すべきことを簡潔に日本語で説明してください。',
			'箇条書きで出力し、機密情報は推測で補わないでください。',
			'対象ログ行数: ' . $report_data['max_lines'],
		);

		if ($report_data['is_large']) {
			$meta_lines[] = sprintf('ログファイルは大きなサイズです（約 %.2f MB）。その旨を要約に含めてください。', $report_data['file_size'] / 1024 / 1024);
		}

		$meta_lines[] = "---- LOG START ----\n" . implode("\n", $report_data['lines']) . "\n---- LOG END ----";

		return implode("\n", $meta_lines);
	}

	private function extract_output_text($data) {
		if (isset($data['output_text']) && is_string($data['output_text'])) {
			return trim($data['output_text']);
		}

		if (!isset($data['output']) || !is_array($data['output'])) {
			return '';
		}

		$chunks = array();
		foreach ($data['output'] as $output_item) {
			if (!isset($output_item['content']) || !is_array($output_item['content'])) {
				continue;
			}
			foreach ($output_item['content'] as $content_item) {
				if (isset($content_item['text']) && is_string($content_item['text'])) {
					$chunks[] = $content_item['text'];
				}
			}
		}

		return trim(implode("\n", $chunks));
	}

	private function tail_lines($file_path, $max_lines) {
		$handle = @fopen($file_path, 'r');
		if (!$handle) {
			return false;
		}

		$buffer = array();
		while (($line = fgets($handle)) !== false) {
			$buffer[] = rtrim($line, "\r\n");
			if (count($buffer) > $max_lines) {
				array_shift($buffer);
			}
		}
		fclose($handle);

		return $buffer;
	}

	private function parse_recipients($raw_recipients) {
		$raw_list = explode(',', (string) $raw_recipients);
		$result   = array();

		foreach ($raw_list as $email) {
			$clean = sanitize_email(trim($email));
			if ($clean && is_email($clean)) {
				$result[] = $clean;
			}
		}

		return array_values(array_unique($result));
	}

	private function parse_size_to_bytes($value) {
		$raw = strtoupper(trim((string) $value));
		if ($raw === '') {
			return 1024 * 1024;
		}

		if (!preg_match('/^(\d+)\s*(B|KB|MB)$/', $raw, $matches)) {
			return 1024 * 1024;
		}

		$number = (int) $matches[1];
		$unit   = $matches[2];

		if ($unit === 'MB') {
			return $number * 1024 * 1024;
		}
		if ($unit === 'KB') {
			return $number * 1024;
		}

		return $number;
	}

	private function parse_interval_seconds($minutes) {
		$value = (int) $minutes;
		if ($value < 1) {
			$value = 1;
		}

		return $value * MINUTE_IN_SECONDS;
	}
}
