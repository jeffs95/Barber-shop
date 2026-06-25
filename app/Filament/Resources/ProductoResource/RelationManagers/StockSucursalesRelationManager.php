<?php

namespace App\Filament\Resources\ProductoResource\RelationManagers;

use App\Models\StockSucursal;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockSucursalesRelationManager extends RelationManager
{
    protected static string $relationship = 'stockSucursales';

    protected static ?string $title = 'Stock por sucursal';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('stock_minimo')
                ->label('Stock mínimo')
                ->numeric()
                ->minValue(0)
                ->required()
                ->hint('Nivel de alerta de reposición para esta sucursal'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sucursal.nombre')
            ->columns([
                TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->sortable(),

                TextColumn::make('stock_actual')
                    ->label('Stock actual')
                    ->numeric(decimalPlaces: 0)
                    ->badge()
                    ->color(fn (StockSucursal $record): string => match (true) {
                        $record->stock_actual <= 0             => 'danger',
                        $record->stock_actual <= $record->stock_minimo => 'warning',
                        default                                => 'success',
                    })
                    ->sortable(),

                TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn () => auth()->user()?->esDuenio() ?? false),
            ])
            ->paginated(false);
    }
}
