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
        Schema::table('carreras', function (Blueprint $table) {
            $table->enum('nivel', ['tsu', 'licenciatura', 'ingenieria'])
                  ->default('licenciatura')
                  ->after('nombre')
                  ->comment('TSU, Licenciatura o Ingeniería');
            $table->foreignId('carrera_base_id')
                  ->nullable()
                  ->after('nivel')
                  ->constrained('carreras')
                  ->onDelete('set null')
                  ->comment('Para TSU: referencia a la carrera completa asociada');
            
            $table->index('nivel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carreras', function (Blueprint $table) {
            $table->dropForeign(['carrera_base_id']);
            $table->dropColumn(['nivel', 'carrera_base_id']);
        });
    }
};
