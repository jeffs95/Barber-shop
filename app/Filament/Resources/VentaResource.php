<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaResource\Pages;
use App\Models\Caja;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Venta;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $slug = 'ventas';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $modelLabel = 'venta';

    protected static ?string $pluralModelLabel = 'ventas';

    protected static string|\UnitEnum|null $navigationGroup = 'Punto de Venta';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $total = Venta::where('estado', 'completada')
            ->whereDate('created_at', today())
            ->sum('total');

        return $total > 0 ? 'Q ' . number_format($total, 0) : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if ($user?->esAdminSucursal() && ($sucursalId = $user->getSucursalId())) {
            $query->whereHas('caja', fn (Builder $q) => $q->where('sucursal_id', $sucursalId));
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos de la venta')
                ->columns(2)
                ->schema([
                    Select::make('caja_id')
                        ->label('Caja')
                        ->options(Caja::all()->mapWithKeys(fn ($c) => [
                            $c->id => 'Caja #' . $c->id . ' — ' . $c->fecha_apertura->format('d/m/Y'),
                        ]))
                        ->default(fn () => Caja::cajaAbierta()?->id)
                        ->required()
                        ->searchable(),

                    Select::make('empleado_id')
                        ->label('Empleado')
                        ->options(
                            Empleado::where('es_activo', true)
                                ->with('usuario')
                                ->get()
                                ->mapWithKeys(fn ($e) => [$e->id => $e->nombre_completo])
                        )
                        ->required()
                        ->searchable(),

                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(
                            Cliente::orderBy('nombre')
                                ->get()
                                ->mapWithKeys(fn ($c) => [$c->id => $c->nombreCompleto()])
                        )
                        ->nullable()
                        ->searchable(),

                    Select::make('metodo_pago')
                        ->label('Método de pago')
                        ->options([
                            'efectivo'      => 'Efectivo',
                            'tarjeta'       => 'Tarjeta',
                            'transferencia' => 'Transferencia',
                            'otro'          => 'Otro',
                        ])
                        ->default('efectivo')
                        ->required(),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'completada'  => 'Completada',
                            'cancelada'   => 'Cancelada',
                            'reembolsada' => 'Reembolsada',
                        ])
                        ->default('completada')
                        ->required(),

                    TextInput::make('descuento')
                        ->label('Descuento (Q)')
                        ->numeric()
                        ->prefix('Q')
                        ->default(0)
                        ->minValue(0),

                    TextInput::make('propina')
                        ->label('Propina (Q)')
                        ->numeric()
                        ->prefix('Q')
                        ->default(0)
                        ->minValue(0),

                    Textarea::make('notas')
                        ->label('Notas')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Ítems de la venta')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->label('')
                        ->schema([
                            Select::make('tipo')
                                ->label('Tipo')
                                ->options([
                                    'servicio' => 'Servicio',
                                    'producto' => 'Producto',
                                ])
                                ->required()
                                ->reactive()
                                ->columnSpan(1),

                            Select::make('servicio_id')
                                ->label('Servicio')
                                ->options(Servicio::where('es_activo', true)->pluck('nombre', 'id'))
                                ->searchable()
                                ->visible(fn ($get): bool => $get('tipo') === 'servicio')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $s = Servicio::find($state);
                                    if ($s) {
                                        $set('descripcion', $s->nombre);
                                        $set('precio_unitario', $s->precio_base);
                                    }
                                })
                                ->columnSpan(2),

                            Select::make('producto_id')
                                ->label('Producto')
                                ->options(Producto::activo()->pluck('nombre', 'id'))
                                ->searchable()
                                ->visible(fn ($get): bool => $get('tipo') === 'producto')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $p = Producto::find($state);
                                    if ($p) {
                                        $set('descripcion', $p->nombre);
                                        $set('precio_unitario', $p->precio_venta);
                                    }
                                })
                                ->columnSpan(2),

                            TextInput::make('descripcion')
                                ->label('Descripción')
                                ->required()
                                ->maxLength(150)
                                ->columnSpan(2),

                            TextInput::make('precio_unitario')
                                ->label('Precio (Q)')
                                ->numeric()
                                ->prefix('Q')
                                ->required()
                                ->minValue(0)
                                ->columnSpan(1),

                            TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->integer()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->columnSpan(1),
                        ])
                        ->columns(7)
                        ->addActionLabel('Agregar ítem')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('empleado.usuario.nombre')
                    ->label('Empleado')
                    ->getStateUsing(fn (Venta $record): string => $record->empleado->nombre_completo ?? '—'),

                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->getStateUsing(fn (Venta $record): string => $record->cliente?->nombreCompleto() ?? 'Anónimo')
                    ->placeholder('Anónimo'),

                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Ítems')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('descuento')
                    ->label('Descuento')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('propina')
                    ->label('Propina')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total')
                    ->label('Total')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('metodo_pago')
                    ->label('Pago')
                    ->formatStateUsing(fn (string $state): string => Venta::metodoPagoLabel($state))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => Venta::estadoColor($state)),
            ])
            ->filters([
                SelectFilter::make('metodo_pago')
                    ->label('Método de pago')
                    ->options([
                        'efectivo'      => 'Efectivo',
                        'tarjeta'       => 'Tarjeta',
                        'transferencia' => 'Transferencia',
                        'otro'          => 'Otro',
                    ]),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'completada'  => 'Completada',
                        'cancelada'   => 'Cancelada',
                        'reembolsada' => 'Reembolsada',
                    ]),

                SelectFilter::make('empleado_id')
                    ->label('Empleado')
                    ->relationship('empleado.usuario', 'nombre'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVentas::route('/'),
            'create' => Pages\CreateVenta::route('/create'),
            'edit'   => Pages\EditVenta::route('/{record}/edit'),
        ];
    }
}
