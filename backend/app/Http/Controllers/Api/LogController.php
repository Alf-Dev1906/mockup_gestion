<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    /**
     * Obtener logs del sistema
     */
    public function index(Request $request)
    {
        try {
            $tipo = $request->query('tipo', 'laravel'); // laravel, query, auth
            $lineas = $request->query('lineas', 100);

            $logPath = storage_path('logs/laravel.log');

            if (!File::exists($logPath)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No hay logs disponibles'
                ]);
            }

            // Leer las últimas N líneas del archivo
            $file = new \SplFileObject($logPath, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLineas = $file->key() + 1;

            $inicioLinea = max(0, $totalLineas - $lineas);
            
            $logs = [];
            $file->seek($inicioLinea);
            
            while (!$file->eof()) {
                $linea = trim($file->fgets());
                if (!empty($linea)) {
                    $logs[] = $linea;
                }
            }

            // Parsear logs
            $logsParsed = $this->parsearLogs($logs);

            return response()->json([
                'success' => true,
                'data' => $logsParsed,
                'total' => count($logsParsed)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parsear líneas de log
     */
    private function parsearLogs($lineas)
    {
        $logs = [];
        
        foreach ($lineas as $linea) {
            // Parsear formato: [2024-01-01 12:00:00] local.ERROR: mensaje
            if (preg_match('/\[(.*?)\]\s+(\w+)\.(\w+):\s+(.*)/', $linea, $matches)) {
                $logs[] = [
                    'fecha' => $matches[1] ?? '',
                    'ambiente' => $matches[2] ?? 'local',
                    'nivel' => $matches[3] ?? 'INFO',
                    'mensaje' => $matches[4] ?? $linea,
                    'linea_completa' => $linea,
                ];
            } else {
                // Si no coincide con el formato, agregar como está
                $logs[] = [
                    'fecha' => '',
                    'ambiente' => 'local',
                    'nivel' => 'INFO',
                    'mensaje' => $linea,
                    'linea_completa' => $linea,
                ];
            }
        }

        return array_reverse($logs); // Más recientes primero
    }

    /**
     * Limpiar logs
     */
    public function limpiar(Request $request)
    {
        try {
            $logPath = storage_path('logs/laravel.log');

            if (File::exists($logPath)) {
                File::put($logPath, '');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Logs limpiados exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Archivo de logs no encontrado'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de logs
     */
    public function estadisticas(Request $request)
    {
        try {
            $logPath = storage_path('logs/laravel.log');

            if (!File::exists($logPath)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'total_lineas' => 0,
                        'tamano' => '0 KB',
                        'errores' => 0,
                        'warnings' => 0,
                        'info' => 0,
                    ]
                ]);
            }

            $contenido = File::get($logPath);
            $lineas = explode("\n", $contenido);
            
            $errores = substr_count($contenido, '.ERROR:');
            $warnings = substr_count($contenido, '.WARNING:');
            $info = substr_count($contenido, '.INFO:');
            
            $tamano = File::size($logPath);
            $tamanoFormateado = $this->formatearTamano($tamano);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_lineas' => count($lineas),
                    'tamano' => $tamanoFormateado,
                    'errores' => $errores,
                    'warnings' => $warnings,
                    'info' => $info,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatear tamaño de archivo
     */
    private function formatearTamano($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
