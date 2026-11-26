/*==============================================================================
  Algorithm Hardware Plug - Retail Management System
  Database Backup & Recovery Script (Suffix: _060)
  Target DBMS: Microsoft SQL Server
==============================================================================*/

-- Prerequisites:
-- 1. Create the backup folder on the SQL Server host, e.g. C:\SQLBackups
-- 2. Grant the SQL Server service account (e.g. NT SERVICE\MSSQLSERVER)
--    Read/Write/Modify permissions on C:\SQLBackups.

USE [master];
GO

/*------------------------------------------------------------------------------
  1. FULL DATABASE BACKUP
     Captures the entire database (schema, data, roles, triggers, procedures).
------------------------------------------------------------------------------*/
BACKUP DATABASE [RetailManagementDB]
    TO DISK = N'C:\SQLBackups\RetailManagementDB_060_Full.bak'
    WITH INIT,
         COMPRESSION,
         NAME = N'RetailManagementDB-Full-060',
         STATS = 10;
GO

/*------------------------------------------------------------------------------
  2. DIFFERENTIAL DATABASE BACKUP
     Captures only extents changed since the last full backup.
------------------------------------------------------------------------------*/
BACKUP DATABASE [RetailManagementDB]
    TO DISK = N'C:\SQLBackups\RetailManagementDB_060_Diff.bak'
    WITH DIFFERENTIAL,
         COMPRESSION,
         NAME = N'RetailManagementDB-Diff-060',
         STATS = 10;
GO

/*------------------------------------------------------------------------------
  3. VERIFY BACKUP INTEGRITY
     Confirms the backup file is readable and complete without restoring yet.
------------------------------------------------------------------------------*/
RESTORE VERIFYONLY
    FROM DISK = N'C:\SQLBackups\RetailManagementDB_060_Full.bak';
GO

/*------------------------------------------------------------------------------
  4. NON-DESTRUCTIVE TEST RESTORE (Recommended for verification)
     Restores to a side-by-side test database without touching production.
     Adjust DATA/LOG file paths to match your SQL Server instance directory.
------------------------------------------------------------------------------*/
RESTORE DATABASE [RetailManagementDB_TestRestore_060]
    FROM DISK = N'C:\SQLBackups\RetailManagementDB_060_Full.bak'
    WITH MOVE N'RetailManagementDB'
             TO N'C:\Program Files\Microsoft SQL Server\MSSQL17.MSSQLSERVER\MSSQL\DATA\RetailManagementDB_TestRestore_060.mdf',
         MOVE N'RetailManagementDB_log'
             TO N'C:\Program Files\Microsoft SQL Server\MSSQL17.MSSQLSERVER\MSSQL\DATA\RetailManagementDB_TestRestore_060.ldf',
         STATS = 10;
GO

/*------------------------------------------------------------------------------
  5. FULL LIVE DATABASE RECOVERY (Use only during planned recovery window)
------------------------------------------------------------------------------*/
/*
ALTER DATABASE [RetailManagementDB] SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
GO

RESTORE DATABASE [RetailManagementDB]
    FROM DISK = N'C:\SQLBackups\RetailManagementDB_060_Full.bak'
    WITH REPLACE, RECOVERY, STATS = 10;
GO

ALTER DATABASE [RetailManagementDB] SET MULTI_USER;
GO
*/
