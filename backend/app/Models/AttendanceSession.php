<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    protected $fillable = [
        'horario_id',
        'profesor_id',
        'session_date',
        'codigo_dinamico',
        'codigo_expira_at',
        'abierta',
        'cierre_automatico_at',
        'total_presentes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'codigo_expira_at' => 'datetime',
        'cierre_automatico_at' => 'datetime',
        'abierta' => 'boolean',
    ];

    /**
     * Relación: Sesión pertenece a un Horario
     */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    /**
     * Relación: Sesión tiene muchos registros de asistencia
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'session_id');
    }

    /**
     * Scope: Sesiones abiertas
     */
    public function scopeAbiertas($query)
    {
        return $query->where('abierta', true);
    }

    /**
     * Scope: Sesiones cerradas
     */
    public function scopeCerradas($query)
    {
        return $query->where('abierta', false);
    }

    /**
     * Scope: Sesiones de un horario específico
     */
    public function scopeDelHorario($query, $horarioId)
    {
        return $query->where('horario_id', $horarioId);
    }

    /**
     * Verificar si el código aún es válido
     */
    public function esValido(): bool
    {
        return $this->abierta && now() <= $this->codigo_expira_at;
    }

    /**
     * Verificar si la sesión está expirada
     */
    public function estaExpirada(): bool
    {
        return now() > $this->codigo_expira_at;
    }

    /**
     * Obtener tiempo restante en segundos
     */
    public function tiempoRestanteSeg(): int
    {
        if (!$this->esValido()) return 0;
        return max(0, (int) now()->diffInSeconds($this->codigo_expira_at));
    }

    /**
     * Generar código dinámico aleatorio
     */
    public static function generarCodigoDinamico(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular porcentaje de asistencia
     */
    public function porcentajeAsistencia(): float
    {
        $total = $this->attendances()->count();
        if ($total === 0) return 0;
        
        $presentes = $this->attendances()
            ->where('estatus', 'presente')
            ->orWhere('estatus', 'justificado')
            ->count();
        
        return round(($presentes / $total) * 100, 2);
    }
}
