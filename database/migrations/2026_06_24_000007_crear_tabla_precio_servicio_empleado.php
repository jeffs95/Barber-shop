<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precio_servicio_empleado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')
                ->constrained('servicio')
                ->cascadeOnDelete();
            $table->foreignId('empleado_id')
                ->constrained('empleado')
                ->cascadeOnDelete();
            $table->decimal('precio', 10, 2)
                ->comment('Precio personalizado que cobra este barbero por este servicio');
            $table->timestamps();

            $table->unique(['servicio_id', 'empleado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precio_servicio_empleado');
    }
};
