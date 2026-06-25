<?php

namespace App\Filament\Resources\EmpleadoResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HorariosRelationManager extends RelationManager
{
    protected static string $relationship = 'horarios';

    protected static ?string $title = 'Horarios de trabajo';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('dia_semana')
                ->label('Día')
                ->options([
                    1 => 'Lunes',
                    2 => 'Martes',
                    3 => 'Miércoles',
                    4 => 'Jueves',
                    5 => 'Viernes',
                    6 => 'Sábado',
                    7 => 'Domingo',
                ])
                ->required(),

            TimePicker::make('hora_inicio')
                ->label('Hora de entrada')
                ->seconds(false)
                ->required(),

            TimePicker::make('hora_fin')
                ->label('Hora de salida')
                ->seconds(false)
                ->required()
                ->after('hora_inicio'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('dia_semana')
            ->columns([
                TextColumn::make('dia_semana')
                    ->label('Día')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Lunes',
                        2 => 'Martes',
                        3 => 'Miércoles',
                        4 => 'Jueves',
                        5 => 'Viernes',
                        6 => 'Sábado',
                        7 => 'Domingo',
                        default => '—',
                    })
                    ->sortable(),

                TextColumn::make('hora_inicio')
                    ->label('Entrada')
                    ->formatStateUsing(fn (string $state) => substr($state, 0, 5)),

                TextColumn::make('hora_fin')
                    ->label('Salida')
                    ->formatStateUsing(fn (string $state) => substr($state, 0, 5)),

                TextColumn::make('horas')
                    ->label('Horas')
                    ->state(function ($record): string {
                        [$h1, $m1] = explode(':', $record->hora_inicio);
                        [$h2, $m2] = explode(':', $record->hora_fin);
                        $minutos = ($h2 * 60 + $m2) - ($h1 * 60 + $m1);
                        return $minutos > 0
                            ? intdiv($minutos, 60) . 'h ' . ($minutos % 60) . 'm'
                            : '—';
                    }),
            ])
            ->headerActions([
                CreateAction::make()->label('Agregar turno'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('dia_semana')
            ->paginated(false);
    }
}
