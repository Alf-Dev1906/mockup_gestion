<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrera extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facultad_id',
        'codigo',
        'nombre',
        'nivel',
        'carrera_base_id',
        'titulo_otorgado',
        'duracion_semestres',
        'creditos_totales',
        'coordinador',
        'descripcion',
        'modalidad',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'duracion_semestres' => 'integer',
        'creditos_totales' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function carreraBase()
    {
        return $this->belongsTo(Carrera::class, 'carrera_base_id');
    }

    public function tsusAsociados()
    {
        return $this->hasMany(Carrera::class, 'carrera_base_id');
    }

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class);
    }

    public function materias()
    {
        return $this->hasMany(Materia::class);
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorFacultad($query, $facultadId)
    {
        return $query->where('facultad_id', $facultadId);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('codigo', 'like', "%{$termino}%")
              ->orWhere('titulo_otorgado', 'like', "%{$termino}%");
        });
    }

    // Accessor
    public function getEstudiantesActivosCountAttribute()
    {
        return $this->estudiantes()->where('estatus', 'activo')->count();
    }
}
