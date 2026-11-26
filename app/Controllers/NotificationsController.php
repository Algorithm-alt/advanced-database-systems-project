<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\BusinessData;

final class NotificationsController
{
    public static function index(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee');

        $notifications = [];
        foreach (BusinessData::inventory('', true) as $row) {
            if ((int)($row['IsLowStock'] ?? 0) !== 1) {
                continue;
            }

            $notifications[] = [
                'id' => 'stock-' . (int)$row['ProductID'],
                'level' => 'warning',
                'title' => 'Low stock: ' . (string)$row['ProductName'],
                'message' => sprintf(
                    '%d in stock; reorder level is %d.',
                    (int)$row['QuantityOnHand'],
                    (int)$row['ReorderLevel']
                ),
                'url' => Request::url('inventory'),
            ];
        }

        if (Auth::hasRole('Administrator', 'Manager')) {
            foreach (BusinessData::returnHistory() as $row) {
                if (strtoupper((string)$row['Status']) !== 'PENDING') {
                    continue;
                }

                $notifications[] = [
                    'id' => 'return-' . (int)$row['ReturnID'],
                    'level' => 'warning',
                    'title' => 'Return awaiting review',
                    'message' => (string)$row['ReturnNumber'] . ' for ' . (string)($row['CustomerName'] ?? 'Walk-in customer'),
                    'url' => Request::url('returns'),
                ];
            }
        }

        header('Cache-Control: no-store');
        Response::json([
            'notifications' => $notifications,
            'checked_at' => date(DATE_ATOM),
        ]);
    }
}
