<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para gestionar solicitudes de admisión
     * Almacena el progreso del wizard de admisión paso por paso
     */
    public function up(): void
    {
        Schema::create('solicitudes_admision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->string('numero_referencia', 50)->unique()->comment('Ej: SOL-2024-00123');
            
            // ────────────────────────────────────────────────────────────────
            // PASO 1: Información Personal Completa
            // ────────────────────────────────────────────────────────────────
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('telefono_alternativo', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('estado_provincia', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            
            // Contacto de emergencia
            $table->string('contacto_emergencia_nombre', 150)->nullable();
            $table->string('contacto_emergencia_telefono', 20)->nullable();
            $table->string('contacto_emergencia_relacion', 50)->nullable();
            
            // ────────────────────────────────────────────────────────────────
            // PASO 2: Selección de Carrera y Preferencias
            // ────────────────────────────────────────────────────────────────
            $table->foreignId('carrera_id')->nullable()->constrained('carreras')->onDelete('set null');
            $table->foreignId('carrera_alternativa_id')->nullable()->constrained('carreras')->onDelete('set null');
            $table->enum('modalidad', ['presencial', 'semipresencial', 'distancia'])->nullable();
            $table->enum('turno_preferido', ['mañana', 'tarde', 'noche'])->nullable();
            $table->text('motivacion')->nullable()->comment('Por qué quiere estudiar esta carrera');
            
            // ────────────────────────────────────────────────────────────────
            // PASO 3: Datos Académicos Previos
            // ────────────────────────────────────────────────────────────────
            $table->enum('nivel_educativo', [
                'bachiller', 
                'tsu', 
                'universitario_incompleto',
                'licenciatura',
                'posgrado'
            ])->nullable();
            $table->string('institucion_egreso', 200)->nullable();
            $table->integer('año_graduacion')->nullable();
            $table->decimal('promedio_notas', 4, 2)->nullable()->comment('Escala 1-20 venezolana');
            $table->enum('tipo_bachillerato', ['ciencias', 'humanidades', 'tecnico'])->nullable();
            $table->string('mencion', 100)->nullable();
            
            // Convalidaciones (si aplica)
            $table->boolean('desea_convalidar')->default(false);
            $table->string('universidad_procedencia', 200)->nullable();
            $table->string('carrera_previa', 200)->nullable();
            $table->integer('semestres_completados')->nullable();
            $table->text('materias_aprobar_convalidacion')->nullable()->comment('JSON con lista de materias');
            
            // ────────────────────────────────────────────────────────────────
            // PASO 4: Documentos (rutas de archivos en storage)
            // ────────────────────────────────────────────────────────────────
            $table->string('doc_cedula', 255)->nullable()->comment('Ambos lados');
            $table->string('doc_partida_nacimiento', 255)->nullable();
            $table->string('doc_titulo_bachiller', 255)->nullable();
            $table->string('doc_notas_certificadas', 255)->nullable();
            $table->string('doc_foto_carnet', 255)->nullable();
            $table->string('doc_comprobante_domicilio', 255)->nullable();
            $table->string('doc_certificado_medico', 255)->nullable();
            $table->string('doc_carta_conducta', 255)->nullable();
            $table->json('documentos_adicionales')->nullable()->comment('Array de documentos extras');
            
            // ────────────────────────────────────────────────────────────────
            // PASO 5: Declaraciones y Confirmación
            // ────────────────────────────────────────────────────────────────
            $table->boolean('acepta_terminos')->default(false);
            $table->boolean('declara_veracidad')->default(false);
            $table->timestamp('fecha_envio')->nullable()->comment('Cuando completó el wizard');
            
            // ────────────────────────────────────────────────────────────────
            // Control de Progreso del Wizard
            // ────────────────────────────────────────────────────────────────
            $table->boolean('paso1_completado')->default(false);
            $table->boolean('paso2_completado')->default(false);
            $table->boolean('paso3_completado')->default(false);
            $table->boolean('paso4_completado')->default(false);
            $table->boolean('paso5_completado')->default(false);
            $table->integer('paso_actual')->default(1)->comment('Último paso accesible');
            
            // ────────────────────────────────────────────────────────────────
            // Estado de la Solicitud y Revisión
            // ────────────────────────────────────────────────────────────────
            $table->enum('estado', [
                'borrador',           // Creada pero no enviada
                'pendiente',          // Enviada, esperando revisión
                'en_revision',        // Siendo revisada por admin
                'aprobada',           // Admitido
                'rechazada',          // No admitido
                'requiere_correccion' // Necesita correcciones
            ])->default('borrador');
            
            $table->text('razon_rechazo')->nullable();
            $table->text('comentarios_admin')->nullable();
            $table->json('correcciones_solicitadas')->nullable()->comment('Lista de campos a corregir');
            
            // Admin que revisó
            $table->foreignId('revisado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_revision')->nullable();
            
            // ────────────────────────────────────────────────────────────────
            // Metadata
            // ────────────────────────────────────────────────────────────────
            $table->string('ip_solicitud', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // ────────────────────────────────────────────────────────────────
            // Índices para búsquedas frecuentes
            // ────────────────────────────────────────────────────────────────
            $table->index('numero_referencia');
            $table->index('estado');
            $table->index(['estudiante_id', 'estado']);
            $table->index('carrera_id');
            $table->index('fecha_envio');
            $table->index('paso_actual');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_admision');
    }
};
