<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('doctores')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->string('motivo');
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'atendida'])
                ->default('pendiente');
            $table->timestamps();

            $table->index(['doctor_id', 'inicio', 'fin'], 'citas_doctor_rango_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
