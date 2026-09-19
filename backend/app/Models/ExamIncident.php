<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamIncident extends Model
{
    protected $fillable = [
        'attempt_id',
        'tipo',
        'descripcion',
        'metadata',
        'ocurrido_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'ocurrido_at' => 'datetime',
    ];

    /**
     * Relación: Incidencia pertenece a un Intento
     */
    public function intento(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Scope: Incidencias de cambio de pestaña
     */
    public function scopeCambioDePestana($query)
    {
        return $query->where('tipo', 'cambio_pestana');
    }

    /**
     * Scope: Incidencias de pérdida de foco
     */
    public function scopePerdidaDeFoco($query)
    {
        return $query->where('tipo', 'perdida_foco');
    }

    /**
     * Scope: Incidencias por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope: Incidencias recientes
     */
    public function scopeRecientes($query, $minutos = 30)
    {
        return $query->where('ocurrido_at', '>=', now()->subMinutes($minutos));
    }

    /**
     * Scope: Ordenadas por fecha descendente
     */
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ocurrido_at', 'desc');
    }

    /**
     * Obtener descripción legible del tipo de incidencia
     */
    public function obtenerDescripcionTipo(): string
    {
        $descripciones = [
            'cambio_pestana' => 'Cambio de pestaña',
            'perdida_foco' => 'Pérdida de foco',
            'pantalla_completa' => 'Salida de pantalla completa',
            'inactividad' => 'Inactividad prolongada',
            'fraude_timer' => 'Intento de manipular el timer',
            'advertencia_enviada' => 'Advertencia enviada',
            'auto_submit' => 'Envío automático',
        ];

        return $descripciones[$this->tipo] ?? 'Incidencia desconocida';
    }
}
