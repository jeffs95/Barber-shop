<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicioResource\Pages;
use App\Filament\Resources\ServicioResource\RelationManagers\PreciosEmpleadoRelationManager;
use App\Models\Servicio;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
use Filament\Tables\Table;

class ServicioResource extends Resource
{
    protected static ?string $model = Servicio::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scissors';
    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';
    protected static ?string $navigationLabel = 'Servicios';
    protected static ?string $modelLabel = 'servicio';
    protected static ?string $pluralModelLabel = 'Servicios';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('es_activo', true)->count();
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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información general')->schema([
                Select::make('categoria_servicio_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('nombre')
                    ->label('Nombre del servicio')
                    ->required()
                    ->maxLength(100),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Precio y duración')->schema([
                TextInput::make('precio_base')
                    ->label('Precio base (Q)')
                    ->numeric()
                    ->prefix('Q')
                    ->minValue(0)
                    ->required(),

                TextInput::make('duracion_minutos')
                    ->label('Duración (minutos)')
                    ->numeric()
                    ->suffix('min')
                    ->minValue(5)
                    ->required(),

                Toggle::make('es_activo')
                    ->label('Activo')
                    ->default(true)
                    ->inline(false),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->color('amber')
                    ->sortable(),

                TextColumn::make('precio_base')
                    ->label('Precio base')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('duracion_minutos')
                    ->label('Duración')
                    ->suffix(' min')
                    ->sortable(),

                IconColumn::make('es_activo')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categoria_servicio_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),

                TernaryFilter::make('es_activo')->label('Estado'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nombre');
    }

    public static function getRelations(): array
    {
        return [
            PreciosEmpleadoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServicios::route('/'),
            'create' => Pages\CreateServicio::route('/create'),
            'edit'   => Pages\EditServicio::route('/{record}/edit'),
        ];
    }
}
