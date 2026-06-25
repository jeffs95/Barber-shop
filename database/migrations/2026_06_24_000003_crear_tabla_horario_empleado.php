<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_empleado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')
                ->constrained('empleado')
                ->cascadeOnDelete()
                ->comment('Empleado al que pertenece este bloque de horario');
            $table->tinyInteger('dia_semana')
                ->comment('Día de la semana: 1 = lunes, 7 = domingo');
            $table->time('hora_inicio')
                ->comment('Hora de inicio del turno');
            $table->time('hora_fin')
                ->comment('Hora de fin del turno');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_empleado');
    }
};
