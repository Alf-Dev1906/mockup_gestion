<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'horario_id',
        'titulo',
        'descripcion',
        'tipo',
        'instrucciones',
        'duracion_minutos',
        'intentos_permitidos',
        'advertencias_max',
        'fecha_inicio',
        'fecha_fin',
        'visible_desde',
        'estado',
        'orden_aleatorio',
        'mostrar_resultado_inmediato',
        'created_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'visible_desde' => 'datetime',
        'orden_aleatorio' => 'boolean',
        'mostrar_resultado_inmediato' => 'boolean',
    ];

    /**
     * Relación: Quiz pertenece a un Horario
     */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    /**
     * Relación: Quiz tiene muchas preguntas
     */
    public function preguntas(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id');
    }

    /**
     * Relación: Quiz tiene muchos intentos
     */
    public function intentos(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }

    /**
     * Relación: Quiz fue creado por un usuario
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Quizzes publicados
     */
    public function scopePublicados($query)
    {
        return $query->where('estado', 'publicado');
    }

    /**
     * Scope: Quizzes en borrador
     */
    public function scopeBorrador($query)
    {
        return $query->where('estado', 'borrador');
    }

    /**
     * Scope: Quizzes disponibles (publicados y no cerrados)
     */
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'publicado')
                    ->where(function ($q) {
                        $q->whereNull('fecha_fin')
                          ->orWhere('fecha_fin', '>', now());
                    });
    }

    /**
     * Scope: Quizzes por horario
     */
    public function scopePorHorario($query, $horarioId)
    {
        return $query->where('horario_id', $horarioId);
    }

    /**
     * Verificar si el quiz tiene disponibilidad de intentos
     */
    public function tieneIntentosDisponibles($estudianteId): bool
    {
        $intentosTomados = $this->intentos()
            ->where('estudiante_id', $estudianteId)
            ->whereIn('estado', ['en_progreso', 'enviado', 'calificado'])
            ->count();
        
        return $intentosTomados < $this->intentos_permitidos;
    }

    /**
     * Obtener el intento actual del estudiante
     */
    public function intentoActual($estudianteId)
    {
        return $this->intentos()
            ->where('estudiante_id', $estudianteId)
            ->where('estado', 'en_progreso')
            ->first();
    }
}
