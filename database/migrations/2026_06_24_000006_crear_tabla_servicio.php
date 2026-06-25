<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_servicio_id')
                ->constrained('categoria_servicio')
                ->restrictOnDelete()
                ->comment('Categoría a la que pertenece el servicio');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_base', 10, 2)
                ->comment('Precio estándar; puede ser sobreescrito por precio_servicio_empleado');
            $table->unsignedSmallInteger('duracion_minutos')
                ->comment('Duración estimada en minutos, usada para calcular disponibilidad en agenda');
            $table->boolean('es_activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio');
    }
};
