<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;

final class HealthController
{
    public static function index(): void
    {
        $row = Database::fetchOne('SELECT DB_NAME() AS DatabaseName, @@VERSION AS Version');
        Response::json([
            'status' => 'ok',
            'database' => $row['DatabaseName'] ?? null,
            'version' => $row['Version'] ?? null,
        ]);
    }
}
