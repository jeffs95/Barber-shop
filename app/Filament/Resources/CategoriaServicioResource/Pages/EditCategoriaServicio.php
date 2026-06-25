<?php

namespace App\Filament\Resources\CategoriaServicioResource\Pages;

use App\Filament\Resources\CategoriaServicioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaServicio extends EditRecord
{
    protected static string $resource = CategoriaServicioResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
