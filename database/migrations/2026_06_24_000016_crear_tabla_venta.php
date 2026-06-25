<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('caja')->restrictOnDelete();
            $table->foreignId('empleado_id')->constrained('empleado')->restrictOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('cliente')->nullOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained('cita')->nullOnDelete()
                ->comment('Cita de origen si aplica');
            $table->decimal('subtotal', 10, 2)->default(0)->comment('Suma bruta de todos los ítems');
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('propina', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0)->comment('subtotal - descuento + propina');
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia', 'otro'])->default('efectivo');
            $table->enum('estado', ['completada', 'cancelada', 'reembolsada'])->default('completada');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['caja_id', 'estado']);
            $table->index(['empleado_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta');
    }
};
