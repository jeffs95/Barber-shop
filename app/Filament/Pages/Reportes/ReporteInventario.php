<?php

namespace App\Filament\Pages\Reportes;

use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReporteInventario extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.reportes.inventario';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Inventario';
    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'reportes/inventario';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockSucursal::query()
                    ->with(['producto.proveedor', 'sucursal'])
                    ->join('producto', 'stock_sucursal.producto_id', '=', 'producto.id')
                    ->where('producto.es_activo', true)
                    ->select('stock_sucursal.*')
            )
            ->columns([
                TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->badge()
                    ->color('amber')
                    ->sortable(),

                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('producto.categoria')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => Producto::categoriaLabel($state)),

                TextColumn::make('stock_actual')
                    ->label('Stock actual')
                    ->numeric(decimalPlaces: 0)
                    ->badge()
                    ->color(fn (StockSucursal $record): string => match (true) {
                        $record->stock_actual <= 0                          => 'danger',
                        $record->stock_actual <= $record->stock_minimo      => 'warning',
                        default                                             => 'success',
                    })
                    ->sortable(),

                TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('producto.proveedor.nombre')
                    ->label('Proveedor')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(Sucursal::pluck('nombre', 'id')),

                SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->options([
                        'cuidado_cabello' => 'Cuidado del cabello',
                        'barba'           => 'Barba',
                        'herramienta'     => 'Herramienta',
                        'consumible'      => 'Consumible',
                        'otro'            => 'Otro',
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['value'], fn ($q, $v) => $q->whereHas('producto', fn ($p) => $p->where('categoria', $v)))
                    ),

                TernaryFilter::make('bajo_minimo')
                    ->label('Bajo mínimo')
                    ->trueLabel('Solo bajo mínimo')
                    ->falseLabel('Solo en buen nivel')
                    ->queries(
                        true:  fn ($q) => $q->whereColumn('stock_sucursal.stock_actual', '<=', 'stock_sucursal.stock_minimo'),
                        false: fn ($q) => $q->whereColumn('stock_sucursal.stock_actual', '>', 'stock_sucursal.stock_minimo'),
                    ),
            ])
            ->defaultSort('stock_sucursal.stock_actual', 'asc');
    }
}
