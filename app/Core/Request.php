<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_POST)) {
            return is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key];
        }
        if (array_key_exists($key, $_GET)) {
            return is_string($_GET[$key]) ? trim($_GET[$key]) : $_GET[$key];
        }
        return $default;
    }

    public static function route(): string
    {
        $route = self::input('route', 'home');
        $route = is_string($route) ? strtolower(trim($route, "/ \t\n\r\0\x0B")) : 'home';
        if (!preg_match('/^[a-z0-9\/-]*$/', $route)) {
            return '404';
        }
        return $route !== '' ? $route : 'home';
    }

    public static function ip(): string
    {
        $candidate = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (is_string($candidate) && str_contains($candidate, ',')) {
            $candidate = trim(explode(',', $candidate)[0]);
        }
        return is_string($candidate) ? substr($candidate, 0, 45) : '127.0.0.1';
    }

    public static function userAgent(): string
    {
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return is_string($agent) ? substr($agent, 0, 300) : '';
    }

    public static function url(string $route = ''): string
    {
        $base = rtrim((string)Config::get('app.base_url', ''), '/');
        if ($route === '') {
            return $base . '/index.php';
        }
        $parts = explode('&', $route, 2);
        $path = (string)array_shift($parts);
        $query = 'route=' . rawurlencode($path);
        if ($parts !== []) {
            $query .= '&' . $parts[0];
        }
        return $base . '/index.php?' . $query;
    }

    public static function asset(string $path): string
    {
        $base = rtrim((string)Config::get('app.base_url', ''), '/');
        return $base . '/assets/' . ltrim($path, '/');
    }
}
