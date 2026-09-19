<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'respuesta',
        'es_correcta',
        'puntos_obtenidos',
        'archivo_url',
    ];

    protected $casts = [
        'respuesta' => 'array',
        'es_correcta' => 'boolean',
    ];

    /**
     * Relación: Respuesta pertenece a un Intento
     */
    public function intento(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Relación: Respuesta pertenece a una Pregunta
     */
    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    /**
     * Scope: Respuestas correctas
     */
    public function scopeCorrectas($query)
    {
        return $query->where('es_correcta', true);
    }

    /**
     * Scope: Respuestas incorrectas
     */
    public function scopeIncorrectas($query)
    {
        return $query->where('es_correcta', false);
    }

    /**
     * Scope: Respuestas sin calificar
     */
    public function scopeSinCalificar($query)
    {
        return $query->whereNull('es_correcta');
    }

    /**
     * Scope: Por intento
     */
    public function scopePorIntento($query, $intentoId)
    {
        return $query->where('attempt_id', $intentoId);
    }

    /**
     * Marcar como correcta
     */
    public function marcarCorrecta($puntosObtenidos = null): void
    {
        $this->update([
            'es_correcta' => true,
            'puntos_obtenidos' => $puntosObtenidos ?? $this->pregunta->puntos,
        ]);
    }

    /**
     * Marcar como incorrecta
     */
    public function marcarIncorrecta($puntosObtenidos = 0): void
    {
        $this->update([
            'es_correcta' => false,
            'puntos_obtenidos' => $puntosObtenidos,
        ]);
    }
}
