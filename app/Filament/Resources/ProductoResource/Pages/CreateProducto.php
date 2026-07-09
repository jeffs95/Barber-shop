<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateProducto extends CreateRecord
{
    protected static string $resource = ProductoResource::class;

    protected function afterCreate(): void
    {
        $this->persistirImagen();
    }

    private function persistirImagen(): void
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
            Storage::disk('ftp_images')->put('productos/' . $basename, $contenido);
            Storage::disk('public')->delete($localPath);
            $this->record->updateQuietly(['foto' => $basename]);
        } catch (\Throwable) {
            Storage::disk('public')->delete($localPath);
            $this->record->updateQuietly(['foto' => null]);

            Notification::make()
                ->title('Imagen no guardada')
                ->body('No se pudo conectar al servidor FTP. El producto se creó sin foto.')
                ->warning()
                ->send();
        }
    }
}
