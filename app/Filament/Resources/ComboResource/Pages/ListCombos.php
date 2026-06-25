<?php

namespace App\Filament\Resources\ComboResource\Pages;

use App\Filament\Resources\ComboResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCombos extends ListRecords
{
    protected static string $resource = ComboResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
