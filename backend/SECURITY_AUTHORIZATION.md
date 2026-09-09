# 🔐 Sistema de Autorización y Seguridad

## Jerarquía de Roles

El sistema implementa 5 niveles jerárquicos de roles, donde cada nivel superior hereda los permisos de los niveles inferiores:

```
┌─────────────────────────────────────────────────────────────┐
│ NIVEL 5 - DESARROLLADOR                                     │
│ Control total del sistema                                   │
└─────────────────────────────────────────────────────────────┘
                          ↓ hereda
┌─────────────────────────────────────────────────────────────┐
│ NIVEL 4 - SOPORTE IT                                        │
│ Gestión de usuarios, logs, backups                          │
└─────────────────────────────────────────────────────────────┘
                          ↓ hereda
┌─────────────────────────────────────────────────────────────┐
│ NIVEL 3 - ADMINISTRATIVO                                    │
│ Gestión académica, aprobar admisiones                        │
└─────────────────────────────────────────────────────────────┘
                          ↓ hereda
┌─────────────────────────────────────────────────────────────┐
│ NIVEL 2 - PROFESOR                                          │
│ Gestionar calificaciones de sus materias                    │
└─────────────────────────────────────────────────────────────┘
                          ↓ hereda
┌─────────────────────────────────────────────────────────────┐
│ NIVEL 1 - ESTUDIANTE                                        │
│ Ver datos propios, inscribirse (si está activo)             │
└─────────────────────────────────────────────────────────────┘
```

## Componentes del Sistema

### 1. Middleware: `EnsureUserRole`

**Ubicación:** `app/Http/Middleware/EnsureUserRole.php`

**Uso en rutas:**
```php
// Requiere rol mínimo
Route::middleware(['auth:sanctum', 'role:administrativo'])->group(function () {
    // Solo administrativo, soporte_it y desarrollador pueden acceder
});

// Requiere rol específico + permiso
Route::middleware(['auth:sanctum', 'role:profesor,gestionar_calificaciones_propias'])->group(function () {
    // Solo profesores con ese permiso específico
});
```

**Características:**
- ✅ Verifica autenticación
- ✅ Valida jerarquía de roles
- ✅ Verifica permisos específicos
- ✅ Control especial para estudiantes solicitantes/suspendidos
- ✅ Logging automático de intentos de acceso no autorizado
- ✅ Auditoría de rutas sensibles

### 2. Trait: `HasRoleAuthorization`

**Ubicación:** `app/Traits/HasRoleAuthorization.php`

**Uso en el modelo User:**
```php
$user->hasRole('administrativo');        // true si es administrativo o superior
$user->hasExactRole('estudiante');       // true solo si es exactamente estudiante
$user->isDesarrollador();                // shortcut
$user->canManageUsers();                 // verifica permiso específico
$user->getRoleLevel();                   // retorna 1-5
$user->isEstudianteActivo();             // verifica estatus de estudiante
```

### 3. Policies

**Ubicación:** `app/Policies/`

Implementa autorización granular para modelos específicos:

#### `EstudiantePolicy`
- `viewAny`: Solo administrativos y superiores
- `view`: Estudiante solo ve su perfil, profesor ve estudiantes de sus materias
- `create`: Solo administrativos y superiores
- `update`: Estudiante solo sus datos, admin cualquiera
- `delete`: Solo administrativos y superiores
- `changeStatus`: Solo administrativos y superiores

#### `CalificacionPolicy`
- `viewAny`: Todos (pero filtrado por scope)
- `view`: Estudiante solo las suyas, profesor solo de sus materias
- `create`: Profesores y superiores
- `update`: Profesor solo de sus materias, admin cualquiera
- `delete`: Solo administrativos y superiores

#### `UserPolicy`
- `viewAny`: Solo soporte IT y superiores
- `view`: Usuario su propio perfil, soporte IT todos
- `create`: Solo soporte IT y superiores
- `update`: Usuario su perfil, soporte IT solo niveles inferiores
- `delete`: No se puede eliminar a sí mismo, soporte IT solo niveles inferiores
- `assignRole`: Solo soporte IT puede asignar roles < su nivel, solo dev puede asignar dev
- `resetPassword`: Soporte IT solo para niveles inferiores

