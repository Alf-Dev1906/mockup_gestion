<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'estudiante_id',
        'inscripcion_id',
        'estado',
        'inicio_at',
        'fin_at',
        'tiempo_restante_seg',
        'nota_obtenida',
        'nota_maxima',
        'ip_address',
        'user_agent',
        'advertencias_count',
        'auto_enviado',
    ];

    protected $casts = [
        'inicio_at' => 'datetime',
        'fin_at' => 'datetime',
        'auto_enviado' => 'boolean',
    ];

    /**
     * Relación: Intento pertenece a un Quiz
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Relación: Intento pertenece a un Estudiante
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    /**
     * Relación: Intento pertenece a una Inscripción
     */
    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class);
    }

    /**
     * Relación: Intento tiene muchas respuestas
     */
    public function respuestas(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    /**
     * Relación: Intento tiene muchas incidencias
     */
    public function incidencias(): HasMany
    {
        return $this->hasMany(ExamIncident::class, 'attempt_id');
    }

    /**
     * Scope: Intentos en progreso
     */
    public function scopeEnProgreso($query)
    {
        return $query->where('estado', 'en_progreso');
    }

    /**
     * Scope: Intentos enviados
     */
    public function scopeEnviados($query)
    {
        return $query->where('estado', 'enviado');
    }

    /**
     * Scope: Intentos calificados
     */
    public function scopeCalificados($query)
    {
        return $query->where('estado', 'calificado');
    }

    /**
     * Scope: Por estudiante
     */
    public function scopePorEstudiante($query, $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    /**
     * Scope: Por quiz
     */
    public function scopePorQuiz($query, $quizId)
    {
        return $query->where('quiz_id', $quizId);
    }

    /**
     * Calcular el porcentaje de respuesta correcta
     */
    public function getPorcentajeCorrecciones(): float
    {
        if ($this->nota_maxima == 0) {
            return 0;
        }

        return ($this->nota_obtenida ?? 0) / $this->nota_maxima * 100;
    }

    /**
     * Verificar si el intento está vencido
     */
    public function estaVencido(): bool
    {
        if ($this->fin_at) {
            return true;
        }

        $duracion = $this->quiz->duracion_minutos * 60;
        $tiempoTranscurrido = now()->diffInSeconds($this->inicio_at);

        return $tiempoTranscurrido > $duracion;
    }

    /**
     * Obtener el tiempo restante en segundos
     */
    public function obtenerTiempoRestante(): int
    {
        if ($this->fin_at || $this->estado !== 'en_progreso') {
            return 0;
        }

        $duracion = $this->quiz->duracion_minutos * 60;
        $tiempoTranscurrido = now()->diffInSeconds($this->inicio_at);
        $tiempoRestante = max(0, $duracion - $tiempoTranscurrido);

        return $tiempoRestante;
    }

    /**
     * Registrar advertencia
     */
    public function registrarAdvertencia($tipo, $descripcion = null): void
    {
        $this->increment('advertencias_count');

        ExamIncident::create([
            'attempt_id' => $this->id,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'ocurrido_at' => now(),
        ]);

        // Si se alcanza el máximo de advertencias, auto-enviar
        if ($this->advertencias_count >= $this->quiz->advertencias_max) {
            $this->auto_enviar();
        }
    }

    /**
     * Auto-enviar el intento
     */
    public function auto_enviar(): void
    {
        $this->update([
            'estado' => 'enviado',
            'fin_at' => now(),
            'auto_enviado' => true,
        ]);

        ExamIncident::create([
            'attempt_id' => $this->id,
            'tipo' => 'auto_submit',
            'descripcion' => 'Examen auto-enviado por exceso de advertencias',
            'ocurrido_at' => now(),
        ]);
    }
}
