<?php

declare(strict_types=1);

// Develop Mode
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);

// Do not expose PHP stack traces or credentials in HTTP responses.
//ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/set.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Cross-origin access is disabled by default. Configure a trusted origin explicitly.
$allowedOrigin = getenv('CORS_ALLOWED_ORIGIN') ?: '';
if ($allowedOrigin !== '' && ($_SERVER['HTTP_ORIGIN'] ?? '') === $allowedOrigin) {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    \Dasintranet\Framework\Loader::run(dirname(__DIR__) . '/app');
} catch (Throwable $exception) {
    error_log((string) $exception);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Internal Server Error';
}
