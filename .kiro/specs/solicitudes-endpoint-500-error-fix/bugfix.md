# Bugfix Requirements Document

## Introduction

The Solicitudes (Admissions Applications) feature at `/admin/solicitudes` is a critical administrative function for reviewing and processing student admission requests. The endpoint exists in the `AdministrativoController` and is accessible from the developer panel navigation, but accessing it results in "Error interno del servidor" (HTTP 500 Internal Server Error). This prevents both administrative and developer roles from viewing, filtering, or managing admission applications, blocking a core workflow in the university management system.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN a user with 'desarrollador' role navigates to `/admin/solicitudes` THEN the system returns HTTP 500 Internal Server Error instead of displaying the list of admission applications

1.2 WHEN a user with 'administrativo' role attempts to access the solicitudes endpoint THEN the system returns HTTP 500 Internal Server Error instead of returning paginated solicitud records

1.3 WHEN the solicitudes endpoint attempts to execute the database query with multiple JOIN operations THEN the system encounters an error (likely due to missing tables, incorrect foreign keys, or typos in field names)

### Expected Behavior (Correct)

2.1 WHEN a user with appropriate role accesses GET `/admin/solicitudes` without query parameters THEN the system SHALL return HTTP 200 with paginated JSON response containing 20 solicitudes per page, ordered by fecha_envio descending

2.2 WHEN a user requests solicitudes with query parameter `?estatus=pendiente` THEN the system SHALL filter results to show only solicitudes where estado = 'pendiente'

2.3 WHEN a user requests solicitudes with query parameter `?per_page=50` THEN the system SHALL return up to 50 records per page

2.4 WHEN the query executes successfully THEN each solicitud record SHALL include: all solicitudes_admision fields, estudiante_nombre (concatenated), estudiante_cedula, estudiante_email, carrera_nombre, and revisado_por_nombre

2.5 WHEN a solicitud has no associated estudiante, revisor, or carrera THEN the system SHALL handle NULL values gracefully via LEFT JOIN operations

### Unchanged Behavior (Regression Prevention)

3.1 WHEN users access other administrative endpoints (dashboard, estudiantes, profesores, etc.) THEN the system SHALL CONTINUE TO function correctly

3.2 WHEN the solicitudes method applies role-based authorization THEN the system SHALL CONTINUE TO enforce access control via middleware

3.3 WHEN the database contains solicitudes with fecha_envio = NULL THEN the system SHALL CONTINUE TO exclude these draft applications from the results (WHERE fecha_envio IS NOT NULL)

## Bug Condition Derivation

```pascal
FUNCTION isBugCondition(X)
  INPUT: X of type SolicitudesEndpointRequest
  OUTPUT: boolean
  
  // Returns true when solicitudes endpoint returns 500 error
  RETURN X.endpoint = '/admin/solicitudes' AND X.method = 'GET' AND X.responseStatus = 500
END FUNCTION
```

## Property Specification

```pascal
// Property: Fix Checking - Solicitudes Endpoint Functionality
FOR ALL X WHERE isBugCondition(X) DO
  result ← GET_admin_solicitudes'(X)
  ASSERT result.status = 200 AND
         result.data HAS_FIELDS ['data', 'current_page', 'last_page', 'per_page', 'total'] AND
         result.data.data IS Array AND
         FORALL solicitud IN result.data.data:
           solicitud HAS_FIELDS ['id', 'estado', 'fecha_envio', 'estudiante_nombre', 
                                  'estudiante_cedula', 'estudiante_email', 'carrera_nombre']
END FOR

FOR ALL X WHERE isBugCondition(X) AND X.queryParams.estatus IS NOT NULL DO
  result ← GET_admin_solicitudes'(X)
  ASSERT result.status = 200 AND
         FORALL solicitud IN result.data.data:
           solicitud.estado = X.queryParams.estatus
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
- `backend/app/Http/Controllers/Api/AdministrativoController.php` (method: `solicitudes()` at line ~48)
- `backend/routes/api.php` (route likely under administrativo middleware group)

**Possible Root Causes:**
- Typo in table name: `solicitudes_admision` vs `solicitud_admision` (singular)
- Missing `solicitudes_admision` table in database schema
- Incorrect field name: `estado` vs `estatus` (query uses 'estado', filtering uses 'estatus')
- Foreign key column mismatch (estudiante_id, carrera_id, revisado_por may not exist)
- Missing database migrations for solicitudes_admision table
- Field name mismatch between frontend expectation and database schema

**Critical Field Name Issue:**
The query filters by `solicitudes_admision.estado` but the frontend sends `estatus` as the query parameter, and the condition checks `$estado` which comes from `$request->query('estatus')`. This suggests either:
- The database column should be `estatus` not `estado`, OR
- The variable mapping is incorrect: `$estado` from query param 'estatus' but WHERE clause uses 'estado'
