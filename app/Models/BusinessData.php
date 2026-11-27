<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class BusinessData
{
    public static function inventory(string $search = '', bool $lowStockOnly = false): array
    {
        $rows = Database::fetchAll(
            'EXEC dbo.sp_GetInventoryOverview_060 @SearchText = :Search, @LowStockOnly = :LowStock',
            ['Search' => $search !== '' ? $search : null, 'LowStock' => $lowStockOnly ? 1 : 0]
        );
        return array_values(array_filter($rows, static fn(array $row): bool => (int)($row['IsActive'] ?? 1) === 1));
    }

    public static function product(int $productId): ?array
    {
        return Database::fetchOne(
            'SELECT p.ProductID, p.SKU, p.Barcode, p.ProductName, p.CategoryID, p.SupplierID,
                    p.UnitPrice, p.CostPrice, p.ReorderLevel, p.IsActive
             FROM dbo.Products_060 p
             WHERE p.ProductID = :ProductID',
            ['ProductID' => $productId]
        );
    }

    public static function updateProduct(
        int $productId,
        string $sku,
        string $productName,
        int $categoryId,
        int $supplierId,
        float $unitPrice,
        float $costPrice,
        int $reorderLevel,
        ?string $barcode
    ): void {
        Database::execute(
            'UPDATE dbo.Products_060
             SET SKU = :SKU, Barcode = :Barcode, ProductName = :ProductName,
                 CategoryID = :CategoryID, SupplierID = :SupplierID,
                 UnitPrice = :UnitPrice, CostPrice = :CostPrice, ReorderLevel = :ReorderLevel
             WHERE ProductID = :ProductID',
            [
                'ProductID' => $productId,
                'SKU' => $sku,
                'Barcode' => $barcode !== '' ? $barcode : null,
                'ProductName' => $productName,
                'CategoryID' => $categoryId,
                'SupplierID' => $supplierId,
                'UnitPrice' => $unitPrice,
                'CostPrice' => $costPrice,
                'ReorderLevel' => $reorderLevel,
            ]
        );
        Database::execute(
            'EXEC dbo.sp_WriteAudit_060 @Action = :Action, @EntityType = :EntityType,
             @EntityID = :EntityID, @Details = :Details',
            [
                'Action' => 'PRODUCT_UPDATE',
                'EntityType' => 'Product',
                'EntityID' => $productId,
                'Details' => $productName,
            ]
        );
    }

    public static function deactivateProduct(int $productId): void
    {
        Database::execute(
            'UPDATE dbo.Products_060 SET IsActive = 0 WHERE ProductID = :ProductID',
            ['ProductID' => $productId]
        );
        Database::execute(
            'EXEC dbo.sp_WriteAudit_060 @Action = :Action, @EntityType = :EntityType,
             @EntityID = :EntityID, @Details = :Details',
            [
                'Action' => 'PRODUCT_DEACTIVATE',
                'EntityType' => 'Product',
                'EntityID' => $productId,
                'Details' => 'Product deactivated',
            ]
        );
    }

    public static function activateProduct(int $productId): void
    {
        Database::execute(
            'UPDATE dbo.Products_060 SET IsActive = 1 WHERE ProductID = :ProductID',
            ['ProductID' => $productId]
        );
    }

    public static function permanentlyDeleteProduct(int $productId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $product = Database::fetchOne(
                'SELECT ProductID, IsActive FROM dbo.Products_060 WHERE ProductID = :ProductID',
                ['ProductID' => $productId]
            );
            if ($product === null) {
                throw new \RuntimeException('Product not found.');
            }

            $inventory = Database::fetchOne(
                'SELECT QuantityOnHand, QuantityReserved
                 FROM dbo.Inventory_060 WHERE ProductID = :ProductID',
                ['ProductID' => $productId]
            );
            if ($inventory !== null
                && ((int)$inventory['QuantityOnHand'] !== 0 || (int)$inventory['QuantityReserved'] !== 0)) {
                throw new \RuntimeException('Product cannot be deleted while stock is on hand or reserved.');
            }

            $hasHistory = Database::fetchOne(
                'SELECT TOP 1 ProductID
                 FROM dbo.InventoryTransactions_060
                 WHERE ProductID = :TransactionProductID
                 UNION ALL
                 SELECT TOP 1 ProductID
                 FROM dbo.SaleItems_060
                 WHERE ProductID = :SaleProductID',
                ['TransactionProductID' => $productId, 'SaleProductID' => $productId]
            );
            if ($hasHistory !== null) {
                throw new \RuntimeException('Product has sales or stock movement history. Deactivate it instead.');
            }

            Database::execute(
                'DELETE FROM dbo.Inventory_060 WHERE ProductID = :ProductID',
                ['ProductID' => $productId]
            );
            $deleted = Database::execute(
                'DELETE FROM dbo.Products_060 WHERE ProductID = :ProductID',
                ['ProductID' => $productId]
            );
            if ($deleted !== 1) {
                throw new \RuntimeException('Product not found.');
            }
            Database::execute(
                'EXEC dbo.sp_WriteAudit_060 @Action = :Action, @EntityType = :EntityType,
                 @EntityID = :EntityID, @Details = :Details',
                [
                    'Action' => 'PRODUCT_DELETE',
                    'EntityType' => 'Product',
                    'EntityID' => $productId,
                    'Details' => 'Product permanently deleted',
                ]
            );
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    public static function supplier(int $supplierId): ?array
    {
        return Database::fetchOne(
            'SELECT SupplierID, SupplierCode, CompanyName AS SupplierName, ContactName, Phone, Email, [Address], IsActive
             FROM dbo.Suppliers_060 WHERE SupplierID = :SupplierID',
            ['SupplierID' => $supplierId]
        );
    }

    public static function updateSupplier(
        int $supplierId,
        string $companyName,
        ?string $contactName,
        ?string $phone,
        ?string $email,
        ?string $address
    ): void {
        Database::execute(
            'UPDATE dbo.Suppliers_060
             SET CompanyName = :CompanyName, ContactName = :ContactName, Phone = :Phone,
                 Email = :Email, [Address] = :Address
             WHERE SupplierID = :SupplierID',
            [
                'SupplierID' => $supplierId,
                'CompanyName' => $companyName,
                'ContactName' => $contactName,
                'Phone' => $phone,
                'Email' => $email !== '' ? $email : null,
                'Address' => $address,
            ]
        );
        Database::execute(
            'EXEC dbo.sp_WriteAudit_060 @Action = :Action, @EntityType = :EntityType,
             @EntityID = :EntityID, @Details = :Details',
            [
                'Action' => 'SUPPLIER_UPDATE',
                'EntityType' => 'Supplier',
                'EntityID' => $supplierId,
                'Details' => $companyName,
            ]
        );
    }

    public static function deactivateSupplier(int $supplierId): void
    {
        Database::execute(
            'UPDATE dbo.Suppliers_060 SET IsActive = 0 WHERE SupplierID = :SupplierID',
            ['SupplierID' => $supplierId]
        );
        Database::execute(
            'EXEC dbo.sp_WriteAudit_060 @Action = :Action, @EntityType = :EntityType,
             @EntityID = :EntityID, @Details = :Details',
            [
                'Action' => 'SUPPLIER_DEACTIVATE',
                'EntityType' => 'Supplier',
                'EntityID' => $supplierId,
                'Details' => 'Supplier deactivated',
            ]
        );
    }

    public static function activateSupplier(int $supplierId): void
    {
        Database::execute(
            'UPDATE dbo.Suppliers_060 SET IsActive = 1 WHERE SupplierID = :SupplierID',
            ['SupplierID' => $supplierId]
        );
    }

    public static function permanentlyDeleteSupplier(int $supplierId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $supplier = Database::fetchOne(
                'SELECT SupplierID FROM dbo.Suppliers_060 WHERE SupplierID = :SupplierID',
                ['SupplierID' => $supplierId]
            );
            if ($supplier === null) {
                throw new \RuntimeException('Supplier not found.');
            }

            $hasProducts = Database::fetchOne(
                'SELECT TOP 1 ProductID FROM dbo.Products_060 WHERE SupplierID = :ProductSupplierID',
                ['ProductSupplierID' => $supplierId]
            );
            if ($hasProducts !== null) {
                throw new \RuntimeException('Supplier cannot be deleted while products are assigned to it. Deactivate it instead.');
            }

            $deleted = Database::execute(
                'DELETE FROM dbo.Suppliers_060 WHERE SupplierID = :SupplierID',
                ['SupplierID' => $supplierId]
            );
            if ($deleted < 1) {
                throw new \RuntimeException('Supplier could not be deleted.');
            }
            self::audit('SUPPLIER_DELETE', 'Supplier', $supplierId, 'Supplier permanently deleted');
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    public static function customer(int $customerId): ?array
    {
        return Database::fetchOne(
            'SELECT CustomerID, CustomerCode, FullName, Email, Phone, [Address], LoyaltyPoints, IsActive
             FROM dbo.Customers_060 WHERE CustomerID = :CustomerID',
            ['CustomerID' => $customerId]
        );
    }

    public static function updateCustomer(
        int $customerId,
        string $fullName,
        string $email,
        ?string $phone,
        ?string $address
    ): void {
        Database::execute(
            'UPDATE dbo.Customers_060
             SET FullName = :FullName, Email = :Email, Phone = :Phone, [Address] = :Address
             WHERE CustomerID = :CustomerID',
            [
                'CustomerID' => $customerId,
                'FullName' => $fullName,
                'Email' => $email,
                'Phone' => $phone,
                'Address' => $address,
            ]
        );
        Database::execute(
            'EXEC dbo.sp_WriteAudit_060 @Action = :Action, @EntityType = :EntityType,
             @EntityID = :EntityID, @Details = :Details',
            [
                'Action' => 'CUSTOMER_UPDATE',
                'EntityType' => 'Customer',
                'EntityID' => $customerId,
                'Details' => $fullName,
            ]
        );
    }

    public static function deactivateCustomer(int $customerId): void
    {
        Database::execute(
            'UPDATE dbo.Customers_060 SET IsActive = 0 WHERE CustomerID = :CustomerID',
            ['CustomerID' => $customerId]
        );
        Database::execute(
            'EXEC dbo.sp_WriteAudit_060 @Action = :Action, @EntityType = :EntityType,
             @EntityID = :EntityID, @Details = :Details',
            [
                'Action' => 'CUSTOMER_DEACTIVATE',
                'EntityType' => 'Customer',
                'EntityID' => $customerId,
                'Details' => 'Customer deactivated',
            ]
        );
    }

    public static function activateCustomer(int $customerId): void
    {
        Database::execute(
            'UPDATE dbo.Customers_060 SET IsActive = 1 WHERE CustomerID = :CustomerID',
            ['CustomerID' => $customerId]
        );
    }

    public static function permanentlyDeleteCustomer(int $customerId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $customer = Database::fetchOne(
                'SELECT CustomerID FROM dbo.Customers_060 WHERE CustomerID = :CustomerID',
                ['CustomerID' => $customerId]
            );
            if ($customer === null) {
                throw new \RuntimeException('Customer not found.');
            }

            $hasHistory = Database::fetchOne(
                'SELECT TOP 1 SaleID AS RecordID
                 FROM dbo.Sales_060 WHERE CustomerID = :SaleCustomerID
                 UNION ALL
                 SELECT TOP 1 ReturnID AS RecordID
                 FROM dbo.Returns_060 WHERE CustomerID = :ReturnCustomerID',
                ['SaleCustomerID' => $customerId, 'ReturnCustomerID' => $customerId]
            );
            if ($hasHistory !== null) {
                throw new \RuntimeException('Customer has sales or return history. Deactivate it instead.');
            }

            $deleted = Database::execute(
                'DELETE FROM dbo.Customers_060 WHERE CustomerID = :CustomerID',
                ['CustomerID' => $customerId]
            );
            if ($deleted < 1) {
                throw new \RuntimeException('Customer could not be deleted.');
            }
            self::audit('CUSTOMER_DELETE', 'Customer', $customerId, 'Customer permanently deleted');
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }

    public static function categories(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetCategories_060');
    }

    public static function suppliers(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetSuppliers_060');
    }

    public static function suppliersAll(): array
    {
        return Database::fetchAll(
            'SELECT SupplierID, SupplierCode, CompanyName AS SupplierName, ContactName, Phone, Email,
                    [Address], IsActive
             FROM dbo.Suppliers_060 ORDER BY CompanyName'
        );
    }

    public static function customers(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetCustomers_060');
    }

    public static function customersAll(): array
    {
        return Database::fetchAll(
            'SELECT CustomerID, CustomerCode, FullName, Email, Phone, [Address], LoyaltyPoints, IsActive
             FROM dbo.Customers_060 ORDER BY FullName'
        );
    }

    public static function inventoryAll(string $search = '', bool $lowStockOnly = false): array
    {
        $rows = Database::fetchAll(
            'EXEC dbo.sp_GetInventoryOverview_060 @SearchText = :Search, @LowStockOnly = :LowStock',
            ['Search' => $search !== '' ? $search : null, 'LowStock' => $lowStockOnly ? 1 : 0]
        );
        return $rows;
    }

    public static function employees(): array
    {
        return Database::fetchAll('EXEC dbo.sp_GetEmployees_060');
    }

    public static function employeesAll(): array
    {
        return Database::fetchAll(
            'SELECT EmployeeID, EmployeeCode, FullName, Position, Department, Phone, Email, HireDate, IsActive
             FROM dbo.Employees_060 ORDER BY FullName'
        );
    }

    public static function employee(int $employeeId): ?array
    {
        return Database::fetchOne(
            'SELECT EmployeeID, EmployeeCode, FullName, Position, Department, Phone, Email, HireDate, IsActive
             FROM dbo.Employees_060 WHERE EmployeeID = :EmployeeID',
            ['EmployeeID' => $employeeId]
        );
    }

    public static function updateEmployee(
        int $employeeId,
        string $fullName,
        string $position,
        ?string $department,
        ?string $phone,
        ?string $email,
        string $hireDate
    ): void {
        Database::execute(
            'UPDATE dbo.Employees_060
             SET FullName = :FullName, Position = :Position, Department = :Department,
                 Phone = :Phone, Email = :Email, HireDate = :HireDate
             WHERE EmployeeID = :EmployeeID',
            [
                'EmployeeID' => $employeeId,
                'FullName' => $fullName,
                'Position' => $position,
                'Department' => $department,
                'Phone' => $phone,
                'Email' => $email !== '' ? $email : null,
                'HireDate' => $hireDate,
            ]
        );
        self::audit('EMPLOYEE_UPDATE', 'Employee', $employeeId, $fullName);
    }

    public static function deactivateEmployee(int $employeeId): void
    {
        Database::execute(
            'UPDATE dbo.Employees_060 SET IsActive = 0 WHERE EmployeeID = :EmployeeID',
            ['EmployeeID' => $employeeId]
        );
        self::audit('EMPLOYEE_DEACTIVATE', 'Employee', $employeeId, 'Employee deactivated');
    }

    public static function activateEmployee(int $employeeId): void
    {
        Database::execute(
            'UPDATE dbo.Employees_060 SET IsActive = 1 WHERE EmployeeID = :EmployeeID',
            ['EmployeeID' => $employeeId]
        );
        self::audit('EMPLOYEE_ACTIVATE', 'Employee', $employeeId, 'Employee activated');
    }

    public static function permanentlyDeleteEmployee(int $employeeId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $employee = Database::fetchOne(
                'SELECT EmployeeID FROM dbo.Employees_060 WHERE EmployeeID = :EmployeeID',
                ['EmployeeID' => $employeeId]
            );
            if ($employee === null) {
                throw new \RuntimeException('Employee not found.');
            }

            $hasHistory = Database::fetchOne(
                'SELECT TOP 1 SaleID AS RecordID
                 FROM dbo.Sales_060 WHERE EmployeeID = :SaleEmployeeID
                 UNION ALL
                 SELECT TOP 1 ReturnID AS RecordID
                 FROM dbo.Returns_060 WHERE EmployeeID = :ReturnEmployeeID',
                ['SaleEmployeeID' => $employeeId, 'ReturnEmployeeID' => $employeeId]
            );
            if ($hasHistory !== null) {
                throw new \RuntimeException('Employee has sales or return history. Deactivate it instead.');
            }

            $deleted = Database::execute(
                'DELETE FROM dbo.Employees_060 WHERE EmployeeID = :EmployeeID',
                ['EmployeeID' => $employeeId]
            );
            if ($deleted < 1) {
                throw new \RuntimeException('Employee could not be deleted.');
            }
            self::audit('EMPLOYEE_DELETE', 'Employee', $employeeId, 'Employee permanently deleted');
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
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

    public static function user(int $userId): ?array
    {
        return Database::fetchOne(
            'SELECT UserID, Username, Email, FullName, Phone, IsActive, IsLocked
             FROM dbo.Users_060 WHERE UserID = :UserID',
            ['UserID' => $userId]
        );
    }

    public static function updateUser(
        int $userId,
        string $fullName,
        string $username,
        string $email,
        ?string $phone
    ): void {
        Database::execute(
            'UPDATE dbo.Users_060
             SET FullName = :FullName, Username = :Username, Email = :Email,
                 Phone = :Phone, UpdatedAt = SYSDATETIME()
             WHERE UserID = :UserID',
            [
                'UserID' => $userId,
                'FullName' => $fullName,
                'Username' => $username,
                'Email' => $email,
                'Phone' => $phone,
            ]
        );
        self::audit('USER_UPDATE', 'User', $userId, $username);
    }

    public static function deactivateUser(int $userId): void
    {
        $user = self::user($userId);
        if ($user === null) {
            throw new \RuntimeException('User not found.');
        }
        if ((int)($user['IsActive'] ?? 0) !== 1) {
            return;
        }
        self::assertAnotherActiveAdministrator($userId);
        Database::execute(
            'UPDATE dbo.Users_060 SET IsActive = 0, IsLocked = 1 WHERE UserID = :UserID',
            ['UserID' => $userId]
        );
        self::audit('USER_DEACTIVATE', 'User', $userId, 'User deactivated');
    }

    public static function activateUser(int $userId): void
    {
        Database::execute(
            'UPDATE dbo.Users_060
             SET IsActive = 1, IsLocked = 0, FailedLoginAttempts = 0
             WHERE UserID = :UserID',
            ['UserID' => $userId]
        );
        self::audit('USER_ACTIVATE', 'User', $userId, 'User activated');
    }

    public static function permanentlyDeleteUser(int $userId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $user = self::user($userId);
            if ($user === null) {
                throw new \RuntimeException('User not found.');
            }
            self::assertAnotherActiveAdministrator($userId);

            $hasHistory = Database::fetchOne(
                'SELECT TOP 1 UserID AS RecordID FROM dbo.Employees_060 WHERE UserID = :EmployeeUserID
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.Customers_060 WHERE UserID = :CustomerUserID
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.UserRoles_060 WHERE AssignedByUserID = :RoleAssignedByUserID
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.InventoryTransactions_060 WHERE PerformedByUserID = :TransactionPerformedBy
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.InventoryTransactions_060 WHERE ApprovedByUserID = :TransactionApprovedBy
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.Sales_060 WHERE CancelledByUserID = :SaleCancelledBy
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.Payments_060 WHERE ProcessedByUserID = :PaymentProcessedBy
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.Payments_060 WHERE VoidedByUserID = :PaymentVoidedBy
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.Returns_060 WHERE ApprovedByUserID = :ReturnApprovedBy
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.Returns_060 WHERE ProcessedByUserID = :ReturnProcessedBy
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.AuditLogs_060 WHERE UserID = :AuditUserID
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.SensitiveActionApprovals_060 WHERE UserID = :ApprovalUserID
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.SensitiveActionApprovals_060 WHERE ConfirmedByUserID = :ApprovalConfirmedBy
                 UNION ALL
                 SELECT TOP 1 UserID AS RecordID FROM dbo.SystemSettings_060 WHERE UpdatedByUserID = :SettingsUpdatedBy',
                [
                    'EmployeeUserID' => $userId,
                    'CustomerUserID' => $userId,
                    'RoleAssignedByUserID' => $userId,
                    'TransactionPerformedBy' => $userId,
                    'TransactionApprovedBy' => $userId,
                    'SaleCancelledBy' => $userId,
                    'PaymentProcessedBy' => $userId,
                    'PaymentVoidedBy' => $userId,
                    'ReturnApprovedBy' => $userId,
                    'ReturnProcessedBy' => $userId,
                    'AuditUserID' => $userId,
                    'ApprovalUserID' => $userId,
                    'ApprovalConfirmedBy' => $userId,
                    'SettingsUpdatedBy' => $userId,
                ]
            );
            if ($hasHistory !== null) {
                throw new \RuntimeException('User has linked business or audit history. Deactivate it instead.');
            }

            Database::execute(
                'DELETE FROM dbo.UserRoles_060 WHERE UserID = :UserID',
                ['UserID' => $userId]
            );
            $deleted = Database::execute(
                'DELETE FROM dbo.Users_060 WHERE UserID = :UserID',
                ['UserID' => $userId]
            );
            if ($deleted < 1) {
                throw new \RuntimeException('User could not be deleted.');
            }
            self::audit('USER_DELETE', 'User', $userId, 'User permanently deleted');
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
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

    private static function audit(string $action, string $entityType, int $entityId, string $details): void
    {
        Database::execute(
            'EXEC dbo.sp_WriteAudit_060 @Action = :Action, @EntityType = :EntityType,
             @EntityID = :EntityID, @Details = :Details',
            [
                'Action' => $action,
                'EntityType' => $entityType,
                'EntityID' => $entityId,
                'Details' => $details,
            ]
        );
    }

    private static function assertAnotherActiveAdministrator(int $userId): void
    {
        $isAdministrator = Database::fetchOne(
            'SELECT TOP 1 ur.UserID
             FROM dbo.UserRoles_060 ur
             JOIN dbo.Roles_060 r ON r.RoleID = ur.RoleID
             WHERE ur.UserID = :UserID AND ur.IsActive = 1 AND r.IsActive = 1
               AND r.RoleName = N\'Administrator\'',
            ['UserID' => $userId]
        );
        if ($isAdministrator === null) {
            return;
        }

        $anotherAdministrator = Database::fetchOne(
            'SELECT TOP 1 u.UserID
             FROM dbo.Users_060 u
             JOIN dbo.UserRoles_060 ur ON ur.UserID = u.UserID AND ur.IsActive = 1
             JOIN dbo.Roles_060 r ON r.RoleID = ur.RoleID AND r.IsActive = 1
             WHERE u.IsActive = 1 AND u.UserID <> :UserID AND r.RoleName = N\'Administrator\'',
            ['UserID' => $userId]
        );
        if ($anotherAdministrator === null) {
            throw new \RuntimeException('At least one active administrator must remain.');
        }
    }
}
