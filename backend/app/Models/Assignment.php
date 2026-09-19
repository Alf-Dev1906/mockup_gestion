<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    protected $fillable = [
        'horario_id',
        'titulo',
        'descripcion',
        'instrucciones',
        'archivo_guia_url',
        'fecha_apertura',
        'fecha_limite',
        'permitir_entrega_tardia',
        'peso_calificacion',
        'nota_maxima',
        'tipos_permitidos',
        'tamano_maximo_mb',
        'estatus',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_limite' => 'datetime',
        'permitir_entrega_tardia' => 'boolean',
        'peso_calificacion' => 'decimal:2',
        'nota_maxima' => 'decimal:2',
    ];

    // Relaciones
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    // Accesores
    public function getTiempoRestanteAttribute(): int
    {
        $ahora = now();
        $restante = $this->fecha_limite->diffInSeconds($ahora, false);
        return max(0, $restante);
    }

    public function getEstaVencidaAttribute(): bool
    {
        return now()->greaterThan($this->fecha_limite);
    }

    public function getActivaAttribute(): bool
    {
        return $this->estatus === 'publicado';
    }

    public function getPuedoEntregerAttribute(): bool
    {
        if ($this->esta_vencida && !$this->permitir_entrega_tardia) {
            return false;
        }
        
        return $this->estatus === 'publicado';
    }

    public function getExtensionesPermitidas(): array
    {
        return array_map('trim', explode(',', $this->tipos_permitidos ?? ''));
    }
}
