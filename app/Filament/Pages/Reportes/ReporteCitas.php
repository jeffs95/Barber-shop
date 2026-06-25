<?php

namespace App\Filament\Pages\Reportes;

use App\Models\Cita;
use App\Models\Empleado;
use App\Models\Sucursal;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReporteCitas extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.reportes.citas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Citas';
    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'reportes/citas';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Cita::query()
                    ->with(['cliente', 'empleado.usuario', 'sucursal', 'servicios'])
                    ->latest('fecha_hora')
            )
            ->columns([
                TextColumn::make('fecha_hora')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->badge()
                    ->color('amber'),

                TextColumn::make('empleado.usuario.nombre')
                    ->label('Barbero')
                    ->formatStateUsing(fn ($record) => $record->empleado?->nombre_completo ?? '—'),

                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($record) => $record->cliente
                        ? $record->cliente->nombre . ' ' . $record->cliente->apellido
                        : 'Sin registrar'),

                TextColumn::make('servicios_count')
                    ->label('Servicios')
                    ->counts('servicios')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('duracion_estimada_min')
                    ->label('Duración')
                    ->suffix(' min'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => Cita::estadoColor($state))
                    ->formatStateUsing(fn (string $state) => Cita::estadoLabel($state))
                    ->summarize([
                        Count::make()->label('Total citas'),
                    ]),
            ])
            ->filters([
                Filter::make('fecha')
                    ->form([
                        DatePicker::make('fecha_desde')
                            ->label('Desde')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('fecha_hasta')
                            ->label('Hasta')
                            ->default(now()),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['fecha_desde'], fn ($q, $d) => $q->whereDate('fecha_hora', '>=', $d))
                        ->when($data['fecha_hasta'], fn ($q, $d) => $q->whereDate('fecha_hora', '<=', $d))
                    )
                    ->columns(2),

                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(Sucursal::pluck('nombre', 'id')),

                SelectFilter::make('empleado_id')
                    ->label('Empleado')
                    ->options(
                        Empleado::with('usuario')->get()
                            ->mapWithKeys(fn ($e) => [$e->id => $e->nombre_completo])
                    ),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(Cita::estadoOpciones()),
            ])
            ->filtersFormColumns(3)
            ->defaultSort('fecha_hora', 'desc');
    }
}
