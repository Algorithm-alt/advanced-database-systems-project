<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class ApprovalService
{
    public static function request(
        string $actionKey,
        string $entityType,
        string $entityId,
        string $clientRequestId,
        ?string $details = null
    ): string {
        $row = Database::call(
            'dbo.sp_RequestSensitiveAction_060',
            [
                'ActionKey' => $actionKey,
                'EntityType' => $entityType,
                'EntityID' => $entityId,
                'ClientRequestID' => $clientRequestId,
                'Details' => $details,
            ],
            ['Token' => 'uniqueidentifier']
        );

        $token = (string)($row['Token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('No approval token was returned.');
        }
        return $token;
    }

    public static function confirm(string $token, string $verificationMethod, ?int $verifierUserId = null): array
    {
        return Database::call(
            'dbo.sp_ConfirmSensitiveAction_060',
            [
                'Token' => $token,
                'VerificationMethod' => $verificationMethod,
                'VerifierUserID' => $verifierUserId,
            ],
            [
                'ActionKey' => 'nvarchar(80)',
                'EntityType' => 'nvarchar(50)',
                'EntityID' => 'nvarchar(50)',
                'RiskLevel' => 'nvarchar(20)',
                'UserID' => 'int',
            ]
        );
    }

    public static function cancel(string $token): void
    {
        Database::execute('EXEC dbo.sp_CancelSensitiveAction_060 @Token = :Token', ['Token' => $token]);
    }
}
