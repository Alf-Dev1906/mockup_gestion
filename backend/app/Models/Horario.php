<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Horario extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'materia_id',
        'profesor_id',
        'aula_id',
        'seccion',
        'periodo_academico',
        'anno',
        'periodo',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'cupo_maximo',
        'cupo_actual',
        'lista_espera',
        'estatus',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'anno' => 'integer',
        'periodo' => 'integer',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'cupo_maximo' => 'integer',
        'cupo_actual' => 'integer',
        'lista_espera' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'inscripciones')
                    ->withPivot([
                        'periodo_academico',
                        'fecha_inscripcion',
                        'estatus',
                        'nota_final',
                        'inasistencias'
                    ])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeAbierto($query)
    {
        return $query->where('estatus', 'abierto');
    }

    public function scopeEnCurso($query)
    {
        return $query->where('estatus', 'en_curso');
    }

    public function scopePorPeriodo($query, $periodoAcademico)
    {
        return $query->where('periodo_academico', $periodoAcademico);
    }

    public function scopePorMateria($query, $materiaId)
    {
        return $query->where('materia_id', $materiaId);
    }

    public function scopePorProfesor($query, $profesorId)
    {
        return $query->where('profesor_id', $profesorId);
    }

    public function scopePorDia($query, $dia)
    {
        return $query->where('dia_semana', $dia);
    }

    public function scopeConCuposDisponibles($query)
    {
        return $query->whereColumn('cupo_actual', '<', 'cupo_maximo');
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->whereHas('materia', function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('codigo', 'like', "%{$termino}%");
        })->orWhereHas('profesor', function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('apellido', 'like', "%{$termino}%");
        });
    }

    // Accessors
    public function getCuposDisponiblesAttribute()
    {
        return $this->cupo_maximo - $this->cupo_actual;
    }

    public function getPorcentajeOcupacionAttribute()
    {
        if ($this->cupo_maximo == 0) {
            return 0;
        }
        return round(($this->cupo_actual / $this->cupo_maximo) * 100, 2);
    }

    public function getEstaLlenoAttribute()
    {
        return $this->cupo_actual >= $this->cupo_maximo;
    }

    // Métodos de negocio
    public function tieneConflicto()
    {
        // Verificar conflicto de aula
        $conflictoAula = self::where('id', '!=', $this->id)
            ->where('aula_id', $this->aula_id)
            ->where('dia_semana', $this->dia_semana)
            ->where('periodo_academico', $this->periodo_academico)
            ->where(function ($query) {
                $query->whereBetween('hora_inicio', [$this->hora_inicio, $this->hora_fin])
                      ->orWhereBetween('hora_fin', [$this->hora_inicio, $this->hora_fin])
                      ->orWhere(function ($q) {
                          $q->where('hora_inicio', '<=', $this->hora_inicio)
                            ->where('hora_fin', '>=', $this->hora_fin);
                      });
            })
            ->exists();

        // Verificar conflicto de profesor
        $conflictoProfesor = self::where('id', '!=', $this->id)
            ->where('profesor_id', $this->profesor_id)
            ->where('dia_semana', $this->dia_semana)
            ->where('periodo_academico', $this->periodo_academico)
            ->where(function ($query) {
                $query->whereBetween('hora_inicio', [$this->hora_inicio, $this->hora_fin])
                      ->orWhereBetween('hora_fin', [$this->hora_inicio, $this->hora_fin])
                      ->orWhere(function ($q) {
                          $q->where('hora_inicio', '<=', $this->hora_inicio)
                            ->where('hora_fin', '>=', $this->hora_fin);
                      });
            })
            ->exists();

        return $conflictoAula || $conflictoProfesor;
    }

    public function incrementarCupo()
    {
        if ($this->cupo_actual < $this->cupo_maximo) {
            $this->increment('cupo_actual');
            return true;
        }
        return false;
    }

    public function decrementarCupo()
    {
        if ($this->cupo_actual > 0) {
            $this->decrement('cupo_actual');
            return true;
        }
        return false;
    }
}
