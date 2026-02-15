#!/usr/bin/env php
<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	define('ABSPATH', dirname(__DIR__) . '/');
}

$configPath = dirname(__DIR__) . '/config.php';
if (!file_exists($configPath)) {
	fwrite(STDERR, "config.php が見つかりません: {$configPath}\n");
	exit(1);
}

$config = require $configPath;
if (!is_array($config)) {
	fwrite(STDERR, "config.php の戻り値が配列ではありません。\n");
	exit(1);
}

$apiKey = isset($config['api_key']) ? (string) $config['api_key'] : '';
$model  = isset($config['model']) && $config['model'] !== '' ? (string) $config['model'] : 'gpt-4.1-mini';

if ($apiKey === '') {
	fwrite(STDERR, "api_key が空です。config.php を確認してください。\n");
	exit(1);
}

$prompt = 'Reply with exactly: OK';
if (isset($argv[1]) && trim((string) $argv[1]) !== '') {
	$prompt = trim((string) $argv[1]);
}

$payload = array(
	'model'             => $model,
	'max_output_tokens' => 32,
	'input'             => array(
		array(
			'role'    => 'system',
			'content' => array(
				array(
					'type' => 'input_text',
					'text' => 'You are a connectivity test assistant.',
				),
			),
		),
		array(
			'role'    => 'user',
			'content' => array(
				array(
					'type' => 'input_text',
					'text' => $prompt,
				),
			),
		),
	),
);

$url     = 'https://api.openai.com/v1/responses';
$json    = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$headers = array(
	'Authorization: Bearer ' . $apiKey,
	'Content-Type: application/json',
);

$statusCode = 0;
$response   = '';
$error      = '';

if (function_exists('curl_init')) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	$response = (string) curl_exec($ch);
	if ($response === '') {
		$err = curl_error($ch);
		if ($err !== '') {
			$error = $err;
		}
	}
	$statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
} else {
	$context = stream_context_create(
		array(
			'http' => array(
				'method'  => 'POST',
				'header'  => implode("\r\n", $headers),
				'content' => $json,
				'timeout' => 30,
			),
		)
	);
	$response = @file_get_contents($url, false, $context);
	if ($response === false) {
		$error = 'file_get_contents に失敗しました。';
		$response = '';
	}
	if (isset($http_response_header) && is_array($http_response_header)) {
		foreach ($http_response_header as $line) {
			if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $line, $m)) {
				$statusCode = (int) $m[1];
				break;
			}
		}
	}
}

echo "Model: {$model}\n";
echo "HTTP Status: {$statusCode}\n";

if ($error !== '') {
	echo "Transport Error: {$error}\n";
}

if ($response === '') {
	echo "Response Body: (empty)\n";
	exit(2);
}

$data = json_decode($response, true);
if (!is_array($data)) {
	echo "Response JSON parse failed.\n";
	echo "Raw Body (first 500 chars): " . substr($response, 0, 500) . "\n";
	exit($statusCode >= 200 && $statusCode < 300 ? 0 : 2);
}

$text = '';
if (isset($data['output_text']) && is_string($data['output_text'])) {
	$text = trim($data['output_text']);
} elseif (isset($data['output']) && is_array($data['output'])) {
	$chunks = array();
	foreach ($data['output'] as $item) {
		if (!isset($item['content']) || !is_array($item['content'])) {
			continue;
		}
		foreach ($item['content'] as $content) {
			if (isset($content['text']) && is_string($content['text'])) {
				$chunks[] = $content['text'];
			}
		}
	}
	$text = trim(implode("\n", $chunks));
}

if ($text !== '') {
	echo "Output Text: {$text}\n";
} else {
	echo "Output Text: (empty)\n";
}

if ($statusCode < 200 || $statusCode >= 300) {
	echo "Raw Body (first 800 chars): " . substr($response, 0, 800) . "\n";
	exit(2);
}

exit(0);

