<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Core\View;
use App\Models\BusinessData;
use Throwable;

final class PosController
{
    public static function index(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee');
        View::render('pos/index', [
            'title' => 'Point of sale - Algorithm Hardware Plug',
            'products' => BusinessData::inventory(),
            'customers' => BusinessData::customers(),
            'employees' => BusinessData::employees(),
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee');
        Security::assertCsrf();

        $customerId = (int)Request::input('customer_id', 0);
        $employeeId = (int)Request::input('employee_id', 0);
        $paymentMethod = (string)Request::input('payment_method', 'CASH');
        $amountPaid = (float)Request::input('amount_paid', 0);
        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $discounts = $_POST['discount'] ?? [];

        $catalog = [];
        foreach (BusinessData::inventory() as $product) {
            $catalog[(int)$product['ProductID']] = $product;
        }

        $items = [];
        foreach ((array)$productIds as $index => $productId) {
            $pid = (int)$productId;
            $qty = (int)(is_array($quantities) ? ($quantities[$index] ?? 0) : 0);
            $discount = (float)(is_array($discounts) ? ($discounts[$index] ?? 0) : 0);
            if ($pid <= 0 || $qty <= 0 || !isset($catalog[$pid])) {
                continue;
            }
            $unitPrice = (float)$catalog[$pid]['UnitPrice'];
            $maxDiscount = $unitPrice * $qty;
            if ($discount < 0 || $discount > $maxDiscount) {
                $discount = 0;
            }
            $items[] = [
                'productId' => $pid,
                'quantity' => $qty,
                'unitPrice' => $unitPrice,
                'discount' => round($discount, 2),
            ];
        }

        if ($items === [] || $employeeId <= 0 || $amountPaid < 0) {
            Session::flash('error', 'Select an employee, add at least one valid sale item, and enter a valid payment amount.');
            Response::redirect(Request::url('pos'));
        }

        try {
            $result = BusinessData::processCartSale(
                $customerId > 0 ? $customerId : null,
                $employeeId,
                json_encode($items, JSON_THROW_ON_ERROR),
                $amountPaid,
                $paymentMethod,
                (int)(Auth::user()['id'] ?? 0)
            );
            Session::flash('success', 'Sale ' . (string)($result['SaleNumberOut'] ?? '') . ' completed.');
            Response::redirect(Request::url('pos/receipt&sale_id=' . (int)($result['NewSaleID'] ?? 0)));
        } catch (Throwable $exception) {
            Session::flash('error', 'Sale failed: ' . $exception->getMessage());
            Response::redirect(Request::url('pos'));
        }
    }

    public static function receipt(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee');
        $saleId = (int)Request::input('sale_id', 0);
        $header = BusinessData::saleHeader($saleId);
        if ($header === null) {
            Session::flash('error', 'Sale not found.');
            Response::redirect(Request::url('pos'));
        }

        View::render('pos/receipt', [
            'title' => 'Sale receipt - Algorithm Hardware Plug',
            'sale' => $header,
            'lines' => BusinessData::saleLines($saleId),
        ]);
    }

    public static function invoice(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee');
        $saleId = (int)Request::input('sale_id', 0);
        $header = BusinessData::saleHeader($saleId);
        if ($header === null || (string)$header['Status'] !== 'COMPLETED') {
            Session::flash('error', 'Invoice is available only for completed sales.');
            Response::redirect(Request::url('pos'));
        }

        View::render('pos/receipt', [
            'title' => 'Invoice - Algorithm Hardware Plug',
            'documentType' => 'Invoice',
            'sale' => $header,
            'lines' => BusinessData::saleLines($saleId),
        ]);
    }
}
