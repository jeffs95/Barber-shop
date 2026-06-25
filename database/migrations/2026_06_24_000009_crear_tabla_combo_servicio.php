<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')
                ->constrained('combo')
                ->cascadeOnDelete();
            $table->foreignId('servicio_id')
                ->constrained('servicio')
                ->cascadeOnDelete();

            $table->unique(['combo_id', 'servicio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_servicio');
    }
};
