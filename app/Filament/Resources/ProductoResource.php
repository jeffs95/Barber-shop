<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers\StockSucursalesRelationManager;
use App\Models\Producto;
use App\Models\Proveedor;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'producto';

    protected static ?string $pluralModelLabel = 'productos';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = Producto::activo()->stockBajo()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Productos con stock bajo';
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        // Incluye los productos eliminados; el TrashedFilter decide si se muestran.
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información general')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre del producto')
                        ->required()
                        ->maxLength(120)
                        ->columnSpanFull(),

                    Select::make('proveedor_id')
                        ->label('Proveedor')
                        ->options(Proveedor::where('es_activo', true)->pluck('nombre', 'id'))
                        ->searchable()
                        ->nullable(),

                    Select::make('categoria')
                        ->label('Categoría')
                        ->options([
                            'cuidado_cabello' => 'Cuidado del cabello',
                            'barba'           => 'Barba',
                            'herramienta'     => 'Herramienta',
                            'consumible'      => 'Consumible',
                            'otro'            => 'Otro',
                        ])
                        ->default('otro')
                        ->required(),

                    TextInput::make('codigo_barras')
                        ->label('Código de barras')
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->hint('EAN, UPC o QR'),

                    Select::make('unidad')
                        ->label('Unidad')
                        ->options([
                            'unidad' => 'Unidad',
                            'ml'     => 'Mililitros (ml)',
                            'oz'     => 'Onzas (oz)',
                            'g'      => 'Gramos (g)',
                            'kg'     => 'Kilogramos (kg)',
                            'L'      => 'Litros (L)',
                        ])
                        ->default('unidad')
                        ->required(),

                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->rows(2)
                        ->columnSpanFull(),

                    Toggle::make('es_activo')
                        ->label('Activo')
                        ->default(true),
                ]),

            Section::make('Precios')
                ->columns(2)
                ->schema([
                    TextInput::make('precio_compra')
                        ->label('Precio de compra (Q)')
                        ->numeric()
                        ->prefix('Q')
                        ->minValue(0)
                        ->required(),

                    TextInput::make('precio_venta')
                        ->label('Precio de venta (Q)')
                        ->numeric()
                        ->prefix('Q')
                        ->minValue(0)
                        ->required(),
                ]),

            Section::make('Stock')
                ->columns(2)
                ->schema([
                    TextInput::make('stock_actual')
                        ->label('Stock inicial')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->hint('Usa Movimientos de Inventario para ajustar después de creado')
                        ->disabled(fn (string $operation): bool => $operation === 'edit'),

                    TextInput::make('stock_minimo')
                        ->label('Stock mínimo')
                        ->numeric()
                        ->minValue(0)
                        ->default(5)
                        ->required()
                        ->hint('Alerta cuando el stock llegue a este nivel'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('codigo_barras')
                    ->label('Código')
                    ->copyable()
                    ->copyMessage('Código copiado')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('categoria')
                    ->label('Categoría')
                    ->formatStateUsing(fn (string $state): string => Producto::categoriaLabel($state))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('precio_venta')
                    ->label('Precio venta')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('stock_actual')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (Producto $record): string => match($record->estado_stock) {
                        'sin_stock' => 'danger',
                        'bajo'      => 'warning',
                        default     => 'success',
                    })
                    ->suffix(fn (Producto $record): string => ' ' . $record->unidad)
                    ->sortable(),

                TextColumn::make('stock_minimo')
                    ->label('Mín.')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('es_activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('proveedor_id')
                    ->label('Proveedor')
                    ->relationship('proveedor', 'nombre'),

                SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->options([
                        'cuidado_cabello' => 'Cuidado del cabello',
                        'barba'           => 'Barba',
                        'herramienta'     => 'Herramienta',
                        'consumible'      => 'Consumible',
                        'otro'            => 'Otro',
                    ]),

                TernaryFilter::make('es_activo')
                    ->label('Estado')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),

                TrashedFilter::make(),
            ])
            ->defaultSort('nombre')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make()
                    ->visible(fn (): bool => auth()->user()?->esDuenio() ?? false),
                ForceDeleteAction::make()
                    ->visible(fn (): bool => auth()->user()?->esDuenio() ?? false),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StockSucursalesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit'   => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}
