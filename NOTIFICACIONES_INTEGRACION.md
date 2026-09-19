# Sistema de Notificaciones — Guía de Integración

## Overview
El sistema de notificaciones está completamente implementado con:
- **Backend**: Modelo, servicio, controlador y rutas
- **Frontend**: Composable de polling, campana en header, vista completa
- **DB**: Tabla `notifications` con 7 tipos automáticos

## Migración
```bash
php artisan migrate
```

## Tabla: `notifications`
```sql
id, user_id, tipo, titulo, mensaje, url, datos (JSON),
leida, leida_at, created_at, updated_at
```

### Tipos de notificaciones:
- `examen_publicado`
- `tarea_publicada`
- `tarea_por_vencer`
- `examen_calificado`
- `asistencia_ausente`
- `entrega_calificada`
- `anuncio_general`

## Endpoints API (7 total)

### Para el frontend (polling + CRUD):
```
GET    /notificaciones                       (paginadas, filtro)
GET    /notificaciones/no-leidas-count       (para polling 30s)
GET    /notificaciones/tipos-disponibles     (estadísticas)
POST   /notificaciones/{id}/leer             (marcar leída)
POST   /notificaciones/leer-todas            (marcar todas leídas)
DELETE /notificaciones/{id}                  (eliminar)
POST   /notificaciones/eliminar-todas        (eliminar leídas)
```

## Integración en eventos existentes

### 1. Cuando se PUBLICA UN EXAMEN (QuizController::store)
```php
use App\Services\NotificationService;

public function store(Request $request, NotificationService $notificationService)
{
    // ... crear quiz ...
    
    $quiz = Quiz::create([...]);
    
    // Notificar a estudiantes
    $notificationService->notificarExamenPublicado($quiz->id);
    
    return response()->json([...], 201);
}
```

### 2. Cuando se PUBLICA UNA TAREA (AssignmentController::crearProfesor)
```php
// Ya está parcialmente implementado. Complétalo así:
public function crearProfesor(Request $request, NotificationService $notificationService)
{
    // ... crear assignment ...
    
    $assignment = Assignment::create([...]);
    
    // Notificar a estudiantes
    $notificationService->notificarTareaPublicada($assignment->id);
    
    return response()->json([...], 201);
}
```

### 3. Cuando se CALIFICA UN EXAMEN (QuizResultController::calificarRespuesta)
```php
public function calificarRespuesta(Request $request, NotificationService $notificationService)
{
    // ... guardar calificación ...
    
    $attempt->update(['estado' => 'calificado', ...]);
    
    // Notificar al estudiante
    $notificationService->notificarExamenCalificado($attempt->id);
    
    return response()->json([...]);
}
```

### 4. Cuando se CALIFICA UNA ENTREGA (AssignmentController - crear nuevo endpoint)
```php
// Agregar a AssignmentController
public function calificarEntrega(Request $request, Submission $submission, NotificationService $notificationService)
{
    // Validar permisos (es profesor)
    
    $submission->update([
        'estado' => 'calificado',
        'calificacion' => $request->input('calificacion'),
        'comentario_profesor' => $request->input('comentario'),
    ]);
    
    // Notificar al estudiante
    $notificationService->notificarEntregaCalificada($submission->id);
    
    return response()->json([...]);
}

// Agregar ruta:
// PUT /profesor/tareas/{id}/entregas/{submission_id}/calificar
```

### 5. Cuando se CIERRA SESIÓN DE ASISTENCIA (AttendanceController::cerrarSesion)
```php
public function cerrarSesion($sessionId, NotificationService $notificationService)
{
    // ... marcar session como cerrada ...
    
    $session->update(['abierta' => false]);
    
    // Notificar a ausentes
    $notificationService->notificarAusentes($session->id);
    
    return response()->json([...]);
}
```

### 6. TAREAS POR VENCER EN 24H (Comando/Job)
Crear comando artisan para ejecutar cada hora:

```php
// app/Console/Commands/NotificarTareasPorVencer.php
<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotificarTareasPorVencer extends Command
{
    protected $signature = 'notificaciones:tareas-por-vencer';
    protected $description = 'Notificar tareas que vencen en 24 horas';

    public function handle(NotificationService $notificationService)
    {
        $notificationService->notificarTareasPorVencer();
        $this->info('✅ Tareas por vencer notificadas');
    }
}
```

