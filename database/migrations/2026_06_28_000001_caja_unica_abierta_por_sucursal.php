<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Índice único parcial: garantiza a nivel de BD que solo exista UNA caja
     * con estado 'abierta' por sucursal. Las cajas cerradas no se ven afectadas,
     * y los sucursal_id nulos se tratan como distintos (no chocan entre sí).
     */
    public function up(): void
    {
        DB::statement(
            "CREATE UNIQUE INDEX caja_una_abierta_por_sucursal ON caja (sucursal_id) WHERE estado = 'abierta'"
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS caja_una_abierta_por_sucursal');
    }
};
