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
            $table->foreignId('rol_id')
                ->nullable()
                ->after('id')
                ->constrained('rol')
                ->nullOnDelete()
                ->comment('Rol del usuario en el sistema');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Rol::class, 'rol_id');
            $table->dropColumn('rol_id');
        });
    }
};
