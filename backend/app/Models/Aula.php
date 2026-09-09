<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aula extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facultad_id',
        'codigo',
        'nombre',
        'edificio',
        'piso',
        'capacidad',
        'tipo',
        'equipamiento',
        'tiene_proyector',
        'tiene_aire_acondicionado',
        'tiene_computadoras',
        'numero_computadoras',
        'accesible_discapacitados',
        'estatus',
    ];

    protected $casts = [
        'capacidad' => 'integer',
        'numero_computadoras' => 'integer',
        'tiene_proyector' => 'boolean',
        'tiene_aire_acondicionado' => 'boolean',
        'tiene_computadoras' => 'boolean',
        'accesible_discapacitados' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    // Scopes
    public function scopeDisponible($query)
    {
        return $query->where('estatus', 'disponible');
    }

    public function scopePorFacultad($query, $facultadId)
    {
        return $query->where('facultad_id', $facultadId);
    }

    public function scopePorEdificio($query, $edificio)
    {
        return $query->where('edificio', $edificio);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeConCapacidadMinima($query, $capacidad)
    {
        return $query->where('capacidad', '>=', $capacidad);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('codigo', 'like', "%{$termino}%")
              ->orWhere('edificio', 'like', "%{$termino}%");
        });
    }

    // Métodos de negocio
    public function estaDisponible($dia, $horaInicio, $horaFin, $periodoAcademico)
    {
        return !$this->horarios()
            ->where('dia_semana', $dia)
            ->where('periodo_academico', $periodoAcademico)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                      ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
                      ->orWhere(function ($q) use ($horaInicio, $horaFin) {
                          $q->where('hora_inicio', '<=', $horaInicio)
                            ->where('hora_fin', '>=', $horaFin);
                      });
            })
            ->exists();
    }

    public function getOcupacionAttribute()
    {
        // Calcular porcentaje de ocupación basado en horarios asignados
        $horasSemanales = $this->horarios()
            ->where('estatus', 'en_curso')
            ->count() * 2; // Asumiendo 2 horas por sesión

        return min(100, ($horasSemanales / 40) * 100); // 40 horas semanales máximo
    }
}
