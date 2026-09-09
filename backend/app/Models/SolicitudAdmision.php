<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudAdmision extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes_admision';

    protected $fillable = [
        'estudiante_id',
        'numero_referencia',
        
        // Paso 1: Personal
        'fecha_nacimiento',
        'genero',
        'telefono',
        'telefono_alternativo',
        'direccion',
        'ciudad',
        'estado',
        'codigo_postal',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'contacto_emergencia_relacion',
        
        // Paso 2: Carrera
        'carrera_id',
        'carrera_alternativa_id',
        'modalidad',
        'turno_preferido',
        'motivacion',
        
        // Paso 3: Académico
        'nivel_educativo',
        'institucion_egreso',
        'año_graduacion',
        'promedio_notas',
        'tipo_bachillerato',
        'mencion',
        'desea_convalidar',
        'universidad_procedencia',
        'carrera_previa',
        'semestres_completados',
        'materias_aprobar_convalidacion',
        
        // Paso 4: Documentos
        'doc_cedula',
        'doc_partida_nacimiento',
        'doc_titulo_bachiller',
        'doc_notas_certificadas',
        'doc_foto_carnet',
        'doc_comprobante_domicilio',
        'doc_certificado_medico',
        'doc_carta_conducta',
        'documentos_adicionales',
        
        // Paso 5: Confirmación
        'acepta_terminos',
        'declara_veracidad',
        'fecha_envio',
        
        // Control de progreso
        'paso1_completado',
        'paso2_completado',
        'paso3_completado',
        'paso4_completado',
        'paso5_completado',
        'paso_actual',
        
        // Estado y revisión
        'estado',
        'razon_rechazo',
        'comentarios_admin',
        'correcciones_solicitadas',
        'revisado_por',
        'fecha_revision',
        
        // Metadata
        'ip_solicitud',
        'user_agent',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_envio' => 'datetime',
        'fecha_revision' => 'datetime',
        'desea_convalidar' => 'boolean',
        'acepta_terminos' => 'boolean',
        'declara_veracidad' => 'boolean',
        'paso1_completado' => 'boolean',
        'paso2_completado' => 'boolean',
        'paso3_completado' => 'boolean',
        'paso4_completado' => 'boolean',
        'paso5_completado' => 'boolean',
        'promedio_notas' => 'decimal:2',
        'documentos_adicionales' => 'array',
        'correcciones_solicitadas' => 'array',
        'materias_aprobar_convalidacion' => 'array',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function carreraAlternativa()
    {
        return $this->belongsTo(Carrera::class, 'carrera_alternativa_id');
    }

    public function revisadoPor()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnRevision($query)
    {
        return $query->where('estado', 'en_revision');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    public function scopeRechazadas($query)
    {
        return $query->where('estado', 'rechazada');
    }

    public function scopeBorradores($query)
    {
        return $query->where('estado', 'borrador');
    }

    // ─── Métodos de utilidad ─────────────────────────────────────────────────

    /**
     * Verifica si un paso específico está completado
     */
    public function pasoCompletado(int $paso): bool
    {
        return $this->{"paso{$paso}_completado"} ?? false;
    }

    /**
     * Marca un paso como completado y desbloquea el siguiente
     */
    public function completarPaso(int $paso): void
    {
        $this->{"paso{$paso}_completado"} = true;
        
        // Desbloquear el siguiente paso si no estaba desbloqueado
        if ($this->paso_actual < $paso + 1) {
            $this->paso_actual = min(5, $paso + 1);
        }
        
        $this->save();
    }

    /**
     * Verifica si se puede acceder a un paso específico
     */
    public function puedeAccederPaso(int $paso): bool
    {
        return $paso <= $this->paso_actual;
    }

    /**
     * Calcula el porcentaje de completitud del wizard
     */
    public function porcentajeCompletitud(): int
    {
        $pasosCompletados = 0;
        for ($i = 1; $i <= 5; $i++) {
            if ($this->pasoCompletado($i)) {
                $pasosCompletados++;
            }
        }
        return ($pasosCompletados / 5) * 100;
    }

    /**
     * Verifica si todos los pasos están completados
     */
    public function todosLo sPasosCompletados(): bool
    {
        return $this->paso1_completado 
            && $this->paso2_completado 
            && $this->paso3_completado 
            && $this->paso4_completado 
            && $this->paso5_completado;
    }

    /**
     * Genera un número de referencia único
     */
    public static function generarNumeroReferencia(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return sprintf('SOL-%s-%05d', $year, $count);
    }

    /**
     * Marca la solicitud como enviada (completa el wizard)
     */
    public function enviar(): void
    {
        $this->estado = 'pendiente';
        $this->fecha_envio = now();
        $this->save();
    }

    /**
     * Aprobar solicitud
     */
    public function aprobar(User $admin, ?string $comentarios = null): void
    {
        $this->estado = 'aprobada';
        $this->revisado_por = $admin->id;
        $this->fecha_revision = now();
        $this->comentarios_admin = $comentarios;
        $this->save();
        
        // Actualizar el estudiante a estatus "activo"
        $this->estudiante->update(['estatus' => 'activo']);
    }

    /**
     * Rechazar solicitud
     */
    public function rechazar(User $admin, string $razon, ?string $comentarios = null): void
    {
        $this->estado = 'rechazada';
        $this->razon_rechazo = $razon;
        $this->revisado_por = $admin->id;
        $this->fecha_revision = now();
        $this->comentarios_admin = $comentarios;
        $this->save();
    }

    /**
     * Solicitar correcciones
     */
    public function solicitarCorrecciones(User $admin, array $correcciones, ?string $comentarios = null): void
    {
        $this->estado = 'requiere_correccion';
        $this->correcciones_solicitadas = $correcciones;
        $this->revisado_por = $admin->id;
        $this->fecha_revision = now();
        $this->comentarios_admin = $comentarios;
        $this->save();
    }
}
