<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        $this->persistirImagen();
    }

    private function persistirImagen(): void
    {
        $localPath = $this->record->foto;

        // Solo actuar si el valor tiene directorio → archivo recién subido (disco public)
        // Si es solo basename → ya está en FTP o en local permanente, no mover
        if (! $localPath || ! str_contains($localPath, '/')) {
            return;
        }

        if (! Storage::disk('public')->exists($localPath)) {
            return;
        }

        $basename = basename($localPath);

        try {
            $contenido = Storage::disk('public')->get($localPath);
            Storage::disk('ftp_images')->put('productos/' . $basename, $contenido);
            Storage::disk('public')->delete($localPath);
            $this->record->updateQuietly(['foto' => $basename]);
        } catch (\Throwable $e) {
            Log::warning('FTP no disponible, imagen queda en disco local: ' . $e->getMessage());
        }
    }
}
