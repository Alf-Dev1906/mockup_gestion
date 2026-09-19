<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id',
        'estudiante_id',
        'inscripcion_id',
        'archivo_path',
        'nombre_archivo',
        'tamano_bytes',
        'entregado_at',
        'es_tardia',
        'dias_retraso',
        'estado',
        'calificacion',
        'comentario_profesor',
    ];

    protected $casts = [
        'entregado_at' => 'datetime',
        'es_tardia' => 'boolean',
        'calificacion' => 'decimal:2',
    ];

    // Relaciones
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class);
    }

    // Proteger contra modificaciones no autorizadas
    protected static function booted()
    {
        // Solo permitir actualizar estado, calificacion y comentario_profesor
        static::updating(function ($model) {
            $fillable_on_update = ['estado', 'calificacion', 'comentario_profesor'];
            $changed = array_keys($model->getDirty());
            
            foreach ($changed as $field) {
                if (!in_array($field, $fillable_on_update) && !in_array($field, $model->getHidden())) {
                    // Permitir timestamps
                    if (!in_array($field, ['updated_at'])) {
                        throw new \Exception("No se puede modificar el campo: {$field}");
                    }
                }
            }
        });

        // No permitir eliminación
        static::deleting(function ($model) {
            throw new \Exception("No se pueden eliminar entregas. Solo marcar como rechazado.");
        });
    }

    public function justificar(string $comentario = null): bool
    {
        // Método para justificar una entrega tardía
        $this->estado = 'calificado';
        if ($comentario) {
            $this->comentario_profesor = $comentario;
        }
        return $this->save();
    }
}
