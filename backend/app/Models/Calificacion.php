<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calificacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calificaciones';

    protected $fillable = [
        'inscripcion_id',
        'nota_corte_1',
        'nota_corte_2',
        'nota_corte_3',
        'nota_final',
        'estatus',
        'observaciones',
        'asistencias',
    ];

    protected $casts = [
        'nota_corte_1' => 'decimal:2',
        'nota_corte_2' => 'decimal:2',
        'nota_corte_3' => 'decimal:2',
        'nota_final' => 'decimal:2',
        'asistencias' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    // Accesorios
    public function getEsAprobadoAttribute(): bool
    {
        return $this->nota_final >= 10.00;
    }

    public function getLiteralAttribute(): string
    {
        if (!$this->nota_final) return 'S/N';
        
        if ($this->nota_final >= 18) return 'A (Excelente)';
        if ($this->nota_final >= 16) return 'B (Muy Bueno)';
        if ($this->nota_final >= 13) return 'C (Bueno)';
        if ($this->nota_final >= 10) return 'D (Aprobado)';
        return 'E (Reprobado)';
    }

    // Métodos de cálculo
    public function calcularNotaFinal(): void
    {
        $notas = array_filter([
            $this->nota_corte_1,
            $this->nota_corte_2,
            $this->nota_corte_3
        ]);

        if (count($notas) > 0) {
            $this->nota_final = round(array_sum($notas) / count($notas), 2);
            $this->estatus = $this->nota_final >= 10.00 ? 'aprobado' : 'reprobado';
            $this->save();
        }
    }

    // Scopes
    public function scopeAprobados($query)
    {
        return $query->where('estatus', 'aprobado')->where('nota_final', '>=', 10.00);
    }

    public function scopeReprobados($query)
    {
        return $query->where('estatus', 'reprobado')->where('nota_final', '<', 10.00);
    }

    public function scopeCursando($query)
    {
        return $query->where('estatus', 'cursando');
    }
}
