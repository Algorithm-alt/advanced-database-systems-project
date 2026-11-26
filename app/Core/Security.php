<?php

declare(strict_types=1);

namespace App\Core;

final class Security
{
    public static function assertCsrf(): void
    {
        $token = (string)Request::input('csrf_token', '');
        if (!Request::isPost() || !Session::verifyCsrf($token)) {
            Response::status(419);
            echo '<!doctype html><html><head><meta charset="utf-8"><title>Security check failed</title></head><body>';
            echo '<h1>Security check failed</h1><p>Reload the page and try again.</p></body></html>';
            exit;
        }
    }
}
