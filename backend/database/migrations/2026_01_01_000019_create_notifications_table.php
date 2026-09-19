<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 50);
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('url_accion')->nullable();
            $table->boolean('leida')->default(false);
            $table->dateTime('leida_at')->nullable();
            $table->json('datos')->nullable();
            $table->timestamps();
            
            // Índice crítico para rendimiento
            $table->index(['user_id', 'leida']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
