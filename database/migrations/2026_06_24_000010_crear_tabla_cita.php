<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('cliente')
                ->nullOnDelete()
                ->comment('Null permitido para walk-ins sin cliente registrado');
            $table->foreignId('empleado_id')
                ->constrained('empleado')
                ->restrictOnDelete();
            $table->dateTime('fecha_hora')
                ->comment('Fecha y hora de inicio de la cita');
            $table->unsignedSmallInteger('duracion_estimada_min')
                ->default(30)
                ->comment('Suma de duracion_minutos de los servicios agendados');
            $table->enum('estado', ['pendiente', 'confirmada', 'en_proceso', 'completada', 'cancelada', 'no_asistio'])
                ->default('pendiente');
            $table->enum('origen', ['presencial', 'enlace', 'telefono'])
                ->default('presencial')
                ->comment('Canal por el que se originó la cita');
            $table->text('notas')->nullable();
            $table->text('notas_barbero')
                ->nullable()
                ->comment('Notas privadas del barbero: qué le hizo, preferencias, observaciones');
            $table->timestamps();

            $table->index(['empleado_id', 'fecha_hora']);
            $table->index(['fecha_hora', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cita');
    }
};
