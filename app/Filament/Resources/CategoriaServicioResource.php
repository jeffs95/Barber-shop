<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaServicioResource\Pages;
use App\Models\CategoriaServicio;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoriaServicioResource extends Resource
{
    protected static ?string $model = CategoriaServicio::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';
    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';
    protected static ?string $navigationLabel = 'Categorías';
    protected static ?string $modelLabel = 'categoría';
    protected static ?string $pluralModelLabel = 'Categorías';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(80)
                ->unique(ignoreRecord: true)
                ->columnSpanFull(),

            Textarea::make('descripcion')
                ->label('Descripción')
                ->rows(3)
                ->columnSpanFull(),

            Toggle::make('es_activo')
                ->label('Activa')
                ->default(true)
                ->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('servicios_count')
                    ->label('Servicios')
                    ->counts('servicios')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->placeholder('—'),

                IconColumn::make('es_activo')
                    ->label('Activa')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategoriaServicios::route('/'),
            'create' => Pages\CreateCategoriaServicio::route('/create'),
            'edit'   => Pages\EditCategoriaServicio::route('/{record}/edit'),
        ];
    }
}
