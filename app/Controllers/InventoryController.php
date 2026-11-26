<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApprovalFlow;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Core\View;
use App\Models\BusinessData;
use Throwable;

final class InventoryController
{
    public static function index(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee');
        $search = (string)Request::input('search', '');
        $lowStock = (string)Request::input('low_stock', '') === '1';

        View::render('inventory/index', [
            'title' => 'Inventory - Algorithm Hardware Plug',
            'rows' => BusinessData::inventory($search, $lowStock),
            'search' => $search,
            'lowStock' => $lowStock,
        ]);
    }

    public static function create(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        View::render('inventory/create', [
            'title' => 'Register product - Algorithm Hardware Plug',
            'categories' => BusinessData::categories(),
            'suppliers' => BusinessData::suppliers(),
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        Security::assertCsrf();

        $sku = (string)Request::input('sku', '');
        $productName = (string)Request::input('product_name', '');
        $categoryId = (int)Request::input('category_id', 0);
        $supplierId = (int)Request::input('supplier_id', 0);
        $unitPrice = (float)Request::input('unit_price', -1);
        $costPrice = (float)Request::input('cost_price', -1);
        $reorderLevel = (int)Request::input('reorder_level', 5);
        $barcode = (string)Request::input('barcode', '');

        if ($sku === '' || $productName === '' || $categoryId <= 0 || $supplierId <= 0 || $unitPrice < 0 || $costPrice < 0 || $reorderLevel < 0) {
            Session::flash('error', 'Complete all required fields with valid non-negative prices and reorder level.');
            Response::redirect(Request::url('inventory/create'));
        }

        try {
            $res = BusinessData::registerProduct(
                $sku,
                $productName,
                $categoryId,
                $supplierId,
                $unitPrice,
                $costPrice,
                $reorderLevel,
                $barcode
            );
            Session::flash('success', 'Product "' . $productName . '" registered (ID #' . (int)($res['NewProductID'] ?? 0) . ').');
            Response::redirect(Request::url('inventory'));
        } catch (Throwable $exception) {
            Session::flash('error', 'Could not register product: ' . $exception->getMessage());
            Response::redirect(Request::url('inventory/create'));
        }
    }

    public static function adjust(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        $productId = (int)Request::input('product_id', 0);
        $products = BusinessData::inventory();
        $product = null;
        foreach ($products as $row) {
            if ((int)$row['ProductID'] === $productId) {
                $product = $row;
                break;
            }
        }

        $pending = ApprovalFlow::pending();
        $approval = null;
        if ($pending !== null && ($pending['action_key'] ?? '') === 'ADJUST_STOCK_DOWN') {
            $approval = self::modal($pending, 'inventory/adjust/confirm', 'inventory/adjust/cancel');
        }

        View::render('inventory/adjust', [
            'title' => 'Adjust stock - Algorithm Hardware Plug',
            'products' => $products,
            'product' => $product,
            'productId' => $productId,
            'approval' => $approval,
        ]);
    }

    public static function requestAdjustment(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        Security::assertCsrf();

        $productId = (int)Request::input('product_id', 0);
        $change = (int)Request::input('quantity_change', 0);
        $reason = (string)Request::input('reason', '');
        $returnRoute = 'inventory/adjust&product_id=' . $productId;

        if ($productId <= 0 || $change === 0 || $reason === '') {
            Session::flash('error', 'Select a product, enter a non-zero quantity and provide a reason.');
            Response::redirect(Request::url($returnRoute));
        }

        if ($change > 0) {
            try {
                Database::call('dbo.sp_UpdateInventory_060', [
                    'ProductID' => $productId,
                    'QuantityChange' => $change,
                    'TransactionType' => 'ADJUSTMENT_IN',
                    'PerformedByUserID' => (int)(Auth::user()['id'] ?? 0),
                    'Reason' => $reason,
                ]);
                Session::flash('success', 'Stock was increased successfully.');
            } catch (Throwable $exception) {
                Session::flash('error', 'Stock increase failed: ' . $exception->getMessage());
            }
            Response::redirect(Request::url('inventory'));
        }

        try {
            ApprovalFlow::begin(
                'ADJUST_STOCK_DOWN',
                'Product',
                (string)$productId,
                $returnRoute,
                [
                    'details' => $reason,
                    'product_id' => $productId,
                    'quantity_change' => $change,
                    'reason' => $reason,
                    'verification_method' => 'PASSWORD',
                    'show_password' => true,
                ]
            );
            Session::flash('info', 'Confirm the stock reduction with your password.');
        } catch (Throwable $exception) {
            Session::flash('error', 'Could not create the approval: ' . $exception->getMessage());
        }
        Response::redirect(Request::url($returnRoute));
    }

    public static function confirmAdjustment(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        $pending = ApprovalFlow::pending();
        $productId = (int)($pending['ui']['product_id'] ?? 0);

        try {
            $result = ApprovalFlow::confirm();
            $ui = is_array($result['pending']['ui'] ?? null) ? $result['pending']['ui'] : [];
            $productId = (int)($ui['product_id'] ?? $productId);
            $change = (int)($ui['quantity_change'] ?? 0);
            $user = Auth::user();

            Database::call('dbo.sp_UpdateInventory_060', [
                'ProductID' => $productId,
                'QuantityChange' => $change,
                'TransactionType' => 'ADJUSTMENT_OUT',
                'PerformedByUserID' => (int)($user['id'] ?? 0),
                'ApprovedByUserID' => (int)($user['id'] ?? 0),
                'ApprovalReference' => $result['token'],
                'Reason' => (string)($ui['reason'] ?? 'Approved stock reduction'),
            ]);

            ApprovalFlow::clear();
            Session::flash('success', 'Approved stock reduction completed.');
            Response::redirect(Request::url('inventory'));
        } catch (Throwable $exception) {
            Session::flash('error', 'Stock reduction failed: ' . $exception->getMessage());
            $fallback = $productId > 0 ? 'inventory/adjust&product_id=' . $productId : 'inventory/adjust';
            Response::redirect(Request::url($fallback));
        }
    }

    public static function cancelAdjustment(): void
    {
        Auth::requireRole('Administrator', 'Manager');
        $productId = (int)(ApprovalFlow::pending()['ui']['product_id'] ?? 0);
        try {
            ApprovalFlow::cancel();
            Session::flash('success', 'Stock adjustment approval cancelled.');
        } catch (Throwable $exception) {
            Session::flash('error', 'Could not cancel approval: ' . $exception->getMessage());
        }
        Response::redirect(Request::url('inventory/adjust&product_id=' . $productId));
    }

    public static function transactions(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee');
        $productId = (int)Request::input('product_id', 0);
        $from = (string)Request::input('from_date', '');
        $to = (string)Request::input('to_date', '');

        View::render('inventory/transactions', [
            'title' => 'Stock movements - Algorithm Hardware Plug',
            'rows' => BusinessData::inventoryTransactions(
                $productId > 0 ? $productId : null,
                $from !== '' ? $from : null,
                $to !== '' ? $to : null
            ),
            'products' => BusinessData::inventory(),
            'productId' => $productId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    private static function modal(array $pending, string $confirmRoute, string $cancelRoute): array
    {
        $token = (string)($pending['token'] ?? '');
        return [
            'title' => 'Confirm stock reduction',
            'message' => 'This will reduce stock on hand. Your password is required to complete the approved adjustment.',
            'detail' => substr($token, 0, 8) . '...' . substr($token, -4),
            'action' => Request::url($confirmRoute),
            'cancel_action' => Request::url($cancelRoute),
            'token' => $token,
            'show_password' => true,
            'confirm_label' => 'Confirm reduction',
        ];
    }
}
