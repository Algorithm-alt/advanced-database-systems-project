<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApprovalFlow;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Core\View;
use App\Models\BusinessData;
use Throwable;

final class AdminController
{
    public static function users(): void
    {
        Auth::requireRole('Administrator');
        $pending = ApprovalFlow::pending();
        $approval = null;
        if ($pending !== null && ($pending['action_key'] ?? '') === 'CHANGE_USER_ROLE') {
            $approval = self::modal($pending);
        }

        View::render('admin/users', [
            'title' => 'User administration - Algorithm Hardware Plug',
            'users' => BusinessData::allUsers(),
            'roles' => BusinessData::allRoles(),
            'approval' => $approval,
        ]);
    }

    public static function requestRole(): void
    {
        Auth::requireRole('Administrator');
        Security::assertCsrf();

        $userId = (int)Request::input('user_id', 0);
        $roleId = (int)Request::input('role_id', 0);
        if ($userId <= 0 || $roleId <= 0) {
            Session::flash('error', 'Select a user and a role.');
            Response::redirect(Request::url('admin/users'));
        }

        try {
            ApprovalFlow::begin(
                'CHANGE_USER_ROLE',
                'User',
                (string)$userId,
                'admin/users',
                [
                    'details' => 'User ' . $userId . ' role ' . $roleId,
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'approvers' => BusinessData::administrators(),
                    'verification_method' => 'PASSWORD_AND_PHRASE',
                    'show_password' => true,
                    'show_phrase' => true,
                    'require_phrase' => true,
                    'require_verifier' => true,
                    'phrase_hint' => self::approvalPhraseHint(),
                ]
            );
            Session::flash('info', 'Role change approval created. Confirm with a second administrator.');
        } catch (Throwable $exception) {
            Session::flash('error', 'Could not create the approval: ' . $exception->getMessage());
        }
        Response::redirect(Request::url('admin/users'));
    }

    public static function confirmRole(): void
    {
        Auth::requireRole('Administrator');
        try {
            $result = ApprovalFlow::confirm();
            $ui = is_array($result['pending']['ui'] ?? null) ? $result['pending']['ui'] : [];
            $verifierId = (int)Request::input('approval_verifier', 0);
            $currentUserId = (int)(Auth::user()['id'] ?? 0);
            if ($verifierId === $currentUserId) {
                throw new \RuntimeException('A second administrator must approve this change.');
            }

            BusinessData::assignRole(
                (int)($ui['user_id'] ?? 0),
                (int)($ui['role_id'] ?? 0),
                $result['token'],
                $currentUserId
            );

            ApprovalFlow::clear();
            Session::flash('success', 'User role updated successfully.');
        } catch (Throwable $exception) {
            Session::flash('error', 'Role change failed: ' . $exception->getMessage());
        }
        Response::redirect(Request::url('admin/users'));
    }

    public static function cancelRole(): void
    {
        Auth::requireRole('Administrator');
        try {
            ApprovalFlow::cancel();
            Session::flash('success', 'Role change approval cancelled.');
        } catch (Throwable $exception) {
            Session::flash('error', 'Could not cancel approval: ' . $exception->getMessage());
        }
        Response::redirect(Request::url('admin/users'));
    }

    private static function modal(array $pending): array
    {
        $ui = is_array($pending['ui'] ?? null) ? $pending['ui'] : [];
        $approvers = [];
        foreach ((array)($ui['approvers'] ?? []) as $approver) {
            if ((int)($approver['UserID'] ?? 0) !== (int)(Auth::user()['id'] ?? 0)) {
                $approvers[] = $approver;
            }
        }
        $token = (string)($pending['token'] ?? '');
        return [
            'title' => 'Approve role change',
            'message' => 'This is a critical action. Verify your password and phrase, then select a different administrator as second approver.',
            'detail' => substr($token, 0, 8) . '...' . substr($token, -4),
            'action' => Request::url('admin/role/confirm'),
            'cancel_action' => Request::url('admin/role/cancel'),
            'token' => $token,
            'show_password' => true,
            'show_phrase' => true,
            'phrase_hint' => (string)($ui['phrase_hint'] ?? self::approvalPhraseHint()),
            'approvers' => $approvers,
            'confirm_label' => 'Approve role change',
        ];
    }

    private static function approvalPhraseHint(): string
    {
        return Config::get('app.env') === 'local'
            ? (string)Config::get('app.approval_phrase', '')
            : 'Enter the approval phrase';
    }
}
