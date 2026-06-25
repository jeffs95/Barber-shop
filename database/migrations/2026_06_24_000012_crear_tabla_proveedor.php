<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedor', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('contacto', 100)->nullable()->comment('Nombre de la persona de contacto');
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('es_activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor');
    }
};
