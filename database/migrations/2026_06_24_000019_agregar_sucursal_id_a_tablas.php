<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleado', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('usuario_id')
                ->constrained('sucursal')
                ->nullOnDelete();
        });

        Schema::table('cita', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('empleado_id')
                ->constrained('sucursal')
                ->nullOnDelete();
        });

        Schema::table('caja', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('usuario_id')
                ->constrained('sucursal')
                ->nullOnDelete();
        });

        Schema::table('movimiento_inventario', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('usuario_id')
                ->constrained('sucursal')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimiento_inventario', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Sucursal::class);
            $table->dropColumn('sucursal_id');
        });

        Schema::table('caja', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Sucursal::class);
            $table->dropColumn('sucursal_id');
        });

        Schema::table('cita', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Sucursal::class);
            $table->dropColumn('sucursal_id');
        });

        Schema::table('empleado', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Sucursal::class);
            $table->dropColumn('sucursal_id');
        });
    }
};
