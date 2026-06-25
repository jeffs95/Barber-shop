<?php

namespace App\Filament\Resources\ServicioResource\RelationManagers;

use App\Models\Empleado;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PreciosEmpleadoRelationManager extends RelationManager
{
    protected static string $relationship = 'preciosEmpleado';
    protected static ?string $title = 'Precios por barbero';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('empleado_id')
                ->label('Barbero')
                ->options(
                    Empleado::with('usuario')
                        ->where('rol', 'barbero')
                        ->where('es_activo', true)
                        ->get()
                        ->mapWithKeys(fn ($e) => [$e->id => $e->nombre_completo])
                )
                ->searchable()
                ->required(),

            TextInput::make('precio')
                ->label('Precio personalizado (Q)')
                ->numeric()
                ->prefix('Q')
                ->minValue(0)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('empleado.nombre_completo')
                    ->label('Barbero')
                    ->getStateUsing(fn ($record) => $record->empleado->nombre_completo),

                TextColumn::make('precio')
                    ->label('Precio')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2),
            ])
            ->headerActions([
                CreateAction::make()->label('Agregar precio'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
