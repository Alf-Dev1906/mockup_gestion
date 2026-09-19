# Bugfix Requirements Document

## Introduction

The Database Explorer feature (`/dev/database`) is accessible through the developer panel and includes a complete frontend UI for viewing database tables, executing SELECT queries, and inspecting table statistics. However, the backend endpoints `/dev/database` (GET) and `/dev/database/query` (POST) return "Error interno del servidor" (500 Internal Server Error) instead of functioning correctly. The backend controller methods exist in `DesarrolladorController.php` but appear to be failing at runtime, preventing developers from using this critical database exploration tool.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN a developer with role 'desarrollador' accesses the `/dev/database` page THEN the system returns HTTP 500 Internal Server Error instead of returning the list of database tables

1.2 WHEN a developer submits a SELECT query via the POST `/dev/database/query` endpoint THEN the system returns HTTP 500 Internal Server Error instead of executing the query and returning results

1.3 WHEN the frontend attempts to load table information on component mount THEN the system displays "Error interno del servidor" and shows no tables in the table list panel

### Expected Behavior (Correct)

2.1 WHEN a developer with role 'desarrollador' accesses the `/dev/database` page THEN the system SHALL return HTTP 200 with a JSON array containing all database tables, each with properties: name, rows (count), and size_mb

2.2 WHEN a developer submits a valid SELECT query via POST `/dev/database/query` THEN the system SHALL execute the query and return HTTP 200 with JSON containing: success (true), rows (count), and data (array of result objects)

2.3 WHEN a developer submits a non-SELECT query (INSERT, UPDATE, DELETE, DROP, etc.) THEN the system SHALL return HTTP 403 with error message "Solo se permiten queries SELECT. Para modificaciones use las migraciones."

2.4 WHEN the query execution fails due to SQL syntax error or database error THEN the system SHALL return HTTP 400 with the exception message in the error field

### Unchanged Behavior (Regression Prevention)

3.1 WHEN a user without 'desarrollador' role attempts to access `/dev/database` endpoints THEN the system SHALL CONTINUE TO return HTTP 403 Forbidden via the Gate authorization

3.2 WHEN a developer accesses other `/dev/*` endpoints (dashboard, logs, config, backups) THEN the system SHALL CONTINUE TO function correctly without regression

3.3 WHEN the security logging service logs database access THEN the system SHALL CONTINUE TO record SQL command execution in the security logs

## Bug Condition Derivation

```pascal
FUNCTION isBugCondition(X)
  INPUT: X of type DatabaseEndpointRequest
  OUTPUT: boolean
  
  // Returns true when database endpoint returns 500 error
  RETURN (X.endpoint = '/dev/database' AND X.method = 'GET') OR
         (X.endpoint = '/dev/database/query' AND X.method = 'POST')
END FUNCTION
```

## Property Specification

```pascal
// Property: Fix Checking - Database Endpoint Functionality
FOR ALL X WHERE isBugCondition(X) AND X.method = 'GET' DO
  result ← GET_dev_database'(X)
  ASSERT result.status IN [200] AND
         result.data IS Array AND
         FORALL table IN result.data: 
           table HAS_FIELDS ['name', 'rows', 'size_mb']
END FOR

FOR ALL X WHERE isBugCondition(X) AND X.method = 'POST' AND X.query MATCHES '^\s*SELECT' DO
  result ← POST_dev_database_query'(X)
  ASSERT result.status IN [200, 400] AND
         (result.status = 200 IMPLIES result.data HAS_FIELDS ['success', 'rows', 'data'])
END FOR

FOR ALL X WHERE isBugCondition(X) AND X.method = 'POST' AND NOT (X.query MATCHES '^\s*SELECT') DO
  result ← POST_dev_database_query'(X)
  ASSERT result.status = 403 AND
         result.error CONTAINS 'Solo se permiten queries SELECT'
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
- `backend/app/Http/Controllers/Api/DesarrolladorController.php` (methods: `database()`, `executeQuery()`)
- `backend/routes/api.php` (routes registered at lines 315-316)

**Possible Root Causes:**
- Missing database permissions or Gates (`acceso-base-datos`)
- Missing Gate definitions in `AppServiceProvider` or Policy classes
- Database connection issues or incorrect table information queries
- Missing `system_logs` table for SecurityLogService
- Uncaught exceptions in DB::select() calls
