<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SubmissionService
{
    /**
     * Valida que un archivo sea válido para la tarea
     */
    public function validarArchivo(UploadedFile $file, Assignment $assignment): array
    {
        $errores = [];

        // 1. Validar extensión
        $extension = strtolower($file->getClientOriginalExtension());
        $extensiones_permitidas = $assignment->getExtensionesPermitidas();

        if (!in_array($extension, $extensiones_permitidas)) {
            $errores[] = "Extensión no permitida. Acepto: " . implode(', ', $extensiones_permitidas);
        }

        // 2. Validar tamaño
        $tamano_mb = $file->getSize() / (1024 * 1024);
        if ($tamano_mb > $assignment->tamano_maximo_mb) {
            $errores[] = "Archivo muy grande. Máximo: {$assignment->tamano_maximo_mb}MB. Actual: " . round($tamano_mb, 2) . "MB";
        }

        // 3. Validar MIME type básico
        $mime = $file->getMimeType();
        $mimes_permitidos = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
            'application/zip',
            'application/x-zip-compressed',
        ];

        if (!in_array($mime, $mimes_permitidos)) {
            $errores[] = "Tipo de archivo no válido: {$mime}";
        }

        return $errores;
    }

    /**
     * Valida si la entrega está permitida (tiempo, permisos, etc)
     */
    public function validarEntrega(Assignment $assignment): array
    {
        $errores = [];

        if (!$assignment->activa) {
            $errores[] = "Esta tarea no está activa";
        }

        if ($assignment->esta_vencida && !$assignment->permitir_entrega_tardia) {
            $errores[] = "Plazo cerrado. No se aceptan entregas después de " . $assignment->fecha_limite->format('d/m/Y H:i');
        }

        if ($assignment->permitir_entrega_tardia && $assignment->esta_vencida) {
            $limite_tardia = $assignment->fecha_limite->addDays($assignment->dias_extension_tardia);
            if (now()->greaterThan($limite_tardia)) {
                $errores[] = "Plazo extendido finalizado. Límite: " . $limite_tardia->format('d/m/Y H:i');
            }
        }

        return $errores;
    }

    /**
     * Guarda una entrega (o actualiza si ya existe)
     */
    public function guardarEntrega(
        Assignment $assignment,
        Estudiante $estudiante,
        UploadedFile $file
    ): Submission {
        // Validar archivo
        $errores_archivo = $this->validarArchivo($file, $assignment);
        if (!empty($errores_archivo)) {
            throw new \Exception(implode('; ', $errores_archivo));
        }

        // Validar entrega
        $errores_entrega = $this->validarEntrega($assignment);
        if (!empty($errores_entrega)) {
            throw new \Exception(implode('; ', $errores_entrega));
        }

        // Obtener inscripción
        $inscripcion = Inscripcion::where('estudiante_id', $estudiante->id)
            ->where('horario_id', $assignment->horario_id)
            ->firstOrFail();

        // Calcular si es tardía
        $es_tardia = now()->greaterThan($assignment->fecha_limite);
        $dias_retraso = 0;

        if ($es_tardia) {
            $dias_retraso = ceil($assignment->fecha_limite->diffInMinutes(now()) / (24 * 60));
        }

        // Ruta de almacenamiento
        $dir = "submissions/{$assignment->id}";
        $nombre_archivo = $file->getClientOriginalName();
        $archivo_path = $file->storeAs($dir, $nombre_archivo, 'public');

        // Buscar entrega existente (reentrega)
        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('estudiante_id', $estudiante->id)
            ->first();

        if ($submission) {
            // Eliminar archivo anterior
            if (Storage::disk('public')->exists($submission->archivo_path)) {
                Storage::disk('public')->delete($submission->archivo_path);
            }

            // Actualizar
            $submission->update([
                'archivo_path' => $archivo_path,
                'nombre_archivo' => $nombre_archivo,
                'tamano_bytes' => $file->getSize(),
                'entregado_at' => now(),
                'es_tardia' => $es_tardia,
                'dias_retraso' => $dias_retraso,
                'estado' => 'entregado',
                'calificacion' => null,
                'comentario_profesor' => null,
            ]);
        } else {
            // Crear nueva
            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'estudiante_id' => $estudiante->id,
                'inscripcion_id' => $inscripcion->id,
                'archivo_path' => $archivo_path,
                'nombre_archivo' => $nombre_archivo,
                'tamano_bytes' => $file->getSize(),
                'entregado_at' => now(),
                'es_tardia' => $es_tardia,
                'dias_retraso' => $dias_retraso,
                'estado' => 'entregado',
            ]);
        }

        return $submission;
    }

    /**
     * Calcula tiempo restante para entregar
     */
    public function getTiempoRestante(Assignment $assignment): int
    {
        if (!$assignment->permitir_entrega_tardia) {
            return max(0, $assignment->fecha_limite->diffInSeconds(now(), false));
        }

        $limite = $assignment->fecha_limite->addDays($assignment->dias_extension_tardia);
        return max(0, $limite->diffInSeconds(now(), false));
    }
}
