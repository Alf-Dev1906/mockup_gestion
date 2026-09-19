# Bugfix Requirements Document

## Introduction

The Logs del Sistema feature (`/dev/logs`) provides two tabs for viewing system logs: "Logs de Seguridad" (security logs from database) and "Log de Laravel" (Laravel log file). The frontend UI is complete with filtering, tabs, and display components. However, the backend endpoints `/dev/logs` (GET) for Laravel logs and `/dev/logs/security` (GET) for security logs are returning errors or not functioning properly, preventing developers from viewing critical system logs needed for debugging and security monitoring.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN a developer accesses the `/dev/logs` page and selects the "Log de Laravel" tab THEN the system returns an error or fails to display the contents of `storage/logs/laravel.log`

1.2 WHEN a developer accesses the `/dev/logs` page and selects the "Logs de Seguridad" tab THEN the system returns an error or fails to query the `system_logs` table from the database

1.3 WHEN the Laravel log file does not exist at `storage/logs/laravel.log` THEN the system may crash with HTTP 500 instead of returning HTTP 404 with appropriate error message

1.4 WHEN the `system_logs` table does not exist in the database THEN the system may crash with HTTP 500 instead of handling the missing table gracefully

### Expected Behavior (Correct)

2.1 WHEN a developer requests Laravel logs via GET `/dev/logs?type=laravel&lines=100` THEN the system SHALL read the last 100 lines from `storage/logs/laravel.log` and return HTTP 200 with JSON containing: type ('laravel'), lines (count), logs (array of strings)

2.2 WHEN a developer requests security logs via GET `/dev/logs/security?limit=100&action=login_exitoso` THEN the system SHALL query the `system_logs` table with filters and return HTTP 200 with JSON array of log records

2.3 WHEN the Laravel log file does not exist THEN the system SHALL return HTTP 404 with error message "Archivo de log no encontrado"

2.4 WHEN the log file exists and is readable THEN the system SHALL use the `tailFile()` private method to efficiently read the last N lines without loading the entire file into memory

2.5 WHEN security logs are requested with an action filter THEN the system SHALL apply the WHERE clause filtering by the accion column

### Unchanged Behavior (Regression Prevention)

3.1 WHEN a user without 'desarrollador' role attempts to access `/dev/logs` endpoints THEN the system SHALL CONTINUE TO return HTTP 403 Forbidden via the Gate authorization

3.2 WHEN a developer accesses other `/dev/*` endpoints (database, dashboard, config) THEN the system SHALL CONTINUE TO function correctly without regression

3.3 WHEN log files are accessed THEN the system SHALL CONTINUE TO enforce read-only access (no write, delete, or modify operations)

## Bug Condition Derivation

```pascal
FUNCTION isBugCondition(X)
  INPUT: X of type LogsEndpointRequest
  OUTPUT: boolean
  
  // Returns true when logs endpoints fail to function properly
  RETURN (X.endpoint = '/dev/logs' AND X.method = 'GET') OR
         (X.endpoint = '/dev/logs/security' AND X.method = 'GET')
END FUNCTION
```

## Property Specification

```pascal
// Property: Fix Checking - Logs Endpoint Functionality
FOR ALL X WHERE isBugCondition(X) AND X.endpoint = '/dev/logs' DO
  result ← GET_dev_logs'(X)
  IF file_exists(X.logFilePath) THEN
    ASSERT result.status = 200 AND
           result.data HAS_FIELDS ['type', 'lines', 'logs'] AND
           result.data.logs IS Array AND
           length(result.data.logs) <= X.requestedLines
  ELSE
    ASSERT result.status = 404 AND
           result.error = 'Archivo de log no encontrado'
  END IF
END FOR

FOR ALL X WHERE isBugCondition(X) AND X.endpoint = '/dev/logs/security' DO
  result ← GET_dev_logs_security'(X)
  ASSERT result.status = 200 AND
         result.data IS Array AND
         length(result.data) <= X.limit AND
         (X.action IS NOT NULL IMPLIES 
           FORALL log IN result.data: log.accion = X.action)
END FOR
```

## Preservation Goal

```pascal
// Property: Preservation Checking
FOR ALL X WHERE NOT isBugCondition(X) DO
  ASSERT handleRequest(X) = handleRequest'(X)
END FOR
```

**Affected Files:**
- `backend/app/Http/Controllers/Api/DesarrolladorController.php` (methods: `logs()`, `securityLogs()`, `tailFile()`)
- `backend/routes/api.php` (routes at lines 317-318)

**Possible Root Causes:**
- Missing Gate definitions (`ver-logs-sistema`)
- Missing `system_logs` table in database schema
- Log file path issues (wrong storage path or permissions)
- `tailFile()` method implementation errors (file pointer, SplFileObject usage)
- Missing exception handling for file operations
- SecurityLogService attempting to write to non-existent table
