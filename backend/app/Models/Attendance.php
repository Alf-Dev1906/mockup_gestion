<?php

namespace App\Models;

use App\Exceptions\ImmutableRecordException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'session_id',
        'estudiante_id',
        'inscripcion_id',
        'estatus',
        'observacion',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * REGLA DE INMUTABILIDAD
     * 
     * Los registros de asistencia NO pueden ser modificados o eliminados
     * Una vez creados, son permanentes. Solo se permite la justificación.
     */
    protected static function booted()
    {
        // Prevenir actualizaciones no autorizadas
        static::updating(function ($model) {
            // Solo permitir cambios en 'estatus' y 'observacion' vía método justificar()
            $original = $model->getOriginal();
            $cambios = $model->getChanges();
            
            // Si cambió algo distinto a 'estatus' u 'observacion', rechazar
            foreach ($cambios as $field => $value) {
                if (!in_array($field, ['estatus', 'observacion', 'updated_at'])) {
                    throw new ImmutableRecordException(
                        "El campo '{$field}' no puede ser modificado en un registro de asistencia"
                    );
                }
            }
            
            // Validar que estatus solo cambie a 'justificado'
            if (isset($cambios['estatus']) && $cambios['estatus'] !== 'justificado') {
                throw new ImmutableRecordException(
                    "El estatus solo puede cambiar a 'justificado', no a '{$cambios['estatus']}'"
                );
            }
        });

        // Prevenir eliminaciones
        static::deleting(function ($model) {
            throw new ImmutableRecordException(
                'Los registros de asistencia no pueden ser eliminados'
            );
        });
    }

    /**
     * Relación: Asistencia pertenece a una Sesión
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    /**
     * Relación: Asistencia pertenece a un Estudiante
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    /**
     * Relación: Asistencia pertenece a una Inscripción
     */
    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class);
    }

    /**
     * Justificar una asistencia (ÚNICA modificación permitida)
     * 
     * @param string $observacion Razón de la justificación
     * @return bool
     */
    public function justificar(string $observacion = null): bool
    {
        // Desactivar temporalmente el modelo event para permitir esta actualización
        $this->disableModelEvents = true;
        
        try {
            $this->estatus = 'justificado';
            if ($observacion) {
                $this->observacion = $observacion;
            }
            $this->save();
            return true;
        } finally {
            $this->disableModelEvents = false;
        }
    }

    /**
     * Scope: Asistencias de una sesión específica
     */
    public function scopeDeLaSesion($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope: Asistencias presentes
     */
    public function scopePresentes($query)
    {
        return $query->whereIn('estatus', ['presente', 'justificado']);
    }

    /**
     * Scope: Asistencias ausentes
     */
    public function scopeAusentes($query)
    {
        return $query->where('estatus', 'ausente');
    }

    /**
     * Scope: Asistencias justificadas
     */
    public function scopeJustificadas($query)
    {
        return $query->where('estatus', 'justificado');
    }

    /**
     * Scope: De un estudiante
     */
    public function scopeDelEstudiante($query, $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    /**
     * Obtener etiqueta de estatus en español
     */
    public function obtenerEstatusLegible(): string
    {
        $labels = [
            'presente' => 'Presente',
            'ausente' => 'Ausente',
            'justificado' => 'Justificado',
        ];
        return $labels[$this->estatus] ?? $this->estatus;
    }
}
