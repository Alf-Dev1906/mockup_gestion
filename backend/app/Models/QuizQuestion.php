<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'orden',
        'tipo',
        'enunciado',
        'contenido',
        'puntos',
        'obligatoria',
        'retroalimentacion',
        'archivo_url',
    ];

    protected $casts = [
        'contenido' => 'array',
        'obligatoria' => 'boolean',
    ];

    /**
     * Relación: Pregunta pertenece a un Quiz
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Relación: Pregunta tiene muchas respuestas
     */
    public function respuestas(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'question_id');
    }

    /**
     * Scope: Preguntas obligatorias
     */
    public function scopeObligatorias($query)
    {
        return $query->where('obligatoria', true);
    }

    /**
     * Scope: Preguntas de tipo específico
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope: Ordenadas por orden
     */
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden');
    }

    /**
     * Obtener la puntuación máxima de la pregunta
     */
    public function getPuntuacionMaxima(): float
    {
        return (float) $this->puntos;
    }

    /**
     * Obtener contenido específico según el tipo de pregunta
     */
    public function getOpcionesCorrectas(): array
    {
        if (!$this->contenido) {
            return [];
        }

        return $this->contenido['opciones_correctas'] ?? [];
    }
}
