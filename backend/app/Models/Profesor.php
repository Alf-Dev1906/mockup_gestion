<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profesor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'profesores';

    protected $fillable = [
        'facultad_id',
        'cedula',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'foto_url',
        'codigo_empleado',
        'fecha_contratacion',
        'tipo_contrato',
        'categoria',
        'especialidad',
        'titulo_academico',
        'experiencia',
        'horas_semanales',
        'estatus',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_contratacion' => 'date',
        'horas_semanales' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['nombre_completo'];

    // Relaciones
    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function materias()
    {
        return $this->hasManyThrough(Materia::class, Horario::class);
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return "{$this->titulo_academico} {$this->nombre} {$this->apellido}";
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estatus', 'activo');
    }

    public function scopePorFacultad($query, $facultadId)
    {
        return $query->where('facultad_id', $facultadId);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorEspecialidad($query, $especialidad)
    {
        return $query->where('especialidad', 'like', "%{$especialidad}%");
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('apellido', 'like', "%{$termino}%")
              ->orWhere('cedula', 'like', "%{$termino}%")
              ->orWhere('codigo_empleado', 'like', "%{$termino}%")
              ->orWhere('email', 'like', "%{$termino}%");
        });
    }

    // Métodos de negocio
    public function getCargaHoraria($periodoAcademico)
    {
        return $this->horarios()
                    ->where('periodo_academico', $periodoAcademico)
                    ->count();
    }
}
