<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedorResource\Pages;
use App\Models\Proveedor;
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

class ProveedorResource extends Resource
{
    protected static ?string $model = Proveedor::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $slug = 'proveedores';

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $modelLabel = 'proveedor';

    protected static ?string $pluralModelLabel = 'proveedores';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información del proveedor')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre / Empresa')
                        ->required()
                        ->maxLength(120)
                        ->columnSpanFull(),

                    TextInput::make('contacto')
                        ->label('Persona de contacto')
                        ->maxLength(100),

                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->columnSpanFull(),

                    Textarea::make('notas')
                        ->label('Notas')
                        ->rows(3)
                        ->columnSpanFull(),

                    Toggle::make('es_activo')
                        ->label('Activo')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contacto')
                    ->label('Contacto')
                    ->placeholder('—'),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->placeholder('—'),

                TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('productos_count')
                    ->counts('productos')
                    ->label('Productos')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('es_activo')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index'  => Pages\ListProveedores::route('/'),
            'create' => Pages\CreateProveedor::route('/create'),
            'edit'   => Pages\EditProveedor::route('/{record}/edit'),
        ];
    }
}
