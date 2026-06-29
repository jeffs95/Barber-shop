<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Borrado reversible (soft delete) en cliente, empleado y producto:
     * al "eliminar" solo se marca deleted_at, conservando el historial de
     * citas, ventas y movimientos que los referencian.
     */
    public function up(): void
    {
        Schema::table('cliente', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('empleado', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('producto', fn (Blueprint $table) => $table->softDeletes());
    }

    public function down(): void
    {
        Schema::table('cliente', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('empleado', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('producto', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
