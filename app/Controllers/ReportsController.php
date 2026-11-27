<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\BusinessData;

final class ReportsController
{
    public static function index(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        $from = (string)Request::input('from_date', date('Y-m-01'));
        $to = (string)Request::input('to_date', date('Y-m-d'));
        $customerId = (int)Request::input('customer_id', 0);
        $categoryId = (int)Request::input('category_id', 0);

        $rows = BusinessData::salesReport(
            $from,
            $to,
            $customerId > 0 ? $customerId : null,
            $categoryId > 0 ? $categoryId : null
        );

        if ((string)Request::input('export', '') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="sales-report.csv"');
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Sale Number', 'Date', 'Customer', 'Employee', 'Subtotal', 'Discount', 'Tax', 'Total', 'Paid', 'Status']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    self::safeCsvText((string)$row['SaleNumber']),
                    self::safeCsvText((string)$row['SaleDate']),
                    self::safeCsvText((string)($row['CustomerName'] ?? '')),
                    self::safeCsvText((string)$row['EmployeeName']),
                    $row['SubTotal'], $row['DiscountAmount'], $row['TaxAmount'],
                    $row['TotalAmount'], $row['AmountPaid'],
                    self::safeCsvText((string)$row['Status']),
                ]);
            }
            fclose($handle);
            exit;
        }

        View::render('reports/index', [
            'title' => 'Sales reports - Algorithm Hardware Plug',
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'customerId' => $customerId,
            'categoryId' => $categoryId,
            'customers' => BusinessData::customers(),
            'categories' => BusinessData::categories(),
        ]);
    }

    public static function audit(): void
    {
        Auth::requireRole('Administrator');
        $from = (string)Request::input('from_date', '');
        $to = (string)Request::input('to_date', '');
        $userId = (int)Request::input('user_id', 0);

        View::render('reports/audit', [
            'title' => 'Audit trail - Algorithm Hardware Plug',
            'rows' => BusinessData::auditTrail(
                $from !== '' ? $from . ' 00:00:00' : null,
                $to !== '' ? $to . ' 23:59:59' : null,
                $userId > 0 ? $userId : null
            ),
            'from' => $from,
            'to' => $to,
            'userId' => $userId,
            'users' => BusinessData::allUsers(),
        ]);
    }

    private static function safeCsvText(string $value): string
    {
        return preg_match('/^[\t\r ]*[=+\-@]/', $value) === 1 ? "'" . $value : $value;
    }
}
