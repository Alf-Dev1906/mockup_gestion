<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->string('concepto'); // inscripcion, mensualidad, examen, etc.
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago')->nullable(); // transferencia, deposito, tarjeta, efectivo
            $table->string('numero_referencia')->unique()->nullable();
            $table->enum('estatus', ['pendiente', 'pagado', 'verificado', 'rechazado'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('verificado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verificado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
