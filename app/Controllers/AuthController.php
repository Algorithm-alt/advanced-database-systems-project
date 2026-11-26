<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApprovalService;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Core\View;
use Throwable;

final class AuthController
{
    public static function showLogin(): void
    {
        if (Auth::user() !== null) {
            Response::redirect(Request::url('dashboard'));
        }

        View::render('auth/login', [
            'title' => 'Sign in - Algorithm Hardware Plug',
            'oldLogin' => (string)Session::get('old_login', ''),
            'loggedOut' => $_GET['logged_out'] ?? null,
        ]);
        Session::forget('old_login');
    }

    public static function login(): void
    {
        Security::assertCsrf();

        $login = (string)Request::input('login', '');
        $password = (string)Request::input('password', '');

        if ($login === '' || $password === '') {
            Session::flash('error', 'Enter your username or email and password.');
            Session::put('old_login', $login);
            Response::redirect(Request::url('login'));
        }

        $result = Auth::attempt($login, $password);
        if (!$result['ok']) {
            Session::flash('error', (string)$result['message']);
            Session::put('old_login', $login);
            Response::redirect(Request::url('login'));
        }

        Session::forget('logout_token', 'logout_client_request_id', 'old_login');
        Session::flash('success', (string)$result['message']);
        Response::redirect(Request::url('dashboard'));
    }

    public static function dashboard(): void
    {
        Auth::requireLogin();
        View::render('dashboard/index', [
            'title' => 'Dashboard - Algorithm Hardware Plug',
            'user' => Auth::user(),
        ]);
    }

    public static function showLogout(): void
    {
        Auth::requireLogin();
        $token = self::ensureLogoutToken();

        View::render('auth/logout', [
            'title' => 'Sign out - Algorithm Hardware Plug',
            'user' => Auth::user(),
            'logoutToken' => $token,
        ]);
    }

    public static function requestLogout(): void
    {
        Auth::requireLogin();
        Security::assertCsrf();

        $token = self::ensureLogoutToken();
        if ($token !== '') {
            Session::flash('info', 'Confirm or cancel the sign-out request below.');
        }

        Response::redirect(Request::url('logout'));
    }

    public static function confirmLogout(): void
    {
        Auth::requireLogin();
        Security::assertCsrf();

        $token = (string)Request::input('approval_token', '');
        if ($token === '' || $token !== (string)Session::get('logout_token', '')) {
            Session::flash('error', 'The sign-out request is no longer valid.');
            Response::redirect(Request::url('logout'));
        }

        try {
            ApprovalService::confirm($token, 'NONE');
        } catch (Throwable $exception) {
            Session::flash('error', 'Sign-out approval failed: ' . $exception->getMessage());
            Response::redirect(Request::url('logout'));
        }

        Session::forget('logout_token', 'logout_client_request_id');
        Auth::logout();
        Response::redirect(Request::url('login') . '&logged_out=1');
    }

    public static function cancelLogout(): void
    {
        Auth::requireLogin();
        Security::assertCsrf();

        $token = (string)Request::input('approval_token', '');
        if ($token !== '' && $token === (string)Session::get('logout_token', '')) {
            try {
                ApprovalService::cancel($token);
                Session::flash('success', 'Sign-out cancelled. You are still signed in.');
            } catch (Throwable $exception) {
                Session::flash('error', 'Could not cancel the approval: ' . $exception->getMessage());
            }
        }

        Session::forget('logout_token', 'logout_client_request_id');
        Response::redirect(Request::url('dashboard'));
    }

    private static function ensureLogoutToken(): string
    {
        $token = (string)Session::get('logout_token', '');
        if ($token !== '') {
            return $token;
        }

        $user = Auth::user();
        $clientRequestId = (string)Session::get('logout_client_request_id', '');
        if ($clientRequestId === '') {
            $clientRequestId = bin2hex(random_bytes(16));
            Session::put('logout_client_request_id', $clientRequestId);
        }

        try {
            $token = ApprovalService::request(
                'LOGOUT',
                'User',
                (string)($user['id'] ?? ''),
                $clientRequestId,
                'Sign out requested by ' . (string)($user['username'] ?? '')
            );
            Session::put('logout_token', $token);
        } catch (Throwable $exception) {
            Session::flash('error', 'Could not create the sign-out approval: ' . $exception->getMessage());
        }

        return $token;
    }
}
