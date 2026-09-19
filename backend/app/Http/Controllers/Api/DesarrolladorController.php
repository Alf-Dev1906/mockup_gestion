<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Services\SecurityLogService;

class DesarrolladorController extends Controller
{
    /**
     * Dashboard con estadísticas del sistema
     */
    public function dashboard(): JsonResponse
    {
        Gate::authorize('acceso-configuracion-critica');

        $stats = [
            'database' => [
                'size' => $this->getDatabaseSize(),
                'tables' => $this->getTablesInfo(),
                'connections' => DB::table('jobs')->count(),
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => config('app.env'),
                'debug_mode' => config('app.debug'),
                'timezone' => config('app.timezone'),
            ],
            'storage' => [
                'disk_usage' => $this->getDiskUsage(),
                'backups_count' => DB::table('backups')->count(),
                'logs_size' => $this->getLogsSize(),
            ],
            'security' => [
                'failed_logins_today' => $this->getFailedLoginsToday(),
                'unauthorized_attempts_today' => $this->getUnauthorizedAttemptsToday(),
                'active_sessions' => DB::table('personal_access_tokens')->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Explorador de base de datos
     */
    public function database(): JsonResponse
    {
        Gate::authorize('acceso-base-datos');

        $driver = DB::getDriverName();
        
        // Obtener tablas según el driver
        if ($driver === 'pgsql') {
            $tables = DB::select("
                SELECT tablename as table_name 
                FROM pg_catalog.pg_tables 
                WHERE schemaname = 'public'
                ORDER BY tablename
            ");
        } else {
            // MySQL/MariaDB
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
        }

        $result = [];
        
        foreach ($tables as $table) {
            // Obtener nombre de tabla según driver
            if ($driver === 'pgsql') {
                $tableName = $table->table_name;
            } else {
                $databaseName = DB::getDatabaseName();
                $tableName = $table->{"Tables_in_$databaseName"};
            }
            
            try {
                $count = DB::table($tableName)->count();
            } catch (\Exception $e) {
                $count = -1;
            }
            
            // Obtener tamaño según driver
            if ($driver === 'pgsql') {
                $sizeQuery = DB::select("
                    SELECT pg_size_pretty(pg_total_relation_size(?::regclass)) as size_pretty,
                           pg_total_relation_size(?::regclass) as size_bytes
                ", [$tableName, $tableName]);
                
                $sizeMB = isset($sizeQuery[0]) ? round($sizeQuery[0]->size_bytes / 1024 / 1024, 2) : 0;
                $sizePretty = $sizeQuery[0]->size_pretty ?? '0 bytes';
            } else {
                $sizeQuery = DB::select("
                    SELECT 
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                    FROM information_schema.TABLES 
                    WHERE table_schema = ? AND table_name = ?
                ", [$databaseName, $tableName]);
                
                $sizeMB = $sizeQuery[0]->size_mb ?? 0;
                $sizePretty = $sizeMB . ' MB';
            }

            $result[] = [
                'name' => $tableName,
                'rows' => $count,
                'size_mb' => $sizeMB,
                'size_pretty' => $sizePretty,
                'error' => $count === -1 ? 'No se puede acceder' : null,
            ];
        }

        return response()->json([
            'driver' => $driver,
            'database' => DB::getDatabaseName(),
            'tables' => $result,
            'total_tables' => count($result),
        ]);
    }

    /**
     * Ejecutar query SQL (PELIGROSO - solo desarrollador)
     */
    public function executeQuery(Request $request): JsonResponse
    {
        Gate::authorize('acceso-base-datos');

        $request->validate([
            'query' => 'required|string',
        ]);

        $query = $request->input('query');

        // Log de seguridad
        SecurityLogService::logCommandExecution(
            $request->user(),
            "SQL: " . substr($query, 0, 100)
        );

        try {
            // Solo permitir SELECT por seguridad
            if (!preg_match('/^\s*SELECT/i', $query)) {
                return response()->json([
                    'error' => 'Solo se permiten queries SELECT. Para modificaciones use las migraciones.'
                ], 403);
            }

            $results = DB::select($query);

            return response()->json([
                'success' => true,
                'rows' => count($results),
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ver logs del sistema
     */
    public function logs(Request $request): JsonResponse
    {
        Gate::authorize('ver-logs-sistema');

        $type = $request->query('type', 'laravel');
        $lines = $request->query('lines', 100);

        $logFile = match($type) {
            'laravel' => storage_path('logs/laravel.log'),
            'security' => storage_path('logs/security.log'),
            default => storage_path('logs/laravel.log'),
        };

        if (!file_exists($logFile)) {
            return response()->json([
                'error' => 'Archivo de log no encontrado'
            ], 404);
        }

        $logs = $this->tailFile($logFile, $lines);

        return response()->json([
            'type' => $type,
            'lines' => count($logs),
            'logs' => $logs,
        ]);
    }

    /**
     * Logs de seguridad desde BD
     */
    public function securityLogs(Request $request): JsonResponse
    {
        Gate::authorize('ver-logs-sistema');

        $limit = $request->query('limit', 100);
        $action = $request->query('action');

        $query = DB::table('system_logs')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($action) {
            $query->where('accion', $action);
        }

        $logs = $query->get();

        return response()->json($logs);
    }

    /**
     * Crear backup de la base de datos
     */
    public function createBackup(Request $request): JsonResponse
    {
        Gate::authorize('gestionar-backups');

        $request->validate([
            'notas' => 'nullable|string|max:500',
        ]);

        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_{$timestamp}.sql";
        $filepath = storage_path("app/backups/{$filename}");

        // Asegurar que existe el directorio
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        // Ejecutar mysqldump
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = "mysqldump -h{$host} -u{$username} -p{$password} {$database} > {$filepath}";
        
        // En producción, usar variables de entorno seguras
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json([
                'error' => 'Error al crear el backup'
            ], 500);
        }

        $size = file_exists($filepath) ? filesize($filepath) : 0;

        // Registrar en BD
        $backupId = DB::table('backups')->insertGetId([
            'nombre' => $filename,
            'ruta' => $filepath,
            'tamano' => $size,
            'tipo' => 'manual',
            'estatus' => 'completado',
            'creado_por' => $request->user()->id,
            'notas' => $request->notas,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SecurityLogService::logBackupCreated($request->user(), $filename, $size);

        return response()->json([
            'success' => true,
            'backup' => [
                'id' => $backupId,
                'filename' => $filename,
                'size_mb' => round($size / 1024 / 1024, 2),
            ],
        ]);
    }

    /**
     * Listar backups
     */
    public function listBackups(): JsonResponse
    {
        Gate::authorize('gestionar-backups');

        $backups = DB::table('backups')
            ->select('id', 'nombre', 'tamano', 'tipo', 'estatus', 'notas', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($backups);
    }

    /**
     * Ver configuración del sistema
     */
    public function config(Request $request): JsonResponse
    {
        Gate::authorize('acceso-configuracion-critica');

        $key = $request->query('key');

        if ($key) {
            SecurityLogService::logCriticalConfigAccess($request->user(), $key);
        }

        $config = [
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ],
            'database' => [
                'driver' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
                'database' => config('database.connections.mysql.database'),
            ],
            'mail' => [
                'driver' => config('mail.default'),
                'from' => config('mail.from'),
            ],
            'sanctum' => [
                'expiration' => config('sanctum.expiration'),
            ],
        ];

        return response()->json($config);
    }

    /**
     * Ejecutar comando Artisan
     */
    public function artisanCommand(Request $request): JsonResponse
    {
        Gate::authorize('ejecutar-comandos-sistema');

        $request->validate([
            'command' => 'required|string',
        ]);

        $command = $request->command;

        // Lista blanca de comandos permitidos
        $allowedCommands = [
            'cache:clear',
            'config:clear',
            'route:clear',
            'view:clear',
            'optimize',
            'optimize:clear',
            'queue:work',
            'schedule:run',
        ];

        $commandBase = explode(' ', $command)[0];

        if (!in_array($commandBase, $allowedCommands)) {
            return response()->json([
                'error' => 'Comando no permitido por seguridad'
            ], 403);
        }

        SecurityLogService::logCommandExecution($request->user(), $command);

        try {
            Artisan::call($command);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Estadísticas de uso del sistema
     */
    public function statistics(): JsonResponse
    {
        Gate::authorize('ver-logs-sistema');

        $stats = [
            'usuarios' => [
                'total' => DB::table('users')->count(),
                'por_rol' => DB::table('users')
                    ->select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get(),
            ],
            'estudiantes' => [
                'total' => DB::table('estudiantes')->count(),
                'por_estatus' => DB::table('estudiantes')
                    ->select('estatus', DB::raw('count(*) as count'))
                    ->groupBy('estatus')
                    ->get(),
            ],
            'academico' => [
                'profesores' => DB::table('profesores')->count(),
                'materias' => DB::table('materias')->count(),
                'aulas' => DB::table('aulas')->count(),
                'carreras' => DB::table('carreras')->count(),
                'inscripciones' => DB::table('inscripciones')->count(),
            ],
            'actividad_reciente' => DB::table('system_logs')
                ->select('accion', DB::raw('count(*) as count'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('accion')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    // ============ MÉTODOS AUXILIARES ============

    private function getDatabaseSize(): string
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            $database = DB::getDatabaseName();
            $result = DB::select("
                SELECT pg_size_pretty(pg_database_size(?)) as size_pretty
            ", [$database]);
            
            return $result[0]->size_pretty ?? '0 bytes';
        } else {
            // MySQL/MariaDB
            $database = DB::getDatabaseName();
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$database]);

            return ($result[0]->size_mb ?? 0) . ' MB';
        }
    }

    private function getTablesInfo(): int
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            $result = DB::select("
                SELECT COUNT(*) as count
                FROM pg_catalog.pg_tables 
                WHERE schemaname = 'public'
            ");
            return $result[0]->count ?? 0;
        } else {
            // MySQL/MariaDB
            $result = DB::select("SHOW TABLES");
            return count($result);
        }
    }

    private function getDiskUsage(): array
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;

        return [
            'total_gb' => round($total / 1024 / 1024 / 1024, 2),
            'used_gb' => round($used / 1024 / 1024 / 1024, 2),
            'free_gb' => round($free / 1024 / 1024 / 1024, 2),
            'percent_used' => round(($used / $total) * 100, 2),
        ];
    }

    private function getLogsSize(): string
    {
        $logPath = storage_path('logs');
        $size = 0;

        if (is_dir($logPath)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($logPath)) as $file) {
                $size += $file->getSize();
            }
        }

        return round($size / 1024 / 1024, 2) . ' MB';
    }

    private function getFailedLoginsToday(): int
    {
        return DB::table('system_logs')
            ->where('accion', 'login_fallido')
            ->whereDate('created_at', today())
            ->count();
    }

    private function getUnauthorizedAttemptsToday(): int
    {
        return DB::table('system_logs')
            ->where('accion', 'intento_acceso_no_autorizado')
            ->whereDate('created_at', today())
            ->count();
    }

    private function tailFile(string $filepath, int $lines): array
    {
        $file = new \SplFileObject($filepath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        $startLine = max(0, $lastLine - $lines);

        $result = [];
        $file->seek($startLine);
        while (!$file->eof()) {
            $result[] = trim($file->fgets());
        }

        return array_filter($result);
    }
}