Agregar a `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('notificaciones:tareas-por-vencer')
        ->hourly()
        ->runInBackground();
}
```

### 7. LIMPIAR NOTIFICACIONES ANTIGUAS (Comando/Job)
Crear comando para ejecutar diariamente:

```php
// app/Console/Commands/LimpiarNotificaciones.php
<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class LimpiarNotificaciones extends Command
{
    protected $signature = 'notificaciones:limpiar';
    protected $description = 'Limpiar notificaciones leídas > 90 días';

    public function handle(NotificationService $notificationService)
    {
        $eliminadas = $notificationService->limpiarNotificacionesAntiguas();
        $this->info("✅ $eliminadas notificaciones antiguas eliminadas");
    }
}
```

Agregar a `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('notificaciones:limpiar')
        ->daily()
        ->at('02:00');
}
```

## Frontend - Integración en Header.vue

```vue
<template>
  <header class="bg-white shadow">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
      <!-- Logo/Menu aquí -->
      
      <!-- Campana de notificaciones -->
      <NotificationBell />
      
      <!-- Avatar/Menu usuario aquí -->
    </nav>
  </header>
</template>

<script setup>
import NotificationBell from '@/components/NotificationBell.vue'
</script>
```

## Polling automático

El composable `useNotifications()` inicia automáticamente:
- Al montar el componente
- Polling cada 30 segundos al endpoint `/notificaciones/no-leidas-count`
- Se detiene al desmontar

No requiere configuración adicional.

## Estructura de datos

### Notificación en DB:
```json
{
  "id": 123,
  "user_id": 45,
  "tipo": "examen_calificado",
  "titulo": "📊 Examen calificado",
  "mensaje": "Matemática I — Nota: 18/20",
  "url": "/estudiante/quizzes/5/resultado",
  "datos": {
    "quiz_id": 5,
    "quiz_titulo": "Matemática I",
    "nota_obtenida": 18,
    "puntos_totales": 20
  },
  "leida": false,
  "leida_at": null,
  "created_at": "2026-01-15 14:30:00",
  "updated_at": "2026-01-15 14:30:00"
}
```

## Ciclo de vida típico

1. **Evento** → Se publica quiz/tarea/calificación
2. **Notificación creada** → `NotificationService::notificar()`/`notificarGrupo()`
3. **DB INSERT** → Tabla `notifications`
4. **Frontend polling** → `GET /notificaciones/no-leidas-count` (cada 30s)
5. **Badge actualiza** → Contador en campana
6. **Usuario hace clic** → Panel dropdown o va a `/notificaciones`
7. **Usuario abre** → Auto marca como leída + navega si hay URL
8. **Limpieza automática** → Job diario elimina > 90 días leídas

## Testing

```bash
# Ver todas las notificaciones de un usuario
GET /notificaciones?per_page=50

# Ver solo no leídas
GET /notificaciones?filtro=no-leidas

# Contar no leídas (simula polling)
GET /notificaciones/no-leidas-count

# Crear notificación manualmente (para testing)
POST /api/test/notifications
{
  "user_id": 1,
  "tipo": "anuncio_general",
  "titulo": "Test",
  "mensaje": "Mensaje de prueba"
}
```

## TODO: Completar integraciones

- [ ] Agregar `notificationService` a QuizController::store()
- [ ] Agregar `notificationService` a AssignmentController::crearProfesor()
- [ ] Crear endpoint PUT `/profesor/tareas/{id}/entregas/{submission}/calificar`
- [ ] Agregar `notificationService` a calificar
- [ ] Crear comandos Artisan (tareas-por-vencer, limpiar)
- [ ] Integrar NotificationBell.vue en Header.vue
- [ ] Probar polling (F12 → Network → filter by "no-leidas-count")

## Notas importantes

- El polling es **ligero**: solo obtiene contador, no lista completa
- Las notificaciones **nunca se eliminan de verdad**, solo se marcan como leídas
- La limpieza de antiguas ocurre cada noche automáticamente
- El composable **reinicia automáticamente** al navegar si es necesario
- **URLs relatibas** en `notification.url` para navegar dentro de la SPA
