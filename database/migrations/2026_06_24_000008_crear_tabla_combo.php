<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)
                ->comment('Precio final del combo; puede ser menor que la suma de sus servicios');
            $table->decimal('porcentaje_descuento', 5, 2)
                ->default(0)
                ->comment('Descuento informativo en porcentaje, solo para mostrar al cliente');
            $table->date('fecha_inicio')->nullable()
                ->comment('Fecha desde la que el combo está disponible; null = siempre');
            $table->date('fecha_fin')->nullable()
                ->comment('Fecha hasta la que el combo está disponible; null = sin vencimiento');
            $table->boolean('es_activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo');
    }
};
