<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensaje',
        'url',
        'datos',
        'leida',
        'leida_at',
    ];

    protected $casts = [
        'datos' => 'json',
        'leida' => 'boolean',
        'leida_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopeLeidas($query)
    {
        return $query->where('leida', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    // Accesores
    public function getFormattedTimestampAttribute(): string
    {
        return $this->created_at->format('Y-m-d H:i');
    }

    public function getHaceAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
