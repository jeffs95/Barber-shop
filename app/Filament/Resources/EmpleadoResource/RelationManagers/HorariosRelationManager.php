<?php

namespace App\Filament\Resources\EmpleadoResource\RelationManagers;

use App\Models\HorarioEmpleado;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
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
                            ? intdiv($minutos, 60) . 'h ' . ($minutos % 60 ? ($minutos % 60) . 'm' : '')
                            : '—';
                    }),
            ])
            ->headerActions([
                Action::make('horario_rapido')
                    ->label('Aplicar horario rápido')
                    ->icon('heroicon-o-calendar-days')
                    ->modalHeading('Aplicar horario a varios días')
                    ->modalDescription('Selecciona los días y el rango horario. Si ya existe un registro para ese día, se sobreescribe.')
                    ->form([
                        CheckboxList::make('dias')
                            ->label('Días de trabajo')
                            ->options([
                                1 => 'Lunes',
                                2 => 'Martes',
                                3 => 'Miércoles',
                                4 => 'Jueves',
                                5 => 'Viernes',
                                6 => 'Sábado',
                                7 => 'Domingo',
                            ])
                            ->columns(4)
                            ->required()
                            ->minItems(1),

                        TimePicker::make('hora_inicio')
                            ->label('Hora de entrada')
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('hora_fin')
                            ->label('Hora de salida')
                            ->seconds(false)
                            ->required()
                            ->after('hora_inicio'),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        $empleadoId = $livewire->getOwnerRecord()->id;

                        foreach ($data['dias'] as $dia) {
                            HorarioEmpleado::updateOrCreate(
                                [
                                    'empleado_id' => $empleadoId,
                                    'dia_semana'  => (int) $dia,
                                ],
                                [
                                    'hora_inicio' => $data['hora_inicio'],
                                    'hora_fin'    => $data['hora_fin'],
                                ]
                            );
                        }
                    })
                    ->modalSubmitActionLabel('Aplicar'),

                CreateAction::make()->label('Agregar día suelto'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('dia_semana')
            ->paginated(false);
    }
}
