<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedor')->nullOnDelete();
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->string('codigo_barras', 50)->unique()->nullable()->comment('Código EAN, UPC o QR del producto');
            $table->decimal('precio_compra', 10, 2)->comment('Costo de adquisición al proveedor');
            $table->decimal('precio_venta', 10, 2)->comment('Precio de venta al cliente');
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(5)->comment('Umbral para alerta de stock bajo');
            $table->string('unidad', 20)->default('unidad')->comment('Ej: unidad, ml, g, kg, L, oz');
            $table->enum('categoria', ['cuidado_cabello', 'barba', 'herramienta', 'consumible', 'otro'])->default('otro');
            $table->boolean('es_activo')->default(true);
            $table->timestamps();

            $table->index(['categoria', 'es_activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
