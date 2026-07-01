<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreateEmpleado extends CreateRecord
{
    protected static string $resource = EmpleadoResource::class;

    protected function afterCreate(): void
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

        $basename = basename($localPath);

        try {
            $contenido = Storage::disk('public')->get($localPath);
            Storage::disk('ftp_images')->put('empleados/' . $basename, $contenido);
            Storage::disk('public')->delete($localPath);
            $this->record->updateQuietly(['foto' => $basename]);
        } catch (\Throwable $e) {
            Log::warning('FTP foto empleado: ' . $e->getMessage());
        }
    }
}
