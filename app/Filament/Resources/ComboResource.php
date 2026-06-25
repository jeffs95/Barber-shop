<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComboResource\Pages;
use App\Models\Combo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

class ComboResource extends Resource
{
    protected static ?string $model = Combo::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';
    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';
    protected static ?string $navigationLabel = 'Combos';
    protected static ?string $modelLabel = 'combo';
    protected static ?string $pluralModelLabel = 'Combos';
    protected static ?int $navigationSort = 3;

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
                TextInput::make('nombre')
                    ->label('Nombre del combo')
                    ->required()
                    ->maxLength(100)
                    ->columnSpanFull(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('servicios')
                    ->label('Servicios incluidos')
                    ->relationship('servicios', 'nombre')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),
            ]),

            Section::make('Precio y vigencia')->schema([
                TextInput::make('precio')
                    ->label('Precio del combo (Q)')
                    ->numeric()
                    ->prefix('Q')
                    ->minValue(0)
                    ->required(),

                TextInput::make('porcentaje_descuento')
                    ->label('Descuento (%)')
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),

                DatePicker::make('fecha_inicio')
                    ->label('Válido desde')
                    ->placeholder('Sin restricción'),

                DatePicker::make('fecha_fin')
                    ->label('Válido hasta')
                    ->placeholder('Sin vencimiento')
                    ->afterOrEqual('fecha_inicio'),

                Toggle::make('es_activo')
                    ->label('Activo')
                    ->default(true)
                    ->inline(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Combo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('servicios_count')
                    ->label('Servicios')
                    ->counts('servicios')
                    ->badge()
                    ->color('amber'),

                TextColumn::make('precio')
                    ->label('Precio')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('porcentaje_descuento')
                    ->label('Descuento')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 0)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('fecha_fin')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->placeholder('Sin vencimiento'),

                IconColumn::make('es_activo')
                    ->label('Activo')
                    ->boolean(),
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
            'index'  => Pages\ListCombos::route('/'),
            'create' => Pages\CreateCombo::route('/create'),
            'edit'   => Pages\EditCombo::route('/{record}/edit'),
        ];
    }
}
