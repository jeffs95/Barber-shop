<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleado', function (Blueprint $table) {
            // Datos personales
            $table->string('telefono', 20)
                ->nullable()
                ->after('es_activo')
                ->comment('Número de teléfono personal del empleado');

            $table->string('direccion', 255)
                ->nullable()
                ->after('telefono')
                ->comment('Dirección de residencia');

            $table->date('fecha_nacimiento')
                ->nullable()
                ->after('direccion')
                ->comment('Fecha de nacimiento para calcular edad y cumpleaños');

            // Información laboral / administrativa
            $table->decimal('sueldo_base', 10, 2)
                ->default(0)
                ->after('fecha_nacimiento')
                ->comment('Salario fijo mensual en quetzales (Q)');

            $table->date('fecha_contratacion')
                ->nullable()
                ->after('sueldo_base')
                ->comment('Fecha de inicio de contrato');

            $table->enum('tipo_contrato', ['tiempo_completo', 'medio_tiempo', 'por_servicio'])
                ->default('tiempo_completo')
                ->after('fecha_contratacion')
                ->comment('Modalidad de contratación del empleado');
        });
    }

    public function down(): void
    {
        Schema::table('empleado', function (Blueprint $table) {
            $table->dropColumn([
                'telefono',
                'direccion',
                'fecha_nacimiento',
                'sueldo_base',
                'fecha_contratacion',
                'tipo_contrato',
            ]);
        });
    }
};