**Uso en controladores:**
```php
// Autorizar acción
$this->authorize('view', $estudiante);

// Verificar sin lanzar excepción
if ($request->user()->can('update', $estudiante)) {
    // ...
}

// En rutas
Route::get('/estudiantes/{estudiante}', function (Estudiante $estudiante) {
    Gate::authorize('view', $estudiante);
    // ...
});
```

### 4. Gates Personalizados

**Ubicación:** Registrados en `AppServiceProvider.php`

```php
// Verificar gate
Gate::authorize('aprobar-solicitud-admision');

// O sin excepción
if (Gate::allows('gestionar-backups')) {
    // ...
}
```

**Gates disponibles:**
- `aprobar-solicitud-admision` - Administrativos
- `gestionar-backups` - Soporte IT
- `acceso-configuracion-critica` - Solo desarrollador
- `ver-logs-sistema` - Soporte IT
- `gestionar-usuarios` - Soporte IT
- `ejecutar-comandos-sistema` - Solo desarrollador
- `acceso-base-datos` - Solo desarrollador
- `deploy-actualizaciones` - Solo desarrollador
- `gestionar-pagos` - Administrativos
- `ver-reportes-academicos` - Administrativos

### 5. SecurityLogService

**Ubicación:** `app/Services/SecurityLogService.php`

Registra todos los eventos de seguridad en `system_logs`:

```php
use App\Services\SecurityLogService;

// Log de acceso no autorizado
SecurityLogService::logUnauthorizedAccess($user, 'ruta-admin', 'rol insuficiente');

// Log de cambio de rol
SecurityLogService::logRoleChange($adminUser, $targetUser, 'estudiante', 'profesor');

// Log de aprobación de admisión
SecurityLogService::logAdmissionApproval($adminUser, $solicitudId, true);

// Log de modificación de calificación
SecurityLogService::logGradeChange($user, $calificacionId, $oldGrades, $newGrades);

// Log de backup
SecurityLogService::logBackupCreated($user, 'backup-2026-09-07.sql', 52428800);

// Obtener logs de usuario
$logs = SecurityLogService::getUserLogs($userId, 50);

// Obtener logs de seguridad recientes
$securityLogs = SecurityLogService::getRecentSecurityLogs(100);
```

## Restricciones por Estado de Estudiante

Los estudiantes tienen sub-estados que limitan su acceso:

### `solicitante` (Pendiente de aprobación)
**Puede acceder SOLO a:**
- Ver estado de solicitud
- Subir documentos requeridos
- Ver perfil
- Logout

**NO puede:**
- Inscribirse en materias
- Ver horarios
- Ver calificaciones
- Ver catálogo de materias

### `suspendido`
**NO puede:**
- Realizar nuevas inscripciones
- Inscribir materias

**Puede:**
- Ver sus datos existentes
- Ver calificaciones
- Ver horarios pasados

### `activo`
**Acceso completo a todas las funciones de estudiante**

## Ejemplos de Uso

### En Controladores

```php
use Illuminate\Support\Facades\Gate;
use App\Services\SecurityLogService;

class SolicitudController extends Controller
{
    public function aprobar(Request $request, $id)
    {
        // 1. Verificar autorización (lanza 403 si falla)
        Gate::authorize('aprobar-solicitud-admision');
        
        // 2. Lógica de negocio
        $solicitud = SolicitudAdmision::findOrFail($id);
        $solicitud->update(['estatus' => 'aprobado']);
        
        // 3. Actualizar estudiante
        $estudiante = $solicitud->estudiante;
        $estudiante->update(['estatus' => 'activo']);
        
        // 4. Log de seguridad
        SecurityLogService::logAdmissionApproval(
            $request->user(),
            $id,
            true
        );
        
        return response()->json(['message' => 'Solicitud aprobada']);
    }
}
```

### En Rutas

