<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    private const FLASH_KEY = '_flash_060';
    private const CSRF_KEY = '_csrf_060';

    public static function start(array $app): void
    {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        session_name($app['session_name']);
        session_set_cookie_params([
            'lifetime' => (int)$app['session_lifetime'],
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        if (!isset($_SESSION[self::CSRF_KEY]) || !is_string($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }
        if (!isset($_SESSION[self::FLASH_KEY]) || !is_array($_SESSION[self::FLASH_KEY])) {
            $_SESSION[self::FLASH_KEY] = [];
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string ...$keys): void
    {
        foreach ($keys as $key) {
            unset($_SESSION[$key]);
        }
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION[self::FLASH_KEY][$type][] = $message;
    }

    public static function consumeFlash(): array
    {
        $flash = $_SESSION[self::FLASH_KEY] ?? [];
        $_SESSION[self::FLASH_KEY] = [];
        return is_array($flash) ? $flash : [];
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function csrfToken(): string
    {
        return (string)($_SESSION[self::CSRF_KEY] ?? '');
    }

    public static function verifyCsrf(string $token): bool
    {
        $expected = self::csrfToken();
        return $expected !== '' && $token !== '' && hash_equals($expected, $token);
    }
}
