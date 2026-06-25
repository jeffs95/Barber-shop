<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100)->nullable();
            $table->string('telefono', 20)
                ->nullable()
                ->unique()
                ->comment('Teléfono principal, usado para contacto y búsqueda rápida');
            $table->string('email')->nullable()->unique();
            $table->string('foto')->nullable()->comment('Ruta relativa a la foto del cliente');
            $table->date('fecha_nacimiento')->nullable()->comment('Se usa para enviar promo de cumpleaños');
            $table->enum('tipo', ['nuevo', 'regular', 'frecuente', 'vip'])
                ->default('nuevo')
                ->comment('Clasificación automática según frecuencia de visita');
            $table->text('notas')->nullable()->comment('Notas generales sobre el cliente');
            $table->integer('puntos_fidelidad')
                ->default(0)
                ->comment('Puntos acumulados por visitas y compras, canjeables en servicios');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
