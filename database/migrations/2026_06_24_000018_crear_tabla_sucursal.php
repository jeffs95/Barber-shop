<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursal', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('direccion', 200)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->boolean('es_activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursal');
    }
};
