/*==============================================================================
  ALGORITHM HARDWARE PLUG - RETAIL MANAGEMENT SYSTEM
  database/database_schema.sql
  Database: RetailManagementDB | All user objects suffixed _060

  EXECUTION GUIDE
  - Run once:       Section 1 creates the database only when missing.
  - Safely rerun:   Sections 2-13 use IF NOT EXISTS / CREATE OR ALTER and skip
                    existing sample rows. Re-running will not duplicate data.
  - Demonstrations: Section 14 changes no data but intentionally produces
                    expected permission errors (caught and printed).
  - Environment:    Section 15 contains BACKUP/RESTORE examples that require a
                    valid local path and SQL Server service-account permission.
                    They are commented out and are NOT executed automatically.
  - Verification:   Section 16 contains read-only checks. Data-changing sample
                    procedure calls there are commented out.

  Verified environment: SQL Server 2025 default instance MSSQLSERVER on
  Desktop-Pmod3, Mixed Mode, TCP 1433. Run in SSMS connected with an account
  that may CREATE DATABASE (or run Section 1 as setup).
==============================================================================*/

SET NOCOUNT ON;
SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

/*==============================================================================
  SECTION 1 - DATABASE CREATION (run once; safe because of the IF check)
==============================================================================*/
IF DB_ID(N'RetailManagementDB') IS NULL
BEGIN
    EXEC sys.sp_executesql N'CREATE DATABASE [RetailManagementDB];';
END
GO

USE [RetailManagementDB];
GO

PRINT 'SECTION 1 OK - Database [RetailManagementDB] is available.';
GO

/*==============================================================================
  SECTION 2 - SCHEMA AND TABLE CREATION (safely rerunnable)
==============================================================================*/

