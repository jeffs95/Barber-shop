<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
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

        // Solo actuar si hay un path con directorio (archivo recién subido al disco public)
        if (! $localPath || ! str_contains($localPath, '/')) {
            return;
        }

        if (! Storage::disk('public')->exists($localPath)) {
            return;
        }

        $basename = basename($localPath);

        // Intentar mover al FTP
        try {
            $contenido = Storage::disk('public')->get($localPath);
            Storage::disk('ftp_images')->put('productos/' . $basename, $contenido);
            Storage::disk('public')->delete($localPath);
            // En FTP: foto guarda solo el basename (el proxy añade el directorio)
            $this->record->updateQuietly(['foto' => $basename]);
        } catch (\Throwable $e) {
            // FTP no disponible: mantener en disco public, guardar path completo
            Log::warning('FTP no disponible, imagen queda en disco local: ' . $e->getMessage());
            // foto queda como "productos/uuid.png" → landing detecta disco local
        }
    }
}
