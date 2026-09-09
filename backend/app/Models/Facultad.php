<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facultad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'facultades';

    protected $fillable = [
        'codigo',
        'nombre',
        'decano',
        'email',
        'telefono',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function carreras()
    {
        return $this->hasMany(Carrera::class);
    }

    public function profesores()
    {
        return $this->hasMany(Profesor::class);
    }

    public function aulas()
    {
        return $this->hasMany(Aula::class);
    }

    // Scopes útiles
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('codigo', 'like', "%{$termino}%");
        });
    }
}
