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
    public static function records(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee');

        $sectionRoles = self::recordSections();
        $availableSections = array_filter(
            $sectionRoles,
            static fn(array $item): bool => Auth::hasRole(...$item['roles'])
        );
        $defaultSection = Auth::hasRole('Employee') && !Auth::hasRole('Administrator', 'Manager')
            ? 'customers'
            : 'suppliers';
        $section = (string)Request::input('section', $defaultSection);
        if (!isset($sectionRoles[$section])) {
            $section = $defaultSection;
        }
        Auth::requireRole(...$sectionRoles[$section]['roles']);

        $records = ['suppliers' => [], 'customers' => [], 'employees' => []];
        $records[$section] = match ($section) {
            'suppliers' => BusinessData::suppliers(),
            'customers' => BusinessData::customers(),
            'employees' => BusinessData::employees(),
        };

        View::render('admin/records', [
            'title' => 'Business records - Algorithm Hardware Plug',
            'section' => $section,
            'sections' => $availableSections,
            'suppliers' => $records['suppliers'],
            'customers' => $records['customers'],
            'employees' => $records['employees'],
        ]);
    }

    public static function createRecord(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee');
        Security::assertCsrf();

        $section = (string)Request::input('type', '');
        $sectionRoles = self::recordSections();
        if (!isset($sectionRoles[$section])) {
            Session::flash('error', 'Choose a valid business record type.');
            Response::redirect(Request::url('admin/records'));
        }
        Auth::requireRole(...$sectionRoles[$section]['roles']);

        $email = (string)Request::input('email', '');
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            Session::flash('error', 'Enter a valid email address.');
            Response::redirect(Request::url('admin/records&section=' . $section));
        }

        try {
            if ($section === 'suppliers') {
                $companyName = (string)Request::input('company_name', '');
                $contactName = (string)Request::input('contact_name', '');
                $phone = (string)Request::input('phone', '');
                $address = (string)Request::input('address', '');
                if ($companyName === '' || strlen($companyName) > 100 || strlen($contactName) > 100
                    || strlen($phone) > 30 || strlen($email) > 256 || strlen($address) > 255) {
                    Session::flash('error', 'Enter a supplier company name and keep contact details within the allowed lengths.');
                    Response::redirect(Request::url('admin/records&section=suppliers'));
                }

                $result = BusinessData::registerSupplier(
                    self::newRecordCode('SUP'),
                    $companyName,
                    $contactName !== '' ? $contactName : null,
                    $phone !== '' ? $phone : null,
                    $email !== '' ? $email : null,
                    $address !== '' ? $address : null
                );
                Session::flash('success', 'Supplier added (ID #' . (int)($result['NewSupplierID'] ?? 0) . ').');
            } elseif ($section === 'customers') {
                $fullName = (string)Request::input('full_name', '');
                $phone = (string)Request::input('phone', '');
                $address = (string)Request::input('address', '');
                if ($fullName === '' || $email === '' || strlen($fullName) > 100
                    || strlen($phone) > 30 || strlen($email) > 256 || strlen($address) > 255) {
                    Session::flash('error', 'Enter the customer name and email, and keep each value within the allowed length.');
                    Response::redirect(Request::url('admin/records&section=customers'));
                }

                $result = BusinessData::registerCustomer(
                    self::newRecordCode('CUST'),
                    $fullName,
                    $email,
                    $phone !== '' ? $phone : null,
                    $address !== '' ? $address : null
                );
                Session::flash('success', 'Customer added (ID #' . (int)($result['NewCustomerID'] ?? 0) . ').');
            } else {
                $fullName = (string)Request::input('full_name', '');
                $position = (string)Request::input('position', '');
                $department = (string)Request::input('department', '');
                $phone = (string)Request::input('phone', '');
                $hireDate = (string)Request::input('hire_date', '');
                $parsedHireDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $hireDate);
                if ($fullName === '' || $position === '' || $parsedHireDate === false
                    || $parsedHireDate->format('Y-m-d') !== $hireDate
                    || strlen($fullName) > 100 || strlen($position) > 50 || strlen($department) > 50
                    || strlen($phone) > 30 || strlen($email) > 256) {
                    Session::flash('error', 'Enter a valid employee name, position, hire date and contact details.');
                    Response::redirect(Request::url('admin/records&section=employees'));
                }

                $result = BusinessData::registerEmployee(
                    self::newRecordCode('EMP'),
                    $fullName,
                    $position,
                    $department !== '' ? $department : null,
                    $phone !== '' ? $phone : null,
                    $email !== '' ? $email : null,
                    $hireDate
                );
                Session::flash('success', 'Employee added (ID #' . (int)($result['NewEmployeeID'] ?? 0) . ').');
            }
        } catch (Throwable $exception) {
            Session::flash('error', 'Could not add the record: ' . $exception->getMessage());
        }

        Response::redirect(Request::url('admin/records&section=' . $section));
    }

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

    private static function newRecordCode(string $prefix): string
    {
        return $prefix . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
    }

    private static function recordSections(): array
    {
        return [
            'suppliers' => ['label' => 'Suppliers', 'roles' => ['Administrator', 'Manager']],
            'customers' => ['label' => 'Customers', 'roles' => ['Administrator', 'Manager', 'Employee']],
            'employees' => ['label' => 'Employees', 'roles' => ['Administrator']],
        ];
    }
}
