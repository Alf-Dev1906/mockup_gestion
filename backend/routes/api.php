<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacultadController;
use App\Http\Controllers\Api\CarreraController;
use App\Http\Controllers\Api\EstudianteController;
use App\Http\Controllers\Api\ProfesorController;
use App\Http\Controllers\Api\MateriaController;
use App\Http\Controllers\Api\AulaController;
use App\Http\Controllers\Api\HorarioController;
use App\Http\Controllers\Api\InscripcionController;
use App\Http\Controllers\Api\CalificacionController;
use App\Http\Controllers\Api\DesarrolladorController;
use App\Http\Controllers\Api\SoporteController;
use App\Http\Controllers\Api\AdministrativoController;
use App\Http\Controllers\Api\EstudianteDashboardController;
use App\Http\Controllers\Api\ProfesorDashboardController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\RegistroController;
use App\Http\Controllers\Api\AdmisionController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuizAttemptController;
use App\Http\Controllers\Api\QuizResultController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AuditoriaController;


/*
|--------------------------------------------------------------------------
| Rutas PÚBLICAS — sin autenticación
|--------------------------------------------------------------------------
*/

// ⚠️ RUTAS TEMPORALES DE SETUP - BORRAR DESPUÉS DE MIGRACIONES
require __DIR__ . '/setup.php';

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/public/registro', [RegistroController::class, 'registro']);
});

// Rutas públicas para el sitio web
Route::get('/public/carreras', [CarreraController::class, 'index']);
Route::get('/public/facultades', [FacultadController::class, 'index']);
Route::get('/public/verificar-disponibilidad', [RegistroController::class, 'verificarDisponibilidad']);

// TEMPORAL: Endpoint de depuración (REMOVER EN PRODUCCIÓN)
Route::get('/debug/check-user/{email}', function($email) {
    $user = \App\Models\User::where('email', $email)->first();
    $estudiante = \App\Models\Estudiante::where('email', $email)->first();
    
    return response()->json([
        'email' => $email,
        'user_exists' => $user ? true : false,
        'user_data' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'has_password' => !empty($user->password),
            'password_length' => strlen($user->password ?? ''),
            'created_at' => $user->created_at,
        ] : null,
        'estudiante_exists' => $estudiante ? true : false,
        'estudiante_data' => $estudiante ? [
            'id' => $estudiante->id,
            'nombre' => $estudiante->nombre,
            'apellido' => $estudiante->apellido,
            'cedula' => $estudiante->cedula,
            'estatus' => $estudiante->estatus,
            'matricula' => $estudiante->matricula,
        ] : null,
    ]);
});

