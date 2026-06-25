<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                ->constrained('producto')
                ->cascadeOnDelete()
                ->comment('Producto al que pertenece este registro de stock');
            $table->foreignId('sucursal_id')
                ->constrained('sucursal')
                ->cascadeOnDelete()
                ->comment('Sucursal que posee este stock');
            $table->decimal('stock_actual', 10, 2)->default(0)->comment('Unidades disponibles en esta sucursal');
            $table->decimal('stock_minimo', 10, 2)->default(0)->comment('Alerta de reposición para esta sucursal');
            $table->timestamps();

            $table->unique(['producto_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_sucursal');
    }
};
