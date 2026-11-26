<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

$appConfig = require APP_ROOT . '/config/app.php';
$databaseConfig = require APP_ROOT . '/config/database.php';

require_once APP_ROOT . '/app/Core/Config.php';

\App\Core\Config::load(['app' => $appConfig, 'db' => $databaseConfig]);
date_default_timezone_set((string)$appConfig['timezone']);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = APP_ROOT . '/app/' . $relative . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $exception): void {
    $entry = sprintf(
        "[%s] %s in %s:%d\n%s\n",
        date('c'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    error_log($entry, 3, APP_ROOT . '/storage/logs/error.log');

    if (ob_get_length() !== false) {
        ob_end_clean();
    }
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');

    if ((bool)\App\Core\Config::get('app.debug', false)) {
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Application error</title></head><body>';
        echo '<h1>Application error</h1><p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<pre>' . htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre></body></html>';
        return;
    }

    echo '<!doctype html><html><head><meta charset="utf-8"><title>Application error</title></head><body>';
    echo '<h1>Something went wrong.</h1><p>Please try again later.</p></body></html>';
});

\App\Core\Session::start($appConfig);
\App\Core\Database::connect($databaseConfig);
\App\Core\Auth::applyContext();
