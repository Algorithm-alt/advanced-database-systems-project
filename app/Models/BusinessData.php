<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class BusinessData
{
    public static function inventory(string $search = '', bool $lowStockOnly = false): array
    {
        return Database::fetchAll(
            'EXEC dbo.sp_GetInventoryOverview_060 @SearchText = :Search, @LowStockOnly = :LowStock',
            ['Search' => $search !== '' ? $search : null, 'LowStock' => $lowStockOnly ? 1 : 0]
        );
    }

    public static function categories(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetCategories_060');
    }

    public static function suppliers(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetSuppliers_060');
    }

    public static function customers(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetCustomers_060');
    }

    public static function employees(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetEmployees_060');
    }

    public static function registerCustomer(
        string $customerCode,
        string $fullName,
        string $email,
        ?string $phone,
        ?string $address
    ): array {
        return Database::call(
            'dbo.sp_RegisterCustomer_060',
            [
                'CustomerCode' => $customerCode,
                'FullName' => $fullName,
                'Email' => $email,
                'Phone' => $phone,
                'Address' => $address,
            ],
            ['NewCustomerID' => 'int']
        );
    }

    public static function registerSupplier(
        string $supplierCode,
        string $companyName,
        ?string $contactName,
        ?string $phone,
        ?string $email,
        ?string $address
    ): array {
        return Database::call(
            'dbo.sp_RegisterSupplier_060',
            [
                'SupplierCode' => $supplierCode,
                'CompanyName' => $companyName,
                'ContactName' => $contactName,
                'Phone' => $phone,
                'Email' => $email,
                'Address' => $address,
            ],
            ['NewSupplierID' => 'int']
        );
    }

    public static function registerEmployee(
        string $employeeCode,
        string $fullName,
        string $position,
        ?string $department,
        ?string $phone,
        ?string $email,
        string $hireDate
    ): array {
        return Database::call(
            'dbo.sp_RegisterEmployee_060',
            [
                'EmployeeCode' => $employeeCode,
                'FullName' => $fullName,
                'Position' => $position,
                'Department' => $department,
                'Phone' => $phone,
                'Email' => $email,
                'HireDate' => $hireDate,
            ],
            ['NewEmployeeID' => 'int']
        );
    }

    public static function inventoryTransactions(?int $productId = null, ?string $from = null, ?string $to = null): array
    {
        return Database::fetchAll(
            'EXEC dbo.sp_GetInventoryTransactions_060 @ProductID = :ProductID, @FromDate = :FromDate, @ToDate = :ToDate',
            ['ProductID' => $productId, 'FromDate' => $from, 'ToDate' => $to]
        );
    }

    public static function saleHeader(int $saleId): ?array
    {
        return Database::fetchOne('EXEC dbo.sp_GetSaleHeader_060 @SaleID = :SaleID', ['SaleID' => $saleId]);
    }

    public static function saleLines(int $saleId): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetSaleLines_060 @SaleID = :SaleID', ['SaleID' => $saleId]);
    }

    public static function returnableSales(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetReturnableSales_060');
    }

    public static function returnHistory(?string $from = null, ?string $to = null): array
    {
        return Database::fetchAll(
            'EXEC dbo.sp_GetReturnHistory_060 @FromDate = :FromDate, @ToDate = :ToDate',
            ['FromDate' => $from, 'ToDate' => $to]
        );
    }

    public static function allUsers(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetAllUsers_060');
    }

    public static function allRoles(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetAllRoles_060');
    }

    public static function administrators(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetAdministrators_060');
    }

    public static function salesReport(string $from, string $to, ?int $customerId, ?int $categoryId): array
    {
        return Database::fetchAll(
            'EXEC dbo.sp_GenerateSalesReport_060 @FromDate = :FromDate, @ToDate = :ToDate, @CustomerID = :CustomerID, @CategoryID = :CategoryID',
            ['FromDate' => $from, 'ToDate' => $to, 'CustomerID' => $customerId, 'CategoryID' => $categoryId]
        );
    }

    public static function auditTrail(?string $from, ?string $to, ?int $userId): array
    {
        return Database::fetchAll(
            'EXEC dbo.sp_GetAuditTrail_060 @FromDate = :FromDate, @ToDate = :ToDate, @UserID = :UserID',
            ['FromDate' => $from, 'ToDate' => $to, 'UserID' => $userId]
        );
    }

    public static function processCartSale(
        ?int $customerId,
        int $employeeId,
        string $itemsJson,
        float $amountPaid,
        string $paymentMethod,
        int $processedByUserId
    ): array {
        return Database::call(
            'dbo.sp_ProcessCartSale_060',
            [
                'CustomerID' => $customerId,
                'EmployeeID' => $employeeId,
                'ItemsJson' => $itemsJson,
                'AmountPaid' => $amountPaid,
                'PaymentMethod' => $paymentMethod,
                'ProcessedByUserID' => $processedByUserId,
            ],
            [
                'NewSaleID' => 'int',
                'SaleNumberOut' => 'nvarchar(30)',
            ]
        );
    }

    public static function processCartReturn(
        int $saleId,
        string $itemsJson,
        string $reason,
        float $refundAmount,
        int $employeeId,
        int $approvedByUserId,
        string $approvalReference,
        int $processedByUserId
    ): array {
        return Database::call(
            'dbo.sp_ProcessCartReturn_060',
            [
                'SaleID' => $saleId,
                'ItemsJson' => $itemsJson,
                'Reason' => $reason,
                'RefundAmount' => $refundAmount,
                'EmployeeID' => $employeeId,
                'ApprovedByUserID' => $approvedByUserId,
                'ApprovalReference' => $approvalReference,
                'ProcessedByUserID' => $processedByUserId,
            ],
            [
                'NewReturnID' => 'int',
                'ReturnNumberOut' => 'nvarchar(30)',
            ]
        );
    }

    public static function assignRole(int $userId, int $roleId, string $approvalReference, int $assignedByUserId): void
    {
        Database::execute(
            'EXEC dbo.sp_AssignUserRole_060 @UserID = :UserID, @RoleID = :RoleID, @ApprovalReference = :ApprovalReference, @AssignedByUserID = :AssignedByUserID',
            [
                'UserID' => $userId,
                'RoleID' => $roleId,
                'ApprovalReference' => $approvalReference,
                'AssignedByUserID' => $assignedByUserId,
            ]
        );
    }

    public static function productCatalog(?int $categoryId = null, ?string $search = null): array
    {
        return Database::fetchAll(
            'EXEC dbo.sp_GetProductCatalog_060 @CategoryID = :CategoryID, @SearchText = :SearchText',
            ['CategoryID' => $categoryId, 'SearchText' => $search]
        );
    }

    public static function myCustomerProfile(int $userId): ?array
    {
        return Database::fetchOne(
            'SELECT TOP 1 CustomerID, CustomerCode, FullName, Email, Phone FROM dbo.Customers_060 WHERE UserID = :UserID AND IsActive = 1',
            ['UserID' => $userId]
        );
    }

    public static function mySales(int $customerId): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetMySales_060 @CustomerID = :CustomerID', ['CustomerID' => $customerId]);
    }

    public static function registerProduct(
        string $sku,
        string $productName,
        int $categoryId,
        int $supplierId,
        float $unitPrice,
        float $costPrice,
        int $reorderLevel,
        ?string $barcode = null
    ): array {
        return Database::call(
            'dbo.sp_RegisterProduct_060',
            [
                'SKU' => $sku,
                'ProductName' => $productName,
                'CategoryID' => $categoryId,
                'SupplierID' => $supplierId,
                'UnitPrice' => $unitPrice,
                'CostPrice' => $costPrice,
                'ReorderLevel' => $reorderLevel,
                'Barcode' => $barcode !== '' ? $barcode : null,
            ],
            ['NewProductID' => 'int']
        );
    }
}
