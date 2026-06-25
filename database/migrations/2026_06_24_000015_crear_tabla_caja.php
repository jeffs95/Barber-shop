<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->nullOnDelete()
                ->comment('Usuario que abrió la caja');
            $table->dateTime('fecha_apertura');
            $table->decimal('monto_inicial', 10, 2)->default(0)->comment('Efectivo inicial al abrir caja');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_cierre_esperado', 10, 2)->nullable()
                ->comment('Calculado: inicial + ventas efectivo');
            $table->decimal('monto_cierre_real', 10, 2)->nullable()
                ->comment('Conteo físico al cerrar');
            $table->decimal('diferencia', 10, 2)->nullable()
                ->comment('Real menos esperado');
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja');
    }
};
