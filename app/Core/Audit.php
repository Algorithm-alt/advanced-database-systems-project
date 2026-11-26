<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Audit
{
    public static function auth(?int $userId, string $username, string $action, string $outcome): void
    {
        try {
            Database::call('dbo.sp_RecordAuthAudit_060', [
                'UserID' => $userId,
                'Username' => substr($username, 0, 50),
                'Action' => $action,
                'Outcome' => $outcome,
                'IPAddress' => Request::ip(),
                'UserAgent' => Request::userAgent(),
            ]);
        } catch (Throwable $exception) {
            error_log(
                '[' . date('c') . '] Authentication audit failed: ' . $exception->getMessage() . "\n",
                3,
                APP_ROOT . '/storage/logs/error.log'
            );
        }
    }
}
