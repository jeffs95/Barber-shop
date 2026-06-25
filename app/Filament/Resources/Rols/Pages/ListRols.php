<?php

namespace App\Filament\Resources\Rols\Pages;

use App\Filament\Resources\Rols\RolResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRols extends ListRecords
{
    protected static string $resource = RolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
