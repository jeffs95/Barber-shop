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
        Schema::create('rol', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique()->comment('Identificador único: dueño, admin_sucursal, barbero');
            $table->string('descripcion', 255)->nullable()->comment('Descripción legible del rol');
            $table->boolean('es_activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol');
    }
};
