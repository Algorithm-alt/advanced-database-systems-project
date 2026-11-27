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

    public static function registerCustomer(
        string $username,
        string $email,
        string $passwordHash,
        string $fullName,
        ?string $phone
    ): int {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $existing = Database::fetchOne(
                'SELECT TOP 1 UserID FROM dbo.Users_060
                 WHERE Username = :Username OR Email = :Email',
                ['Username' => $username, 'Email' => $email]
            );
            if ($existing !== null) {
                throw new \RuntimeException('This username or email is already registered.');
            }

            $customerExisting = Database::fetchOne(
                'SELECT TOP 1 CustomerID FROM dbo.Customers_060 WHERE Email = :Email',
                ['Email' => $email]
            );
            if ($customerExisting !== null) {
                throw new \RuntimeException('A customer account already uses this email.');
            }

            $row = Database::fetchOne(
                'INSERT INTO dbo.Users_060
                    (Username, Email, PasswordHash, FullName, Phone, IsActive, IsLocked, FailedLoginAttempts,
                     PasswordChangedAt, CreatedAt)
                 OUTPUT INSERTED.UserID
                 VALUES
                    (:Username, :Email, :PasswordHash, :FullName, :Phone, 1, 0, 0,
                     SYSDATETIME(), SYSDATETIME())',
                [
                    'Username' => $username,
                    'Email' => $email,
                    'PasswordHash' => $passwordHash,
                    'FullName' => $fullName,
                    'Phone' => $phone,
                ]
            );
            $userId = (int)($row['UserID'] ?? 0);
            if ($userId <= 0) {
                throw new \RuntimeException('Could not create the account.');
            }

            $role = Database::fetchOne(
                'SELECT RoleID FROM dbo.Roles_060 WHERE RoleName = N\'Customer\' AND IsActive = 1'
            );
            $roleId = (int)($role['RoleID'] ?? 0);
            if ($roleId <= 0) {
                throw new \RuntimeException('The customer role is unavailable.');
            }

            Database::execute(
                'INSERT INTO dbo.UserRoles_060 (UserID, RoleID, AssignedByUserID, IsActive)
                 VALUES (:UserID, :RoleID, NULL, 1)',
                ['UserID' => $userId, 'RoleID' => $roleId]
            );

            $customerCode = 'CUST-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
            $customerRow = Database::fetchOne(
                'INSERT INTO dbo.Customers_060
                    (UserID, CustomerCode, FullName, Email, Phone, Address, LoyaltyPoints, IsActive, CreatedAt)
                 OUTPUT INSERTED.CustomerID
                 VALUES
                    (:UserID, :CustomerCode, :FullName, :Email, :Phone, NULL, 0, 1, SYSDATETIME())',
                [
                    'UserID' => $userId,
                    'CustomerCode' => $customerCode,
                    'FullName' => $fullName,
                    'Email' => $email,
                    'Phone' => $phone,
                ]
            );
            if (($customerRow['CustomerID'] ?? 0) <= 0) {
                throw new \RuntimeException('Could not create the customer profile.');
            }

            $pdo->commit();
            return $userId;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }
}
