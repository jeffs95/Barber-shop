<?php

namespace App\Filament\Resources\CitaResource\RelationManagers;

use App\Models\Empleado;
use App\Models\Servicio;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiciosRelationManager extends RelationManager
{
    protected static string $relationship = 'itemsCita';
    protected static ?string $title = 'Servicios de la cita';

    public function form(Schema $schema): Schema
    {
        $cita = $this->getOwnerRecord();

        return $schema->components([
            Select::make('servicio_id')
                ->label('Servicio')
                ->options(Servicio::where('es_activo', true)->pluck('nombre', 'id'))
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) use ($cita) {
                    if (! $state) {
                        return;
                    }

                    $servicio = Servicio::find($state);
                    if (! $servicio) {
                        return;
                    }

                    // Precio personalizado del barbero, o precio base del servicio
                    $precio = $servicio->precioParaEmpleado($cita->empleado_id);
                    $set('precio', $precio);
                }),

            TextInput::make('precio')
                ->label('Precio (Q)')
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
                TextColumn::make('servicio.nombre')
                    ->label('Servicio'),

                TextColumn::make('servicio.duracion_minutos')
                    ->label('Duración')
                    ->suffix(' min'),

                TextColumn::make('precio')
                    ->label('Precio')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar servicio')
                    ->after(function () {
                        $this->getOwnerRecord()->recalcularDuracion();
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->recalcularDuracion();
                    }),
            ]);
    }
}
