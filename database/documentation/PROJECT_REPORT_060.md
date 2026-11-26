# Advanced Database System — Project 1 Report

**Domain:** Retail Management System (*Algorithm Hardware Plug*)  
**Database:** `RetailManagementDB` (Microsoft SQL Server)  
**Unique Object Suffix:** `_060`  
**UI Stack:** PHP 8.2 (MVC), HTML5, CSS3, Vanilla JavaScript (`PDO_SQLSRV`)

---

## Section A: Database Setup

### 1. Schema Design & Relationships (`database_schema.sql`)
All tables, constraints, stored procedures, triggers, functions, and roles end with `_060`. The core retail & security tables include:

| Table | Purpose | Key Relationships |
|---|---|---|
| `Roles_060` | Defines application roles (`Administrator`, `Manager`, `Employee`, `Customer`) | Referenced by `UserRoles_060` |
| `Users_060` | Stores staff and customer login credentials (`PasswordHash`, `IsLocked`, `FailedLoginAttempts`) | Referenced by `UserRoles_060`, `Employees_060`, `Customers_060`, `AuditLogs_060` |
| `UserRoles_060` | Many-to-many junction mapping users to roles | `FK` to `Users_060` & `Roles_060` |
| `Categories_060` & `Suppliers_060` | Product classification and supplier master records | Referenced by `Products_060` |
| `Products_060` | Hardware SKU catalog (`UnitPrice`, `CostPrice`, `ReorderLevel`) | `FK` to `Categories_060` & `Suppliers_060` |
| `Inventory_060` | Current stock on hand (`QuantityOnHand`, `QuantityReserved`) | 1:1 `FK` to `Products_060` |
| `InventoryTransactions_060` | Immutable ledger of every `SALE`, `RETURN`, `ADJUSTMENT_IN`, `ADJUSTMENT_OUT` | `FK` to `Products_060` & `Users_060` |
| `Customers_060` & `Employees_060` | Customer profiles and staff directory | Optional `FK` to `Users_060` |
| `Sales_060` & `SaleItems_060` | Sales headers and line items | `FK` to `Customers_060`, `Employees_060`, `Products_060` |
| `Payments_060` | Payment records (`CASH`, `CARD`, `BANK_TRANSFER`, `OTHER`) | `FK` to `Sales_060` |
| `Returns_060` & `ReturnItems_060` | Approved customer returns and restocked line items | `FK` to `Sales_060`, `SaleItems_060`, `Employees_060` |
| `SensitiveActionRequests_060` & `AuditLogs_060` | Two-step approval tokens and tamper-evident audit trail | `FK` to `Users_060` |

**Why these tables and relationships are necessary:**
- Normalizing `Sales_060` / `SaleItems_060` and `Returns_060` / `ReturnItems_060` prevents data duplication and supports multi-item carts and partial returns.
- Separating `Inventory_060` from `InventoryTransactions_060` provides both O(1) stock-level lookups and a full historical ledger of every stock movement.
- Foreign keys enforce referential integrity (e.g., preventing sales of non-existent products or orphaned return items).

### 2. User Roles & Sensitive Data Restrictions
- **Administrator (`rms_admin_060`):** Full schema access, user/role administration (`sp_AssignUserRole_060`), audit trail access (`sp_GetAuditTrail_060`), and critical-action approval.
- **Manager (`rms_manager_060`):** Inventory management (`sp_RegisterProduct_060`, `sp_UpdateInventory_060`), sales, returns/refunds (`sp_ProcessCartReturn_060`), and sales reports (`sp_GenerateSalesReport_060`). Restricted from `Users_060` modifications, role assignments, and audit logs.
- **Employee (`rms_staff_060`):** POS sales (`sp_ProcessCartSale_060`), customer registration (`sp_RegisterCustomer_060`), and read-only inventory/movement views. Restricted from stock reductions, refunds, reports, and user admin.
- **Customer:** Read-only product catalog (`sp_GetProductCatalog_060`) and personal purchase history (`sp_GetMySales_060`), enforced via `SESSION_CONTEXT('AppUserID')`.

---

## Section B: Access Control (`GRANT`, `REVOKE`, `RBAC`)

### 3. `GRANT` Command & `WITH GRANT OPTION`
- `rms_admin_060` is granted full `SELECT, INSERT, UPDATE, DELETE` and `EXECUTE` on `SCHEMA::dbo`.
- `rms_manager_060` is granted `SELECT, INSERT, UPDATE, DELETE` on operational tables (`Products_060`, `Inventory_060`, `Sales_060`, etc.), `SELECT` only on `AuditLogs_060`, and `SELECT` on `Products_060` `WITH GRANT OPTION` so managers can delegate read-only catalog visibility to temporary staff.
- Application procedures use explicit `GRANT EXECUTE` per role (least privilege).

### 4. `REVOKE` Command
- A temporary user `rms_demo_temp_060` is granted `SELECT` on `dbo.Products_060`, then immediately revoked via:
  ```sql
  REVOKE SELECT ON dbo.Products_060 FROM rms_demo_temp_060;
  ```
- Once revoked, any `SELECT` attempt by `rms_demo_temp_060` fails with SQL Server permission-denied error `229`.

