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
        
        // 2. Actualizar columna role en users (PostgreSQL compatible)
        // En PostgreSQL, cambiar el tipo requiere usar ALTER TYPE o recrear
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50)");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'estudiante'");

        // 2b. Actualizar columna estatus en estudiantes (PostgreSQL compatible)
        DB::statement("ALTER TABLE estudiantes ALTER COLUMN estatus TYPE VARCHAR(50)");
        DB::statement("ALTER TABLE estudiantes ALTER COLUMN estatus SET DEFAULT 'activo'");

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

        // 4. Tabla de solicitudes de admisión se crea en migración separada
        // Ver: 2026_09_08_013915_create_solicitudes_admision_table.php

        // 5. Crear tabla de pagos (para administrativos)
        if (!Schema::hasTable('pagos')) {
            Schema::create('pagos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
                $table->string('concepto', 100);
                $table->decimal('monto', 10, 2);
                $table->string('estatus', 20)->default('pendiente');
                $table->date('fecha_vencimiento')->nullable();
                $table->timestamp('fecha_pago')->nullable();
                $table->string('referencia', 50)->nullable();
                $table->timestamps();
                $table->index(['estudiante_id', 'estatus']);
            });
        }

        // 6. Crear tabla de logs de sistema (para soporte y desarrollador)
        if (!Schema::hasTable('system_logs')) {
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
        }

        // 7. Crear tabla de backups (para desarrollador)
        if (!Schema::hasTable('backups')) {
            Schema::create('backups', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 255);
                $table->string('ruta', 500);
                $table->bigInteger('tamano')->comment('Tamaño en bytes');
                $table->string('tipo', 20)->default('manual');
                $table->string('estatus', 20)->default('en_proceso');
                $table->foreignId('creado_por')->nullable()->constrained('users')->onDelete('set null');
                $table->text('notas')->nullable();
                $table->timestamps();
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('pagos');
        // solicitudes_admision se elimina en su migración correspondiente
        
        Schema::table('profesores', function (Blueprint $table) {
            if (Schema::hasColumn('profesores', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
        
        Schema::table('estudiantes', function (Blueprint $table) {
            if (Schema::hasColumn('estudiantes', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
