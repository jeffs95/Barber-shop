<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('descripcion')
                ->comment('Nombre del archivo de imagen almacenado en FTP (Barber-shop/imagenes/productos/)');
        });
    }

    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
