<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('producto')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->nullOnDelete()
                ->comment('Usuario que registró el movimiento');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste'])
                ->comment('entrada: compra/recepción, salida: venta/uso, ajuste: corrección de stock');
            $table->integer('cantidad')
                ->comment('Unidades; para ajuste = nuevo stock absoluto');
            $table->integer('stock_antes')->comment('Snapshot del stock antes del movimiento');
            $table->integer('stock_despues')->comment('Snapshot del stock después del movimiento');
            $table->string('motivo', 150)->nullable()->comment('Descripción del motivo');
            $table->string('referencia', 100)->nullable()->comment('Nro. factura, ID de venta, etc.');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['producto_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventario');
    }
};
