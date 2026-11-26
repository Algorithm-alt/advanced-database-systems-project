<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class ApprovalFlow
{
    private const KEY = 'pending_approval_060';

    public static function begin(
        string $actionKey,
        string $entityType,
        string $entityId,
        string $returnRoute,
        array $ui = []
    ): string {
        $clientRequestId = bin2hex(random_bytes(16));
        $token = ApprovalService::request(
            $actionKey,
            $entityType,
            $entityId,
            $clientRequestId,
            (string)($ui['details'] ?? '')
        );

        Session::put(self::KEY, [
            'token' => $token,
            'action_key' => $actionKey,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'return_route' => $returnRoute,
            'client_request_id' => $clientRequestId,
            'ui' => $ui,
        ]);

        return $token;
    }

    public static function pending(): ?array
    {
        $pending = Session::get(self::KEY);
        return is_array($pending) ? $pending : null;
    }

    public static function clear(): void
    {
        Session::forget(self::KEY);
    }

    public static function confirm(): array
    {
        Security::assertCsrf();
        $pending = self::pending();
        if ($pending === null) {
            throw new RuntimeException('No pending approval was found.');
        }

        $password = (string)Request::input('approval_password', '');
        if ($password === '' || !Auth::verifyCurrentUserPassword($password)) {
            throw new RuntimeException('The password you entered is incorrect.');
        }

        $ui = is_array($pending['ui'] ?? null) ? $pending['ui'] : [];
        $method = (string)($ui['verification_method'] ?? 'PASSWORD');
        $verifierId = null;

        if (!empty($ui['require_phrase'])) {
            $phrase = (string)Request::input('approval_phrase', '');
            if (!hash_equals((string)Config::get('app.approval_phrase', ''), $phrase)) {
                throw new RuntimeException('The approval phrase is incorrect.');
            }
        }

        if (!empty($ui['require_verifier'])) {
            $verifierId = (int)Request::input('approval_verifier', 0);
            if ($verifierId <= 0) {
                throw new RuntimeException('Select a second approver.');
            }
            if ($verifierId === (int)(Auth::user()['id'] ?? 0)) {
                throw new RuntimeException('A second approver must be a different administrator.');
            }
        }

        $confirmed = ApprovalService::confirm((string)$pending['token'], $method, $verifierId);

        return [
            'token' => (string)$pending['token'],
            'pending' => $pending,
            'confirmed' => $confirmed,
        ];
    }

    public static function cancel(): void
    {
        Security::assertCsrf();
        $pending = self::pending();
        if ($pending !== null) {
            ApprovalService::cancel((string)$pending['token']);
        }
        self::clear();
    }
}
