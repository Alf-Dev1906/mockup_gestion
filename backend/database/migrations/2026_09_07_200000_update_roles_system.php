<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Primero actualizar usuarios con roles antiguos a los nuevos
        DB::table('users')->where('role', 'admin')->update(['role' => 'administrativo']);
        DB::table('users')->whereNotIn('role', ['estudiante', 'profesor', 'administrativo'])->update(['role' => 'estudiante']);
        
        // 2. Actualizar enum de roles en users
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('estudiante', 'profesor', 'administrativo', 'soporte_it', 'desarrollador') NOT NULL DEFAULT 'estudiante'");

        // 2b. Actualizar enum de estatus en estudiantes para incluir 'solicitante'
        DB::statement("ALTER TABLE estudiantes MODIFY COLUMN estatus ENUM('solicitante', 'activo', 'inactivo', 'egresado', 'retirado', 'suspendido') NOT NULL DEFAULT 'activo'");

        // 2c. Agregar user_id a estudiantes
        Schema::table('estudiantes', function (Blueprint $table) {
            if (!Schema::hasColumn('estudiantes', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            }
        });

        // 3. Agregar user_id a profesores
        Schema::table('profesores', function (Blueprint $table) {
            if (!Schema::hasColumn('profesores', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            }
        });

        // 4. Crear tabla de solicitudes de admisión
        Schema::create('solicitudes_admision', function (Blueprint $table) {
            $table->id();
            $table->string('numero_solicitud', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->onDelete('set null');
            
            // Datos en JSON
            $table->json('datos_personales');
            $table->json('datos_contacto');
            $table->json('datos_academicos');
            $table->json('contacto_emergencia');
            
            // Estado
            $table->enum('estatus', ['pendiente', 'en_revision', 'aprobado', 'rechazado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_revision')->nullable();
            
            $table->timestamps();
            $table->index('estatus');
        });

        // 5. Crear tabla de pagos (para administrativos)
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->string('concepto', 100);
            $table->decimal('monto', 10, 2);
            $table->enum('estatus', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->string('referencia', 50)->nullable();
            $table->timestamps();
            $table->index(['estudiante_id', 'estatus']);
        });

        // 6. Crear tabla de logs de sistema (para soporte y desarrollador)
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('accion', 100);
            $table->string('modelo', 50)->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
            $table->index('accion');
        });

        // 7. Crear tabla de backups (para desarrollador)
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('ruta', 500);
            $table->bigInteger('tamano')->comment('Tamaño en bytes');
            $table->enum('tipo', ['manual', 'automatico'])->default('manual');
            $table->enum('estatus', ['completado', 'fallido', 'en_proceso'])->default('en_proceso');
            $table->foreignId('creado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('solicitudes_admision');
        
        Schema::table('profesores', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
