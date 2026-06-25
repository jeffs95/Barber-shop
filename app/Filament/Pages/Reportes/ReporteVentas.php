<?php

namespace App\Filament\Pages\Reportes;

use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\Venta;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReporteVentas extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.reportes.ventas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Ventas';
    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'reportes/ventas';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Venta::query()
                    ->with(['caja.sucursal', 'empleado.usuario', 'cliente'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('caja.sucursal.nombre')
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
                        : 'Sin registrar')
                    ->placeholder('Sin registrar'),

                TextColumn::make('metodo_pago')
                    ->label('Método')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'efectivo'      => 'success',
                        'tarjeta'       => 'info',
                        'transferencia' => 'warning',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => Venta::metodoPagoLabel($state)),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => Venta::estadoColor($state)),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('descuento')
                    ->label('Descuento')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total')
                    ->label('Total')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->weight('bold')
                    ->summarize([
                        Sum::make()->label('Total recaudado')->prefix('Q '),
                        Count::make()->label('# Ventas'),
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
                        ->when($data['fecha_desde'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['fecha_hasta'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
                    )
                    ->columns(2),

                SelectFilter::make('sucursal')
                    ->label('Sucursal')
                    ->options(Sucursal::pluck('nombre', 'id'))
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'], fn ($q, $v) => $q->whereHas('caja', fn ($q2) => $q2->where('sucursal_id', $v)))
                    ),

                SelectFilter::make('empleado_id')
                    ->label('Empleado')
                    ->options(
                        Empleado::with('usuario')->get()
                            ->mapWithKeys(fn ($e) => [$e->id => $e->nombre_completo])
                    ),

                SelectFilter::make('metodo_pago')
                    ->label('Método de pago')
                    ->options([
                        'efectivo'      => 'Efectivo',
                        'tarjeta'       => 'Tarjeta',
                        'transferencia' => 'Transferencia',
                    ]),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'completada'  => 'Completada',
                        'cancelada'   => 'Cancelada',
                        'reembolsada' => 'Reembolsada',
                    ])
                    ->default('completada'),
            ])
            ->filtersFormColumns(3)
            ->defaultSort('created_at', 'desc');
    }
}
