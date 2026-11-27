<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CustomerController;
use App\Controllers\HealthController;
use App\Controllers\HomeController;
use App\Controllers\InventoryController;
use App\Controllers\NotificationsController;
use App\Controllers\PosController;
use App\Controllers\ReportsController;
use App\Controllers\ReturnsController;

final class Router
{
    public static function dispatch(string $route): void
    {
        match ($route) {
            'home' => HomeController::index(),
            'login' => Request::isPost() ? AuthController::login() : AuthController::showLogin(),
            'logout' => AuthController::showLogout(),
            'logout/request' => AuthController::requestLogout(),
            'logout/confirm' => AuthController::confirmLogout(),
            'logout/cancel' => AuthController::cancelLogout(),
            'dashboard' => AuthController::dashboard(),
            'health' => HealthController::index(),
            'notifications' => NotificationsController::index(),
            'inventory' => InventoryController::index(),
            'inventory/create' => InventoryController::create(),
            'inventory/store' => Request::isPost() ? InventoryController::store() : self::notFound(),
            'inventory/adjust' => InventoryController::adjust(),
            'inventory/adjust/request' => Request::isPost() ? InventoryController::requestAdjustment() : self::notFound(),
            'inventory/adjust/confirm' => Request::isPost() ? InventoryController::confirmAdjustment() : self::notFound(),
            'inventory/adjust/cancel' => Request::isPost() ? InventoryController::cancelAdjustment() : self::notFound(),
            'inventory/transactions' => InventoryController::transactions(),
            'pos' => PosController::index(),
            'pos/store' => Request::isPost() ? PosController::store() : self::notFound(),
            'pos/receipt' => PosController::receipt(),
            'pos/invoice' => PosController::invoice(),
            'returns' => ReturnsController::index(),
            'returns/create' => ReturnsController::create(),
            'returns/request' => Request::isPost() ? ReturnsController::requestApproval() : self::notFound(),
            'returns/confirm' => Request::isPost() ? ReturnsController::confirm() : self::notFound(),
            'returns/cancel' => Request::isPost() ? ReturnsController::cancel() : self::notFound(),
            'reports' => ReportsController::index(),
            'reports/audit' => ReportsController::audit(),
            'admin/records' => AdminController::records(),
            'admin/records/create' => Request::isPost() ? AdminController::createRecord() : self::notFound(),
            'admin/users' => AdminController::users(),
            'admin/role/request' => Request::isPost() ? AdminController::requestRole() : self::notFound(),
            'admin/role/confirm' => Request::isPost() ? AdminController::confirmRole() : self::notFound(),
            'admin/role/cancel' => Request::isPost() ? AdminController::cancelRole() : self::notFound(),
            'catalog' => CustomerController::catalog(),
            'my-sales' => CustomerController::mySales(),
            default => self::notFound(),
        };
    }

    private static function notFound(): never
    {
        Response::status(404);
        View::render('errors/404', ['title' => 'Page not found - Algorithm Hardware Plug']);
        exit;
    }
}
