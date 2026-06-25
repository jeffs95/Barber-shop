<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('venta')->cascadeOnDelete();
            $table->enum('tipo', ['servicio', 'producto']);
            $table->foreignId('servicio_id')->nullable()->constrained('servicio')->nullOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('producto')->nullOnDelete();
            $table->string('descripcion', 150)->comment('Snapshot del nombre al momento de la venta');
            $table->decimal('precio_unitario', 10, 2);
            $table->unsignedSmallInteger('cantidad')->default(1);
            $table->decimal('subtotal', 10, 2)->comment('precio_unitario * cantidad');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_venta');
    }
};
