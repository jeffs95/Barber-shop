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
        Schema::table('usuario', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('rol_id')
                ->constrained('sucursal')
                ->nullOnDelete()
                ->comment('Sucursal asignada (solo para admin_sucursal; null = acceso a todo)');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Sucursal::class, 'sucursal_id');
            $table->dropColumn('sucursal_id');
        });
    }
};
