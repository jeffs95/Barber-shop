<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\FiltraPorSucursal;
use App\Filament\Resources\MovimientoInventarioResource\Pages;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MovimientoInventarioResource extends Resource
{
    use FiltraPorSucursal;

    protected static ?string $model = MovimientoInventario::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $slug = 'movimientos-inventario';

    protected static ?string $navigationLabel = 'Movimientos';

    protected static ?string $modelLabel = 'movimiento';

    protected static ?string $pluralModelLabel = 'movimientos de inventario';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Registrar movimiento')
                ->columns(2)
                ->schema([
                    Select::make('producto_id')
                        ->label('Producto')
                        ->options(Producto::activo()->pluck('nombre', 'id'))
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->columnSpanFull(),

                    Select::make('tipo')
                        ->label('Tipo de movimiento')
                        ->options([
                            'entrada' => 'Entrada — ingreso de stock',
                            'salida'  => 'Salida — retiro de stock',
                            'ajuste'  => 'Ajuste — corrección de stock',
                        ])
                        ->required()
                        ->reactive(),

                    TextInput::make('cantidad')
                        ->label(fn ($get): string => $get('tipo') === 'ajuste'
                            ? 'Nuevo stock total'
                            : 'Cantidad de unidades')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->hint(function ($get): ?string {
                            $productoId = $get('producto_id');
                            if (! $productoId) {
                                return null;
                            }
                            $producto = Producto::find($productoId);
                            if (! $producto) {
                                return null;
                            }
                            $actual = $producto->stock_actual . ' ' . $producto->unidad;
                            return match($get('tipo')) {
                                'entrada' => "Stock actual: {$actual}",
                                'salida'  => "Stock actual: {$actual}",
                                'ajuste'  => "Stock actual: {$actual} → ingresa el nuevo total",
                                default   => null,
                            };
                        }),

                    TextInput::make('motivo')
                        ->label('Motivo')
                        ->maxLength(150)
                        ->placeholder('Ej: Compra mensual, Uso en servicio, Conteo físico...'),

                    TextInput::make('referencia')
                        ->label('Referencia')
                        ->maxLength(100)
                        ->placeholder('Nro. de factura, ID de venta...')
                        ->hint('Opcional'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => MovimientoInventario::tipoColor($state))
                    ->formatStateUsing(fn (string $state): string => MovimientoInventario::tipoLabel($state)),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('stock_antes')
                    ->label('Antes')
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('stock_despues')
                    ->label('Después')
                    ->numeric()
                    ->badge()
                    ->color(fn (MovimientoInventario $record): string => $record->stock_despues <= 0
                        ? 'danger'
                        : ($record->stock_despues <= ($record->producto->stock_minimo ?? 5) ? 'warning' : 'success'))
                    ->alignEnd(),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->placeholder('—')
                    ->limit(40),

                TextColumn::make('referencia')
                    ->label('Referencia')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('usuario.nombre')
                    ->label('Registrado por')
                    ->placeholder('Sistema')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),

                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'salida'  => 'Salida',
                        'ajuste'  => 'Ajuste',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMovimientosInventario::route('/'),
            'create' => Pages\CreateMovimientoInventario::route('/create'),
        ];
    }
}
