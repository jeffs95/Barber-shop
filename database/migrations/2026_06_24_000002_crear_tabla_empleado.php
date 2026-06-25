<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('usuario')
                ->cascadeOnDelete()
                ->comment('Usuario del sistema asociado al empleado');
            $table->enum('rol', ['barbero', 'recepcionista', 'administrador'])
                ->comment('Rol del empleado dentro de la barbería');
            $table->decimal('porcentaje_comision', 5, 2)
                ->default(0)
                ->comment('Porcentaje de comisión sobre servicios realizados');
            $table->string('color_agenda', 7)
                ->nullable()
                ->comment('Color hexadecimal para distinguirlo en la agenda, ej: #F59E0B');
            $table->boolean('es_activo')
                ->default(true)
                ->comment('Indica si el empleado está activo en el sistema');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado');
    }
};