IF OBJECT_ID(N'dbo.Roles_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Roles_060
    (
        RoleID      int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Roles_060 PRIMARY KEY,
        RoleName    nvarchar(50) NOT NULL
            CONSTRAINT UQ_Roles_060_RoleName UNIQUE,
        [Description] nvarchar(255) NULL,
        IsActive    bit NOT NULL
            CONSTRAINT DF_Roles_060_IsActive DEFAULT (1),
        CreatedAt   datetime2(0) NOT NULL
            CONSTRAINT DF_Roles_060_CreatedAt DEFAULT (SYSDATETIME())
    );
END
GO

IF OBJECT_ID(N'dbo.Users_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Users_060
    (
        UserID              int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Users_060 PRIMARY KEY,
        Username            nvarchar(50) NOT NULL
            CONSTRAINT UQ_Users_060_Username UNIQUE,
        Email               nvarchar(256) NOT NULL
            CONSTRAINT UQ_Users_060_Email UNIQUE,
        PasswordHash        nvarchar(255) NOT NULL,
        FullName            nvarchar(100) NOT NULL,
        Phone               nvarchar(30) NULL,
        IsActive            bit NOT NULL
            CONSTRAINT DF_Users_060_IsActive DEFAULT (1),
        IsLocked            bit NOT NULL
            CONSTRAINT DF_Users_060_IsLocked DEFAULT (0),
        FailedLoginAttempts int NOT NULL
            CONSTRAINT DF_Users_060_FailedLoginAttempts DEFAULT (0)
            CONSTRAINT CK_Users_060_FailedLoginAttempts CHECK (FailedLoginAttempts >= 0),
        LastLoginAt         datetime2(0) NULL,
        PasswordChangedAt   datetime2(0) NOT NULL
            CONSTRAINT DF_Users_060_PasswordChangedAt DEFAULT (SYSDATETIME()),
        CreatedAt           datetime2(0) NOT NULL
            CONSTRAINT DF_Users_060_CreatedAt DEFAULT (SYSDATETIME()),
        UpdatedAt           datetime2(0) NULL
    );
END
GO

IF OBJECT_ID(N'dbo.UserRoles_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.UserRoles_060
    (
        UserID          int NOT NULL,
        RoleID          int NOT NULL,
        AssignedAt      datetime2(0) NOT NULL
            CONSTRAINT DF_UserRoles_060_AssignedAt DEFAULT (SYSDATETIME()),
        AssignedByUserID int NULL,
        IsActive        bit NOT NULL
            CONSTRAINT DF_UserRoles_060_IsActive DEFAULT (1),
        CONSTRAINT PK_UserRoles_060 PRIMARY KEY (UserID, RoleID)
    );
END
GO

IF OBJECT_ID(N'dbo.Employees_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Employees_060
    (
        EmployeeID   int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Employees_060 PRIMARY KEY,
        UserID       int NULL,
        EmployeeCode nvarchar(20) NOT NULL
            CONSTRAINT UQ_Employees_060_EmployeeCode UNIQUE,
        FullName     nvarchar(100) NOT NULL,
        Position     nvarchar(50) NOT NULL,
        Department   nvarchar(50) NULL,
        Phone        nvarchar(30) NULL,
        Email        nvarchar(256) NULL,
        HireDate     date NOT NULL,
        IsActive     bit NOT NULL
            CONSTRAINT DF_Employees_060_IsActive DEFAULT (1)
    );
END
GO

IF OBJECT_ID(N'dbo.Customers_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Customers_060
    (
        CustomerID    int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Customers_060 PRIMARY KEY,
        UserID        int NULL,
        CustomerCode  nvarchar(20) NOT NULL
            CONSTRAINT UQ_Customers_060_CustomerCode UNIQUE,
        FullName      nvarchar(100) NOT NULL,
        Email         nvarchar(256) NOT NULL
            CONSTRAINT UQ_Customers_060_Email UNIQUE,
        Phone         nvarchar(30) NULL,
        [Address]     nvarchar(255) NULL,
        LoyaltyPoints int NOT NULL
            CONSTRAINT DF_Customers_060_LoyaltyPoints DEFAULT (0)
            CONSTRAINT CK_Customers_060_LoyaltyPoints CHECK (LoyaltyPoints >= 0),
        IsActive      bit NOT NULL
            CONSTRAINT DF_Customers_060_IsActive DEFAULT (1),
        CreatedAt     datetime2(0) NOT NULL
            CONSTRAINT DF_Customers_060_CreatedAt DEFAULT (SYSDATETIME())
    );
END
GO

IF OBJECT_ID(N'dbo.Suppliers_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Suppliers_060
    (
        SupplierID   int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Suppliers_060 PRIMARY KEY,
        SupplierCode nvarchar(20) NOT NULL
            CONSTRAINT UQ_Suppliers_060_SupplierCode UNIQUE,
        CompanyName  nvarchar(100) NOT NULL,
        ContactName  nvarchar(100) NULL,
        Phone        nvarchar(30) NULL,
        Email        nvarchar(256) NULL,
        [Address]    nvarchar(255) NULL,
        IsActive     bit NOT NULL
            CONSTRAINT DF_Suppliers_060_IsActive DEFAULT (1)
    );
END
GO

IF OBJECT_ID(N'dbo.Categories_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Categories_060
    (
        CategoryID       int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Categories_060 PRIMARY KEY,
        CategoryName     nvarchar(80) NOT NULL
            CONSTRAINT UQ_Categories_060_CategoryName UNIQUE,
        [Description]    nvarchar(255) NULL,
        ParentCategoryID int NULL,
        IsActive         bit NOT NULL
            CONSTRAINT DF_Categories_060_IsActive DEFAULT (1)
    );
END
GO

IF OBJECT_ID(N'dbo.Products_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Products_060
    (
        ProductID    int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Products_060 PRIMARY KEY,
        SKU          nvarchar(30) NOT NULL
            CONSTRAINT UQ_Products_060_SKU UNIQUE,
        Barcode      nvarchar(50) NULL
            CONSTRAINT UQ_Products_060_Barcode UNIQUE,
        ProductName  nvarchar(150) NOT NULL,
        CategoryID   int NOT NULL,
        SupplierID   int NOT NULL,
        UnitPrice    decimal(18,2) NOT NULL
            CONSTRAINT CK_Products_060_UnitPrice CHECK (UnitPrice >= 0),
        CostPrice    decimal(18,2) NOT NULL
            CONSTRAINT CK_Products_060_CostPrice CHECK (CostPrice >= 0),
        ReorderLevel int NOT NULL
            CONSTRAINT DF_Products_060_ReorderLevel DEFAULT (5)
            CONSTRAINT CK_Products_060_ReorderLevel CHECK (ReorderLevel >= 0),
        IsActive     bit NOT NULL
            CONSTRAINT DF_Products_060_IsActive DEFAULT (1),
        CreatedAt    datetime2(0) NOT NULL
            CONSTRAINT DF_Products_060_CreatedAt DEFAULT (SYSDATETIME())
    );
END
GO

IF OBJECT_ID(N'dbo.Inventory_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Inventory_060
    (
        InventoryID       int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Inventory_060 PRIMARY KEY,
        ProductID         int NOT NULL
            CONSTRAINT UQ_Inventory_060_ProductID UNIQUE,
        QuantityOnHand    int NOT NULL
            CONSTRAINT DF_Inventory_060_QuantityOnHand DEFAULT (0)
            CONSTRAINT CK_Inventory_060_QuantityOnHand CHECK (QuantityOnHand >= 0),
        QuantityReserved  int NOT NULL
            CONSTRAINT DF_Inventory_060_QuantityReserved DEFAULT (0)
            CONSTRAINT CK_Inventory_060_QuantityReserved CHECK (QuantityReserved >= 0),
        WarehouseLocation nvarchar(50) NULL,
        LastUpdatedAt     datetime2(0) NOT NULL
            CONSTRAINT DF_Inventory_060_LastUpdatedAt DEFAULT (SYSDATETIME()),
        RowVersion        rowversion
    );
END
GO

IF OBJECT_ID(N'dbo.InventoryTransactions_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.InventoryTransactions_060
    (
        TransactionID     int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_InventoryTransactions_060 PRIMARY KEY,
        ProductID         int NOT NULL,
        TransactionType   nvarchar(30) NOT NULL
            CONSTRAINT CK_InventoryTransactions_060_Type CHECK (TransactionType IN
                (N'PURCHASE', N'SALE', N'RETURN', N'ADJUSTMENT_IN', N'ADJUSTMENT_OUT', N'VOID')),
        QuantityChange    int NOT NULL
            CONSTRAINT CK_InventoryTransactions_060_Change CHECK (QuantityChange <> 0),
        QuantityBefore    int NOT NULL
            CONSTRAINT CK_InventoryTransactions_060_Before CHECK (QuantityBefore >= 0),
        QuantityAfter     int NOT NULL
            CONSTRAINT CK_InventoryTransactions_060_After CHECK (QuantityAfter >= 0),
        ReferenceType     nvarchar(30) NULL,
        ReferenceID       int NULL,
        PerformedByUserID int NOT NULL,
        ApprovedByUserID  int NULL,
        ApprovalReference nvarchar(100) NULL,
        Reason            nvarchar(255) NULL,
        TransactionDate   datetime2(0) NOT NULL
            CONSTRAINT DF_InventoryTransactions_060_Date DEFAULT (SYSDATETIME())
    );
END
GO

IF OBJECT_ID(N'dbo.Sales_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Sales_060
    (
        SaleID             int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Sales_060 PRIMARY KEY,
        SaleNumber         nvarchar(30) NOT NULL
            CONSTRAINT UQ_Sales_060_SaleNumber UNIQUE,
        CustomerID         int NULL,
        EmployeeID         int NOT NULL,
        SaleDate           datetime2(0) NOT NULL
            CONSTRAINT DF_Sales_060_SaleDate DEFAULT (SYSDATETIME()),
        SubTotal           decimal(18,2) NOT NULL
            CONSTRAINT DF_Sales_060_SubTotal DEFAULT (0)
            CONSTRAINT CK_Sales_060_SubTotal CHECK (SubTotal >= 0),
        DiscountAmount     decimal(18,2) NOT NULL
            CONSTRAINT DF_Sales_060_Discount DEFAULT (0)
            CONSTRAINT CK_Sales_060_Discount CHECK (DiscountAmount >= 0),
        TaxAmount          decimal(18,2) NOT NULL
            CONSTRAINT DF_Sales_060_Tax DEFAULT (0)
            CONSTRAINT CK_Sales_060_Tax CHECK (TaxAmount >= 0),
        TotalAmount        decimal(18,2) NOT NULL
            CONSTRAINT DF_Sales_060_Total DEFAULT (0)
            CONSTRAINT CK_Sales_060_Total CHECK (TotalAmount >= 0),
        AmountPaid         decimal(18,2) NOT NULL
            CONSTRAINT DF_Sales_060_Paid DEFAULT (0)
            CONSTRAINT CK_Sales_060_Paid CHECK (AmountPaid >= 0),
        ChangeAmount       decimal(18,2) NOT NULL
            CONSTRAINT DF_Sales_060_Change DEFAULT (0)
            CONSTRAINT CK_Sales_060_Change CHECK (ChangeAmount >= 0),
        Status             nvarchar(20) NOT NULL
            CONSTRAINT DF_Sales_060_Status DEFAULT (N'COMPLETED')
            CONSTRAINT CK_Sales_060_Status CHECK (Status IN
                (N'PENDING', N'COMPLETED', N'CANCELLED', N'RETURNED')),
        Notes              nvarchar(500) NULL,
        CancelledByUserID  int NULL,
        CancelledAt        datetime2(0) NULL,
        CancellationReason nvarchar(255) NULL
    );
END
GO

IF OBJECT_ID(N'dbo.SaleItems_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.SaleItems_060
    (
        SaleItemID      int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_SaleItems_060 PRIMARY KEY,
        SaleID          int NOT NULL,
        ProductID       int NOT NULL,
        Quantity        int NOT NULL
            CONSTRAINT CK_SaleItems_060_Quantity CHECK (Quantity > 0),
        UnitPrice       decimal(18,2) NOT NULL
            CONSTRAINT CK_SaleItems_060_UnitPrice CHECK (UnitPrice >= 0),
        DiscountAmount  decimal(18,2) NOT NULL
            CONSTRAINT DF_SaleItems_060_Discount DEFAULT (0)
            CONSTRAINT CK_SaleItems_060_Discount CHECK (DiscountAmount >= 0),
        LineTotal       decimal(18,2) NOT NULL
            CONSTRAINT CK_SaleItems_060_LineTotal CHECK (LineTotal >= 0),
        ReturnedQuantity int NOT NULL
            CONSTRAINT DF_SaleItems_060_ReturnedQuantity DEFAULT (0)
            CONSTRAINT CK_SaleItems_060_ReturnedQuantity CHECK (ReturnedQuantity >= 0)
    );
END
GO

IF OBJECT_ID(N'dbo.Payments_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Payments_060
    (
        PaymentID         int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Payments_060 PRIMARY KEY,
        PaymentNumber     nvarchar(30) NOT NULL
            CONSTRAINT UQ_Payments_060_PaymentNumber UNIQUE,
        SaleID            int NOT NULL,
        Amount            decimal(18,2) NOT NULL
            CONSTRAINT CK_Payments_060_Amount CHECK (Amount > 0),
        PaymentMethod     nvarchar(20) NOT NULL
            CONSTRAINT CK_Payments_060_Method CHECK (PaymentMethod IN
                (N'CASH', N'CARD', N'BANK_TRANSFER', N'OTHER')),
        PaymentDate       datetime2(0) NOT NULL
            CONSTRAINT DF_Payments_060_Date DEFAULT (SYSDATETIME()),
        Status            nvarchar(20) NOT NULL
            CONSTRAINT DF_Payments_060_Status DEFAULT (N'PAID')
            CONSTRAINT CK_Payments_060_Status CHECK (Status IN (N'PAID', N'VOIDED', N'REFUNDED')),
        ReferenceNumber   nvarchar(80) NULL,
        ProcessedByUserID int NOT NULL,
        VoidedByUserID    int NULL,
        VoidedAt          datetime2(0) NULL
    );
END
GO

IF OBJECT_ID(N'dbo.Returns_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Returns_060
    (
        ReturnID          int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_Returns_060 PRIMARY KEY,
        ReturnNumber      nvarchar(30) NOT NULL
            CONSTRAINT UQ_Returns_060_ReturnNumber UNIQUE,
        SaleID            int NOT NULL,
        CustomerID        int NULL,
        EmployeeID        int NOT NULL,
        ReturnDate        datetime2(0) NOT NULL
            CONSTRAINT DF_Returns_060_Date DEFAULT (SYSDATETIME()),
        Reason            nvarchar(255) NOT NULL,
        RefundAmount      decimal(18,2) NOT NULL
            CONSTRAINT CK_Returns_060_Refund CHECK (RefundAmount >= 0),
        Status            nvarchar(20) NOT NULL
            CONSTRAINT DF_Returns_060_Status DEFAULT (N'PENDING')
            CONSTRAINT CK_Returns_060_Status CHECK (Status IN
                (N'PENDING', N'APPROVED', N'COMPLETED', N'REJECTED')),
        ApprovedByUserID  int NULL,
        ApprovalReference nvarchar(100) NULL,
        ProcessedByUserID int NOT NULL
    );
END
GO

IF OBJECT_ID(N'dbo.ReturnItems_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.ReturnItems_060
    (
        ReturnItemID int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_ReturnItems_060 PRIMARY KEY,
        ReturnID     int NOT NULL,
        SaleItemID   int NOT NULL,
        Quantity     int NOT NULL
            CONSTRAINT CK_ReturnItems_060_Quantity CHECK (Quantity > 0),
        RefundAmount decimal(18,2) NOT NULL
            CONSTRAINT CK_ReturnItems_060_Refund CHECK (RefundAmount >= 0),
        [Condition]  nvarchar(20) NOT NULL
            CONSTRAINT DF_ReturnItems_060_Condition DEFAULT (N'GOOD')
            CONSTRAINT CK_ReturnItems_060_Condition CHECK ([Condition] IN (N'GOOD', N'DAMAGED')),
        Restock      bit NOT NULL
            CONSTRAINT DF_ReturnItems_060_Restock DEFAULT (1)
    );
END
GO

IF OBJECT_ID(N'dbo.AuditLogs_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.AuditLogs_060
    (
        AuditLogID             bigint IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_AuditLogs_060 PRIMARY KEY,
        UserID                 int NULL,
        UserRole               nvarchar(50) NULL,
        Action                 nvarchar(100) NOT NULL,
        EntityType             nvarchar(50) NULL,
        EntityID               nvarchar(50) NULL,
        Details                nvarchar(1000) NULL,
        IPAddress              nvarchar(45) NULL,
        UserAgent              nvarchar(300) NULL,
        ConfirmationStatus     nvarchar(20) NOT NULL
            CONSTRAINT DF_AuditLogs_060_Confirmation DEFAULT (N'NOT_REQUIRED')
            CONSTRAINT CK_AuditLogs_060_Confirmation CHECK (ConfirmationStatus IN
                (N'NOT_REQUIRED', N'REQUESTED', N'CONFIRMED', N'CANCELLED')),
        AdditionalVerification nvarchar(30) NOT NULL
            CONSTRAINT DF_AuditLogs_060_Verification DEFAULT (N'NONE')
            CONSTRAINT CK_AuditLogs_060_Verification CHECK (AdditionalVerification IN
                (N'NONE', N'PASSWORD', N'PHRASE', N'ADMIN_APPROVAL', N'PASSWORD_AND_PHRASE')),
        Outcome                nvarchar(20) NOT NULL
            CONSTRAINT DF_AuditLogs_060_Outcome DEFAULT (N'SUCCESS')
            CONSTRAINT CK_AuditLogs_060_Outcome CHECK (Outcome IN
                (N'SUCCESS', N'FAILURE', N'DENIED', N'CANCELLED')),
        OccurredAt             datetime2(0) NOT NULL
            CONSTRAINT DF_AuditLogs_060_OccurredAt DEFAULT (SYSDATETIME())
    );
END
GO

IF OBJECT_ID(N'dbo.SensitiveActions_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.SensitiveActions_060
    (
        ActionKey            nvarchar(80) NOT NULL
            CONSTRAINT PK_SensitiveActions_060 PRIMARY KEY,
        [Description]        nvarchar(255) NOT NULL,
        RiskLevel            nvarchar(20) NOT NULL
            CONSTRAINT CK_SensitiveActions_060_Risk CHECK (RiskLevel IN
                (N'STANDARD', N'ELEVATED', N'CRITICAL')),
        AllowedRoles         nvarchar(200) NOT NULL,
        IsActive             bit NOT NULL
            CONSTRAINT DF_SensitiveActions_060_IsActive DEFAULT (1),
        DefaultExpiryMinutes int NOT NULL
            CONSTRAINT DF_SensitiveActions_060_Expiry DEFAULT (10)
            CONSTRAINT CK_SensitiveActions_060_Expiry CHECK (DefaultExpiryMinutes > 0)
    );
END
GO

IF OBJECT_ID(N'dbo.SensitiveActionApprovals_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.SensitiveActionApprovals_060
    (
        ApprovalID         int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_SensitiveActionApprovals_060 PRIMARY KEY,
        Token              uniqueidentifier NOT NULL
            CONSTRAINT UQ_SensitiveActionApprovals_060_Token UNIQUE
            CONSTRAINT DF_SensitiveActionApprovals_060_Token DEFAULT (NEWID()),
        UserID             int NOT NULL,
        ActionKey          nvarchar(80) NOT NULL,
        EntityType         nvarchar(50) NULL,
        EntityID           nvarchar(50) NULL,
        RiskLevel          nvarchar(20) NOT NULL
            CONSTRAINT CK_SensitiveActionApprovals_060_Risk CHECK (RiskLevel IN
                (N'STANDARD', N'ELEVATED', N'CRITICAL')),
        Status             nvarchar(20) NOT NULL
            CONSTRAINT DF_SensitiveActionApprovals_060_Status DEFAULT (N'PENDING')
            CONSTRAINT CK_SensitiveActionApprovals_060_Status CHECK (Status IN
                (N'PENDING', N'CONFIRMED', N'CANCELLED', N'EXPIRED')),
        ClientRequestID    nvarchar(100) NOT NULL,
        CreatedAt          datetime2(0) NOT NULL
            CONSTRAINT DF_SensitiveActionApprovals_060_CreatedAt DEFAULT (SYSDATETIME()),
        ExpiresAt          datetime2(0) NOT NULL,
        ConfirmedAt        datetime2(0) NULL,
        ConfirmedByUserID  int NULL,
        VerificationMethod nvarchar(30) NULL,
        Details            nvarchar(1000) NULL,
        CONSTRAINT UQ_SensitiveActionApprovals_060_ClientRequest UNIQUE (UserID, ClientRequestID)
    );
END
GO

IF OBJECT_ID(N'dbo.SystemSettings_060', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.SystemSettings_060
    (
        SettingID       int IDENTITY(1,1) NOT NULL
            CONSTRAINT PK_SystemSettings_060 PRIMARY KEY,
        SettingName     nvarchar(80) NOT NULL
            CONSTRAINT UQ_SystemSettings_060_Name UNIQUE,
        SettingValue    nvarchar(500) NOT NULL,
        [Description]   nvarchar(255) NULL,
        IsSensitive     bit NOT NULL
            CONSTRAINT DF_SystemSettings_060_IsSensitive DEFAULT (0),
        UpdatedByUserID int NULL,
        UpdatedAt       datetime2(0) NOT NULL
            CONSTRAINT DF_SystemSettings_060_UpdatedAt DEFAULT (SYSDATETIME())
    );
END
GO

IF TYPE_ID(N'dbo.tt_SaleItem_060') IS NULL
BEGIN
    EXEC sys.sp_executesql N'CREATE TYPE dbo.tt_SaleItem_060 AS TABLE
    (
        ProductID      int NOT NULL,
        Quantity       int NOT NULL,
        UnitPrice      decimal(18,2) NOT NULL,
        DiscountAmount decimal(18,2) NOT NULL
    );';
END
GO

IF TYPE_ID(N'dbo.tt_ReturnItem_060') IS NULL
BEGIN
    EXEC sys.sp_executesql N'CREATE TYPE dbo.tt_ReturnItem_060 AS TABLE
    (
        SaleItemID int NOT NULL,
        Quantity   int NOT NULL
    );';
END
GO

PRINT 'SECTION 2 OK - Tables and table types are available.';
GO

/*==============================================================================
  SECTION 3 - PRIMARY KEYS, FOREIGN KEYS AND RELATIONSHIPS
  PKs were declared in Section 2. FKs and lookup indexes are added here.
==============================================================================*/
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_UserRoles_060_Users'))
BEGIN
    ALTER TABLE dbo.UserRoles_060 ADD CONSTRAINT FK_UserRoles_060_Users
        FOREIGN KEY (UserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_UserRoles_060_Roles'))
BEGIN
    ALTER TABLE dbo.UserRoles_060 ADD CONSTRAINT FK_UserRoles_060_Roles
        FOREIGN KEY (RoleID) REFERENCES dbo.Roles_060(RoleID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_UserRoles_060_AssignedBy'))
BEGIN
    ALTER TABLE dbo.UserRoles_060 ADD CONSTRAINT FK_UserRoles_060_AssignedBy
        FOREIGN KEY (AssignedByUserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Employees_060_Users'))
BEGIN
    ALTER TABLE dbo.Employees_060 ADD CONSTRAINT FK_Employees_060_Users
        FOREIGN KEY (UserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Customers_060_Users'))
BEGIN
    ALTER TABLE dbo.Customers_060 ADD CONSTRAINT FK_Customers_060_Users
        FOREIGN KEY (UserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Categories_060_Parent'))
BEGIN
    ALTER TABLE dbo.Categories_060 ADD CONSTRAINT FK_Categories_060_Parent
        FOREIGN KEY (ParentCategoryID) REFERENCES dbo.Categories_060(CategoryID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Products_060_Categories'))
BEGIN
    ALTER TABLE dbo.Products_060 ADD CONSTRAINT FK_Products_060_Categories
        FOREIGN KEY (CategoryID) REFERENCES dbo.Categories_060(CategoryID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Products_060_Suppliers'))
BEGIN
    ALTER TABLE dbo.Products_060 ADD CONSTRAINT FK_Products_060_Suppliers
        FOREIGN KEY (SupplierID) REFERENCES dbo.Suppliers_060(SupplierID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Inventory_060_Products'))
BEGIN
    ALTER TABLE dbo.Inventory_060 ADD CONSTRAINT FK_Inventory_060_Products
        FOREIGN KEY (ProductID) REFERENCES dbo.Products_060(ProductID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_InventoryTransactions_060_Products'))
BEGIN
    ALTER TABLE dbo.InventoryTransactions_060 ADD CONSTRAINT FK_InventoryTransactions_060_Products
        FOREIGN KEY (ProductID) REFERENCES dbo.Products_060(ProductID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_InventoryTransactions_060_PerformedBy'))
BEGIN
    ALTER TABLE dbo.InventoryTransactions_060 ADD CONSTRAINT FK_InventoryTransactions_060_PerformedBy
        FOREIGN KEY (PerformedByUserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_InventoryTransactions_060_ApprovedBy'))
BEGIN
    ALTER TABLE dbo.InventoryTransactions_060 ADD CONSTRAINT FK_InventoryTransactions_060_ApprovedBy
        FOREIGN KEY (ApprovedByUserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Sales_060_Customers'))
BEGIN
    ALTER TABLE dbo.Sales_060 ADD CONSTRAINT FK_Sales_060_Customers
        FOREIGN KEY (CustomerID) REFERENCES dbo.Customers_060(CustomerID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Sales_060_Employees'))
BEGIN
    ALTER TABLE dbo.Sales_060 ADD CONSTRAINT FK_Sales_060_Employees
        FOREIGN KEY (EmployeeID) REFERENCES dbo.Employees_060(EmployeeID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Sales_060_CancelledBy'))
BEGIN
    ALTER TABLE dbo.Sales_060 ADD CONSTRAINT FK_Sales_060_CancelledBy
        FOREIGN KEY (CancelledByUserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_SaleItems_060_Sales'))
BEGIN
    ALTER TABLE dbo.SaleItems_060 ADD CONSTRAINT FK_SaleItems_060_Sales
        FOREIGN KEY (SaleID) REFERENCES dbo.Sales_060(SaleID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_SaleItems_060_Products'))
BEGIN
    ALTER TABLE dbo.SaleItems_060 ADD CONSTRAINT FK_SaleItems_060_Products
        FOREIGN KEY (ProductID) REFERENCES dbo.Products_060(ProductID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Payments_060_Sales'))
BEGIN
    ALTER TABLE dbo.Payments_060 ADD CONSTRAINT FK_Payments_060_Sales
        FOREIGN KEY (SaleID) REFERENCES dbo.Sales_060(SaleID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Payments_060_ProcessedBy'))
BEGIN
    ALTER TABLE dbo.Payments_060 ADD CONSTRAINT FK_Payments_060_ProcessedBy
        FOREIGN KEY (ProcessedByUserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Payments_060_VoidedBy'))
BEGIN
    ALTER TABLE dbo.Payments_060 ADD CONSTRAINT FK_Payments_060_VoidedBy
        FOREIGN KEY (VoidedByUserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Returns_060_Sales'))
BEGIN
    ALTER TABLE dbo.Returns_060 ADD CONSTRAINT FK_Returns_060_Sales
        FOREIGN KEY (SaleID) REFERENCES dbo.Sales_060(SaleID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Returns_060_Customers'))
BEGIN
    ALTER TABLE dbo.Returns_060 ADD CONSTRAINT FK_Returns_060_Customers
        FOREIGN KEY (CustomerID) REFERENCES dbo.Customers_060(CustomerID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Returns_060_Employees'))
BEGIN
    ALTER TABLE dbo.Returns_060 ADD CONSTRAINT FK_Returns_060_Employees
        FOREIGN KEY (EmployeeID) REFERENCES dbo.Employees_060(EmployeeID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Returns_060_ApprovedBy'))
BEGIN
    ALTER TABLE dbo.Returns_060 ADD CONSTRAINT FK_Returns_060_ApprovedBy
        FOREIGN KEY (ApprovedByUserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Returns_060_ProcessedBy'))
BEGIN
    ALTER TABLE dbo.Returns_060 ADD CONSTRAINT FK_Returns_060_ProcessedBy
        FOREIGN KEY (ProcessedByUserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_ReturnItems_060_Returns'))
BEGIN
    ALTER TABLE dbo.ReturnItems_060 ADD CONSTRAINT FK_ReturnItems_060_Returns
        FOREIGN KEY (ReturnID) REFERENCES dbo.Returns_060(ReturnID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_ReturnItems_060_SaleItems'))
BEGIN
    ALTER TABLE dbo.ReturnItems_060 ADD CONSTRAINT FK_ReturnItems_060_SaleItems
        FOREIGN KEY (SaleItemID) REFERENCES dbo.SaleItems_060(SaleItemID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_AuditLogs_060_Users'))
BEGIN
    ALTER TABLE dbo.AuditLogs_060 ADD CONSTRAINT FK_AuditLogs_060_Users
        FOREIGN KEY (UserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Approvals_060_Users'))
BEGIN
    ALTER TABLE dbo.SensitiveActionApprovals_060 ADD CONSTRAINT FK_Approvals_060_Users
        FOREIGN KEY (UserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Approvals_060_ConfirmedBy'))
BEGIN
    ALTER TABLE dbo.SensitiveActionApprovals_060 ADD CONSTRAINT FK_Approvals_060_ConfirmedBy
        FOREIGN KEY (ConfirmedByUserID) REFERENCES dbo.Users_060(UserID);
END
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'dbo.FK_Settings_060_Users'))
BEGIN
    ALTER TABLE dbo.SystemSettings_060 ADD CONSTRAINT FK_Settings_060_Users
        FOREIGN KEY (UpdatedByUserID) REFERENCES dbo.Users_060(UserID);
END
GO

IF EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'dbo.UQ_Employees_060_UserID') AND type = N'UQ')
    ALTER TABLE dbo.Employees_060 DROP CONSTRAINT UQ_Employees_060_UserID;
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.Employees_060') AND name = N'UX_Employees_060_UserID')
    CREATE UNIQUE INDEX UX_Employees_060_UserID ON dbo.Employees_060(UserID) WHERE UserID IS NOT NULL;
GO
IF EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'dbo.UQ_Customers_060_UserID') AND type = N'UQ')
    ALTER TABLE dbo.Customers_060 DROP CONSTRAINT UQ_Customers_060_UserID;
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.Customers_060') AND name = N'UX_Customers_060_UserID')
    CREATE UNIQUE INDEX UX_Customers_060_UserID ON dbo.Customers_060(UserID) WHERE UserID IS NOT NULL;
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.Sales_060') AND name = N'IX_Sales_060_Date_Status')
    CREATE INDEX IX_Sales_060_Date_Status ON dbo.Sales_060(SaleDate, Status);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.SaleItems_060') AND name = N'IX_SaleItems_060_Sale_Product')
    CREATE INDEX IX_SaleItems_060_Sale_Product ON dbo.SaleItems_060(SaleID, ProductID);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.Payments_060') AND name = N'IX_Payments_060_Sale')
    CREATE INDEX IX_Payments_060_Sale ON dbo.Payments_060(SaleID);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.Returns_060') AND name = N'IX_Returns_060_Sale_Status')
    CREATE INDEX IX_Returns_060_Sale_Status ON dbo.Returns_060(SaleID, Status);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.InventoryTransactions_060') AND name = N'IX_InvTx_060_Product_Date')
    CREATE INDEX IX_InvTx_060_Product_Date ON dbo.InventoryTransactions_060(ProductID, TransactionDate);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.AuditLogs_060') AND name = N'IX_AuditLogs_060_Date_User')
    CREATE INDEX IX_AuditLogs_060_Date_User ON dbo.AuditLogs_060(OccurredAt, UserID);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.Products_060') AND name = N'IX_Products_060_Category_Supplier')
    CREATE INDEX IX_Products_060_Category_Supplier ON dbo.Products_060(CategoryID, SupplierID);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(N'dbo.SensitiveActionApprovals_060') AND name = N'IX_Approvals_060_Status_Expiry')
    CREATE INDEX IX_Approvals_060_Status_Expiry ON dbo.SensitiveActionApprovals_060(Status, ExpiresAt);
GO

PRINT 'SECTION 3 OK - Foreign keys and indexes are in place.';
GO

/*==============================================================================
  SECTION 4 - SAMPLE DATA INSERTION (safely rerunnable; explicit IDs keep FKs
  stable. Historical sales/stock rows are inserted before triggers exist.)
  Demo password hash below corresponds to: Demo@060 (bcrypt, PHP password_hash)
==============================================================================*/

IF NOT EXISTS (SELECT 1 FROM dbo.Roles_060 WHERE RoleID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Roles_060 ON;
    INSERT dbo.Roles_060 (RoleID, RoleName, [Description], IsActive) VALUES
        (1, N'Administrator', N'Full system, security, reports and settings access.', 1),
        (2, N'Manager', N'Catalogue, inventory, sales reports and permitted refunds.', 1),
        (3, N'Employee', N'Point of sale, customers, payments and own sales history.', 1),
        (4, N'Customer', N'Own profile, catalogue, own purchases and own receipts.', 1);
    SET IDENTITY_INSERT dbo.Roles_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Users_060 WHERE UserID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Users_060 ON;
    INSERT dbo.Users_060 (UserID, Username, Email, PasswordHash, FullName, Phone, IsActive, IsLocked, FailedLoginAttempts, PasswordChangedAt, CreatedAt) VALUES
        (1, N'admin_060',      N'admin@algorithmhardwareplug.local',      N'$2y$10$5U8/CgSK41MnBC2RCiI/iujjRuSwMXpAvwwJB77IiY1vgWNMi/dEa', N'Kofi Algorithm',        N'024-410-0001', 1, 0, 0, '2026-11-01T08:00:00', '2026-11-01T08:00:00'),
        (2, N'manager_060',    N'manager@algorithmhardwareplug.local',    N'$2y$10$a25O0dsa74ivIxl2klzOg.Ee2GMTxy3gn00Ff09lM8KTWGoR9LXY2', N'Kofi Owusu-Ansah',      N'024-410-0002', 1, 0, 0, '2026-11-01T08:05:00', '2026-11-01T08:05:00'),
        (3, N'cashier_060',    N'cashier@algorithmhardwareplug.local',    N'$2y$10$NGd/XHA6ihe0dT.sAzOMAO9Iv9zqJRvPNu62fkKDD9JCSidNfvyTG', N'Efua Mensima Quaye',    N'020-410-0003', 1, 0, 0, '2026-11-02T09:00:00', '2026-11-02T09:00:00'),
        (4, N'attendant_060',  N'attendant@algorithmhardwareplug.local',  N'$2y$10$j4WWy3v3o74izsp5fSVccu6xkLzDBBHIS1xTx7jBIdAo5YH/jfrfK', N'Daniel Kojo Appiah',    N'054-410-0004', 1, 0, 0, '2026-11-02T09:10:00', '2026-11-02T09:10:00'),
        (5, N'customer_060',   N'customer@algorithmhardwareplug.local',   N'$2y$10$5U8/CgSK41MnBC2RCiI/iujjRuSwMXpAvwwJB77IiY1vgWNMi/dEa', N'Akosua Adjei-Darko',    N'024-510-0005', 1, 0, 0, '2026-11-03T10:00:00', '2026-11-03T10:00:00'),
        (6, N'customer2_060',  N'customer2@algorithmhardwareplug.local',  N'$2y$10$a25O0dsa74ivIxl2klzOg.Ee2GMTxy3gn00Ff09lM8KTWGoR9LXY2', N'Kwame Nkrumah Mensah',  N'055-510-0006', 1, 0, 0, '2026-11-03T10:05:00', '2026-11-03T10:05:00'),
        (7, N'supervisor_060', N'supervisor@algorithmhardwareplug.local', N'$2y$10$NGd/XHA6ihe0dT.sAzOMAO9Iv9zqJRvPNu62fkKDD9JCSidNfvyTG', N'Michael Kwabena Osei',  N'020-410-0007', 1, 0, 0, '2026-11-04T08:30:00', '2026-11-04T08:30:00'),
        (8, N'inventory_060',  N'inventory@algorithmhardwareplug.local',  N'$2y$10$j4WWy3v3o74izsp5fSVccu6xkLzDBBHIS1xTx7jBIdAo5YH/jfrfK', N'Li Wei',                N'054-410-0008', 1, 0, 0, '2026-11-04T08:40:00', '2026-11-04T08:40:00');
    SET IDENTITY_INSERT dbo.Users_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.UserRoles_060 WHERE UserID = 1 AND RoleID = 1)
BEGIN
    INSERT dbo.UserRoles_060 (UserID, RoleID, AssignedByUserID) VALUES
        (1, 1, 1), (2, 2, 1), (3, 3, 1), (4, 3, 1),
        (5, 4, 1), (6, 4, 1), (7, 2, 1), (8, 3, 1);
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Employees_060 WHERE EmployeeID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Employees_060 ON;
    INSERT dbo.Employees_060 (EmployeeID, UserID, EmployeeCode, FullName, Position, Department, Phone, Email, HireDate, IsActive) VALUES
        (1, 2, N'EMP-060-001', N'Kofi Owusu-Ansah',     N'Store Manager',     N'Operations', N'024-410-0002', N'manager@algorithmhardwareplug.local',   '2024-02-01', 1),
        (2, 3, N'EMP-060-002', N'Efua Mensima Quaye',   N'Cashier',           N'Sales',      N'020-410-0003', N'cashier@algorithmhardwareplug.local',   '2024-06-15', 1),
        (3, 4, N'EMP-060-003', N'Daniel Kojo Appiah',   N'Sales Attendant',   N'Sales',      N'054-410-0004', N'attendant@algorithmhardwareplug.local', '2025-01-10', 1),
        (4, 7, N'EMP-060-004', N'Michael Kwabena Osei', N'Shift Supervisor',  N'Sales',      N'020-410-0007', N'supervisor@algorithmhardwareplug.local','2024-09-20', 1),
        (5, 8, N'EMP-060-005', N'Li Wei',               N'Inventory Officer', N'Warehouse',  N'054-410-0008', N'inventory@algorithmhardwareplug.local', '2025-03-05', 1);
    SET IDENTITY_INSERT dbo.Employees_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Customers_060 WHERE CustomerID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Customers_060 ON;
    INSERT dbo.Customers_060 (CustomerID, UserID, CustomerCode, FullName, Email, Phone, [Address], LoyaltyPoints, IsActive, CreatedAt) VALUES
        (1, 5,    N'CUST-060-001', N'Akosua Adjei-Darko',    N'akosua.adjei@example.com',    N'024-510-0005', N'14 Oxford Street, Osu, Accra',                120, 1, '2026-11-03T10:00:00'),
        (2, 6,    N'CUST-060-002', N'Kwame Nkrumah Mensah',  N'kwame.mensah@example.com',    N'055-510-0006', N'5 Liberation Road, Airport Res., Accra',       85, 1, '2026-11-03T10:05:00'),
        (3, NULL, N'CUST-060-003', N'Samuel Yaw Asare',      N'samuel.asare@example.com',    N'024-510-0007', N'8 Harper Road, Adum, Kumasi',                  40, 1, '2026-11-05T11:00:00'),
        (4, NULL, N'CUST-060-004', N'Fuseina Alhassan',      N'fuseina.alhassan@example.com',N'020-510-0008', N'22 Lamashegu Main Road, Tamale',               15, 1, '2026-11-06T12:00:00'),
        (5, NULL, N'CUST-060-005', N'Grace Efua Sackey',     N'grace.sackey@example.com',    N'054-510-0009', N'3 Market Circle, Takoradi',                    60, 1, '2026-11-08T09:30:00'),
        (6, NULL, N'CUST-060-006', N'Chinedu Okafor',        N'chinedu.okafor@example.com', N'055-510-0010', N'17 Community 11, Tema',                        10, 1, '2026-11-10T14:00:00');
    SET IDENTITY_INSERT dbo.Customers_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Suppliers_060 WHERE SupplierID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Suppliers_060 ON;
    INSERT dbo.Suppliers_060 (SupplierID, SupplierCode, CompanyName, ContactName, Phone, Email, [Address], IsActive) VALUES
        (1, N'SUP-060-001', N'PlugWorks Industrial Ghana Ltd', N'Beatrice Yaa Frimpong', N'030-220-0001', N'beatrice@plugworks.example',       N'Plot 12, Spintex Industrial Area, Accra', 1),
        (2, N'SUP-060-002', N'VoltEdge Components Ltd',        N'Aarav Patel',           N'030-220-0002', N'aarav@voltedge.example',            N'14 Ring Road Central, Accra', 1),
        (3, N'SUP-060-003', N'Ashanti Hardware Distributors',  N'Amina Abdulai',         N'032-220-0003', N'amina@ashantihardware.example',    N'9 Asafo Market Road, Kumasi', 1),
        (4, N'SUP-060-004', N'CableTail Supplies Ghana',       N'Joseph Kofi Hammond',   N'031-220-0004', N'joseph@cabletail.example',         N'6 Harbour Road, Takoradi', 1),
        (5, N'SUP-060-005', N'BrightSwitch Electricals',       N'Victoria Akua Agyeman', N'032-220-0005', N'victoria@brightswitch.example',    N'21 Suame Magazine, Kumasi', 1);
    SET IDENTITY_INSERT dbo.Suppliers_060 OFF;
END
GO

UPDATE dbo.Users_060 SET FullName = N'Li Wei'
WHERE UserID = 8 AND FullName = N'Priscilla Ama Tetteh';

UPDATE dbo.Employees_060 SET FullName = N'Li Wei'
WHERE EmployeeID = 5 AND FullName = N'Priscilla Ama Tetteh';

UPDATE dbo.Customers_060
SET FullName = N'Chinedu Okafor', Email = N'chinedu.okafor@example.com'
WHERE CustomerID = 6 AND FullName = N'Emmanuel Kweku Baidoo';

UPDATE dbo.Suppliers_060
SET ContactName = N'Aarav Patel', Email = N'aarav@voltedge.example'
WHERE SupplierID = 2 AND ContactName = N'Richard Kwasi Danquah';

UPDATE dbo.Users_060 SET FullName = N'Kofi Algorithm'
WHERE UserID = 1 AND FullName = N'Amara Okafor';
UPDATE dbo.Users_060 SET FullName = N'Kofi Algorithm'
WHERE UserID = 1 AND FullName = N'Abena Serwaa Boateng';
UPDATE dbo.Users_060 SET FullName = N'Kofi Owusu-Ansah'
WHERE UserID = 2 AND FullName = N'Chidi Balogun';
UPDATE dbo.Users_060 SET FullName = N'Efua Mensima Quaye'
WHERE UserID = 3 AND FullName = N'Zainab Musa';
UPDATE dbo.Users_060 SET FullName = N'Daniel Kojo Appiah'
WHERE UserID = 4 AND FullName = N'Tunde Adeyemi';
UPDATE dbo.Users_060 SET FullName = N'Akosua Adjei-Darko'
WHERE UserID = 5 AND FullName = N'Ngozi Eze';
UPDATE dbo.Users_060 SET FullName = N'Kwame Nkrumah Mensah'
WHERE UserID = 6 AND FullName = N'Kwame Mensah';
UPDATE dbo.Users_060 SET FullName = N'Michael Kwabena Osei'
WHERE UserID = 7 AND FullName = N'Bola Lawal';
UPDATE dbo.Users_060 SET FullName = N'Li Wei'
WHERE UserID = 8 AND FullName = N'Ifeoma Nwosu';

UPDATE dbo.Employees_060 SET FullName = N'Kofi Owusu-Ansah'
WHERE EmployeeID = 1 AND FullName = N'Chidi Balogun';
UPDATE dbo.Employees_060 SET FullName = N'Efua Mensima Quaye'
WHERE EmployeeID = 2 AND FullName = N'Zainab Musa';
UPDATE dbo.Employees_060 SET FullName = N'Daniel Kojo Appiah'
WHERE EmployeeID = 3 AND FullName = N'Tunde Adeyemi';
UPDATE dbo.Employees_060 SET FullName = N'Michael Kwabena Osei'
WHERE EmployeeID = 4 AND FullName = N'Bola Lawal';
UPDATE dbo.Employees_060 SET FullName = N'Li Wei'
WHERE EmployeeID = 5 AND FullName = N'Ifeoma Nwosu';

UPDATE dbo.Customers_060 SET FullName = N'Akosua Adjei-Darko'
WHERE CustomerID = 1 AND FullName = N'Ngozi Eze';
UPDATE dbo.Customers_060 SET FullName = N'Kwame Nkrumah Mensah'
WHERE CustomerID = 2 AND FullName = N'Kwame Mensah';
UPDATE dbo.Customers_060 SET FullName = N'Samuel Yaw Asare'
WHERE CustomerID = 3 AND FullName = N'Ada Chukwu';
UPDATE dbo.Customers_060 SET FullName = N'Fuseina Alhassan'
WHERE CustomerID = 4 AND FullName = N'Musa Ibrahim';
UPDATE dbo.Customers_060 SET FullName = N'Grace Efua Sackey'
WHERE CustomerID = 5 AND FullName = N'Grace Okoro';
UPDATE dbo.Customers_060 SET FullName = N'Chinedu Okafor'
WHERE CustomerID = 6 AND FullName = N'Yusuf Bello';

UPDATE dbo.Suppliers_060 SET ContactName = N'Beatrice Yaa Frimpong'
WHERE SupplierID = 1 AND ContactName = N'Helen Umeh';
UPDATE dbo.Suppliers_060 SET ContactName = N'Aarav Patel'
WHERE SupplierID = 2 AND ContactName = N'Mark Daniels';
UPDATE dbo.Suppliers_060 SET ContactName = N'Amina Abdulai'
WHERE SupplierID = 3 AND ContactName = N'Sara Bello';
UPDATE dbo.Suppliers_060 SET ContactName = N'Joseph Kofi Hammond'
WHERE SupplierID = 4 AND ContactName = N'Emeka Njoku';
UPDATE dbo.Suppliers_060 SET ContactName = N'Victoria Akua Agyeman'
WHERE SupplierID = 5 AND ContactName = N'Grace Mensah';
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Categories_060 WHERE CategoryID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Categories_060 ON;
    INSERT dbo.Categories_060 (CategoryID, CategoryName, [Description], ParentCategoryID, IsActive) VALUES
        (1, N'Hardware Tools',        N'Hand and power tools for trade and DIY.', NULL, 1),
        (2, N'Electrical Components', N'Switches, sockets, cables and protection devices.', NULL, 1),
        (3, N'Plumbing Supplies',     N'Pipes, fittings and sanitary items.', NULL, 1),
        (4, N'Hand Tools',            N'Hammers, screwdrivers, spanners and sets.', 1, 1),
        (5, N'Power Tools',           N'Drills, grinders and cutting equipment.', 1, 1),
        (6, N'Switches & Sockets',    N'Wiring devices and wall accessories.', 2, 1);
    SET IDENTITY_INSERT dbo.Categories_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Products_060 WHERE ProductID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Products_060 ON;
    INSERT dbo.Products_060 (ProductID, SKU, Barcode, ProductName, CategoryID, SupplierID, UnitPrice, CostPrice, ReorderLevel, IsActive, CreatedAt) VALUES
        (1,  N'ALP-HW-001', N'9000010000011', N'9-Inch Claw Hammer',          4, 1,  45.00,  28.00, 10, 1, '2026-11-01T09:00:00'),
        (2,  N'ALP-HW-002', N'9000010000028', N'Precision Screwdriver Set',   4, 3,  25.00,  15.00, 15, 1, '2026-11-01T09:05:00'),
        (3,  N'ALP-PT-003', N'9000010000035', N'850W Rotary Hammer Drill',    5, 1, 120.00,  85.00,  5, 1, '2026-11-01T09:10:00'),
        (4,  N'ALP-PT-004', N'9000010000042', N'Angle Grinder 115mm',         5, 2,  75.00,  52.00,  6, 1, '2026-11-01T09:15:00'),
        (5,  N'ALP-EL-005', N'9000010000059', N'3m Heavy-Duty Extension Lead',6, 4,  15.00,   9.50, 20, 1, '2026-11-01T09:20:00'),
        (6,  N'ALP-EL-006', N'9000010000066', N'32A Double Pole Switch',      6, 2, 350.00, 260.00,  4, 1, '2026-11-01T09:25:00'),
        (7,  N'ALP-EL-007', N'9000010000073', N'13A Fused Spur Unit',         6, 5,  18.00,  11.00, 20, 1, '2026-11-01T09:30:00'),
        (8,  N'ALP-HW-008', N'9000010000080', N'Adjustable Spanner 300mm',    4, 3,  12.00,   7.00, 25, 1, '2026-11-01T09:35:00'),
        (9,  N'ALP-PL-009', N'9000010000097', N'25mm PVC Conduit Pipe',       3, 4,  32.00,  21.00, 10, 1, '2026-11-01T09:40:00'),
        (10, N'ALP-HW-010', N'9000010000103', N'25-Piece Tool Kit',           4, 5,  95.00,  68.00,  8, 1, '2026-11-01T09:45:00');
    SET IDENTITY_INSERT dbo.Products_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Inventory_060 WHERE InventoryID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Inventory_060 ON;
    INSERT dbo.Inventory_060 (InventoryID, ProductID, QuantityOnHand, QuantityReserved, WarehouseLocation, LastUpdatedAt) VALUES
        (1, 1, 46, 2, N'Aisle A-01', '2026-11-20T16:00:00'),
        (2, 2, 32, 0, N'Aisle A-02', '2026-11-20T16:05:00'),
        (3, 3, 19, 1, N'Aisle B-01', '2026-11-20T16:10:00'),
        (4, 4, 12, 0, N'Aisle B-02', '2026-11-20T16:15:00'),
        (5, 5, 57, 0, N'Aisle C-01', '2026-11-20T16:20:00'),
        (6, 6,  8, 1, N'Aisle C-02', '2026-11-20T16:25:00'),
        (7, 7, 25, 0, N'Aisle C-03', '2026-11-20T16:30:00'),
        (8, 8, 40, 0, N'Aisle A-03', '2026-11-20T16:35:00'),
        (9, 9, 15, 0, N'Aisle D-01', '2026-11-20T16:40:00'),
        (10,10,22, 0, N'Aisle A-04', '2026-11-20T16:45:00');
    SET IDENTITY_INSERT dbo.Inventory_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.InventoryTransactions_060 WHERE TransactionID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.InventoryTransactions_060 ON;
    INSERT dbo.InventoryTransactions_060
        (TransactionID, ProductID, TransactionType, QuantityChange, QuantityBefore, QuantityAfter, ReferenceType, ReferenceID, PerformedByUserID, ApprovedByUserID, ApprovalReference, Reason, TransactionDate)
    VALUES
        (1, 1, N'PURCHASE',       60,  0, 60, N'PO', 9001, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:00:00'),
        (2, 1, N'SALE',          -10, 60, 50, N'SALE', 1001, 3, NULL, NULL, N'Sale S-2026-1001', '2026-11-12T11:05:00'),
        (3, 1, N'ADJUSTMENT_OUT', -5, 50, 45, N'ADJ', 7001, 8, 2, N'APR-060-7001', N'Damaged stock removed after approval', '2026-11-18T15:00:00'),
        (4, 1, N'RETURN',          1, 45, 46, N'RETURN', 2001, 2, 1, N'APR-060-2001', N'Customer return restocked', '2026-11-21T10:00:00'),
        (5, 2, N'PURCHASE',       40,  0, 40, N'PO', 9002, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:05:00'),
        (6, 2, N'SALE',          -10, 40, 30, N'SALE', 1002, 3, NULL, NULL, N'Sale S-2026-1002', '2026-11-13T12:00:00'),
        (7, 2, N'RETURN',          2, 30, 32, N'RETURN', 2002, 2, 1, N'APR-060-2002', N'Customer return restocked', '2026-11-21T10:30:00'),
        (8, 3, N'PURCHASE',       25,  0, 25, N'PO', 9003, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:10:00'),
        (9, 3, N'SALE',           -7, 25, 18, N'SALE', 1003, 4, NULL, NULL, N'Sale S-2026-1003', '2026-11-14T09:45:00'),
        (10,4, N'PURCHASE',       20,  0, 20, N'PO', 9004, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:15:00'),
        (11,4, N'SALE',           -8, 20, 12, N'SALE', 1004, 3, NULL, NULL, N'Sale S-2026-1004', '2026-11-15T14:20:00'),
        (12,5, N'PURCHASE',       60,  0, 60, N'PO', 9005, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:20:00'),
        (13,5, N'SALE',           -3, 60, 57, N'SALE', 1008, 4, NULL, NULL, N'Sale S-2026-1008', '2026-11-19T16:00:00'),
        (14,6, N'PURCHASE',       10,  0, 10, N'PO', 9006, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:25:00'),
        (15,6, N'SALE',           -2, 10,  8, N'SALE', 1005, 3, NULL, NULL, N'Sale S-2026-1005', '2026-11-16T10:00:00'),
        (16,7, N'PURCHASE',       30,  0, 30, N'PO', 9007, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:30:00'),
        (17,7, N'SALE',           -5, 30, 25, N'SALE', 1006, 4, NULL, NULL, N'Sale S-2026-1006', '2026-11-17T11:30:00'),
        (18,8, N'PURCHASE',       40,  0, 40, N'PO', 9008, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:35:00'),
        (19,9, N'PURCHASE',       20,  0, 20, N'PO', 9009, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:40:00'),
        (20,9, N'SALE',           -5, 20, 15, N'SALE', 1007, 3, NULL, NULL, N'Sale S-2026-1007', '2026-11-18T13:10:00'),
        (21,10,N'PURCHASE',       25,  0, 25, N'PO', 9010, 8, NULL, NULL, N'Opening stock purchase', '2026-11-01T10:45:00'),
        (22,10,N'ADJUSTMENT_OUT', -3, 25, 22, N'ADJ', 7010, 8, 2, N'APR-060-7010', N'Stock count correction after approval', '2026-11-18T15:30:00');
    SET IDENTITY_INSERT dbo.InventoryTransactions_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Sales_060 WHERE SaleID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Sales_060 ON;
    INSERT dbo.Sales_060
        (SaleID, SaleNumber, CustomerID, EmployeeID, SaleDate, SubTotal, DiscountAmount, TaxAmount, TotalAmount, AmountPaid, ChangeAmount, Status, Notes)
    VALUES
        (1, N'S-2026-1001', 1, 2, '2026-11-12T11:05:00', 450.00, 0.00, 36.00, 486.00, 486.00, 0.00, N'COMPLETED', N'Claw hammer purchase.'),
        (2, N'S-2026-1002', 2, 2, '2026-11-13T12:00:00', 250.00, 0.00, 20.00, 270.00, 300.00,30.00, N'COMPLETED', N'Screwdriver sets.'),
        (3, N'S-2026-1003', 3, 3, '2026-11-14T09:45:00', 840.00, 0.00, 67.20, 907.20, 907.20, 0.00, N'COMPLETED', N'Rotary hammer drill.'),
        (4, N'S-2026-1004', 4, 2, '2026-11-15T14:20:00', 600.00, 0.00, 48.00, 648.00, 648.00, 0.00, N'COMPLETED', N'Angle grinder.'),
        (5, N'S-2026-1005', 5, 2, '2026-11-16T10:00:00', 700.00, 0.00, 56.00, 756.00, 756.00, 0.00, N'COMPLETED', N'Double pole switch.'),
        (6, N'S-2026-1006', 1, 3, '2026-11-17T11:30:00',  90.00, 5.00,  6.80,  91.80, 100.00, 8.20, N'COMPLETED', N'Fused spur units with approved discount.'),
        (7, N'S-2026-1007', 6, 2, '2026-11-18T13:10:00', 160.00, 0.00, 12.80, 172.80, 172.80, 0.00, N'COMPLETED', N'PVC conduit pipes.'),
        (8, N'S-2026-1008', NULL, 3,'2026-11-19T16:00:00', 45.00, 0.00,  3.60,  48.60,  48.60, 0.00, N'COMPLETED', N'Walk-in extension lead purchase.');
    SET IDENTITY_INSERT dbo.Sales_060 OFF;
END
GO

IF OBJECT_ID(N'dbo.trg_SaleItems_UpdateStock_060', N'TR') IS NOT NULL
    DISABLE TRIGGER dbo.trg_SaleItems_UpdateStock_060 ON dbo.SaleItems_060;
GO

IF NOT EXISTS (SELECT 1 FROM dbo.SaleItems_060 WHERE SaleItemID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.SaleItems_060 ON;
    INSERT dbo.SaleItems_060 (SaleItemID, SaleID, ProductID, Quantity, UnitPrice, DiscountAmount, LineTotal, ReturnedQuantity) VALUES
        (1, 1, 1, 10,  45.00, 0.00, 450.00, 1),
        (2, 2, 2, 10,  25.00, 0.00, 250.00, 2),
        (3, 3, 3,  7, 120.00, 0.00, 840.00, 1),
        (4, 4, 4,  8,  75.00, 0.00, 600.00, 1),
        (5, 5, 6,  2, 350.00, 0.00, 700.00, 1),
        (6, 6, 7,  5,  18.00, 5.00,  90.00, 0),
        (7, 7, 9,  5,  32.00, 0.00, 160.00, 0),
        (8, 8, 5,  3,  15.00, 0.00,  45.00, 0);
    SET IDENTITY_INSERT dbo.SaleItems_060 OFF;
END
GO

IF OBJECT_ID(N'dbo.trg_SaleItems_UpdateStock_060', N'TR') IS NOT NULL
    ENABLE TRIGGER dbo.trg_SaleItems_UpdateStock_060 ON dbo.SaleItems_060;
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Payments_060 WHERE PaymentID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Payments_060 ON;
    INSERT dbo.Payments_060 (PaymentID, PaymentNumber, SaleID, Amount, PaymentMethod, PaymentDate, Status, ReferenceNumber, ProcessedByUserID) VALUES
        (1, N'PAY-2026-1001', 1, 486.00, N'CASH',         '2026-11-12T11:06:00', N'PAID', NULL, 3),
        (2, N'PAY-2026-1002', 2, 300.00, N'CASH',         '2026-11-13T12:01:00', N'PAID', NULL, 3),
        (3, N'PAY-2026-1003', 3, 907.20, N'CARD',         '2026-11-14T09:46:00', N'PAID', N'CARD-8831', 4),
        (4, N'PAY-2026-1004', 4, 648.00, N'BANK_TRANSFER','2026-11-15T14:21:00', N'PAID', N'TRF-7742', 3),
        (5, N'PAY-2026-1005', 5, 756.00, N'CARD',         '2026-11-16T10:01:00', N'PAID', N'CARD-8832', 3),
        (6, N'PAY-2026-1006', 6, 100.00, N'CASH',         '2026-11-17T11:31:00', N'PAID', NULL, 4),
        (7, N'PAY-2026-1007', 7, 172.80, N'BANK_TRANSFER','2026-11-18T13:11:00', N'PAID', N'TRF-7743', 2),
        (8, N'PAY-2026-1008', 8,  48.60, N'CASH',         '2026-11-19T16:01:00', N'PAID', NULL, 4);
    SET IDENTITY_INSERT dbo.Payments_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Returns_060 WHERE ReturnID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.Returns_060 ON;
    INSERT dbo.Returns_060 (ReturnID, ReturnNumber, SaleID, CustomerID, EmployeeID, ReturnDate, Reason, RefundAmount, Status, ApprovedByUserID, ApprovalReference, ProcessedByUserID) VALUES
        (1, N'RET-2026-2001', 1, 1, 2, '2026-11-21T10:00:00', N'Hammer handle defect reported by customer.', 45.00,  N'COMPLETED', 1, N'APR-060-2001', 2),
        (2, N'RET-2026-2002', 2, 2, 2, '2026-11-21T10:30:00', N'Wrong screwdriver size supplied.',          50.00,  N'COMPLETED', 1, N'APR-060-2002', 2),
        (3, N'RET-2026-2003', 3, 3, 3, '2026-11-22T09:15:00', N'Drill overheated during first use.',      120.00, N'APPROVED',  2, N'APR-060-2003', 3),
        (4, N'RET-2026-2004', 4, 4, 2, '2026-11-22T11:45:00', N'Customer changed mind within 7 days.',     75.00, N'PENDING',   NULL, NULL, 2),
        (5, N'RET-2026-2005', 5, 5, 2, '2026-11-23T08:30:00', N'Item damaged after installation.',        350.00, N'REJECTED',  1, N'APR-060-2005', 2);
    SET IDENTITY_INSERT dbo.Returns_060 OFF;
END
GO

IF OBJECT_ID(N'dbo.trg_ReturnItems_Restock_060', N'TR') IS NOT NULL
    DISABLE TRIGGER dbo.trg_ReturnItems_Restock_060 ON dbo.ReturnItems_060;
GO

IF NOT EXISTS (SELECT 1 FROM dbo.ReturnItems_060 WHERE ReturnItemID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.ReturnItems_060 ON;
    INSERT dbo.ReturnItems_060 (ReturnItemID, ReturnID, SaleItemID, Quantity, RefundAmount, [Condition], Restock) VALUES
        (1, 1, 1, 1,  45.00, N'DAMAGED', 1),
        (2, 2, 2, 2,  50.00, N'GOOD',    1),
        (3, 3, 3, 1, 120.00, N'DAMAGED', 0),
        (4, 4, 4, 1,  75.00, N'GOOD',    1),
        (5, 5, 5, 1, 350.00, N'DAMAGED', 0);
    SET IDENTITY_INSERT dbo.ReturnItems_060 OFF;
END
GO

IF OBJECT_ID(N'dbo.trg_ReturnItems_Restock_060', N'TR') IS NOT NULL
    ENABLE TRIGGER dbo.trg_ReturnItems_Restock_060 ON dbo.ReturnItems_060;
GO

IF NOT EXISTS (SELECT 1 FROM dbo.SensitiveActions_060 WHERE ActionKey = N'LOGOUT')
BEGIN
    INSERT dbo.SensitiveActions_060 (ActionKey, [Description], RiskLevel, AllowedRoles, IsActive, DefaultExpiryMinutes) VALUES
        (N'LOGOUT',              N'Sign the current user out of Algorithm Hardware Plug.', N'STANDARD', N'Administrator,Manager,Employee,Customer', 1, 5),
        (N'DELETE_PRODUCT',      N'Permanently delete a product catalogue record.',        N'ELEVATED', N'Administrator,Manager', 1, 10),
        (N'DELETE_CATEGORY',     N'Permanently delete a product category.',                 N'ELEVATED', N'Administrator,Manager', 1, 10),
        (N'DELETE_SUPPLIER',     N'Deactivate or delete a supplier record.',                N'ELEVATED', N'Administrator,Manager', 1, 10),
        (N'ADJUST_STOCK_DOWN',   N'Manually reduce stock on hand.',                         N'ELEVATED', N'Administrator,Manager', 1, 10),
        (N'DEACTIVATE_USER',     N'Deactivate or suspend a user account.',                  N'ELEVATED', N'Administrator', 1, 10),
        (N'CHANGE_USER_ROLE',    N'Assign, change or remove a user role.',                  N'CRITICAL', N'Administrator', 1, 5),
        (N'RESET_USER_PASSWORD', N'Reset another user''s password.',                        N'CRITICAL', N'Administrator', 1, 5),
        (N'ISSUE_REFUND',        N'Issue a refund or complete a financial return.',         N'CRITICAL', N'Administrator,Manager', 1, 5),
        (N'CANCEL_SALE',         N'Cancel a completed sale or void a payment.',             N'CRITICAL', N'Administrator', 1, 5),
        (N'CHANGE_SETTING',      N'Change a system-wide setting.',                          N'CRITICAL', N'Administrator', 1, 5),
        (N'RESTORE_BACKUP',      N'Restore a database backup (destructive maintenance).',   N'CRITICAL', N'Administrator', 1, 3);
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.AuditLogs_060 WHERE AuditLogID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.AuditLogs_060 ON;
    INSERT dbo.AuditLogs_060
        (AuditLogID, UserID, UserRole, Action, EntityType, EntityID, Details, IPAddress, UserAgent, ConfirmationStatus, AdditionalVerification, Outcome, OccurredAt)
    VALUES
        (1, 1, N'Administrator', N'LOGIN', N'User', N'1', N'Administrator signed in.', N'127.0.0.1', N'DemoBrowser/1.0', N'NOT_REQUIRED', N'NONE', N'SUCCESS', '2026-11-20T08:00:00'),
        (2, 1, N'Administrator', N'APPROVAL_CONFIRMED', N'Approval', N'APR-060-7001', N'Confirmed stock reduction with manager co-approval.', N'127.0.0.1', N'DemoBrowser/1.0', N'CONFIRMED', N'ADMIN_APPROVAL', N'SUCCESS', '2026-11-18T14:59:00'),
        (3, 2, N'Manager', N'APPROVAL_CANCELLED', N'Approval', N'APR-060-9001', N'Cancelled a product deletion request; no data changed.', N'127.0.0.1', N'DemoBrowser/1.0', N'CANCELLED', N'NONE', N'CANCELLED', '2026-11-19T09:30:00'),
        (4, 3, N'Employee', N'SALE_CREATED', N'Sale', N'S-2026-1007', N'Created sale with total 172.80.', N'127.0.0.1', N'DemoBrowser/1.0', N'NOT_REQUIRED', N'NONE', N'SUCCESS', '2026-11-18T13:10:00'),
        (5, 1, N'Administrator', N'ACCESS_DENIED', N'User', N'3', N'Unauthorized role change attempt denied.', N'127.0.0.1', N'DemoBrowser/1.0', N'NOT_REQUIRED', N'NONE', N'DENIED', '2026-11-20T10:15:00'),
        (6, 2, N'Manager', N'PRODUCT_UPDATE', N'Product', N'3', N'Updated unit price for rotary hammer drill.', N'127.0.0.1', N'DemoBrowser/1.0', N'NOT_REQUIRED', N'NONE', N'SUCCESS', '2026-11-20T11:00:00');
    SET IDENTITY_INSERT dbo.AuditLogs_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.SensitiveActionApprovals_060 WHERE ApprovalID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.SensitiveActionApprovals_060 ON;
    INSERT dbo.SensitiveActionApprovals_060
        (ApprovalID, Token, UserID, ActionKey, EntityType, EntityID, RiskLevel, Status, ClientRequestID, CreatedAt, ExpiresAt, ConfirmedAt, ConfirmedByUserID, VerificationMethod, Details)
    VALUES
        (1, '11111111-1111-4111-8111-111111111111', 2, N'ADJUST_STOCK_DOWN', N'Product', N'1', N'ELEVATED', N'CONFIRMED', N'DEMO-CLIENT-001', '2026-11-18T14:55:00', '2026-11-18T15:05:00', '2026-11-18T14:59:00', 1, N'ADMIN_APPROVAL', N'Reduce hammer stock by 5 after damage count.'),
        (2, '22222222-2222-4222-8222-222222222222', 2, N'DELETE_PRODUCT', N'Product', N'8', N'ELEVATED', N'CANCELLED', N'DEMO-CLIENT-002', '2026-11-19T09:25:00', '2026-11-19T09:35:00', NULL, NULL, N'NONE', N'User cancelled deletion; product 8 unchanged.'),
        (3, '33333333-3333-4333-8333-333333333333', 1, N'CHANGE_USER_ROLE', N'User', N'4', N'CRITICAL', N'CONFIRMED', N'DEMO-CLIENT-003', '2026-11-20T10:10:00', '2026-11-20T10:15:00', '2026-11-20T10:12:00', 1, N'PASSWORD_AND_PHRASE', N'Role change verified and audited.'),
        (4, '44444444-4444-4444-8444-444444444444', 1, N'CHANGE_SETTING', N'Setting', N'TaxRate', N'CRITICAL', N'EXPIRED', N'DEMO-CLIENT-004', '2026-11-20T11:00:00', '2026-11-20T11:05:00', NULL, NULL, N'NONE', N'Approval window expired.'),
        (5, '55555555-5555-4555-8555-555555555555', 3, N'LOGOUT', N'Session', N'3', N'STANDARD', N'PENDING', N'DEMO-CLIENT-005', '2026-11-23T08:00:00', '2026-11-23T08:05:00', NULL, NULL, N'NONE', N'Pending logout confirmation demo.');
    SET IDENTITY_INSERT dbo.SensitiveActionApprovals_060 OFF;
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.SystemSettings_060 WHERE SettingID = 1)
BEGIN
    SET IDENTITY_INSERT dbo.SystemSettings_060 ON;
    INSERT dbo.SystemSettings_060 (SettingID, SettingName, SettingValue, [Description], IsSensitive, UpdatedByUserID, UpdatedAt) VALUES
        (1, N'ShopName',                  N'Algorithm Hardware Plug', N'Official business name shown across the application.', 0, 1, '2026-11-01T08:00:00'),
        (2, N'Currency',                  N'GHS',                     N'Display currency code for reports and receipts.',     0, 1, '2026-11-01T08:00:00'),
        (3, N'TaxRate',                   N'0.08',                    N'Sales tax rate used by sp_ProcessSale_060.',          1, 1, '2026-11-01T08:00:00'),
        (4, N'LowStockThreshold',         N'5',                       N'Default low-stock warning threshold.',               0, 2, '2026-11-05T09:00:00'),
        (5, N'RequireApprovalForRefunds', N'1',                       N'Refunds require explicit approval before execution.', 1, 1, '2026-11-01T08:00:00');
    SET IDENTITY_INSERT dbo.SystemSettings_060 OFF;
END
GO

UPDATE dbo.SystemSettings_060
SET SettingValue = N'GHS'
WHERE SettingName = N'Currency' AND SettingValue = N'NGN';
GO

PRINT 'SECTION 4 OK - Sample data is present (duplicates skipped on rerun).';
GO

/*==============================================================================
  SECTION 5 - DATABASE ROLES AND USER SETUP
  Three database roles support four application roles. Application role identity
  is carried in SESSION_CONTEXT and checked inside procedures as a second layer.
==============================================================================*/
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = N'rms_admin_060')
    CREATE ROLE rms_admin_060;
GO
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = N'rms_manager_060')
    CREATE ROLE rms_manager_060;
GO
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = N'rms_staff_060')
    CREATE ROLE rms_staff_060;
GO

IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = N'rms_demo_admin_060')
    CREATE USER rms_demo_admin_060 WITHOUT LOGIN;
GO
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = N'rms_demo_manager_060')
    CREATE USER rms_demo_manager_060 WITHOUT LOGIN;
GO
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = N'rms_demo_staff_060')
    CREATE USER rms_demo_staff_060 WITHOUT LOGIN;
GO
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = N'rms_demo_customer_060')
    CREATE USER rms_demo_customer_060 WITHOUT LOGIN;
GO
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = N'rms_demo_temp_060')
    CREATE USER rms_demo_temp_060 WITHOUT LOGIN;
GO

IF NOT EXISTS (SELECT 1 FROM sys.database_role_members drm JOIN sys.database_principals r ON drm.role_principal_id = r.principal_id JOIN sys.database_principals m ON drm.member_principal_id = m.principal_id WHERE r.name = N'rms_admin_060' AND m.name = N'rms_demo_admin_060')
    ALTER ROLE rms_admin_060 ADD MEMBER rms_demo_admin_060;
GO
IF NOT EXISTS (SELECT 1 FROM sys.database_role_members drm JOIN sys.database_principals r ON drm.role_principal_id = r.principal_id JOIN sys.database_principals m ON drm.member_principal_id = m.principal_id WHERE r.name = N'rms_manager_060' AND m.name = N'rms_demo_manager_060')
    ALTER ROLE rms_manager_060 ADD MEMBER rms_demo_manager_060;
GO
IF NOT EXISTS (SELECT 1 FROM sys.database_role_members drm JOIN sys.database_principals r ON drm.role_principal_id = r.principal_id JOIN sys.database_principals m ON drm.member_principal_id = m.principal_id WHERE r.name = N'rms_staff_060' AND m.name = N'rms_demo_staff_060')
    ALTER ROLE rms_staff_060 ADD MEMBER rms_demo_staff_060;
GO
IF NOT EXISTS (SELECT 1 FROM sys.database_role_members drm JOIN sys.database_principals r ON drm.role_principal_id = r.principal_id JOIN sys.database_principals m ON drm.member_principal_id = m.principal_id WHERE r.name = N'rms_staff_060' AND m.name = N'rms_demo_customer_060')
    ALTER ROLE rms_staff_060 ADD MEMBER rms_demo_customer_060;
GO

IF NOT EXISTS (SELECT 1 FROM sys.database_role_members drm JOIN sys.database_principals r ON drm.role_principal_id = r.principal_id JOIN sys.database_principals m ON drm.member_principal_id = m.principal_id WHERE r.name = N'rms_staff_060' AND m.name = N'rms_demo_temp_060')
    ALTER ROLE rms_staff_060 ADD MEMBER rms_demo_temp_060;
GO
IF EXISTS (SELECT 1 FROM sys.database_role_members drm JOIN sys.database_principals r ON drm.role_principal_id = r.principal_id JOIN sys.database_principals m ON drm.member_principal_id = m.principal_id WHERE r.name = N'rms_staff_060' AND m.name = N'rms_demo_temp_060')
    ALTER ROLE rms_staff_060 DROP MEMBER rms_demo_temp_060;
GO

PRINT 'SECTION 5 OK - Database roles created; demo temp user added and removed.';
GO

/*==============================================================================
  SECTION 6 - GRANT PERMISSIONS (including WITH GRANT OPTION)
==============================================================================*/
GRANT SELECT, INSERT, UPDATE, DELETE ON SCHEMA::dbo TO rms_admin_060;
GO
GRANT EXECUTE ON SCHEMA::dbo TO rms_admin_060;
GO

GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.Products_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.Categories_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.Suppliers_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.Inventory_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.InventoryTransactions_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE ON dbo.Customers_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.Sales_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.SaleItems_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE ON dbo.Payments_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE ON dbo.Returns_060 TO rms_manager_060;
GRANT SELECT, INSERT, UPDATE ON dbo.ReturnItems_060 TO rms_manager_060;
GRANT SELECT ON dbo.AuditLogs_060 TO rms_manager_060;
GO

GRANT SELECT ON dbo.Products_060 TO rms_manager_060 WITH GRANT OPTION;
GO

GRANT SELECT ON dbo.Products_060 TO rms_demo_temp_060;
GO

PRINT 'SECTION 6 OK - GRANT permissions applied (including WITH GRANT OPTION).';
GO

/*==============================================================================
  SECTION 7 - REVOKE PERMISSIONS
==============================================================================*/
REVOKE SELECT ON dbo.Products_060 FROM rms_demo_temp_060;
GO

PRINT 'SECTION 7 OK - REVOKE demonstrated on the temporary demo user.';
GO

/*==============================================================================
  SECTION 8 - ROLE-BASED ACCESS CONTROL (RBAC)
  Database roles = SQL Server object permissions.
  Application roles = business roles stored in Roles_060 and checked by PHP and
  by the session-context guard procedures below.
==============================================================================*/
CREATE OR ALTER PROCEDURE dbo.sp_SetAppContext_060
    @AppUserID int,
    @AppRole   nvarchar(50),
    @AppIP     nvarchar(45) = NULL,
    @AppAgent  nvarchar(300) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM dbo.Roles_060 WHERE RoleName = @AppRole AND IsActive = 1)
        THROW 50003, N'Unknown or inactive application role.', 1;

    EXEC sys.sp_set_session_context @key = N'AppUserID', @value = @AppUserID;
    EXEC sys.sp_set_session_context @key = N'AppRole',   @value = @AppRole;
    EXEC sys.sp_set_session_context @key = N'AppIP',     @value = @AppIP;
    EXEC sys.sp_set_session_context @key = N'AppAgent',  @value = @AppAgent;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_AssertAppRole_060
    @AllowedRoles nvarchar(200)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @CurrentRole nvarchar(50) = TRY_CONVERT(nvarchar(50), SESSION_CONTEXT(N'AppRole'));

    IF @CurrentRole IS NULL
        THROW 50004, N'Application role context is missing.', 1;

    IF NOT EXISTS
    (
        SELECT 1
        FROM STRING_SPLIT(@AllowedRoles, N',')
        WHERE LTRIM(RTRIM(value)) = @CurrentRole
    )
        THROW 50005, N'Access denied for the current application role.', 1;
END
GO

GRANT EXECUTE ON dbo.sp_SetAppContext_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_AssertAppRole_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GO

PRINT 'SECTION 8 OK - RBAC context helpers are available.';
GO

/*==============================================================================
  SECTION 9 - STORED PROCEDURES (validation, transactions, error handling)
  Procedure EXECUTE grants are applied here because the procedures are created
  in this section. All objects carry the _060 suffix.
==============================================================================*/
CREATE OR ALTER PROCEDURE dbo.sp_WriteAudit_060
    @Action                 nvarchar(100),
    @EntityType             nvarchar(50)   = NULL,
    @EntityID               nvarchar(50)   = NULL,
    @Details                nvarchar(1000) = NULL,
    @ConfirmationStatus     nvarchar(20)   = N'NOT_REQUIRED',
    @AdditionalVerification nvarchar(30)   = N'NONE',
    @Outcome                nvarchar(20)   = N'SUCCESS'
AS
BEGIN
    SET NOCOUNT ON;
    INSERT dbo.AuditLogs_060
        (UserID, UserRole, Action, EntityType, EntityID, Details, IPAddress, UserAgent,
         ConfirmationStatus, AdditionalVerification, Outcome, OccurredAt)
    VALUES
        (TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
         TRY_CONVERT(nvarchar(50), SESSION_CONTEXT(N'AppRole')),
         @Action, @EntityType, @EntityID, @Details,
         TRY_CONVERT(nvarchar(45), SESSION_CONTEXT(N'AppIP')),
         TRY_CONVERT(nvarchar(300), SESSION_CONTEXT(N'AppAgent')),
         @ConfirmationStatus, @AdditionalVerification, @Outcome, SYSDATETIME());
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_RegisterProduct_060
    @SKU          nvarchar(30),
    @ProductName  nvarchar(150),
    @CategoryID   int,
    @SupplierID   int,
    @UnitPrice    decimal(18,2),
    @CostPrice    decimal(18,2),
    @ReorderLevel int = 5,
    @Barcode      nvarchar(50) = NULL,
    @NewProductID int OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';

        IF @UnitPrice < 0 OR @CostPrice < 0 OR @ReorderLevel < 0
            THROW 50100, N'Prices and reorder level cannot be negative.', 1;
        IF EXISTS (SELECT 1 FROM dbo.Products_060 WHERE SKU = @SKU)
            THROW 50101, N'A product with this SKU already exists.', 1;
        IF NOT EXISTS (SELECT 1 FROM dbo.Categories_060 WHERE CategoryID = @CategoryID AND IsActive = 1)
            THROW 50102, N'Category not found or inactive.', 1;
        IF NOT EXISTS (SELECT 1 FROM dbo.Suppliers_060 WHERE SupplierID = @SupplierID AND IsActive = 1)
            THROW 50103, N'Supplier not found or inactive.', 1;

        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRAN;
            SET @StartedTran = 1;
        END
            INSERT dbo.Products_060 (SKU, Barcode, ProductName, CategoryID, SupplierID, UnitPrice, CostPrice, ReorderLevel, IsActive)
            VALUES (@SKU, @Barcode, @ProductName, @CategoryID, @SupplierID, @UnitPrice, @CostPrice, @ReorderLevel, 1);

            SET @NewProductID = SCOPE_IDENTITY();

            INSERT dbo.Inventory_060 (ProductID, QuantityOnHand, QuantityReserved, WarehouseLocation)
            VALUES (@NewProductID, 0, 0, N'Unassigned');

            EXEC dbo.sp_WriteAudit_060
                @Action = N'PRODUCT_CREATE', @EntityType = N'Product',
                @EntityID = @NewProductID, @Details = @ProductName;
        IF @StartedTran = 1
            COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @StartedTran = 1 AND @@TRANCOUNT > 0
            ROLLBACK TRAN;
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_RegisterCustomer_060
    @CustomerCode  nvarchar(20),
    @FullName      nvarchar(100),
    @Email         nvarchar(256),
    @Phone         nvarchar(30)  = NULL,
    @Address       nvarchar(255) = NULL,
    @NewCustomerID int OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';

        IF @FullName IS NULL OR LTRIM(RTRIM(@FullName)) = N''
            THROW 50110, N'Customer name is required.', 1;
        IF @Email IS NULL OR @Email NOT LIKE N'%@%'
            THROW 50111, N'A valid email address is required.', 1;
        IF EXISTS (SELECT 1 FROM dbo.Customers_060 WHERE Email = @Email)
            THROW 50112, N'A customer with this email already exists.', 1;
        IF EXISTS (SELECT 1 FROM dbo.Customers_060 WHERE CustomerCode = @CustomerCode)
            THROW 50113, N'A customer with this code already exists.', 1;

        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRAN;
            SET @StartedTran = 1;
        END
            INSERT dbo.Customers_060 (CustomerCode, FullName, Email, Phone, [Address], IsActive)
            VALUES (@CustomerCode, @FullName, @Email, @Phone, @Address, 1);

            SET @NewCustomerID = SCOPE_IDENTITY();

            EXEC dbo.sp_WriteAudit_060
                @Action = N'CUSTOMER_CREATE', @EntityType = N'Customer',
                @EntityID = @NewCustomerID, @Details = @FullName;
        IF @StartedTran = 1
            COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @StartedTran = 1 AND @@TRANCOUNT > 0
            ROLLBACK TRAN;
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_RegisterSupplier_060
    @SupplierCode   nvarchar(20),
    @CompanyName    nvarchar(100),
    @ContactName    nvarchar(100) = NULL,
    @Phone          nvarchar(30) = NULL,
    @Email          nvarchar(256) = NULL,
    @Address        nvarchar(255) = NULL,
    @NewSupplierID  int OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';

        IF @SupplierCode IS NULL OR LTRIM(RTRIM(@SupplierCode)) = N''
            THROW 50120, N'Supplier code is required.', 1;
        IF @CompanyName IS NULL OR LTRIM(RTRIM(@CompanyName)) = N''
            THROW 50121, N'Supplier company name is required.', 1;
        IF @Email IS NOT NULL AND LTRIM(RTRIM(@Email)) <> N'' AND @Email NOT LIKE N'%@%'
            THROW 50122, N'A valid supplier email address is required.', 1;
        IF EXISTS (SELECT 1 FROM dbo.Suppliers_060 WHERE SupplierCode = @SupplierCode)
            THROW 50123, N'A supplier with this code already exists.', 1;

        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRAN;
            SET @StartedTran = 1;
        END
            INSERT dbo.Suppliers_060 (SupplierCode, CompanyName, ContactName, Phone, Email, [Address], IsActive)
            VALUES (@SupplierCode, @CompanyName, @ContactName, @Phone, NULLIF(@Email, N''), @Address, 1);

            SET @NewSupplierID = SCOPE_IDENTITY();

            EXEC dbo.sp_WriteAudit_060
                @Action = N'SUPPLIER_CREATE', @EntityType = N'Supplier',
                @EntityID = @NewSupplierID, @Details = @CompanyName;
        IF @StartedTran = 1
            COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @StartedTran = 1 AND @@TRANCOUNT > 0
            ROLLBACK TRAN;
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_RegisterEmployee_060
    @EmployeeCode   nvarchar(20),
    @FullName       nvarchar(100),
    @Position       nvarchar(50),
    @HireDate       date,
    @Department     nvarchar(50) = NULL,
    @Phone          nvarchar(30) = NULL,
    @Email          nvarchar(256) = NULL,
    @NewEmployeeID  int OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator';

        IF @EmployeeCode IS NULL OR LTRIM(RTRIM(@EmployeeCode)) = N''
            THROW 50124, N'Employee code is required.', 1;
        IF @FullName IS NULL OR LTRIM(RTRIM(@FullName)) = N''
            THROW 50125, N'Employee name is required.', 1;
        IF @Position IS NULL OR LTRIM(RTRIM(@Position)) = N''
            THROW 50126, N'Employee position is required.', 1;
        IF @HireDate IS NULL
            THROW 50127, N'Employee hire date is required.', 1;
        IF @Email IS NOT NULL AND LTRIM(RTRIM(@Email)) <> N'' AND @Email NOT LIKE N'%@%'
            THROW 50128, N'A valid employee email address is required.', 1;
        IF EXISTS (SELECT 1 FROM dbo.Employees_060 WHERE EmployeeCode = @EmployeeCode)
            THROW 50129, N'An employee with this code already exists.', 1;

        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRAN;
            SET @StartedTran = 1;
        END
            INSERT dbo.Employees_060 (EmployeeCode, FullName, Position, Department, Phone, Email, HireDate, IsActive)
            VALUES (@EmployeeCode, @FullName, @Position, @Department, @Phone, NULLIF(@Email, N''), @HireDate, 1);

            SET @NewEmployeeID = SCOPE_IDENTITY();

            EXEC dbo.sp_WriteAudit_060
                @Action = N'EMPLOYEE_CREATE', @EntityType = N'Employee',
                @EntityID = @NewEmployeeID, @Details = @FullName;
        IF @StartedTran = 1
            COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @StartedTran = 1 AND @@TRANCOUNT > 0
            ROLLBACK TRAN;
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_UpdateInventory_060
    @ProductID         int,
    @QuantityChange    int,
    @TransactionType   nvarchar(30),
    @PerformedByUserID int,
    @ApprovedByUserID  int   = NULL,
    @ApprovalReference nvarchar(100) = NULL,
    @Reason            nvarchar(255) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';

        IF @QuantityChange = 0
            THROW 50120, N'Quantity change cannot be zero.', 1;
        IF @TransactionType = N'ADJUSTMENT_OUT' AND @ApprovedByUserID IS NULL
            THROW 50121, N'A stock reduction requires administrator approval.', 1;
        IF @TransactionType NOT IN (N'PURCHASE', N'ADJUSTMENT_IN', N'ADJUSTMENT_OUT', N'VOID')
            THROW 50122, N'Invalid manual inventory transaction type.', 1;
        IF @TransactionType IN (N'PURCHASE', N'ADJUSTMENT_IN') AND @QuantityChange < 0
            THROW 50123, N'This transaction type requires a positive quantity.', 1;
        IF @TransactionType IN (N'ADJUSTMENT_OUT', N'VOID') AND @QuantityChange > 0
            THROW 50124, N'This transaction type requires a negative quantity.', 1;

        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRAN;
            SET @StartedTran = 1;
        END
            DECLARE @BeforeQty int, @AfterQty int;

            SELECT @BeforeQty = QuantityOnHand
            FROM dbo.Inventory_060 WITH (UPDLOCK, HOLDLOCK)
            WHERE ProductID = @ProductID;

            IF @BeforeQty IS NULL
                THROW 50125, N'Inventory record not found for this product.', 1;

            SET @AfterQty = @BeforeQty + @QuantityChange;
            IF @AfterQty < 0
                THROW 50126, N'Adjustment would make stock negative.', 1;

            UPDATE dbo.Inventory_060
            SET QuantityOnHand = @AfterQty, LastUpdatedAt = SYSDATETIME()
            WHERE ProductID = @ProductID;

            INSERT dbo.InventoryTransactions_060
                (ProductID, TransactionType, QuantityChange, QuantityBefore, QuantityAfter,
                 ReferenceType, ReferenceID, PerformedByUserID, ApprovedByUserID,
                 ApprovalReference, Reason)
            VALUES
                (@ProductID, @TransactionType, @QuantityChange, @BeforeQty, @AfterQty,
                 N'ADJ', NULL, @PerformedByUserID, @ApprovedByUserID,
                 @ApprovalReference, @Reason);

            DECLARE @AuditDetails nvarchar(1000) =
                @TransactionType + N' change ' + CONVERT(nvarchar(20), @QuantityChange);
            DECLARE @AuditConfirmation nvarchar(20) =
                CASE WHEN @ApprovalReference IS NULL THEN N'NOT_REQUIRED' ELSE N'CONFIRMED' END;
            DECLARE @AuditVerification nvarchar(30) =
                CASE WHEN @ApprovedByUserID IS NULL THEN N'NONE' ELSE N'ADMIN_APPROVAL' END;

            EXEC dbo.sp_WriteAudit_060
                @Action = N'INVENTORY_ADJUST', @EntityType = N'Product',
                @EntityID = @ProductID,
                @Details = @AuditDetails,
                @ConfirmationStatus = @AuditConfirmation,
                @AdditionalVerification = @AuditVerification;
        IF @StartedTran = 1
            COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @StartedTran = 1 AND @@TRANCOUNT > 0
            ROLLBACK TRAN;
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_ProcessSale_060
    @CustomerID        int = NULL,
    @EmployeeID        int,
    @Items             dbo.tt_SaleItem_060 READONLY,
    @AmountPaid        decimal(18,2),
    @PaymentMethod     nvarchar(20) = N'CASH',
    @ProcessedByUserID int,
    @NewSaleID         int OUTPUT,
    @SaleNumberOut     nvarchar(30) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';

        IF NOT EXISTS (SELECT 1 FROM @Items)
            THROW 50130, N'A sale must contain at least one item.', 1;
        IF @AmountPaid < 0
            THROW 50131, N'Amount paid cannot be negative.', 1;
        IF @PaymentMethod NOT IN (N'CASH', N'CARD', N'BANK_TRANSFER', N'OTHER')
            THROW 50132, N'Invalid payment method.', 1;
        IF NOT EXISTS (SELECT 1 FROM dbo.Employees_060 WHERE EmployeeID = @EmployeeID AND IsActive = 1)
            THROW 50133, N'Employee not found or inactive.', 1;
        IF @CustomerID IS NOT NULL AND NOT EXISTS (SELECT 1 FROM dbo.Customers_060 WHERE CustomerID = @CustomerID AND IsActive = 1)
            THROW 50134, N'Customer not found or inactive.', 1;
        IF EXISTS (SELECT 1 FROM @Items WHERE Quantity <= 0 OR UnitPrice < 0 OR DiscountAmount < 0)
            THROW 50135, N'Invalid quantity, price or discount on a sale line.', 1;
        IF EXISTS
        (
            SELECT 1
            FROM @Items i
            LEFT JOIN dbo.Products_060 p ON p.ProductID = i.ProductID
            WHERE p.ProductID IS NULL OR p.IsActive = 0
        )
            THROW 50137, N'One or more products are unavailable.', 1;

        DECLARE @ValidatedItems TABLE
        (
            ProductID      int NOT NULL,
            Quantity       int NOT NULL,
            UnitPrice      decimal(18,2) NOT NULL,
            DiscountAmount decimal(18,2) NOT NULL
        );
        INSERT @ValidatedItems (ProductID, Quantity, UnitPrice, DiscountAmount)
        SELECT i.ProductID, i.Quantity, p.UnitPrice, i.DiscountAmount
        FROM @Items i
        JOIN dbo.Products_060 p ON p.ProductID = i.ProductID
        WHERE p.IsActive = 1;

        IF EXISTS
        (
            SELECT 1
            FROM @ValidatedItems
            WHERE DiscountAmount > Quantity * UnitPrice
        )
            THROW 50139, N'A line discount cannot exceed the product value.', 1;

        CREATE TABLE #Requested
        (
            ProductID int PRIMARY KEY,
            Qty       int NOT NULL
        );
        INSERT #Requested (ProductID, Qty)
        SELECT ProductID, SUM(Quantity) FROM @ValidatedItems GROUP BY ProductID;

        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRAN;
            SET @StartedTran = 1;
        END

        IF EXISTS
        (
            SELECT 1
            FROM #Requested r
            JOIN dbo.Inventory_060 inv WITH (UPDLOCK, HOLDLOCK) ON inv.ProductID = r.ProductID
            WHERE inv.QuantityOnHand < r.Qty
        )
            THROW 50136, N'Insufficient stock for one or more sale items.', 1;

        DECLARE @TaxRate decimal(5,4) = TRY_CONVERT(decimal(5,4),
            (SELECT SettingValue FROM dbo.SystemSettings_060 WHERE SettingName = N'TaxRate'));
        IF @TaxRate IS NULL SET @TaxRate = 0.08;

        DECLARE @SubTotal decimal(18,2) =
            (SELECT SUM(Quantity * UnitPrice - DiscountAmount) FROM @ValidatedItems);
        DECLARE @DiscountTotal decimal(18,2) =
            (SELECT SUM(DiscountAmount) FROM @ValidatedItems);
        DECLARE @TaxAmount decimal(18,2) = ROUND(@SubTotal * @TaxRate, 2);
        DECLARE @TotalAmount decimal(18,2) = @SubTotal + @TaxAmount;

        IF @AmountPaid < @TotalAmount
            THROW 50138, N'Amount paid is less than the sale total.', 1;

        INSERT dbo.Sales_060
            (SaleNumber, CustomerID, EmployeeID, SaleDate, SubTotal, DiscountAmount,
             TaxAmount, TotalAmount, AmountPaid, ChangeAmount, Status, Notes)
        VALUES
            (N'PENDING', @CustomerID, @EmployeeID, SYSDATETIME(), @SubTotal,
             @DiscountTotal, @TaxAmount, @TotalAmount, @AmountPaid,
             @AmountPaid - @TotalAmount, N'COMPLETED', N'Created by sp_ProcessSale_060');

        SET @NewSaleID = SCOPE_IDENTITY();
        SET @SaleNumberOut = N'S-' + CONVERT(nvarchar(4), YEAR(SYSDATETIME()))
            + N'-' + RIGHT(N'000000' + CONVERT(nvarchar(10), @NewSaleID), 6);

        UPDATE dbo.Sales_060 SET SaleNumber = @SaleNumberOut WHERE SaleID = @NewSaleID;

        INSERT dbo.SaleItems_060 (SaleID, ProductID, Quantity, UnitPrice, DiscountAmount, LineTotal)
        SELECT @NewSaleID, ProductID, Quantity, UnitPrice, DiscountAmount,
               Quantity * UnitPrice - DiscountAmount
        FROM @ValidatedItems;

        INSERT dbo.Payments_060
            (PaymentNumber, SaleID, Amount, PaymentMethod, PaymentDate, Status, ProcessedByUserID)
        VALUES
            (N'PAY-PENDING', @NewSaleID, @AmountPaid, @PaymentMethod, SYSDATETIME(),
             N'PAID', @ProcessedByUserID);

        UPDATE dbo.Payments_060
        SET PaymentNumber = N'PAY-' + CONVERT(nvarchar(4), YEAR(SYSDATETIME()))
            + N'-' + RIGHT(N'000000' + CONVERT(nvarchar(10), PaymentID), 6)
        WHERE SaleID = @NewSaleID AND PaymentNumber = N'PAY-PENDING';

        DECLARE @AuditDetails nvarchar(1000) = N'Total ' + CONVERT(nvarchar(20), @TotalAmount);
        EXEC dbo.sp_WriteAudit_060
            @Action = N'SALE_CREATED', @EntityType = N'Sale',
            @EntityID = @SaleNumberOut,
            @Details = @AuditDetails;

        IF @StartedTran = 1
            COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @StartedTran = 1 AND @@TRANCOUNT > 0
            ROLLBACK TRAN;
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_RecordPayment_060
    @SaleID            int,
    @Amount            decimal(18,2),
    @PaymentMethod     nvarchar(20),
    @ProcessedByUserID int,
    @ReferenceNumber   nvarchar(80) = NULL,
    @NewPaymentID      int OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';

        IF @Amount <= 0
            THROW 50140, N'Payment amount must be positive.', 1;
        IF @PaymentMethod NOT IN (N'CASH', N'CARD', N'BANK_TRANSFER', N'OTHER')
            THROW 50141, N'Invalid payment method.', 1;

        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRAN;
            SET @StartedTran = 1;
        END
            DECLARE @Total decimal(18,2), @AlreadyPaid decimal(18,2), @Status nvarchar(20);

            SELECT @Total = TotalAmount, @Status = Status
            FROM dbo.Sales_060 WITH (UPDLOCK, HOLDLOCK)
            WHERE SaleID = @SaleID;

            IF @Total IS NULL
                THROW 50142, N'Sale not found.', 1;
            IF @Status NOT IN (N'PENDING', N'COMPLETED')
                THROW 50143, N'Payments cannot be recorded for a cancelled or returned sale.', 1;

            SELECT @AlreadyPaid = ISNULL(SUM(Amount), 0)
            FROM dbo.Payments_060 WITH (UPDLOCK, HOLDLOCK)
            WHERE SaleID = @SaleID AND Status = N'PAID';

            IF @AlreadyPaid + @Amount > @Total
                THROW 50144, N'Payment would exceed the sale total.', 1;

            INSERT dbo.Payments_060
                (PaymentNumber, SaleID, Amount, PaymentMethod, PaymentDate, Status,
                 ReferenceNumber, ProcessedByUserID)
            VALUES
                (N'PAY-PENDING', @SaleID, @Amount, @PaymentMethod, SYSDATETIME(),
                 N'PAID', @ReferenceNumber, @ProcessedByUserID);

            SET @NewPaymentID = SCOPE_IDENTITY();

            UPDATE dbo.Payments_060
            SET PaymentNumber = N'PAY-' + CONVERT(nvarchar(4), YEAR(SYSDATETIME()))
                + N'-' + RIGHT(N'000000' + CONVERT(nvarchar(10), @NewPaymentID), 6)
            WHERE PaymentID = @NewPaymentID;

            UPDATE dbo.Sales_060
            SET AmountPaid = @AlreadyPaid + @Amount,
                ChangeAmount = CASE WHEN @AlreadyPaid + @Amount > TotalAmount
                                    THEN @AlreadyPaid + @Amount - TotalAmount ELSE 0 END
            WHERE SaleID = @SaleID;

            DECLARE @AuditDetails nvarchar(1000) = N'Amount '
                + CONVERT(nvarchar(20), @Amount) + N' for sale ' + CONVERT(nvarchar(10), @SaleID);
            EXEC dbo.sp_WriteAudit_060
                @Action = N'PAYMENT_RECORDED', @EntityType = N'Payment',
                @EntityID = @NewPaymentID,
                @Details = @AuditDetails;
        IF @StartedTran = 1
            COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @StartedTran = 1 AND @@TRANCOUNT > 0
            ROLLBACK TRAN;
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GenerateSalesReport_060
    @FromDate   date,
    @ToDate     date,
    @CustomerID int = NULL,
    @CategoryID int = NULL
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';

    IF @FromDate > @ToDate
        THROW 50150, N'From date cannot be after to date.', 1;

    SELECT
        s.SaleID,
        s.SaleNumber,
        s.SaleDate,
        s.CustomerID,
        c.FullName AS CustomerName,
        s.EmployeeID,
        e.FullName AS EmployeeName,
        s.SubTotal,
        s.DiscountAmount,
        s.TaxAmount,
        s.TotalAmount,
        s.AmountPaid,
        s.Status
    FROM dbo.Sales_060 s
    LEFT JOIN dbo.Customers_060 c ON c.CustomerID = s.CustomerID
    LEFT JOIN dbo.Employees_060 e ON e.EmployeeID = s.EmployeeID
    WHERE s.SaleDate >= @FromDate
      AND s.SaleDate < DATEADD(day, 1, @ToDate)
      AND (@CustomerID IS NULL OR s.CustomerID = @CustomerID)
      AND (@CategoryID IS NULL OR EXISTS
          (SELECT 1 FROM dbo.SaleItems_060 si
           JOIN dbo.Products_060 p ON p.ProductID = si.ProductID
           WHERE si.SaleID = s.SaleID AND p.CategoryID = @CategoryID))
    ORDER BY s.SaleDate DESC, s.SaleID DESC;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_ProcessReturn_060
    @SaleID            int,
    @Items             dbo.tt_ReturnItem_060 READONLY,
    @Reason            nvarchar(255),
    @RefundAmount      decimal(18,2),
    @EmployeeID        int,
    @ApprovedByUserID  int,
    @ApprovalReference nvarchar(100),
    @ProcessedByUserID int,
    @NewReturnID       int OUTPUT,
    @ReturnNumberOut   nvarchar(30) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';

        IF @ApprovedByUserID IS NULL OR @ApprovalReference IS NULL
            THROW 50160, N'A refund requires explicit administrator/manager approval.', 1;
        IF @RefundAmount < 0
            THROW 50161, N'Refund amount cannot be negative.', 1;
        IF NOT EXISTS (SELECT 1 FROM @Items)
            THROW 50162, N'At least one return item is required.', 1;
        IF NOT EXISTS (SELECT 1 FROM dbo.Sales_060 WHERE SaleID = @SaleID AND Status = N'COMPLETED')
            THROW 50163, N'Sale not found or not eligible for return.', 1;
        
        DECLARE @RequestedReturns TABLE
        (
            SaleItemID int NOT NULL PRIMARY KEY,
            Quantity   int NOT NULL
        );
        INSERT @RequestedReturns (SaleItemID, Quantity)
        SELECT SaleItemID, SUM(Quantity)
        FROM @Items
        GROUP BY SaleItemID;

        IF EXISTS
        (
            SELECT 1
            FROM @RequestedReturns i
            JOIN dbo.SaleItems_060 si ON si.SaleItemID = i.SaleItemID
            WHERE si.SaleID <> @SaleID OR i.Quantity <= 0
               OR i.Quantity > si.Quantity - si.ReturnedQuantity
        )
            THROW 50164, N'Invalid return quantity or item does not belong to this sale.', 1;
        IF EXISTS
        (
            SELECT 1
            FROM @RequestedReturns i
            LEFT JOIN dbo.SaleItems_060 si ON si.SaleItemID = i.SaleItemID
            WHERE si.SaleItemID IS NULL
        )
            THROW 50165, N'One or more return items could not be found.', 1;

        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRAN;
            SET @StartedTran = 1;
        END
            DECLARE @CustomerID int;
            SELECT @CustomerID = CustomerID FROM dbo.Sales_060 WHERE SaleID = @SaleID;

            INSERT dbo.Returns_060
                (ReturnNumber, SaleID, CustomerID, EmployeeID, ReturnDate, Reason,
                 RefundAmount, Status, ApprovedByUserID, ApprovalReference, ProcessedByUserID)
            VALUES
                (N'RET-PENDING', @SaleID, @CustomerID, @EmployeeID, SYSDATETIME(),
                 @Reason, @RefundAmount, N'COMPLETED', @ApprovedByUserID,
                 @ApprovalReference, @ProcessedByUserID);

            SET @NewReturnID = SCOPE_IDENTITY();
            SET @ReturnNumberOut = N'RET-' + CONVERT(nvarchar(4), YEAR(SYSDATETIME()))
                + N'-' + RIGHT(N'000000' + CONVERT(nvarchar(10), @NewReturnID), 6);

            UPDATE dbo.Returns_060 SET ReturnNumber = @ReturnNumberOut
            WHERE ReturnID = @NewReturnID;

            INSERT dbo.ReturnItems_060 (ReturnID, SaleItemID, Quantity, RefundAmount, [Condition], Restock)
            SELECT @NewReturnID, i.SaleItemID, i.Quantity,
                   ROUND(i.Quantity * si.UnitPrice, 2), N'GOOD', 1
            FROM @RequestedReturns i
            JOIN dbo.SaleItems_060 si ON si.SaleItemID = i.SaleItemID;

            UPDATE si
            SET ReturnedQuantity = si.ReturnedQuantity + i.Quantity
            FROM dbo.SaleItems_060 si
            JOIN @RequestedReturns i ON i.SaleItemID = si.SaleItemID;

            DECLARE @AuditDetails nvarchar(1000) = N'Refund ' + CONVERT(nvarchar(20), @RefundAmount);
            EXEC dbo.sp_WriteAudit_060
                @Action = N'RETURN_COMPLETED', @EntityType = N'Return',
                @EntityID = @ReturnNumberOut,
                @Details = @AuditDetails,
                @ConfirmationStatus = N'CONFIRMED',
                @AdditionalVerification = N'ADMIN_APPROVAL';
        IF @StartedTran = 1
            COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @StartedTran = 1 AND @@TRANCOUNT > 0
            ROLLBACK TRAN;
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_RequestSensitiveAction_060
    @ActionKey        nvarchar(80),
    @EntityType       nvarchar(50)   = NULL,
    @EntityID         nvarchar(50)   = NULL,
    @ClientRequestID  nvarchar(100),
    @Details          nvarchar(1000) = NULL,
    @ExpiresInMinutes int            = NULL,
    @Token            uniqueidentifier OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        DECLARE @SessionUserID int = TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID'));
        DECLARE @AllowedRoles nvarchar(200), @ConfiguredRisk nvarchar(20), @DefaultExpiry int;

        SELECT @AllowedRoles = AllowedRoles,
               @ConfiguredRisk = RiskLevel,
               @DefaultExpiry = DefaultExpiryMinutes
        FROM dbo.SensitiveActions_060
        WHERE ActionKey = @ActionKey AND IsActive = 1;

        IF @AllowedRoles IS NULL
            THROW 50170, N'Unknown or inactive sensitive action.', 1;

        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = @AllowedRoles;

        IF @ExpiresInMinutes IS NULL OR @ExpiresInMinutes <= 0
            SET @ExpiresInMinutes = @DefaultExpiry;

        SELECT TOP 1 @Token = Token
        FROM dbo.SensitiveActionApprovals_060
        WHERE UserID = @SessionUserID
          AND ClientRequestID = @ClientRequestID
          AND Status = N'PENDING'
          AND ExpiresAt > SYSDATETIME();

        IF @Token IS NOT NULL RETURN;

        SET @Token = NEWID();

        INSERT dbo.SensitiveActionApprovals_060
            (Token, UserID, ActionKey, EntityType, EntityID, RiskLevel, Status,
             ClientRequestID, ExpiresAt, Details)
        VALUES
            (@Token, @SessionUserID, @ActionKey, @EntityType, @EntityID,
             @ConfiguredRisk, N'PENDING', @ClientRequestID,
             DATEADD(minute, @ExpiresInMinutes, SYSDATETIME()), @Details);

        EXEC dbo.sp_WriteAudit_060
            @Action = N'APPROVAL_REQUESTED', @EntityType = @EntityType,
            @EntityID = @EntityID, @Details = @ActionKey,
            @ConfirmationStatus = N'REQUESTED';
    END TRY
    BEGIN CATCH
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_ConfirmSensitiveAction_060
    @Token              uniqueidentifier,
    @VerificationMethod nvarchar(30)   = N'NONE',
    @VerifierUserID     int            = NULL,
    @ActionKey          nvarchar(80)   OUTPUT,
    @EntityType         nvarchar(50)   OUTPUT,
    @EntityID           nvarchar(50)   OUTPUT,
    @RiskLevel          nvarchar(20)   OUTPUT,
    @UserID             int            OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        DECLARE @Status nvarchar(20), @ExpiresAt datetime2(0), @AllowedRoles nvarchar(200);

        SELECT @Status = a.Status, @ExpiresAt = a.ExpiresAt, @ActionKey = a.ActionKey,
               @EntityType = a.EntityType, @EntityID = a.EntityID,
               @RiskLevel = a.RiskLevel, @UserID = a.UserID
        FROM dbo.SensitiveActionApprovals_060 a WITH (UPDLOCK, HOLDLOCK)
        WHERE a.Token = @Token;

        IF @Status IS NULL
            THROW 50180, N'Approval token not found.', 1;
        IF @Status <> N'PENDING'
            THROW 50181, N'Approval token has already been used or cancelled.', 1;
        IF @ExpiresAt <= SYSDATETIME()
            THROW 50182, N'Approval window has expired.', 1;

        SELECT @AllowedRoles = AllowedRoles
        FROM dbo.SensitiveActions_060 WHERE ActionKey = @ActionKey AND IsActive = 1;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = @AllowedRoles;

        IF @RiskLevel = N'ELEVATED' AND @VerificationMethod NOT IN (N'PASSWORD', N'PHRASE', N'ADMIN_APPROVAL')
            THROW 50183, N'Additional verification is required for this action.', 1;

        IF @RiskLevel = N'CRITICAL'
        BEGIN
            IF @VerificationMethod <> N'PASSWORD_AND_PHRASE' OR @VerifierUserID IS NULL
                THROW 50184, N'Critical actions require password, phrase and a second approver.', 1;
            IF @VerifierUserID = TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID'))
                THROW 50187, N'The requester cannot approve their own critical action.', 1;
            IF NOT EXISTS
            (
                SELECT 1
                FROM dbo.UserRoles_060 ur
                JOIN dbo.Roles_060 r ON r.RoleID = ur.RoleID
                WHERE ur.UserID = @VerifierUserID
                  AND r.RoleName = N'Administrator'
                  AND ur.IsActive = 1
            )
                THROW 50185, N'Second approver is not an active administrator.', 1;
        END

        UPDATE dbo.SensitiveActionApprovals_060
        SET Status = N'CONFIRMED',
            ConfirmedAt = SYSDATETIME(),
            ConfirmedByUserID = ISNULL(@VerifierUserID, TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID'))),
            VerificationMethod = @VerificationMethod
        WHERE Token = @Token AND Status = N'PENDING' AND ExpiresAt > SYSDATETIME();

        IF @@ROWCOUNT <> 1
            THROW 50186, N'Approval could not be confirmed; request was changed concurrently.', 1;

        EXEC dbo.sp_WriteAudit_060
            @Action = N'APPROVAL_CONFIRMED', @EntityType = @EntityType,
            @EntityID = @EntityID, @Details = @ActionKey,
            @ConfirmationStatus = N'CONFIRMED',
            @AdditionalVerification = @VerificationMethod;
    END TRY
    BEGIN CATCH
        THROW;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_CancelSensitiveAction_060
    @Token uniqueidentifier
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @ActionKey nvarchar(80), @EntityType nvarchar(50), @EntityID nvarchar(50), @UserID int;

    SELECT @ActionKey = ActionKey, @EntityType = EntityType,
           @EntityID = EntityID, @UserID = UserID
    FROM dbo.SensitiveActionApprovals_060 WITH (UPDLOCK, HOLDLOCK)
    WHERE Token = @Token;

    IF @ActionKey IS NULL
        THROW 50190, N'Approval token not found.', 1;
    IF @UserID <> TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID'))
        THROW 50191, N'Only the requesting user can cancel this approval.', 1;

    UPDATE dbo.SensitiveActionApprovals_060
    SET Status = N'CANCELLED'
    WHERE Token = @Token AND Status = N'PENDING';

    EXEC dbo.sp_WriteAudit_060
        @Action = N'APPROVAL_CANCELLED', @EntityType = @EntityType,
        @EntityID = @EntityID, @Details = @ActionKey,
        @ConfirmationStatus = N'CANCELLED',
        @Outcome = N'CANCELLED';
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetProductCatalog_060
    @CategoryID int = NULL,
    @SearchText nvarchar(100) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee,Customer';

    SELECT p.ProductID, p.SKU, p.ProductName, p.UnitPrice, p.CategoryID,
           c.CategoryName, inv.QuantityOnHand
    FROM dbo.Products_060 p
    JOIN dbo.Categories_060 c ON c.CategoryID = p.CategoryID
    LEFT JOIN dbo.Inventory_060 inv ON inv.ProductID = p.ProductID
    WHERE p.IsActive = 1
      AND (@CategoryID IS NULL OR p.CategoryID = @CategoryID)
      AND (@SearchText IS NULL OR p.ProductName LIKE N'%' + @SearchText + N'%' OR p.SKU LIKE N'%' + @SearchText + N'%')
    ORDER BY p.ProductName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetMySales_060
    @CustomerID int
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Customer';

    DECLARE @SessionUserID int = TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID'));

    IF NOT EXISTS (SELECT 1 FROM dbo.Customers_060 WHERE CustomerID = @CustomerID AND UserID = @SessionUserID)
        THROW 50200, N'Customers may only view their own purchase history.', 1;

    SELECT s.SaleID, s.SaleNumber, s.SaleDate, s.TotalAmount, s.AmountPaid, s.Status,
           (SELECT COUNT(*) FROM dbo.SaleItems_060 si WHERE si.SaleID = s.SaleID) AS ItemCount
    FROM dbo.Sales_060 s
    WHERE s.CustomerID = @CustomerID
    ORDER BY s.SaleDate DESC;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_AssignUserRole_060
    @UserID           int,
    @RoleID           int,
    @ApprovalReference nvarchar(100),
    @AssignedByUserID int
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @StartedTran bit = 0;
        EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator';

        IF @ApprovalReference IS NULL
            THROW 50210, N'Role changes require a confirmed approval reference.', 1;
        IF NOT EXISTS (SELECT 1 FROM dbo.Users_060 WHERE UserID = @UserID AND IsActive = 1)
            THROW 50211, N'User not found or inactive.', 1;
        IF NOT EXISTS (SELECT 1 FROM dbo.Roles_060 WHERE RoleID = @RoleID AND IsActive = 1)
            THROW 50212, N'Role not found or inactive.', 1;

        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRAN;
            SET @StartedTran = 1;
        END
            IF EXISTS (SELECT 1 FROM dbo.UserRoles_060 WHERE UserID = @UserID AND RoleID = @RoleID)
                UPDATE dbo.UserRoles_060
                SET IsActive = 1, AssignedAt = SYSDATETIME(), AssignedByUserID = @AssignedByUserID
                WHERE UserID = @UserID AND RoleID = @RoleID;
            ELSE
                INSERT dbo.UserRoles_060 (UserID, RoleID, AssignedByUserID, IsActive)
                VALUES (@UserID, @RoleID, @AssignedByUserID, 1);

            DECLARE @AuditDetails nvarchar(1000) = N'RoleID '
                + CONVERT(nvarchar(10), @RoleID) + N' ref ' + @ApprovalReference;
            EXEC dbo.sp_WriteAudit_060
                @Action = N'USER_ROLE_CHANGED', @EntityType = N'User',
                @EntityID = @UserID,
                @Details = @AuditDetails,
                @ConfirmationStatus = N'CONFIRMED',
                @AdditionalVerification = N'PASSWORD_AND_PHRASE';
        IF @StartedTran = 1
            COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @StartedTran = 1 AND @@TRANCOUNT > 0
            ROLLBACK TRAN;
        THROW;
    END CATCH
END
GO

GRANT EXECUTE ON dbo.sp_WriteAudit_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_RegisterProduct_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_RegisterCustomer_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_UpdateInventory_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_ProcessSale_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_RecordPayment_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GenerateSalesReport_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_ProcessReturn_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_RequestSensitiveAction_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_ConfirmSensitiveAction_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_CancelSensitiveAction_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetProductCatalog_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetMySales_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_AssignUserRole_060 TO rms_admin_060;
GO

PRINT 'SECTION 9 OK - Stored procedures created and granted.';
GO

/*==============================================================================
  SECTION 10 - TRIGGERS (set-based and multi-row safe)
==============================================================================*/
CREATE OR ALTER TRIGGER dbo.trg_SaleItems_UpdateStock_060
ON dbo.SaleItems_060 AFTER INSERT
AS
BEGIN
    IF NOT EXISTS (SELECT 1 FROM inserted) RETURN;
    SET NOCOUNT ON;

    IF EXISTS
    (
        SELECT 1
        FROM inserted i
        LEFT JOIN dbo.Inventory_060 inv ON inv.ProductID = i.ProductID
        WHERE inv.ProductID IS NULL
    )
        THROW 50220, N'Cannot sell a product without an inventory record.', 1;

    DECLARE @Changes TABLE
    (
        ProductID int PRIMARY KEY,
        BeforeQty int NOT NULL,
        AfterQty  int NOT NULL
    );

    UPDATE inv
    SET QuantityOnHand = inv.QuantityOnHand - s.Qty,
        LastUpdatedAt = SYSDATETIME()
    OUTPUT inserted.ProductID, deleted.QuantityOnHand, inserted.QuantityOnHand
    INTO @Changes (ProductID, BeforeQty, AfterQty)
    FROM dbo.Inventory_060 inv
    JOIN (SELECT ProductID, SUM(Quantity) AS Qty FROM inserted GROUP BY ProductID) s
      ON s.ProductID = inv.ProductID;

    IF EXISTS (SELECT 1 FROM @Changes WHERE AfterQty < 0)
        THROW 50221, N'Sale would make stock negative.', 1;

    INSERT dbo.InventoryTransactions_060
        (ProductID, TransactionType, QuantityChange, QuantityBefore, QuantityAfter,
         ReferenceType, ReferenceID, PerformedByUserID, Reason)
    SELECT c.ProductID, N'SALE', c.AfterQty - c.BeforeQty, c.BeforeQty, c.AfterQty,
           N'SALE',
           (SELECT MIN(i.SaleID) FROM inserted i WHERE i.ProductID = c.ProductID),
           TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
           N'Stock reduced by sale trigger.'
    FROM @Changes c;
END
GO

CREATE OR ALTER TRIGGER dbo.trg_ReturnItems_Restock_060
ON dbo.ReturnItems_060 AFTER INSERT
AS
BEGIN
    IF NOT EXISTS (SELECT 1 FROM inserted) RETURN;
    SET NOCOUNT ON;

    DECLARE @Changes TABLE
    (
        ProductID int PRIMARY KEY,
        BeforeQty int NOT NULL,
        AfterQty  int NOT NULL
    );

    UPDATE inv
    SET QuantityOnHand = inv.QuantityOnHand + r.Qty,
        LastUpdatedAt = SYSDATETIME()
    OUTPUT inserted.ProductID, deleted.QuantityOnHand, inserted.QuantityOnHand
    INTO @Changes (ProductID, BeforeQty, AfterQty)
    FROM dbo.Inventory_060 inv
    JOIN
    (
        SELECT si.ProductID, SUM(i.Quantity) AS Qty
        FROM inserted i
        JOIN dbo.SaleItems_060 si ON si.SaleItemID = i.SaleItemID
        JOIN dbo.Returns_060 r ON r.ReturnID = i.ReturnID
        WHERE i.Restock = 1 AND r.Status = N'COMPLETED'
        GROUP BY si.ProductID
    ) r ON r.ProductID = inv.ProductID;

    IF @@ROWCOUNT = 0 RETURN;

    INSERT dbo.InventoryTransactions_060
        (ProductID, TransactionType, QuantityChange, QuantityBefore, QuantityAfter,
         ReferenceType, ReferenceID, PerformedByUserID, Reason)
    SELECT c.ProductID, N'RETURN', c.AfterQty - c.BeforeQty, c.BeforeQty, c.AfterQty,
           N'RETURN',
           (SELECT MIN(i.ReturnID) FROM inserted i
            JOIN dbo.SaleItems_060 si ON si.SaleItemID = i.SaleItemID
            WHERE si.ProductID = c.ProductID),
           TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
           N'Stock restored by return trigger.'
    FROM @Changes c;
END
GO

CREATE OR ALTER TRIGGER dbo.trg_PreventInvalidStockOperation_060
ON dbo.InventoryTransactions_060 AFTER INSERT
AS
BEGIN
    IF NOT EXISTS (SELECT 1 FROM inserted) RETURN;
    SET NOCOUNT ON;

    IF EXISTS
    (
        SELECT 1 FROM inserted
        WHERE (TransactionType IN (N'PURCHASE', N'RETURN', N'ADJUSTMENT_IN') AND QuantityChange < 0)
           OR (TransactionType IN (N'SALE', N'ADJUSTMENT_OUT', N'VOID') AND QuantityChange > 0)
           OR (QuantityAfter < 0)
    )
        THROW 50222, N'Invalid stock transaction detected.', 1;

    IF EXISTS
    (
        SELECT 1 FROM inserted
        WHERE TransactionType = N'ADJUSTMENT_OUT' AND ApprovedByUserID IS NULL
    )
        THROW 50223, N'Stock reduction requires an approving user.', 1;
END
GO

CREATE OR ALTER TRIGGER dbo.trg_AuditInventoryTransactions_060
ON dbo.InventoryTransactions_060 AFTER INSERT
AS
BEGIN
    IF NOT EXISTS (SELECT 1 FROM inserted) RETURN;
    SET NOCOUNT ON;

    INSERT dbo.AuditLogs_060
        (UserID, UserRole, Action, EntityType, EntityID, Details,
         ConfirmationStatus, AdditionalVerification, Outcome, OccurredAt)
    SELECT TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
           TRY_CONVERT(nvarchar(50), SESSION_CONTEXT(N'AppRole')),
           N'INVENTORY_TRANSACTION', N'InventoryTransaction', i.TransactionID,
           i.TransactionType + N' change ' + CONVERT(nvarchar(10), i.QuantityChange),
           N'NOT_REQUIRED',
           CASE WHEN i.ApprovedByUserID IS NULL THEN N'NONE' ELSE N'ADMIN_APPROVAL' END,
           N'SUCCESS', SYSDATETIME()
    FROM inserted i;
END
GO

CREATE OR ALTER TRIGGER dbo.trg_AuditProducts_060
ON dbo.Products_060 AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    INSERT dbo.AuditLogs_060
        (UserID, UserRole, Action, EntityType, EntityID, Details, OccurredAt)
    SELECT TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
           TRY_CONVERT(nvarchar(50), SESSION_CONTEXT(N'AppRole')),
           N'PRODUCT_CREATE', N'Product', i.ProductID, i.ProductName, SYSDATETIME()
    FROM inserted i
    WHERE NOT EXISTS (SELECT 1 FROM deleted d WHERE d.ProductID = i.ProductID)

    UNION ALL

    SELECT TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
           TRY_CONVERT(nvarchar(50), SESSION_CONTEXT(N'AppRole')),
           N'PRODUCT_UPDATE', N'Product', i.ProductID,
           i.ProductName + N' (previous price ' + CONVERT(nvarchar(20), d.UnitPrice) + N')',
           SYSDATETIME()
    FROM inserted i
    JOIN deleted d ON d.ProductID = i.ProductID

    UNION ALL

    SELECT TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
           TRY_CONVERT(nvarchar(50), SESSION_CONTEXT(N'AppRole')),
           N'PRODUCT_DELETE', N'Product', d.ProductID, d.ProductName, SYSDATETIME()
    FROM deleted d
    WHERE NOT EXISTS (SELECT 1 FROM inserted i WHERE i.ProductID = d.ProductID);
END
GO

CREATE OR ALTER TRIGGER dbo.trg_AuditUsers_060
ON dbo.Users_060 AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    INSERT dbo.AuditLogs_060
        (UserID, UserRole, Action, EntityType, EntityID, Details, OccurredAt)
    SELECT TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
           TRY_CONVERT(nvarchar(50), SESSION_CONTEXT(N'AppRole')),
           N'USER_CREATE', N'User', i.UserID, i.Username, SYSDATETIME()
    FROM inserted i
    WHERE NOT EXISTS (SELECT 1 FROM deleted d WHERE d.UserID = i.UserID)

    UNION ALL

    SELECT TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
           TRY_CONVERT(nvarchar(50), SESSION_CONTEXT(N'AppRole')),
           N'USER_UPDATE', N'User', i.UserID, i.Username, SYSDATETIME()
    FROM inserted i
    JOIN deleted d ON d.UserID = i.UserID

    UNION ALL

    SELECT TRY_CONVERT(int, SESSION_CONTEXT(N'AppUserID')),
           TRY_CONVERT(nvarchar(50), SESSION_CONTEXT(N'AppRole')),
           N'USER_DELETE', N'User', d.UserID, d.Username, SYSDATETIME()
    FROM deleted d
    WHERE NOT EXISTS (SELECT 1 FROM inserted i WHERE i.UserID = d.UserID);
END
GO

PRINT 'SECTION 10 OK - Triggers created (multi-row safe).';
GO

/*==============================================================================
  SECTION 11 - SCALAR AND TABLE-VALUED FUNCTIONS
==============================================================================*/
CREATE OR ALTER FUNCTION dbo.fn_CalculateTotal_060
(
    @Quantity       int,
    @UnitPrice      decimal(18,2),
    @DiscountAmount decimal(18,2)
)
RETURNS decimal(18,2)
WITH SCHEMABINDING
AS
BEGIN
    DECLARE @Qty int = ISNULL(@Quantity, 0);
    DECLARE @Price decimal(18,2) = ISNULL(@UnitPrice, 0);
    DECLARE @Disc decimal(18,2) = ISNULL(@DiscountAmount, 0);
    RETURN CONVERT(decimal(18,2), (@Qty * @Price) - @Disc);
END
GO

CREATE OR ALTER FUNCTION dbo.fn_GetProductsByCategory_060
(
    @CategoryID int
)
RETURNS TABLE
WITH SCHEMABINDING
AS
RETURN
    SELECT p.ProductID, p.SKU, p.ProductName, p.UnitPrice, p.CategoryID,
           c.CategoryName, i.QuantityOnHand
    FROM dbo.Products_060 p
    JOIN dbo.Categories_060 c ON c.CategoryID = p.CategoryID
    LEFT JOIN dbo.Inventory_060 i ON i.ProductID = p.ProductID
    WHERE p.IsActive = 1 AND (@CategoryID IS NULL OR p.CategoryID = @CategoryID);
GO

CREATE OR ALTER FUNCTION dbo.fn_GetSalesByDateRange_060
(
    @FromDate   date,
    @ToDate     date,
    @CustomerID int = NULL
)
RETURNS TABLE
WITH SCHEMABINDING
AS
RETURN
    SELECT s.SaleID, s.SaleNumber, s.SaleDate, s.CustomerID,
           s.TotalAmount, s.AmountPaid, s.Status
    FROM dbo.Sales_060 s
    WHERE s.SaleDate >= @FromDate
      AND s.SaleDate < DATEADD(day, 1, @ToDate)
      AND (@CustomerID IS NULL OR s.CustomerID = @CustomerID);
GO

GRANT SELECT ON dbo.fn_GetProductsByCategory_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT SELECT ON dbo.fn_GetSalesByDateRange_060 TO rms_admin_060, rms_manager_060;
GO

SELECT dbo.fn_CalculateTotal_060(10, 45.00, 0.00) AS ScalarFunctionResult;
SELECT ProductID, SKU, ProductName, UnitPrice FROM dbo.fn_GetProductsByCategory_060(4);
SELECT SaleNumber, TotalAmount FROM dbo.fn_GetSalesByDateRange_060('2026-11-01', '2026-11-30', 1);
GO

PRINT 'SECTION 11 OK - Functions created and demonstrated.';
GO

/*==============================================================================
  SECTION 12 - IF...ELSE DECISION LOGIC (read-only demonstration)
  The same pattern is used inside sp_ProcessSale_060 for stock eligibility.
==============================================================================*/
DECLARE @ProductID int = 1;
DECLARE @RequiredQty int = 5;
DECLARE @AvailableQty int = ISNULL((SELECT QuantityOnHand FROM dbo.Inventory_060 WHERE ProductID = @ProductID), 0);

IF @AvailableQty >= @RequiredQty
    PRINT N'IF-ELSE RESULT: Product ' + CONVERT(nvarchar(10), @ProductID)
        + N' has sufficient stock (' + CONVERT(nvarchar(10), @AvailableQty) + N').';
ELSE
    PRINT N'IF-ELSE RESULT: Product ' + CONVERT(nvarchar(10), @ProductID)
        + N' has insufficient stock (' + CONVERT(nvarchar(10), @AvailableQty) + N').';
GO

PRINT 'SECTION 12 OK - IF...ELSE demonstrated.';
GO

/*==============================================================================
  SECTION 13 - AUDIT AND LOGGING
==============================================================================*/
CREATE OR ALTER PROCEDURE dbo.sp_GetAuditTrail_060
    @FromDate datetime2(0) = NULL,
    @ToDate   datetime2(0) = NULL,
    @UserID   int = NULL
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator';

    SELECT a.AuditLogID, a.OccurredAt, a.UserID, u.Username, a.UserRole,
           a.Action, a.EntityType, a.EntityID, a.Details,
           a.ConfirmationStatus, a.AdditionalVerification, a.Outcome
    FROM dbo.AuditLogs_060 a
    LEFT JOIN dbo.Users_060 u ON u.UserID = a.UserID
    WHERE (@FromDate IS NULL OR a.OccurredAt >= @FromDate)
      AND (@ToDate IS NULL OR a.OccurredAt <= @ToDate)
      AND (@UserID IS NULL OR a.UserID = @UserID)
    ORDER BY a.OccurredAt DESC, a.AuditLogID DESC;
END
GO

GRANT EXECUTE ON dbo.sp_GetAuditTrail_060 TO rms_admin_060;
GO

PRINT 'SECTION 13 OK - Audit logging procedure available.';
GO

/*==============================================================================
  SECTION 14 - UNAUTHORIZED ACCESS DEMONSTRATIONS
  Expected permission errors are caught and printed. No data is changed.
==============================================================================*/

PRINT 'DEMO 1: Employee database role attempts to read Users_060 (expect DENIAL).';
GO
EXECUTE AS USER = 'rms_demo_staff_060';
GO
BEGIN TRY
    SELECT TOP 1 UserID FROM dbo.Users_060;
    PRINT 'UNEXPECTED: staff read Users_060.';
END TRY
BEGIN CATCH
    PRINT 'EXPECTED DENIAL: ' + ERROR_MESSAGE();
END CATCH;
GO
REVERT;
GO

PRINT 'DEMO 2: Manager attempts to execute administrator-only role assignment (expect DENIAL).';
GO
EXECUTE AS USER = 'rms_demo_manager_060';
GO
BEGIN TRY
    EXEC dbo.sp_AssignUserRole_060 @UserID = 4, @RoleID = 1,
        @ApprovalReference = N'DEMO-BAD', @AssignedByUserID = 2;
    PRINT 'UNEXPECTED: manager executed role assignment.';
END TRY
BEGIN CATCH
    PRINT 'EXPECTED DENIAL: ' + ERROR_MESSAGE();
END CATCH;
GO
REVERT;
GO

PRINT 'DEMO 3: Manager attempts direct UPDATE of Users_060 (expect DENIAL).';
GO
EXECUTE AS USER = 'rms_demo_manager_060';
GO
BEGIN TRY
    UPDATE dbo.Users_060 SET FullName = FullName WHERE UserID = 1;
    PRINT 'UNEXPECTED: manager updated Users_060.';
END TRY
BEGIN CATCH
    PRINT 'EXPECTED DENIAL: ' + ERROR_MESSAGE();
END CATCH;
GO
REVERT;
GO

PRINT 'DEMO 4: Customer session attempts to view another customer history (expect DENIAL).';
GO
EXEC dbo.sp_SetAppContext_060 @AppUserID = 5, @AppRole = N'Customer';
GO
BEGIN TRY
    EXEC dbo.sp_GetMySales_060 @CustomerID = 2;
    PRINT 'UNEXPECTED: customer viewed another customer history.';
END TRY
BEGIN CATCH
    PRINT 'EXPECTED DENIAL: ' + ERROR_MESSAGE();
END CATCH;
GO

PRINT 'DEMO 5: Direct confirmation attempt with a fabricated token (expect DENIAL).';
GO
DECLARE @ak nvarchar(80), @et nvarchar(50), @eid nvarchar(50), @rl nvarchar(20), @uid int;
BEGIN TRY
    EXEC dbo.sp_ConfirmSensitiveAction_060
        @Token = '00000000-0000-0000-0000-000000000000',
        @VerificationMethod = N'NONE', @VerifierUserID = NULL,
        @ActionKey = @ak OUTPUT, @EntityType = @et OUTPUT,
        @EntityID = @eid OUTPUT, @RiskLevel = @rl OUTPUT, @UserID = @uid OUTPUT;
    PRINT 'UNEXPECTED: fabricated token was accepted.';
END TRY
BEGIN CATCH
    PRINT 'EXPECTED DENIAL: ' + ERROR_MESSAGE();
END CATCH;
GO

PRINT 'SECTION 14 OK - Unauthorized attempts demonstrated and denied.';
GO

/*==============================================================================
  SECTION 15 - BACKUP AND RECOVERY (environment-specific; NOT auto-executed)
  The SQL Server service account must have read/write access to the backup path.
  Create C:\SQLBackups first. Replace paths before running in SSMS.
==============================================================================*/
/*
-- One-time setup outside SQL: create folder C:\SQLBackups and grant the
-- MSSQLSERVER service account modify permission on it.

-- Full backup (destructive to an existing file with the same name because INIT):
BACKUP DATABASE [RetailManagementDB]
    TO DISK = N'C:\SQLBackups\RetailManagementDB_060_Full.bak'
    WITH INIT, COMPRESSION, STATS = 10;
GO

-- Differential backup:
BACKUP DATABASE [RetailManagementDB]
    TO DISK = N'C:\SQLBackups\RetailManagementDB_060_Diff.bak'
    WITH DIFFERENTIAL, COMPRESSION, STATS = 10;
GO

-- Verify backup media:
RESTORE VERIFYONLY
    FROM DISK = N'C:\SQLBackups\RetailManagementDB_060_Full.bak';
GO

-- Restore to a TEST copy (non-destructive to the live database).
-- The WITH MOVE clauses are required when file names clash with the live DB.
RESTORE DATABASE [RetailManagementDB_TestRestore_060]
    FROM DISK = N'C:\SQLBackups\RetailManagementDB_060_Full.bak'
    WITH MOVE N'RetailManagementDB' TO N'C:\Program Files\Microsoft SQL Server\MSSQL17.MSSQLSERVER\MSSQL\DATA\RetailManagementDB_TestRestore_060.mdf',
         MOVE N'RetailManagementDB_log' TO N'C:\Program Files\Microsoft SQL Server\MSSQL17.MSSQLSERVER\MSSQL\DATA\RetailManagementDB_TestRestore_060.ldf',
         STATS = 10;
GO

-- Restore the live database (DESTRUCTIVE - requires confirmed maintenance window):
-- ALTER DATABASE [RetailManagementDB] SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
-- RESTORE DATABASE [RetailManagementDB]
--     FROM DISK = N'C:\SQLBackups\RetailManagementDB_060_Full.bak'
--     WITH REPLACE, RECOVERY, STATS = 10;
-- ALTER DATABASE [RetailManagementDB] SET MULTI_USER;
GO
*/

PRINT 'SECTION 15 OK - Backup/restore guidance documented (not executed).';
GO

/*==============================================================================
  SECTION 16 - TESTING QUERIES (read-only; safe to rerun)
==============================================================================*/
SELECT 'Roles' AS Entity, COUNT(*) AS RecordCount FROM dbo.Roles_060
UNION ALL SELECT 'Users', COUNT(*) FROM dbo.Users_060
UNION ALL SELECT 'UserRoles', COUNT(*) FROM dbo.UserRoles_060
UNION ALL SELECT 'Employees', COUNT(*) FROM dbo.Employees_060
UNION ALL SELECT 'Customers', COUNT(*) FROM dbo.Customers_060
UNION ALL SELECT 'Suppliers', COUNT(*) FROM dbo.Suppliers_060
UNION ALL SELECT 'Categories', COUNT(*) FROM dbo.Categories_060
UNION ALL SELECT 'Products', COUNT(*) FROM dbo.Products_060
UNION ALL SELECT 'Inventory', COUNT(*) FROM dbo.Inventory_060
UNION ALL SELECT 'InventoryTransactions', COUNT(*) FROM dbo.InventoryTransactions_060
UNION ALL SELECT 'Sales', COUNT(*) FROM dbo.Sales_060
UNION ALL SELECT 'SaleItems', COUNT(*) FROM dbo.SaleItems_060
UNION ALL SELECT 'Payments', COUNT(*) FROM dbo.Payments_060
UNION ALL SELECT 'Returns', COUNT(*) FROM dbo.Returns_060
UNION ALL SELECT 'ReturnItems', COUNT(*) FROM dbo.ReturnItems_060
UNION ALL SELECT 'AuditLogs', COUNT(*) FROM dbo.AuditLogs_060
UNION ALL SELECT 'SensitiveActions', COUNT(*) FROM dbo.SensitiveActions_060
UNION ALL SELECT 'SensitiveActionApprovals', COUNT(*) FROM dbo.SensitiveActionApprovals_060
UNION ALL SELECT 'SystemSettings', COUNT(*) FROM dbo.SystemSettings_060;
GO

SELECT name AS ObjectName, type_desc AS ObjectType
FROM sys.objects
WHERE name LIKE '%[_]060' AND type IN (N'P', N'TR', N'FN', N'IF', N'U', N'TT')
ORDER BY ObjectType, ObjectName;
GO

SELECT COUNT(*) AS UnapprovedStockReductions
FROM dbo.InventoryTransactions_060
WHERE TransactionType = N'ADJUSTMENT_OUT' AND ApprovedByUserID IS NULL;
GO

SELECT Status, RiskLevel, COUNT(*) AS ApprovalCount
FROM dbo.SensitiveActionApprovals_060
GROUP BY Status, RiskLevel
ORDER BY Status, RiskLevel;
GO

SELECT HAS_PERMS_BY_NAME(N'dbo.Users_060', N'OBJECT', N'SELECT') AS CanSelectUsers,
       HAS_PERMS_BY_NAME(N'dbo.Products_060', N'OBJECT', N'SELECT') AS CanSelectProducts;
GO

/*
-- Data-changing procedure smoke tests (run manually, then verify with the
-- queries above. Each call sets its application role context first.)
EXEC dbo.sp_SetAppContext_060 @AppUserID = 2, @AppRole = N'Manager';
DECLARE @NewProductID int;
EXEC dbo.sp_RegisterProduct_060 @SKU=N'ALP-TEST-060', @ProductName=N'Test Plug',
     @CategoryID=6, @SupplierID=2, @UnitPrice=10.00, @CostPrice=6.00,
     @ReorderLevel=5, @NewProductID=@NewProductID OUTPUT;
SELECT @NewProductID AS CreatedProductID;

DECLARE @Items dbo.tt_SaleItem_060;
INSERT @Items (ProductID, Quantity, UnitPrice, DiscountAmount) VALUES (8, 2, 12.00, 0.00);
DECLARE @SaleID int, @SaleNo nvarchar(30);
EXEC dbo.sp_ProcessSale_060 @CustomerID=1, @EmployeeID=2, @Items=@Items,
     @AmountPaid=25.92, @ProcessedByUserID=3,
     @NewSaleID=@SaleID OUTPUT, @SaleNumberOut=@SaleNo OUTPUT;
SELECT @SaleID AS CreatedSaleID, @SaleNo AS CreatedSaleNumber;
GO
*/

PRINT 'SECTION 16 OK - Testing queries completed.';
GO

CREATE OR ALTER PROCEDURE dbo.sp_AuthenticateUser_060
    @Login nvarchar(256)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT UserID, Username, Email, PasswordHash, FullName,
           IsActive, IsLocked, FailedLoginAttempts, LastLoginAt, PasswordChangedAt
    FROM dbo.Users_060
    WHERE IsActive = 1 AND (Username = @Login OR Email = @Login);
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetActiveUserRoles_060
    @UserID int
AS
BEGIN
    SET NOCOUNT ON;
    SELECT r.RoleName, r.[Description]
    FROM dbo.UserRoles_060 ur
    JOIN dbo.Roles_060 r ON r.RoleID = ur.RoleID
    WHERE ur.UserID = @UserID AND ur.IsActive = 1 AND r.IsActive = 1
    ORDER BY CASE r.RoleName
        WHEN N'Administrator' THEN 1
        WHEN N'Manager' THEN 2
        WHEN N'Employee' THEN 3
        WHEN N'Customer' THEN 4
        ELSE 5 END, r.RoleName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_RecordLoginAttempt_060
    @UserID   int,
    @Success  bit,
    @Lock     bit = 0
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE dbo.Users_060
    SET FailedLoginAttempts = CASE WHEN @Success = 1 THEN 0 ELSE FailedLoginAttempts + 1 END,
        IsLocked = CASE WHEN @Lock = 1 THEN 1 ELSE IsLocked END,
        LastLoginAt = CASE WHEN @Success = 1 THEN SYSDATETIME() ELSE LastLoginAt END,
        UpdatedAt = SYSDATETIME()
    WHERE UserID = @UserID;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_RecordAuthAudit_060
    @UserID    int            = NULL,
    @Username  nvarchar(50),
    @Action    nvarchar(100),
    @Outcome   nvarchar(20),
    @IPAddress nvarchar(45)   = NULL,
    @UserAgent nvarchar(300)  = NULL
AS
BEGIN
    SET NOCOUNT ON;
    INSERT dbo.AuditLogs_060
        (UserID, UserRole, Action, EntityType, EntityID, Details, IPAddress,
         UserAgent, ConfirmationStatus, AdditionalVerification, Outcome, OccurredAt)
    VALUES
        (@UserID, NULL, @Action, N'Authentication', @Username, @Action,
         @IPAddress, @UserAgent, N'NOT_REQUIRED', N'NONE', @Outcome, SYSDATETIME());
END
GO

GRANT EXECUTE ON dbo.sp_AuthenticateUser_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetActiveUserRoles_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_RecordLoginAttempt_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_RecordAuthAudit_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GO

PRINT 'SECTION 17 OK - Authentication support procedures are available.';
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetInventoryOverview_060
    @SearchText   nvarchar(100) = NULL,
    @LowStockOnly bit = 0
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';

    SELECT p.ProductID, p.SKU, p.ProductName, c.CategoryName, s.CompanyName AS SupplierName,
           p.UnitPrice, p.CostPrice, ISNULL(i.QuantityOnHand, 0) AS QuantityOnHand,
           p.ReorderLevel, i.WarehouseLocation, p.IsActive,
           CASE WHEN ISNULL(i.QuantityOnHand, 0) <= p.ReorderLevel THEN 1 ELSE 0 END AS IsLowStock
    FROM dbo.Products_060 p
    JOIN dbo.Categories_060 c ON c.CategoryID = p.CategoryID
    LEFT JOIN dbo.Suppliers_060 s ON s.SupplierID = p.SupplierID
    LEFT JOIN dbo.Inventory_060 i ON i.ProductID = p.ProductID
    WHERE (@SearchText IS NULL OR p.ProductName LIKE N'%' + @SearchText + N'%' OR p.SKU LIKE N'%' + @SearchText + N'%')
      AND (@LowStockOnly = 0 OR ISNULL(i.QuantityOnHand, 0) <= p.ReorderLevel)
    ORDER BY p.ProductName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetCategories_060
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee,Customer';
    SELECT CategoryID, CategoryName FROM dbo.Categories_060 WHERE IsActive = 1 ORDER BY CategoryName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetSuppliers_060
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';
    SELECT SupplierID, SupplierCode, CompanyName AS SupplierName, ContactName, Phone, Email,
           [Address], IsActive
    FROM dbo.Suppliers_060 WHERE IsActive = 1 ORDER BY CompanyName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetCustomers_060
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';
    SELECT CustomerID, CustomerCode, FullName, Email, Phone, [Address], LoyaltyPoints, IsActive
    FROM dbo.Customers_060 WHERE IsActive = 1 ORDER BY FullName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetEmployees_060
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';
    SELECT e.EmployeeID, e.EmployeeCode, e.FullName, e.Position, e.Department,
           e.Phone, e.Email, e.HireDate, e.IsActive
    FROM dbo.Employees_060 e WHERE e.IsActive = 1 ORDER BY e.FullName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetInventoryTransactions_060
    @ProductID int = NULL,
    @FromDate  date = NULL,
    @ToDate    date = NULL
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';

    SELECT t.TransactionID, t.ProductID, p.ProductName, t.TransactionType,
           t.QuantityChange, t.QuantityBefore, t.QuantityAfter, t.ReferenceID,
           t.ReferenceType, t.TransactionDate, u.Username AS PerformedBy
    FROM dbo.InventoryTransactions_060 t
    JOIN dbo.Products_060 p ON p.ProductID = t.ProductID
    LEFT JOIN dbo.Users_060 u ON u.UserID = t.PerformedByUserID
    WHERE (@ProductID IS NULL OR t.ProductID = @ProductID)
      AND (@FromDate IS NULL OR t.TransactionDate >= @FromDate)
      AND (@ToDate IS NULL OR t.TransactionDate < DATEADD(day, 1, @ToDate))
    ORDER BY t.TransactionDate DESC, t.TransactionID DESC;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetSaleHeader_060
    @SaleID int
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';

    SELECT s.SaleID, s.SaleNumber, s.SaleDate, s.Status,
           s.CustomerID, c.FullName AS CustomerName, c.CustomerCode,
           s.EmployeeID, e.FullName AS EmployeeName,
           s.SubTotal, s.DiscountAmount, s.TaxAmount, s.TotalAmount,
           s.AmountPaid, s.ChangeAmount, s.Notes
    FROM dbo.Sales_060 s
    LEFT JOIN dbo.Customers_060 c ON c.CustomerID = s.CustomerID
    LEFT JOIN dbo.Employees_060 e ON e.EmployeeID = s.EmployeeID
    WHERE s.SaleID = @SaleID;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetSaleLines_060
    @SaleID int
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';

    SELECT si.SaleItemID, si.ProductID, p.SKU, p.ProductName, si.Quantity,
           si.UnitPrice, si.DiscountAmount, si.LineTotal, si.ReturnedQuantity,
           si.Quantity - si.ReturnedQuantity AS RemainingQuantity
    FROM dbo.SaleItems_060 si
    JOIN dbo.Products_060 p ON p.ProductID = si.ProductID
    WHERE si.SaleID = @SaleID
    ORDER BY si.SaleItemID;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetReturnableSales_060
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';

    SELECT s.SaleID, s.SaleNumber, s.SaleDate, s.CustomerID,
           c.FullName AS CustomerName, s.TotalAmount
    FROM dbo.Sales_060 s
    LEFT JOIN dbo.Customers_060 c ON c.CustomerID = s.CustomerID
    WHERE s.Status = N'COMPLETED'
      AND EXISTS (SELECT 1 FROM dbo.SaleItems_060 si
                  WHERE si.SaleID = s.SaleID AND si.Quantity > si.ReturnedQuantity)
    ORDER BY s.SaleDate DESC, s.SaleID DESC;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetReturnHistory_060
    @FromDate date = NULL,
    @ToDate   date = NULL
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';

    SELECT r.ReturnID, r.ReturnNumber, r.ReturnDate, r.SaleID, s.SaleNumber,
           r.CustomerID, c.FullName AS CustomerName, r.EmployeeID,
           e.FullName AS EmployeeName, r.Reason, r.RefundAmount, r.Status,
           r.ApprovalReference
    FROM dbo.Returns_060 r
    JOIN dbo.Sales_060 s ON s.SaleID = r.SaleID
    LEFT JOIN dbo.Customers_060 c ON c.CustomerID = r.CustomerID
    LEFT JOIN dbo.Employees_060 e ON e.EmployeeID = r.EmployeeID
    WHERE (@FromDate IS NULL OR r.ReturnDate >= @FromDate)
      AND (@ToDate IS NULL OR r.ReturnDate < DATEADD(day, 1, @ToDate))
    ORDER BY r.ReturnDate DESC, r.ReturnID DESC;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetAllUsers_060
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator';

    SELECT u.UserID, u.Username, u.FullName, u.Email, u.IsActive, u.IsLocked,
           ISNULL(STRING_AGG(r.RoleName, N', ') WITHIN GROUP (ORDER BY r.RoleName), N'') AS Roles
    FROM dbo.Users_060 u
    LEFT JOIN dbo.UserRoles_060 ur ON ur.UserID = u.UserID AND ur.IsActive = 1
    LEFT JOIN dbo.Roles_060 r ON r.RoleID = ur.RoleID AND r.IsActive = 1
    GROUP BY u.UserID, u.Username, u.FullName, u.Email, u.IsActive, u.IsLocked
    ORDER BY u.FullName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetAllRoles_060
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator';
    SELECT RoleID, RoleName, [Description] FROM dbo.Roles_060 WHERE IsActive = 1 ORDER BY RoleName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_GetAdministrators_060
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';

    SELECT u.UserID, u.Username, u.FullName
    FROM dbo.Users_060 u
    JOIN dbo.UserRoles_060 ur ON ur.UserID = u.UserID AND ur.IsActive = 1
    JOIN dbo.Roles_060 r ON r.RoleID = ur.RoleID AND r.IsActive = 1
    WHERE r.RoleName = N'Administrator' AND u.IsActive = 1 AND u.IsLocked = 0
    ORDER BY u.FullName;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_ProcessCartSale_060
    @CustomerID        int = NULL,
    @EmployeeID        int,
    @ItemsJson         nvarchar(max),
    @AmountPaid        decimal(18,2),
    @PaymentMethod     nvarchar(20) = N'CASH',
    @ProcessedByUserID int,
    @NewSaleID         int OUTPUT,
    @SaleNumberOut     nvarchar(30) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager,Employee';

    IF ISNULL(@ItemsJson, N'') = N'' OR @ItemsJson = N'[]'
        THROW 50220, N'At least one sale item is required.', 1;

    DECLARE @Items dbo.tt_SaleItem_060;
    INSERT @Items (ProductID, Quantity, UnitPrice, DiscountAmount)
    SELECT ProductID, Quantity, UnitPrice, DiscountAmount
    FROM OPENJSON(@ItemsJson)
    WITH (
        ProductID int '$.productId',
        Quantity int '$.quantity',
        UnitPrice decimal(18,2) '$.unitPrice',
        DiscountAmount decimal(18,2) '$.discount'
    );

    IF NOT EXISTS (SELECT 1 FROM @Items)
        THROW 50221, N'Sale item data could not be read.', 1;

    EXEC dbo.sp_ProcessSale_060
        @CustomerID = @CustomerID, @EmployeeID = @EmployeeID, @Items = @Items,
        @AmountPaid = @AmountPaid, @PaymentMethod = @PaymentMethod,
        @ProcessedByUserID = @ProcessedByUserID,
        @NewSaleID = @NewSaleID OUTPUT, @SaleNumberOut = @SaleNumberOut OUTPUT;
END
GO

CREATE OR ALTER PROCEDURE dbo.sp_ProcessCartReturn_060
    @SaleID            int,
    @ItemsJson         nvarchar(max),
    @Reason            nvarchar(255),
    @RefundAmount      decimal(18,2),
    @EmployeeID        int,
    @ApprovedByUserID  int,
    @ApprovalReference nvarchar(100),
    @ProcessedByUserID int,
    @NewReturnID       int OUTPUT,
    @ReturnNumberOut   nvarchar(30) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    EXEC dbo.sp_AssertAppRole_060 @AllowedRoles = N'Administrator,Manager';

    IF ISNULL(@ItemsJson, N'') = N'' OR @ItemsJson = N'[]'
        THROW 50230, N'At least one return item is required.', 1;

    DECLARE @Items dbo.tt_ReturnItem_060;
    INSERT @Items (SaleItemID, Quantity)
    SELECT SaleItemID, Quantity
    FROM OPENJSON(@ItemsJson)
    WITH (
        SaleItemID int '$.saleItemId',
        Quantity int '$.quantity'
    );

    IF NOT EXISTS (SELECT 1 FROM @Items)
        THROW 50231, N'Return item data could not be read.', 1;

    EXEC dbo.sp_ProcessReturn_060
        @SaleID = @SaleID, @Items = @Items, @Reason = @Reason,
        @RefundAmount = @RefundAmount, @EmployeeID = @EmployeeID,
        @ApprovedByUserID = @ApprovedByUserID,
        @ApprovalReference = @ApprovalReference,
        @ProcessedByUserID = @ProcessedByUserID,
        @NewReturnID = @NewReturnID OUTPUT,
        @ReturnNumberOut = @ReturnNumberOut OUTPUT;
END
GO

GRANT EXECUTE ON dbo.sp_GetInventoryOverview_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetCategories_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetSuppliers_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_GetCustomers_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetEmployees_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetInventoryTransactions_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetSaleHeader_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetSaleLines_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_GetReturnableSales_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_GetReturnHistory_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_GetAllUsers_060 TO rms_admin_060;
GRANT EXECUTE ON dbo.sp_GetAllRoles_060 TO rms_admin_060;
GRANT EXECUTE ON dbo.sp_GetAdministrators_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_ProcessCartSale_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GRANT EXECUTE ON dbo.sp_ProcessCartReturn_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_RegisterSupplier_060 TO rms_admin_060, rms_manager_060;
GRANT EXECUTE ON dbo.sp_RegisterEmployee_060 TO rms_admin_060;
GO

PRINT 'SECTION 18 OK - Business module procedures are available.';
GO

/*==============================================================================
  SECTION 19 - STANDALONE IF-ELSE CONDITIONAL LOGIC DEMONSTRATION (Question 9)
  Demonstrates decision-based customer discount tiering & stock reorder checks
  across multiple input values.
==============================================================================*/
CREATE OR ALTER PROCEDURE dbo.sp_EvaluateCustomerDiscountTier_060
    @CustomerID int
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @CustomerName nvarchar(100);
    DECLARE @TotalSpend   decimal(18,2) = 0;
    DECLARE @OrderCount   int = 0;
    DECLARE @Tier         nvarchar(30);
    DECLARE @DiscountRate decimal(5,2);

    SELECT @CustomerName = FullName
    FROM dbo.Customers_060
    WHERE CustomerID = @CustomerID;

    IF @CustomerName IS NULL
    BEGIN
        SELECT @CustomerID AS CustomerID, N'Unknown' AS CustomerName,
               0 AS OrderCount, CAST(0.00 AS decimal(18,2)) AS TotalSpend,
               N'NONE' AS LoyaltyTier, CAST(0.00 AS decimal(5,2)) AS DiscountPercent;
        RETURN;
    END

    SELECT @OrderCount = COUNT(*),
           @TotalSpend = ISNULL(SUM(TotalAmount), 0)
    FROM dbo.Sales_060
    WHERE CustomerID = @CustomerID AND [Status] = N'COMPLETED';

    IF @TotalSpend >= 100000 OR @OrderCount >= 10
    BEGIN
        SET @Tier = N'PLATINUM';
        SET @DiscountRate = 10.00;
    END
    ELSE IF @TotalSpend >= 40000 OR @OrderCount >= 4
    BEGIN
        SET @Tier = N'GOLD';
        SET @DiscountRate = 5.00;
    END
    ELSE IF @OrderCount >= 1
    BEGIN
        SET @Tier = N'SILVER';
        SET @DiscountRate = 2.00;
    END
    ELSE
    BEGIN
        SET @Tier = N'STANDARD';
        SET @DiscountRate = 0.00;
    END

    SELECT @CustomerID AS CustomerID, @CustomerName AS CustomerName,
           @OrderCount AS OrderCount, @TotalSpend AS TotalSpend,
           @Tier AS LoyaltyTier, @DiscountRate AS DiscountPercent;
END
GO

GRANT EXECUTE ON dbo.sp_EvaluateCustomerDiscountTier_060 TO rms_admin_060, rms_manager_060, rms_staff_060;
GO

-- Examples of execution with different input values:
EXEC dbo.sp_EvaluateCustomerDiscountTier_060 @CustomerID = 1;
EXEC dbo.sp_EvaluateCustomerDiscountTier_060 @CustomerID = 2;
EXEC dbo.sp_EvaluateCustomerDiscountTier_060 @CustomerID = 9999; -- non-existent customer branch
GO

PRINT 'SECTION 19 OK - Standalone IF-ELSE conditional logic demonstration executed.';
PRINT 'ALGORITHM HARDWARE PLUG database schema script completed successfully.';
GO