// TEMPORAL: Endpoint para recrear usuarios de prueba (versión simplificada)
Route::get('/debug/recreate-test-users', function() {
    try {
        $users = [
            ['email' => 'developer@universidad.edu.ve', 'name' => 'Desarrollador Principal', 'password' => 'Dev2026!', 'role' => 'desarrollador'],
            ['email' => 'soporte@universidad.edu.ve', 'name' => 'Soporte IT', 'password' => 'Soporte2026!', 'role' => 'soporte_it'],
            ['email' => 'admin@universidad.edu.ve', 'name' => 'Administrador Académico', 'password' => 'Admin2026!', 'role' => 'administrativo'],
            ['email' => 'profesor@universidad.edu.ve', 'name' => 'Profesor de Prueba', 'password' => 'Profesor2026!', 'role' => 'profesor'],
            ['email' => 'estudiante@universidad.edu.ve', 'name' => 'Estudiante Activo', 'password' => 'Estudiante2026!', 'role' => 'estudiante'],
            ['email' => 'solicitante@universidad.edu.ve', 'name' => 'Estudiante Solicitante', 'password' => 'Solicitante2026!', 'role' => 'estudiante'],
        ];
        
        $results = [];
        
        foreach ($users as $userData) {
            $user = \App\Models\User::where('email', $userData['email'])->first();
            
            if ($user) {
                // Actualizar usuario existente
                $user->update([
                    'name' => $userData['name'],
                    'password' => \Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'email_verified_at' => now(),
                ]);
                $results[] = "✅ Usuario actualizado: {$userData['email']}";
            } else {
                // Crear nuevo usuario
                \App\Models\User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => \Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'email_verified_at' => now(),
                ]);
                $results[] = "✅ Usuario creado: {$userData['email']}";
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Usuarios de prueba recreados exitosamente',
            'details' => $results,
            'note' => 'Solo se crearon los usuarios. Los registros de estudiante/profesor deben crearse manualmente si es necesario.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al recrear usuarios',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});

// TEMPORAL: Verificar datos en base de datos
Route::get('/debug/check-database', function() {
    try {
        $stats = [
            'facultades' => \DB::table('facultades')->count(),
            'carreras' => \DB::table('carreras')->count(),
            'materias' => \DB::table('materias')->count(),
            'estudiantes' => \DB::table('estudiantes')->count(),
            'profesores' => \DB::table('profesores')->count(),
            'aulas' => \DB::table('aulas')->count(),
            'horarios' => \DB::table('horarios')->count(),
            'inscripciones' => \DB::table('inscripciones')->count(),
            'users' => \DB::table('users')->count(),
            
            // Mostrar algunos registros de ejemplo
            'facultades_sample' => \DB::table('facultades')->limit(3)->get(['id', 'nombre']),
            'carreras_sample' => \DB::table('carreras')->limit(3)->get(['id', 'nombre', 'facultad_id']),
            'estudiantes_sample' => \DB::table('estudiantes')->limit(3)->get(['id', 'nombre', 'apellido', 'email']),
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Datos de la base de datos',
            'stats' => $stats,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al consultar base de datos',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ], 500);
    }
});

// TEMPORAL: Poblar base de datos con datos de prueba
Route::get('/debug/seed-database', function() {
    try {
        \Artisan::call('db:seed', ['--class' => 'DatosCompletosSeeder']);
        $output = \Artisan::output();
        
        return response()->json([
            'success' => true,
            'message' => 'Base de datos poblada exitosamente',
            'output' => $output,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al poblar base de datos',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| Rutas PROTEGIDAS — requieren autenticación
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    |----------------------------------------------------------------------
    | RUTAS COMPARTIDAS - Accesibles para múltiples roles
    |----------------------------------------------------------------------
    */
    // Carreras y facultades (lectura para todos los autenticados)
    Route::get('/carreras', [CarreraController::class, 'index']);
    Route::get('/carreras/{id}', [CarreraController::class, 'show']);
    Route::get('/facultades', [FacultadController::class, 'index']);
    Route::get('/facultades/{id}', [FacultadController::class, 'show']);
    
    // Materias (lectura para todos los autenticados)
    Route::get('/materias', [MateriaController::class, 'index']);
    Route::get('/materias/{id}', [MateriaController::class, 'show']);
    
    // Aulas (lectura para todos los autenticados)
    Route::get('/aulas', [AulaController::class, 'index']);
    Route::get('/aulas/{id}', [AulaController::class, 'show']);
    
    // Notificaciones (para todos los autenticados)
    Route::prefix('notificaciones')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/no-leidas-count', [NotificationController::class, 'contadorNoLeidas']); // Para polling
        Route::get('/tipos-disponibles', [NotificationController::class, 'tiposDisponibles']);
        Route::post('/{notification}/leer', [NotificationController::class, 'marcarLeida']);
        Route::post('/leer-todas', [NotificationController::class, 'marcarTodasLeidas']);
        Route::delete('/{notification}', [NotificationController::class, 'eliminar']);
        Route::post('/eliminar-todas', [NotificationController::class, 'eliminarTodasLeidas']);
    });
    
    // Estudiantes (lectura para profesor y admin)
    Route::middleware('role:profesor')->group(function () {
        Route::get('/estudiantes', [EstudianteController::class, 'index']);
        Route::get('/estudiantes/{id}', [EstudianteController::class, 'show']);
        Route::get('/estudiantes/{id}/inscripciones', [EstudianteController::class, 'inscripciones']);
        Route::get('/estudiantes/{id}/estadisticas', [EstudianteController::class, 'estadisticas']);
    });
    
    // Profesores (lectura para admin)
    Route::middleware('role:administrativo')->group(function () {
        Route::get('/profesores', [ProfesorController::class, 'index']);
        Route::get('/profesores/{id}', [ProfesorController::class, 'show']);
    });
    
    // Horarios (lectura para profesor y admin)
    Route::middleware('role:profesor')->group(function () {
        Route::get('/horarios', [HorarioController::class, 'index']);
        Route::get('/horarios/{id}', [HorarioController::class, 'show']);
        Route::get('/horarios/disponibles/listado', [HorarioController::class, 'disponibles']);
        Route::get('/horarios/{id}/estudiantes', [HorarioController::class, 'estudiantes']);
    });
    
    // Inscripciones (lectura para profesor y admin)
    Route::middleware('role:profesor')->group(function () {
        Route::get('/inscripciones', [InscripcionController::class, 'index']);
        Route::get('/inscripciones/{id}', [InscripcionController::class, 'show']);
    });
    
    // Calificaciones (lectura para profesor y admin)
    Route::middleware('role:profesor')->group(function () {
        Route::get('/calificaciones', [CalificacionController::class, 'index']);
        Route::get('/calificaciones/{id}', [CalificacionController::class, 'show']);
        Route::get('/calificaciones/estadisticas', [CalificacionController::class, 'estadisticas']);
    });
    
    // Escritura de recursos (solo admin)
    Route::middleware('role:administrativo')->group(function () {
        // Estudiantes - escritura
        Route::post('/estudiantes', [EstudianteController::class, 'store']);
        Route::put('/estudiantes/{id}', [EstudianteController::class, 'update']);
        Route::delete('/estudiantes/{id}', [EstudianteController::class, 'destroy']);
        
        // Profesores - escritura
        Route::post('/profesores', [ProfesorController::class, 'store']);
        Route::put('/profesores/{id}', [ProfesorController::class, 'update']);
        Route::delete('/profesores/{id}', [ProfesorController::class, 'destroy']);
        
        // Materias - escritura
        Route::post('/materias', [MateriaController::class, 'store']);
        Route::put('/materias/{id}', [MateriaController::class, 'update']);
        Route::delete('/materias/{id}', [MateriaController::class, 'destroy']);
        
        // Carreras - escritura
        Route::post('/carreras', [CarreraController::class, 'store']);
        Route::put('/carreras/{id}', [CarreraController::class, 'update']);
        Route::delete('/carreras/{id}', [CarreraController::class, 'destroy']);
        Route::get('/carreras/{id}/estudiantes', [CarreraController::class, 'estudiantes']);
        Route::get('/carreras/{id}/materias', [CarreraController::class, 'materias']);
        
        // Facultades - escritura
        Route::post('/facultades', [FacultadController::class, 'store']);
        Route::put('/facultades/{id}', [FacultadController::class, 'update']);
        Route::delete('/facultades/{id}', [FacultadController::class, 'destroy']);
        
        // Aulas - escritura
        Route::post('/aulas', [AulaController::class, 'store']);
        Route::put('/aulas/{id}', [AulaController::class, 'update']);
        Route::delete('/aulas/{id}', [AulaController::class, 'destroy']);
        
        // Horarios - escritura
        Route::post('/horarios', [HorarioController::class, 'store']);
        Route::put('/horarios/{id}', [HorarioController::class, 'update']);
        Route::delete('/horarios/{id}', [HorarioController::class, 'destroy']);
        
        // Inscripciones - escritura
        Route::post('/inscripciones', [InscripcionController::class, 'store']);
        Route::put('/inscripciones/{id}', [InscripcionController::class, 'update']);
        Route::delete('/inscripciones/{id}', [InscripcionController::class, 'destroy']);
        Route::post('/inscripciones/{id}/retirar', [InscripcionController::class, 'retirar']);
    });

    /*
    |----------------------------------------------------------------------
    | NIVEL 5 - DESARROLLADOR
    |----------------------------------------------------------------------
    */
    Route::middleware('role:desarrollador')->prefix('dev')->group(function () {
        Route::get('/dashboard', [DesarrolladorController::class, 'dashboard']);
        Route::get('/database', [DesarrolladorController::class, 'database']);
        Route::post('/database/query', [DesarrolladorController::class, 'executeQuery']);
        Route::get('/logs', [DesarrolladorController::class, 'logs']);
        Route::get('/logs/security', [DesarrolladorController::class, 'securityLogs']);
        Route::get('/config', [DesarrolladorController::class, 'config']);
        Route::post('/artisan', [DesarrolladorController::class, 'artisanCommand']);
        Route::get('/statistics', [DesarrolladorController::class, 'statistics']);
        Route::post('/backup', [DesarrolladorController::class, 'createBackup']);
        Route::get('/backups', [DesarrolladorController::class, 'listBackups']);
    });

    /*
    |----------------------------------------------------------------------
    | NIVEL 4 - SOPORTE IT
    |----------------------------------------------------------------------
    */
    Route::middleware('role:soporte_it')->prefix('soporte')->group(function () {
        Route::get('/dashboard', [SoporteController::class, 'dashboard']);
        
        // Gestión de usuarios
        Route::get('/usuarios', [SoporteController::class, 'usuarios']);
        Route::get('/usuarios/{usuario}', [SoporteController::class, 'showUsuario']);
        Route::post('/usuarios', [SoporteController::class, 'createUsuario']);
        Route::put('/usuarios/{usuario}', [SoporteController::class, 'updateUsuario']);
        Route::delete('/usuarios/{usuario}', [SoporteController::class, 'deleteUsuario']);
        Route::post('/usuarios/{usuario}/cambiar-rol', [SoporteController::class, 'cambiarRol']);
        Route::post('/usuarios/{usuario}/reset-password', [SoporteController::class, 'resetPassword']);
        
        // Logs del sistema (movido desde admin)
        Route::get('/logs', [LogController::class, 'index']);
        Route::delete('/logs', [LogController::class, 'limpiar']);
        Route::get('/logs/estadisticas', [LogController::class, 'estadisticas']);
        
        // Sesiones y monitoreo
        Route::get('/sesiones', [SoporteController::class, 'sesionesActivas']);
        Route::delete('/sesiones/{tokenId}', [SoporteController::class, 'revocarSesion']);
        Route::get('/estadisticas', [SoporteController::class, 'estadisticas']);
        
        // Backups
        Route::get('/backups', [SoporteController::class, 'backups']);
        Route::post('/backups', [SoporteController::class, 'createBackup']);
    });

    /*
    |----------------------------------------------------------------------
    | NIVEL 3 - ADMINISTRATIVO
    |----------------------------------------------------------------------
    */
    Route::middleware('role:administrativo')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdministrativoController::class, 'dashboard']);
        
        // Solicitudes de admisión
        Route::get('/solicitudes', [AdministrativoController::class, 'solicitudes']);
        Route::get('/solicitudes/{id}', [AdministrativoController::class, 'showSolicitud']);
        Route::post('/solicitudes/{id}/aprobar', [AdministrativoController::class, 'aprobarSolicitud']);
        Route::post('/solicitudes/{id}/rechazar', [AdministrativoController::class, 'rechazarSolicitud']);
        Route::post('/solicitudes/{id}/solicitar-correccion', [AdministrativoController::class, 'solicitarCorreccion']);
        
        // Pagos
        Route::get('/pagos', [AdministrativoController::class, 'pagos']);
        Route::post('/pagos', [AdministrativoController::class, 'registrarPago']);
        Route::post('/pagos/{id}/marcar-pagado', [AdministrativoController::class, 'marcarPagado']);
        
        // Reportes
        Route::get('/reportes', [AdministrativoController::class, 'reportes']);
    });

    /*
    |----------------------------------------------------------------------
    | NIVEL 2 - PROFESOR
    |----------------------------------------------------------------------
    */
    Route::middleware('role:profesor')->prefix('profesor')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\ProfesorDashboardController::class, 'dashboard']);
        
        // Usar ProfesorDashboardController para funcionalidades específicas
        Route::get('/materias', [\App\Http\Controllers\Api\ProfesorDashboardController::class, 'misMaterias']);
        Route::get('/materias/{materiaId}/estudiantes', [\App\Http\Controllers\Api\ProfesorDashboardController::class, 'estudiantesMateria']);
        Route::get('/horario', [\App\Http\Controllers\Api\ProfesorDashboardController::class, 'miHorario']);
        Route::get('/horarios', [\App\Http\Controllers\Api\ProfesorDashboardController::class, 'miHorario']); // Alias plural
        Route::get('/estudiantes', [\App\Http\Controllers\Api\ProfesorDashboardController::class, 'misEstudiantes']);
        Route::get('/calificaciones', [\App\Http\Controllers\Api\ProfesorDashboardController::class, 'calificaciones']);
        Route::post('/calificaciones', [\App\Http\Controllers\Api\ProfesorDashboardController::class, 'guardarCalificacion']);
        Route::get('/perfil', [\App\Http\Controllers\Api\ProfesorDashboardController::class, 'miPerfil']);
        
        // Quizzes - Aula Virtual
        Route::get('/quizzes', [QuizController::class, 'index']);
        Route::post('/quizzes', [QuizController::class, 'store']);
        Route::get('/quizzes/{id}', [QuizController::class, 'show']);
        Route::put('/quizzes/{id}', [QuizController::class, 'update']);
        Route::delete('/quizzes/{id}', [QuizController::class, 'destroy']);
        Route::get('/quizzes/{id}/resultados', [QuizController::class, 'resultados']);
        Route::get('/quizzes/{id}/estadisticas', [QuizResultController::class, 'estadisticas']);
        
        // Preguntas de quizzes
        Route::get('/quizzes/{id}/preguntas', [QuizController::class, 'indexPreguntas']);
        Route::post('/quizzes/{id}/preguntas', [QuizController::class, 'storePregunta']);
        Route::put('/preguntas/{id}', [QuizController::class, 'updatePregunta']);
        Route::delete('/preguntas/{id}', [QuizController::class, 'destroyPregunta']);
        
        // Calificación manual y resultados
        Route::get('/quiz-attempts/{id}', [QuizResultController::class, 'show']);
        Route::get('/quiz-attempts/{id}/incidencias', [QuizResultController::class, 'incidencias']);
        Route::put('/quiz-answers/{id}/calificar', [QuizResultController::class, 'calificarRespuesta']);
        Route::post('/quiz-attempts/{id}/publicar-nota', [QuizResultController::class, 'publicarNota']);
        Route::post('/quiz-attempts/{id}/anular', [QuizResultController::class, 'anularIntento']);
        
        // Asistencia
        Route::post('/asistencia/abrir-sesion', [AttendanceController::class, 'abrirSesion']);
        Route::get('/asistencia/sesion-activa/{horario_id}', [AttendanceController::class, 'sesionActiva']);
        Route::post('/asistencia/cerrar-sesion/{session_id}', [AttendanceController::class, 'cerrarSesion']);
        Route::get('/asistencia/historial/{horario_id}', [AttendanceController::class, 'historial']);
        Route::put('/asistencia/{attendance_id}/justificar', [AttendanceController::class, 'justificar']);
        
        // Tareas (Bloque 5.1)
        Route::get('/tareas', [AssignmentController::class, 'listarProfesor']);
        Route::post('/tareas', [AssignmentController::class, 'crearProfesor']);
        Route::put('/tareas/{assignment}', [AssignmentController::class, 'actualizarProfesor']);
        Route::delete('/tareas/{assignment}', [AssignmentController::class, 'eliminarProfesor']);
        Route::get('/tareas/{assignment}/entregas', [AssignmentController::class, 'listarEntregasProfesor']);
        
        // Auditoría Docente (Bloque 6.1)
        Route::get('/auditoria/{horario_id}/estudiantes', [AuditoriaController::class, 'listarEstudiantes']);
        Route::get('/auditoria/{horario_id}/estudiante/{estudiante_id}', [AuditoriaController::class, 'expedienteEstudiante']);
        Route::get('/auditoria/quiz/{quiz_id}/incidencias', [AuditoriaController::class, 'incidenciasPorExamen']);
        
        // Testing (solo desarrollo)
        Route::get('/asistencia/test/codigos', [AttendanceController::class, 'testCodigos']);
    });

    /*
    |----------------------------------------------------------------------
    | NIVEL 1 - ESTUDIANTE
    |----------------------------------------------------------------------
    */
    Route::middleware('role:estudiante')->prefix('estudiante')->group(function () {
        Route::get('/dashboard', [EstudianteDashboardController::class, 'dashboard']);
        
        // Usar EstudianteDashboardController para funcionalidades específicas
        Route::get('/horario', [\App\Http\Controllers\Api\EstudianteDashboardController::class, 'miHorario']);
        Route::get('/calificaciones', [\App\Http\Controllers\Api\EstudianteDashboardController::class, 'misCalificaciones']);
        Route::get('/perfil', [\App\Http\Controllers\Api\EstudianteDashboardController::class, 'miPerfil']);
        Route::put('/perfil', [\App\Http\Controllers\Api\EstudianteDashboardController::class, 'actualizarPerfil']);
        
        // Estado de solicitud para solicitantes
        Route::get('/solicitud', [\App\Http\Controllers\Api\EstudianteDashboardController::class, 'obtenerEstadoSolicitud']);
        
        // Quizzes - Aula Virtual (Estudiante)
        Route::get('/quizzes', [QuizAttemptController::class, 'index']);
        Route::get('/quizzes/{id}', [QuizAttemptController::class, 'show']);
        Route::post('/quizzes/{id}/iniciar', [QuizAttemptController::class, 'iniciar']);
        Route::post('/quizzes/{id}/responder', [QuizAttemptController::class, 'responder']);
        Route::post('/quizzes/{id}/enviar', [QuizAttemptController::class, 'enviar']);
        Route::post('/quizzes/{id}/incidencias', [QuizAttemptController::class, 'registrarIncidencia']);
        Route::patch('/quiz-attempts/{id}', [QuizAttemptController::class, 'sincronizarTimer']);
        Route::get('/quiz-attempts/{id}/resultado', [QuizAttemptController::class, 'resultado']);
        
        // Inscripciones
        Route::get('/inscripciones', [\App\Http\Controllers\Api\EstudianteDashboardController::class, 'misInscripciones']);
        Route::get('/horarios-disponibles', [\App\Http\Controllers\Api\EstudianteDashboardController::class, 'horariosDisponibles']);
        Route::post('/inscribir', [\App\Http\Controllers\Api\EstudianteDashboardController::class, 'inscribir']);
        Route::delete('/inscripciones/{inscripcionId}', [\App\Http\Controllers\Api\EstudianteDashboardController::class, 'retirarInscripcion']);
        
        // Rutas del wizard de admisión (para solicitantes)
        Route::prefix('admision')->group(function () {
            Route::get('/solicitud', [AdmisionController::class, 'obtenerSolicitud']);
            Route::post('/paso-1', [AdmisionController::class, 'guardarPaso1']);
            Route::post('/paso-2', [AdmisionController::class, 'guardarPaso2']);
            Route::post('/paso-3', [AdmisionController::class, 'guardarPaso3']);
            Route::post('/paso-4', [AdmisionController::class, 'guardarPaso4']);
            Route::post('/enviar', [AdmisionController::class, 'enviarSolicitud']);
        });
        
        // Asistencia
        Route::post('/asistencia/marcar', [AttendanceController::class, 'marcar']);
        Route::get('/asistencia/resumen', [AttendanceController::class, 'resumenEstudiante']);
        
        // Quizzes/Exámenes
        Route::get('/quizzes', [QuizController::class, 'listarEstudiante']);
        
        // Tareas (Bloque 5.1)
        Route::get('/tareas', [AssignmentController::class, 'listarEstudiante']);
        Route::get('/tareas/{assignment}', [AssignmentController::class, 'verEstudiante']);
        Route::post('/tareas/{assignment}/entregar', [AssignmentController::class, 'entregarEstudiante']);
        Route::get('/tareas/{assignment}/mi-entrega', [AssignmentController::class, 'verMiEntrega']);
    });
});

