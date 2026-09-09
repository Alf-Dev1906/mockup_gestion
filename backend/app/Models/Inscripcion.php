<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inscripcion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inscripciones';

    protected $fillable = [
        'estudiante_id',
        'horario_id',
        'periodo_academico',
        'fecha_inscripcion',
        'estatus',
        'nota_parcial_1',
        'nota_parcial_2',
        'nota_parcial_3',
        'nota_final',
        'inasistencias',
        'porcentaje_asistencia',
        'fecha_retiro',
        'fecha_calificacion_final',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'date',
        'fecha_retiro' => 'date',
        'fecha_calificacion_final' => 'date',
        'nota_parcial_1' => 'decimal:2',
        'nota_parcial_2' => 'decimal:2',
        'nota_parcial_3' => 'decimal:2',
        'nota_final' => 'decimal:2',
        'inasistencias' => 'integer',
        'porcentaje_asistencia' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    // Scopes
    public function scopeInscrito($query)
    {
        return $query->where('estatus', 'inscrito');
    }

    public function scopeCursando($query)
    {
        return $query->where('estatus', 'cursando');
    }

    public function scopeAprobado($query)
    {
        return $query->where('estatus', 'aprobado');
    }

    public function scopeReprobado($query)
    {
        return $query->where('estatus', 'reprobado');
    }

    public function scopePorEstudiante($query, $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    public function scopePorHorario($query, $horarioId)
    {
        return $query->where('horario_id', $horarioId);
    }

    public function scopePorPeriodo($query, $periodoAcademico)
    {
        return $query->where('periodo_academico', $periodoAcademico);
    }

    // Accessors
    public function getPromedioParcialesAttribute()
    {
        $notas = array_filter([
            $this->nota_parcial_1,
            $this->nota_parcial_2,
            $this->nota_parcial_3,
        ]);

        if (empty($notas)) {
            return null;
        }

        return round(array_sum($notas) / count($notas), 2);
    }

    public function getEstaAprobadoAttribute()
    {
        return $this->nota_final >= 10; // Asumiendo escala 0-20, nota mínima 10
    }

    // Métodos de negocio
    public function calcularNotaFinal()
    {
        // Asumiendo: 25% cada parcial + 25% actividades
        $notaFinal = 0;

        if ($this->nota_parcial_1 !== null) {
            $notaFinal += $this->nota_parcial_1 * 0.25;
        }
        if ($this->nota_parcial_2 !== null) {
            $notaFinal += $this->nota_parcial_2 * 0.25;
        }
        if ($this->nota_parcial_3 !== null) {
            $notaFinal += $this->nota_parcial_3 * 0.25;
        }

        // Los últimos 25% se pueden agregar como actividades/examen final

        return round($notaFinal, 2);
    }

    public function retirar()
    {
        $this->estatus = 'retirado';
        $this->fecha_retiro = now();
        $this->save();

        // Decrementar cupo del horario
        $this->horario->decrementarCupo();
    }

    public function registrarInasistencia()
    {
        $this->increment('inasistencias');
        $this->calcularPorcentajeAsistencia();
    }

    protected function calcularPorcentajeAsistencia()
    {
        // Asumiendo 48 clases por semestre (16 semanas * 3 clases/semana)
        $totalClases = 48;
        $clasesAsistidas = $totalClases - $this->inasistencias;
        $this->porcentaje_asistencia = round(($clasesAsistidas / $totalClases) * 100, 2);
        $this->save();
    }

    public function calificacion()
    {
        return $this->hasOne(Calificacion::class);
    }

    // Event hooks
    protected static function booted()
    {
        static::created(function ($inscripcion) {
            // Incrementar cupo del horario al crear inscripción
            $inscripcion->horario->incrementarCupo();
        });

        static::deleting(function ($inscripcion) {
            // Decrementar cupo del horario al eliminar inscripción
            if ($inscripcion->estatus !== 'retirado') {
                $inscripcion->horario->decrementarCupo();
            }
        });
    }
}
