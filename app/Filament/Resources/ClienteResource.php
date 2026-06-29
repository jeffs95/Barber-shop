<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers\CitasRelationManager;
use App\Models\Cliente;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $slug = 'clientes';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $modelLabel = 'cliente';

    protected static ?string $pluralModelLabel = 'clientes';

    protected static string|\UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public static function getEloquentQuery(): Builder
    {
        // Incluye los clientes eliminados; el TrashedFilter decide si se muestran.
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del cliente')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('apellido')
                        ->label('Apellido')
                        ->maxLength(100),

                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true)
                        ->hint('Se usa para identificar al cliente'),

                    TextInput::make('email')
                        ->label('Correo')
                        ->email()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    DatePicker::make('fecha_nacimiento')
                        ->label('Fecha de nacimiento')
                        ->displayFormat('d/m/Y')
                        ->maxDate(now()),

                    Select::make('tipo')
                        ->label('Tipo de cliente')
                        ->options([
                            'nuevo'     => 'Nuevo',
                            'regular'   => 'Regular',
                            'frecuente' => 'Frecuente',
                            'vip'       => 'VIP',
                        ])
                        ->default('nuevo')
                        ->required(),
                ]),

            Section::make('Fidelidad y notas')
                ->columns(2)
                ->schema([
                    TextInput::make('puntos_fidelidad')
                        ->label('Puntos de fidelidad')
                        ->numeric()
                        ->minValue(0)
                        ->default(0),

                    Textarea::make('notas')
                        ->label('Notas / preferencias')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Cliente')
                    ->getStateUsing(fn (Cliente $record): string => $record->nombreCompleto())
                    ->searchable(['nombre', 'apellido'])
                    ->sortable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Teléfono copiado')
                    ->placeholder('—'),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vip'       => 'warning',
                        'frecuente' => 'success',
                        'regular'   => 'info',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('citas_count')
                    ->label('Citas')
                    ->counts('citas')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('puntos_fidelidad')
                    ->label('Puntos')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nombre')
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo de cliente')
                    ->options([
                        'nuevo'     => 'Nuevo',
                        'regular'   => 'Regular',
                        'frecuente' => 'Frecuente',
                        'vip'       => 'VIP',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
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
            CitasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit'   => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
