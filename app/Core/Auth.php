<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

final class Auth
{
    public static function attempt(string $login, string $password): array
    {
        $login = trim($login);
        $user = UserModel::findByLogin($login);

        if ($user === []) {
            Audit::auth(null, $login, 'LOGIN_FAILURE', 'USER_NOT_FOUND');
            return ['ok' => false, 'message' => 'Invalid username or password.'];
        }

        $userId = (int)$user['UserID'];
        $username = (string)$user['Username'];

        if ((bool)$user['IsLocked']) {
            Audit::auth($userId, $username, 'LOGIN_FAILURE', 'LOCKED');
            return ['ok' => false, 'message' => 'This account is locked. Contact an administrator.'];
        }

        if (!password_verify($password, (string)$user['PasswordHash'])) {
            $attempts = (int)$user['FailedLoginAttempts'] + 1;
            $max = (int)Config::get('app.login_max_attempts', 5);
            $lock = $attempts >= $max;
            UserModel::recordAttempt($userId, false, $lock);
            Audit::auth($userId, $username, 'LOGIN_FAILURE', $lock ? 'LOCKED' : 'FAILURE');
            return [
                'ok' => false,
                'message' => $lock
                    ? 'This account is now locked after too many failed attempts.'
                    : 'Invalid username or password.',
            ];
        }

        $roleRows = UserModel::roles($userId);
        if ($roleRows === []) {
            Audit::auth($userId, $username, 'LOGIN_FAILURE', 'NO_ACTIVE_ROLE');
            return ['ok' => false, 'message' => 'This account has no active application role.'];
        }

        $roles = array_map(static fn(array $row): string => (string)$row['RoleName'], $roleRows);
        UserModel::recordAttempt($userId, true);

        Session::regenerate();
        Session::put('user', [
            'id' => $userId,
            'username' => $username,
            'email' => (string)$user['Email'],
            'full_name' => (string)$user['FullName'],
            'role' => $roles[0],
            'roles' => $roles,
            'logged_in_at' => date('c'),
        ]);

        self::applyContext();
        Audit::auth($userId, $username, 'LOGIN_SUCCESS', 'SUCCESS');

        return ['ok' => true, 'message' => 'Welcome back, ' . (string)$user['FullName'] . '.'];
    }

    public static function user(): ?array
    {
        $user = Session::get('user');
        return is_array($user) ? $user : null;
    }

    public static function requireLogin(): void
    {
        if (self::user() === null) {
            Session::flash('error', 'Please sign in to continue.');
            Response::redirect(Request::url('login'));
        }
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }
        foreach ($roles as $role) {
            if (in_array($role, $user['roles'] ?? [], true)) {
                return true;
            }
        }
        return false;
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!self::hasRole(...$roles)) {
            Session::flash('error', 'Your role does not have access to that module.');
            Response::redirect(Request::url('dashboard'));
        }
    }

    public static function applyContext(): void
    {
        $user = self::user();
        if ($user === null) {
            return;
        }
        Database::call('dbo.sp_SetAppContext_060', [
            'AppUserID' => (int)$user['id'],
            'AppRole' => (string)$user['role'],
            'AppIP' => Request::ip(),
            'AppAgent' => Request::userAgent(),
        ]);
    }

    public static function verifyCurrentUserPassword(string $password): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }
        $record = UserModel::findByLogin((string)$user['username']);
        if ($record === []) {
            return false;
        }
        return password_verify($password, (string)$record['PasswordHash']);
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
