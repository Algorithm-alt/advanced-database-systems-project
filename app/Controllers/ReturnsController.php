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

final class ReturnsController
{
    public static function index(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        $from = (string)Request::input('from_date', '');
        $to = (string)Request::input('to_date', '');

        View::render('returns/index', [
            'title' => 'Returns - Algorithm Hardware Plug',
            'rows' => BusinessData::returnHistory($from !== '' ? $from : null, $to !== '' ? $to : null),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public static function create(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        $saleId = (int)Request::input('sale_id', 0);
        $pending = ApprovalFlow::pending();
        $approval = null;
        if ($pending !== null && ($pending['action_key'] ?? '') === 'ISSUE_REFUND') {
            $approval = self::modal($pending, 'returns/confirm', 'returns/cancel');
        }

        View::render('returns/create', [
            'title' => 'Process return - Algorithm Hardware Plug',
            'sales' => BusinessData::returnableSales(),
            'saleId' => $saleId,
            'header' => $saleId > 0 ? BusinessData::saleHeader($saleId) : null,
            'lines' => $saleId > 0 ? BusinessData::saleLines($saleId) : [],
            'employees' => BusinessData::employees(),
            'approval' => $approval,
        ]);
    }

    public static function requestApproval(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        Security::assertCsrf();

        $saleId = (int)Request::input('sale_id', 0);
        $employeeId = (int)Request::input('employee_id', 0);
        $reason = (string)Request::input('reason', '');
        $refundAmount = round((float)Request::input('refund_amount', 0), 2);
        $returnRoute = 'returns/create&sale_id=' . $saleId;

        $lines = BusinessData::saleLines($saleId);
        $available = [];
        foreach ($lines as $line) {
            $available[(int)$line['SaleItemID']] = $line;
        }

        $quantities = $_POST['return_qty'] ?? [];
        $items = [];
        $expectedRefund = 0.0;
        if (is_array($quantities)) {
            foreach ($quantities as $itemId => $rawQty) {
                $saleItemId = (int)$itemId;
                $qty = (int)$rawQty;
                if ($saleItemId <= 0 || $qty <= 0 || !isset($available[$saleItemId])) {
                    continue;
                }
                $line = $available[$saleItemId];
                $remaining = (int)$line['RemainingQuantity'];
                if ($qty > $remaining) {
                    $qty = $remaining;
                }
                if ($qty <= 0) {
                    continue;
                }
                $items[] = ['saleItemId' => $saleItemId, 'quantity' => $qty];
                $expectedRefund += $qty * (float)$line['UnitPrice'];
            }
        }
        $expectedRefund = round($expectedRefund, 2);

        if ($items === [] || $employeeId <= 0 || $reason === '' || $refundAmount < 0 || $refundAmount > $expectedRefund) {
            Session::flash('error', 'Select valid return items, an employee and a refund amount no greater than the returned value.');
            Response::redirect(Request::url($returnRoute));
        }

        try {
            ApprovalFlow::begin(
                'ISSUE_REFUND',
                'Sale',
                (string)$saleId,
                $returnRoute,
                [
                    'details' => $reason,
                    'sale_id' => $saleId,
                    'items_json' => json_encode($items, JSON_THROW_ON_ERROR),
                    'refund_amount' => $refundAmount,
                    'employee_id' => $employeeId,
                    'reason' => $reason,
                    'approvers' => BusinessData::administrators(),
                    'verification_method' => 'PASSWORD_AND_PHRASE',
                    'show_password' => true,
                    'show_phrase' => true,
                    'require_phrase' => true,
                    'require_verifier' => true,
                    'phrase_hint' => self::approvalPhraseHint(),
                ]
            );
            Session::flash('info', 'Refund approval created. Confirm with your password, phrase and a second administrator.');
        } catch (Throwable $exception) {
            Session::flash('error', 'Could not create the refund approval: ' . $exception->getMessage());
        }
        Response::redirect(Request::url($returnRoute));
    }

    public static function confirm(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        $pending = ApprovalFlow::pending();
        $saleId = (int)($pending['ui']['sale_id'] ?? 0);

        try {
            $result = ApprovalFlow::confirm();
            $ui = is_array($result['pending']['ui'] ?? null) ? $result['pending']['ui'] : [];
            $saleId = (int)($ui['sale_id'] ?? $saleId);
            $verifierId = (int)Request::input('approval_verifier', 0);
            $user = Auth::user();

            $returned = BusinessData::processCartReturn(
                $saleId,
                (string)($ui['items_json'] ?? '[]'),
                (string)($ui['reason'] ?? 'Return'),
                (float)($ui['refund_amount'] ?? 0),
                (int)($ui['employee_id'] ?? 0),
                $verifierId,
                $result['token'],
                (int)($user['id'] ?? 0)
            );

            ApprovalFlow::clear();
            Session::flash('success', 'Return ' . (string)($returned['ReturnNumberOut'] ?? '') . ' completed.');
            Response::redirect(Request::url('returns'));
        } catch (Throwable $exception) {
            Session::flash('error', 'Return failed: ' . $exception->getMessage());
            $fallback = $saleId > 0 ? 'returns/create&sale_id=' . $saleId : 'returns/create';
            Response::redirect(Request::url($fallback));
        }
    }

    public static function cancel(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        $saleId = (int)(ApprovalFlow::pending()['ui']['sale_id'] ?? 0);
        try {
            ApprovalFlow::cancel();
            Session::flash('success', 'Refund approval cancelled.');
        } catch (Throwable $exception) {
            Session::flash('error', 'Could not cancel approval: ' . $exception->getMessage());
        }
        Response::redirect(Request::url('returns/create&sale_id=' . $saleId));
    }

    private static function modal(array $pending, string $confirmRoute, string $cancelRoute): array
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
            'title' => 'Approve refund',
            'message' => 'This is a critical action. Verify your password and phrase, then select an administrator as second approver.',
            'detail' => substr($token, 0, 8) . '...' . substr($token, -4),
            'action' => Request::url($confirmRoute),
            'cancel_action' => Request::url($cancelRoute),
            'token' => $token,
            'show_password' => true,
            'show_phrase' => true,
            'phrase_hint' => (string)($ui['phrase_hint'] ?? self::approvalPhraseHint()),
            'approvers' => $approvers,
            'confirm_label' => 'Approve and process refund',
        ];
    }

    private static function approvalPhraseHint(): string
    {
        return Config::get('app.env') === 'local'
            ? (string)Config::get('app.approval_phrase', '')
            : 'Enter the approval phrase';
    }
}
