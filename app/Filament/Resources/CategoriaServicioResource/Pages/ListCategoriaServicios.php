<?php

namespace App\Filament\Resources\CategoriaServicioResource\Pages;

use App\Filament\Resources\CategoriaServicioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaServicios extends ListRecords
{
    protected static string $resource = CategoriaServicioResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
