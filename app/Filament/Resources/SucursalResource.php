<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SucursalResource\Pages;
use App\Models\Sucursal;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SucursalResource extends Resource
{
    protected static ?string $model = Sucursal::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $slug = 'sucursales';

    protected static ?string $navigationLabel = 'Sucursales';

    protected static ?string $modelLabel = 'sucursal';

    protected static ?string $pluralModelLabel = 'sucursales';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos de la sucursal')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(120)
                        ->columnSpanFull(),

                    TextInput::make('ciudad')
                        ->label('Ciudad')
                        ->maxLength(80),

                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('direccion')
                        ->label('Dirección')
                        ->maxLength(200)
                        ->columnSpanFull(),

                    Toggle::make('es_activa')
                        ->label('Activa')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ciudad')
                    ->label('Ciudad')
                    ->placeholder('—'),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->placeholder('—')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->placeholder('—'),

                TextColumn::make('empleados_count')
                    ->counts('empleados')
                    ->label('Empleados')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('es_activa')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->defaultSort('nombre')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSucursales::route('/'),
            'create' => Pages\CreateSucursal::route('/create'),
            'edit'   => Pages\EditSucursal::route('/{record}/edit'),
        ];
    }
}
