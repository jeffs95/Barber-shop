<?php

namespace App\Filament\Resources\CitaResource\Pages;

use App\Filament\Resources\CitaResource;
use App\Models\Servicio;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateCita extends CreateRecord
{
    protected static string $resource = CitaResource::class;

    protected array $serviciosParaCrear = [];

    // Pre-llena fecha_hora si viene del calendario (?fecha_hora=ISO)
    public function mount(): void
    {
        parent::mount();

        if ($raw = request()->query('fecha_hora')) {
            try {
                $dt = Carbon::parse($raw);
                $this->form->fill([
                    ...$this->form->getRawState(),
                    'fecha_hora' => $dt->format('Y-m-d H:i:s'),
                ]);
            } catch (\Exception) {
                // fecha inválida → ignorar
            }
        }
    }

    // Extrae los servicios del Repeater (campo virtual) antes de crear el modelo
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->serviciosParaCrear = array_values(
            array_filter(
                $data['servicios_form'] ?? [],
                fn ($item) => ! empty($item['servicio_id'])
            )
        );

        unset($data['servicios_form']);

        return $data;
    }

    // Crea los registros cita_servicio después de que el modelo Cita existe
    protected function afterCreate(): void
    {
        if (empty($this->serviciosParaCrear)) {
            return;
        }

        foreach ($this->serviciosParaCrear as $item) {
            $servicio = Servicio::find($item['servicio_id']);
            if (! $servicio) {
                continue;
            }

            // Precio personalizado del barbero; si no tiene, cae al precio base
            $precio = $servicio->precioParaEmpleado($this->record->empleado_id ?? 0);

            $this->record->itemsCita()->create([
                'servicio_id' => $item['servicio_id'],
                'precio'      => $precio,
            ]);
        }

        // Recalcula la duración estimada según los servicios seleccionados
        $this->record->recalcularDuracion();
    }
}
