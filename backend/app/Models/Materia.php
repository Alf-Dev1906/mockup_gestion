<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'carrera_id',
        'codigo',
        'nombre',
        'descripcion',
        'creditos',
        'horas_teoricas',
        'horas_practicas',
        'horas_laboratorio',
        'semestre_recomendado',
        'tipo',
        'competencias',
        'bibliografia',
        'activo',
    ];

    protected $casts = [
        'creditos' => 'integer',
        'horas_teoricas' => 'integer',
        'horas_practicas' => 'integer',
        'horas_laboratorio' => 'integer',
        'semestre_recomendado' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    // Relación de prerrequisitos (muchos a muchos consigo misma)
    public function prerrequisitos()
    {
        return $this->belongsToMany(
            Materia::class,
            'materia_prerrequisitos',
            'materia_id',
            'prerrequisito_id'
        )->withTimestamps();
    }

    // Materias que tienen esta como prerrequisito
    public function materiasQueRequieren()
    {
        return $this->belongsToMany(
            Materia::class,
            'materia_prerrequisitos',
            'prerrequisito_id',
            'materia_id'
        )->withTimestamps();
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCarrera($query, $carreraId)
    {
        return $query->where('carrera_id', $carreraId);
    }

    public function scopePorSemestre($query, $semestre)
    {
        return $query->where('semestre_recomendado', $semestre);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('codigo', 'like', "%{$termino}%")
              ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }

    // Accessors
    public function getTotalHorasAttribute()
    {
        return $this->horas_teoricas + $this->horas_practicas + $this->horas_laboratorio;
    }

    // Métodos de negocio
    public function tienePrerrequisito(Materia $materia)
    {
        return $this->prerrequisitos()->where('prerrequisito_id', $materia->id)->exists();
    }

    public function estudiantePuedeInscribir(Estudiante $estudiante)
    {
        // Verificar si el estudiante ha aprobado todos los prerrequisitos
        $prerrequisitosIds = $this->prerrequisitos->pluck('id');
        
        if ($prerrequisitosIds->isEmpty()) {
            return true;
        }

        $aprobadas = $estudiante->inscripciones()
            ->whereIn('horario_id', function ($query) use ($prerrequisitosIds) {
                $query->select('id')
                      ->from('horarios')
                      ->whereIn('materia_id', $prerrequisitosIds);
            })
            ->where('estatus', 'aprobado')
            ->count();

        return $aprobadas >= $prerrequisitosIds->count();
    }
}
