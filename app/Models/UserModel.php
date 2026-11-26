<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class UserModel
{
    public static function findByLogin(string $login): ?array
    {
        return Database::call('dbo.sp_AuthenticateUser_060', ['Login' => substr($login, 0, 256)]);
    }

    public static function roles(int $userId): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetActiveUserRoles_060 @UserID = :UserID', ['UserID' => $userId]);
    }

    public static function recordAttempt(int $userId, bool $success, bool $lock = false): void
    {
        Database::call('dbo.sp_RecordLoginAttempt_060', [
            'UserID' => $userId,
            'Success' => $success ? 1 : 0,
            'Lock' => $lock ? 1 : 0,
        ]);
    }
}
