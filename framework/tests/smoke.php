<?php

declare(strict_types=1);

// Minimal dependency-free smoke tests; production uses Composer's autoloader.
$frameworkRoot = dirname(__DIR__);
$appRoot = dirname(__DIR__, 2) . '/dasintranet-1.0';
require_once $appRoot . '/set.php';
ob_start();

spl_autoload_register(static function (string $class) use ($frameworkRoot, $appRoot): void {
    foreach ([
        'Dasintranet\\Framework\\' => $frameworkRoot . '/src/',
        'App\\Controllers\\' => $appRoot . '/app/controllers/',
    ] as $prefix => $directory) {
        if (str_starts_with($class, $prefix)) {
            $path = $directory . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($path)) {
                require $path;
            }
        }
    }
});

function verify(bool $condition, string $description): void
{
    if (!$condition) {
        throw new RuntimeException('FAILED: ' . $description);
    }
    echo 'PASS: ' . $description . "\n";
}

use Dasintranet\Framework\App;
use Dasintranet\Framework\Api;
use Dasintranet\Framework\Db;
use Dasintranet\Framework\Loader;
use Dasintranet\Framework\View;

App::setViews($appRoot . '/app/views');

verify(class_exists(App::class), 'PSR-4 class paths: App');
verify(class_exists(Loader::class), 'PSR-4 class paths: Loader');

try {
    Loader::boot($appRoot . '/does-not-exist');
    throw new RuntimeException('Loader accepted a missing application directory');
} catch (InvalidArgumentException) {
    verify(true, 'Loader rejects missing application directory');
}

Loader::boot($appRoot . '/app');
verify(App::getViews() === realpath($appRoot . '/app') . '/views', 'Loader registers views');
verify(class_exists(\App\Controllers\HomeController::class), 'PSR-4 class paths: HomeController');

$view = new View();
verify(trim($view->render('hello')) === 'Hello World!', 'View::render');
$extended = $view->extend('hello');
verify(str_contains($extended, 'Hello World!') && str_contains($extended, '</html>'), 'View::extend');

try {
    $view->render('../set');
    throw new RuntimeException('View traversal was not blocked');
} catch (InvalidArgumentException) {
    verify(true, 'View path traversal blocked');
}

App::get('/hello', [\App\Controllers\HomeController::class, 'hello']);
App::get('/students/{id}', static fn (string $id): array => ['id' => $id]);
App::get('/literal.a', static fn (): string => 'literal');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/hello?x=1';
ob_start();
App::run();
verify(trim((string) ob_get_clean()) === 'Hello World!', 'GET route ignores query string');

$_SERVER['REQUEST_URI'] = '/students/123?expand=1';
ob_start();
App::run();
verify((string) ob_get_clean() === '{"id":"123"}', 'Parameterized route returns JSON');

$_SERVER['REQUEST_URI'] = '/literalXa';
http_response_code(200);
ob_start();
App::run();
ob_end_clean();
verify(http_response_code() === 404, 'Route metacharacters are escaped');

$_SERVER['REQUEST_URI'] = '/missing?<script>alert(1)</script>';
http_response_code(200);
ob_start();
App::run();
$output = (string) ob_get_clean();
verify(http_response_code() === 404 && !str_contains($output, '<script>'), '404 does not reflect URI HTML');

try {
    (new Api())->exec('file:///etc/passwd');
    throw new RuntimeException('Untrusted scheme was not blocked');
} catch (RuntimeException) {
    verify(true, 'Invalid HTTP schemes rejected');
}

try {
    (new Api())->exec('https://example.com', 'GET', null, "bad\r\nInjected: header");
    throw new RuntimeException('Header injection was not blocked');
} catch (InvalidArgumentException) {
    verify(true, 'Bearer header CRLF injection blocked');
}

$identifier = new ReflectionMethod(Db::class, 'identifier');
verify($identifier->invoke(null, 'academic.students') === '`academic`.`students`', 'SQL identifiers quoted');
try {
    $identifier->invoke(null, 'students;DROP TABLE students');
    throw new RuntimeException('SQL identifier injection was not blocked');
} catch (InvalidArgumentException) {
    verify(true, 'SQL identifier injection blocked');
}

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/hello';
ob_start();
Loader::run($appRoot . '/app');
verify(trim((string) ob_get_clean()) === 'Hello World!', 'Loader::run bootstraps routes and dispatches');

echo "All smoke tests passed.\n";
ob_end_flush();