```php
// Rutas para desarrollador
Route::middleware(['auth:sanctum', 'role:desarrollador'])->prefix('dev')->group(function () {
    Route::get('/database', [DevController::class, 'database']);
    Route::get('/logs', [DevController::class, 'logs']);
    Route::post('/backup', [DevController::class, 'backup']);
});

// Rutas para soporte IT
Route::middleware(['auth:sanctum', 'role:soporte_it'])->prefix('soporte')->group(function () {
    Route::get('/usuarios', [SoporteController::class, 'usuarios']);
    Route::post('/usuarios/{id}/reset-password', [SoporteController::class, 'resetPassword']);
});

// Rutas para administrativo
Route::middleware(['auth:sanctum', 'role:administrativo'])->prefix('admin')->group(function () {
    Route::get('/solicitudes', [SolicitudController::class, 'index']);
    Route::post('/solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar']);
});

// Rutas para profesor
Route::middleware(['auth:sanctum', 'role:profesor'])->prefix('profesor')->group(function () {
    Route::get('/materias', [ProfesorController::class, 'materias']);
    Route::post('/calificaciones', [ProfesorController::class, 'storeCalificacion']);
});

// Rutas para estudiante
Route::middleware(['auth:sanctum', 'role:estudiante'])->prefix('estudiante')->group(function () {
    Route::get('/horarios', [EstudianteController::class, 'horarios']);
    Route::get('/calificaciones', [EstudianteController::class, 'calificaciones']);
    Route::post('/inscripciones', [EstudianteController::class, 'inscribir']);
});
```

### Verificación Manual

```php
// Verificar rol
if (!$user->hasRole('administrativo')) {
    return response()->json(['error' => 'No autorizado'], 403);
}

// Verificar policy
if ($user->cannot('update', $estudiante)) {
    abort(403, 'No puede actualizar este estudiante');
}

// Verificar gate
if (Gate::denies('gestionar-backups')) {
    abort(403);
}

// Verificar estatus de estudiante
if ($user->isEstudianteSolicitante()) {
    return response()->json([
        'error' => 'Su solicitud está pendiente de aprobación'
    ], 403);
}
```

## Logging y Auditoría

Todos los eventos importantes de seguridad se registran automáticamente en `system_logs`:

- ✅ Login exitoso/fallido
- ✅ Intentos de acceso no autorizado
- ✅ Cambios de rol
- ✅ Aprobación/rechazo de admisiones
- ✅ Modificación de calificaciones
- ✅ Acceso a configuraciones críticas
- ✅ Ejecución de comandos del sistema
- ✅ Creación de backups

Estos logs pueden ser consultados desde:
- Panel de soporte IT
- Panel de desarrollador
- API: `/api/soporte/logs`

## Mejores Prácticas

1. **Siempre usar middleware en rutas sensibles:**
   ```php
   Route::middleware(['auth:sanctum', 'role:administrativo'])
   ```

2. **Autorizar en controladores antes de lógica de negocio:**
   ```php
   $this->authorize('update', $estudiante);
   ```

3. **Usar policies para lógica de autorización compleja:**
   ```php
   // En Policy, no en Controller
   public function update(User $user, Estudiante $estudiante): bool
   ```

4. **Registrar acciones sensibles:**
   ```php
   SecurityLogService::logGradeChange(...);
   ```

5. **No exponer información sensible en errores:**
   ```php
   // ❌ MAL
   return response()->json(['error' => 'Usuario no existe'], 404);
   
   // ✅ BIEN
   return response()->json(['error' => 'Credenciales incorrectas'], 401);
   ```

6. **Verificar estatus de estudiante cuando sea relevante:**
   ```php
   if ($user->isEstudianteSolicitante()) {
       // Bloquear ciertas acciones
   }
   ```

## Brechas de Seguridad Prevenidas

✅ **Escalación de privilegios:** Jerarquía estricta previene que usuarios asignen roles superiores  
✅ **Acceso no autorizado:** Middleware verifica cada request  
✅ **Manipulación de datos:** Policies validan operaciones CRUD  
✅ **Inyección de roles:** Validación en middleware previene roles inválidos  
✅ **Bypass de autenticación:** Token Sanctum con expiración  
✅ **Información sensible:** Logging de intentos sospechosos  
✅ **Estudiantes sin aprobar:** Control de estatus previene acceso prematuro  
✅ **Auto-modificación:** Usuarios no pueden cambiar su propio rol  
✅ **Eliminación accidental:** Usuarios no pueden eliminarse a sí mismos

## Testing de Seguridad

```bash
# Verificar que las policies funcionan
php artisan test --filter=PolicyTest

# Verificar middleware
php artisan test --filter=RoleMiddlewareTest

# Verificar logs de seguridad
php artisan test --filter=SecurityLogTest
```