### 5. Role-Based Access Control (RBAC)
- Three SQL Server database roles (`rms_admin_060`, `rms_manager_060`, `rms_staff_060`) enforce object-level permissions, while `sp_SetAppContext_060` + `sp_AssertAppRole_060` enforce application-session role checks inside every stored procedure.
- **Assigning / removing users from a role:**
  - Database level: `ALTER ROLE rms_manager_060 ADD MEMBER [user];` / `ALTER ROLE rms_manager_060 DROP MEMBER [user];`
  - Application level: `EXEC dbo.sp_AssignUserRole_060 ...` (requires a critical-action approval token).

---

## Section C: Stored Procedures, Triggers, Functions & `IF-ELSE`

### 6. Stored Procedures
- `sp_RegisterProduct_060`: Validates SKU uniqueness and non-negative pricing, inserts into `Products_060`, initializes `Inventory_060`, and writes an audit record.
- `sp_UpdateInventory_060`: Atomically adjusts `Inventory_060`, enforces approval tokens on `ADJUSTMENT_OUT`, and logs to `InventoryTransactions_060`.
- `sp_ProcessCartSale_060` / `sp_ProcessSale_060`: Validates stock, computes tax from `SystemSettings_060`, creates `Sales_060` + `SaleItems_060` + `Payments_060`, and returns `@NewSaleID` and `@SaleNumberOut`.
- `sp_ProcessCartReturn_060` / `sp_ProcessReturn_060`: Validates remaining returnable quantities and approval token, records refund, and restocks inventory.

### 7. Triggers
- `trg_SaleItems_UpdateStock_060` (`AFTER INSERT` on `SaleItems_060`): Automatically deducts `QuantityOnHand` and logs a `SALE` transaction.
- `trg_ReturnItems_Restock_060` (`AFTER INSERT` on `ReturnItems_060`): Automatically restores `QuantityOnHand` and logs a `RETURN` transaction.
- `trg_PreventInvalidStockOperation_060` (`AFTER UPDATE` on `Inventory_060`): Rolls back any operation that would leave `QuantityOnHand < 0`.
- `trg_AuditInventoryTransactions_060`, `trg_AuditProducts_060`, `trg_AuditUsers_060`: Automatically record `AuditLogs_060` entries even if changes occur outside stored procedures.

### 8. Functions
- **Scalar Function:** `dbo.fn_CalculateTotal_060(@UnitPrice, @Quantity, @DiscountAmount)` — reusable line-total calculation across queries and reports.
- **Inline Table-Valued Functions:**
  - `dbo.fn_GetProductsByCategory_060(@CategoryID)` — reusable filtered product + stock view.
  - `dbo.fn_GetSalesByDateRange_060(@FromDate, @ToDate)` — reusable sales summary table for reporting.

### 9. `IF-ELSE` Conditional Logic
- Implemented inside business procedures (`sp_ProcessSale_060`, `sp_UpdateInventory_060`, `sp_AssignUserRole_060`) and demonstrated standalone in **Section 19** (`dbo.sp_EvaluateCustomerDiscountTier_060`), which evaluates `PLATINUM`, `GOLD`, `SILVER`, `STANDARD`, and `NONE` tiers across multiple test `@CustomerID` values.

---

## Section D: Security, Testing, Audit & Backup

### 10. Simulating Unauthorized Access (Section 14 of `database_schema.sql`)
Five live `EXECUTE AS` / `SESSION_CONTEXT` tests verify that:
1. Staff (`rms_demo_staff_060`) cannot `SELECT` from `Users_060`.
2. Managers (`rms_demo_manager_060`) cannot execute `sp_AssignUserRole_060` or `UPDATE Users_060`.
3. Customers cannot view another customer's history via `sp_GetMySales_060`.
4. Fabricated approval tokens are rejected by `sp_ConfirmSensitiveAction_060`.

### 11. Audit and Logging
- `sp_WriteAudit_060`, `sp_RecordAuthAudit_060`, and audit triggers populate `AuditLogs_060` with user ID, role, IP address, user agent, action, entity, and outcome.
- Retrieved via `EXEC dbo.sp_GetAuditTrail_060 @FromDate, @ToDate, @UserID;` (Administrator-only) and viewed in the UI at `?route=reports/audit`.

### 12. Backup and Recovery
- Provided in `database/backup/backup_restore_060.sql` (Full backup, Differential backup, `RESTORE VERIFYONLY`, non-destructive side-by-side test restore, and full recovery).
- **Importance in Access Control:** Regular, verified backups protect data availability and integrity against privilege misuse, accidental deletions, or ransomware, while ensuring security metadata (roles, permissions, and audit logs) can be restored to a known-good state.

---

## Section E: User Interface (PHP MVC Web Application)

- **Authentication & Session Context:** `?route=login` authenticates against `sp_AuthenticateUser_060` (`password_verify`), locks accounts after 5 failed attempts, and sets SQL Server `SESSION_CONTEXT` on every request via `Auth::applyContext()`.
- **Role-Based UI Views:**
  - **Administrator:** Full access including `?route=admin/users` (two-factor critical-action role assignment) and `?route=reports/audit`.
  - **Manager:** Inventory (`?route=inventory`, `?route=inventory/create`, `?route=inventory/adjust`), POS (`?route=pos`), Returns (`?route=returns/create`), and Sales Reports (`?route=reports` with CSV export).
  - **Employee:** Inventory view, Stock Movements, and POS (`?route=pos`). Restricted from stock reductions, returns, reports, and admin.
  - **Customer (`customer_060`):** Product Catalog (`?route=catalog`) and My Purchases (`?route=my-sales`). Restricted from all internal staff/admin modules.
