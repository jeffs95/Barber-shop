<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use App\Models\Cita;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CitasRelationManager extends RelationManager
{
    protected static string $relationship = 'citas';

    protected static ?string $title = 'Historial de citas';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_hora')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('empleado.nombre_completo')
                    ->label('Barbero')
                    ->getStateUsing(fn (Cita $record): string => $record->empleado?->nombre_completo ?? '—')
                    ->badge()
                    ->color('amber'),

                TextColumn::make('servicios_count')
                    ->label('Servicios')
                    ->counts('servicios')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => Cita::estadoColor($state))
                    ->formatStateUsing(fn (string $state): string => Cita::estadoLabel($state)),
            ])
            ->defaultSort('fecha_hora', 'desc')
            ->paginated([10, 25]);
    }
}
