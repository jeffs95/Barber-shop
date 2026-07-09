<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditEmpleado extends EditRecord
{
    protected static string $resource = EmpleadoResource::class;

    private ?string $fotoAnterior = null;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function beforeSave(): void
    {
        $this->fotoAnterior = $this->record->getOriginal('foto');
    }

    protected function afterSave(): void
    {
        $this->persistirFoto();
    }

    private function persistirFoto(): void
    {
        $localPath = $this->record->foto;

        if (! $localPath || ! str_contains($localPath, '/')) {
            return;
        }

        if (! Storage::disk('public')->exists($localPath)) {
            return;
        }

        $basename  = basename($localPath);
        $contenido = Storage::disk('public')->get($localPath);

        try {
            Storage::disk('ftp_images')->put('empleados/' . $basename, $contenido);
            Storage::disk('public')->delete($localPath);
            $this->record->updateQuietly(['foto' => $basename]);
        } catch (\Throwable) {
            Storage::disk('public')->delete($localPath);
            $this->record->updateQuietly(['foto' => $this->fotoAnterior]);

            Notification::make()
                ->title('Foto no guardada')
                ->body('No se pudo conectar al servidor FTP. Se mantuvo la imagen anterior.')
                ->warning()
                ->send();
        }
    }
}
