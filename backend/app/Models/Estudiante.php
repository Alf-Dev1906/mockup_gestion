<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estudiante extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'carrera_id',
        'cedula',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'ciudad',
        'estado',
        'codigo_postal',
        'foto_url',
        'matricula',
        'fecha_ingreso',
        'semestre_actual',
        'indice_academico',
        'creditos_aprobados',
        'estatus',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'contacto_emergencia_relacion',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'semestre_actual' => 'integer',
        'indice_academico' => 'decimal:2',
        'creditos_aprobados' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['nombre_completo'];

    // Relaciones
    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function horarios()
    {
        return $this->belongsToMany(Horario::class, 'inscripciones')
                    ->withPivot([
                        'periodo_academico',
                        'fecha_inscripcion',
                        'estatus',
                        'nota_final',
                        'inasistencias'
                    ])
                    ->withTimestamps();
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento?->age;
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estatus', 'activo');
    }

    public function scopePorCarrera($query, $carreraId)
    {
        return $query->where('carrera_id', $carreraId);
    }

    public function scopePorSemestre($query, $semestre)
    {
        return $query->where('semestre_actual', $semestre);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('apellido', 'like', "%{$termino}%")
              ->orWhere('cedula', 'like', "%{$termino}%")
              ->orWhere('matricula', 'like', "%{$termino}%")
              ->orWhere('email', 'like', "%{$termino}%");
        });
    }

    // Métodos de negocio
    public function inscribirEnHorario(Horario $horario, $periodoAcademico)
    {
        return $this->inscripciones()->create([
            'horario_id' => $horario->id,
            'periodo_academico' => $periodoAcademico,
            'fecha_inscripcion' => now(),
            'estatus' => 'inscrito',
        ]);
    }
}
