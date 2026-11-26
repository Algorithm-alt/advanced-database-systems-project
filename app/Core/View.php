<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = []): void
    {
        $data['title'] ??= 'Algorithm Hardware Plug';
        extract($data, EXTR_SKIP);

        ob_start();
        require APP_ROOT . '/app/Views/' . $template . '.php';
        $content = ob_get_clean();

        require APP_ROOT . '/app/Views/layout.php';
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
