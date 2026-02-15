<?php

if (!defined('ABSPATH')) {
	exit;
}

class WPAIErrorReport_Masking {
	public static function mask_text($text) {
		if (!is_string($text) || $text == '') {
			return '';
		}

		$masked = $text;

		if (defined('ABSPATH') && ABSPATH) {
			$masked = str_replace(ABSPATH, '/path/to/', $masked);
		}

		$masked = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '***@***', $masked);
		$masked = preg_replace('/\b[A-Za-z0-9]{32,}\b/', '***KEY***', $masked);
		$masked = preg_replace('#/(?:[A-Za-z0-9._-]+/){2,}[A-Za-z0-9._-]*#', '/path/to/', $masked);

		return $masked;
	}
}

