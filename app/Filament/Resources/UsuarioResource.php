<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsuarioResource\Pages;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Usuario;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsuarioResource extends Resource
{
    protected static ?string $model = Usuario::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $modelLabel = 'usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información de la cuenta')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('apellido')
                        ->label('Apellido')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->maxLength(191)
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),
                ]),

            Section::make('Contraseña')
                ->columns(1)
                ->schema([
                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->minLength(8)
                        ->maxLength(100)
                        ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->hint(fn (string $operation) => $operation === 'edit' ? 'Deja en blanco para no cambiar.' : null),
                ]),

            Section::make('Rol y acceso')
                ->columns(2)
                ->schema([
                    Select::make('rol_id')
                        ->label('Rol')
                        ->options(Rol::where('es_activo', true)->pluck('nombre', 'id'))
                        ->searchable()
                        ->required(),

                    Select::make('sucursal_id')
                        ->label('Sucursal asignada')
                        ->options(Sucursal::where('es_activa', true)->pluck('nombre', 'id'))
                        ->searchable()
                        ->nullable()
                        ->placeholder('Sin restricción (acceso global)'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre completo')
                    ->formatStateUsing(fn ($record) => trim("{$record->nombre} {$record->apellido}"))
                    ->searchable(['nombre', 'apellido'])
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('rol.nombre')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'dueño'          => 'danger',
                        'admin_sucursal' => 'warning',
                        'recepcionista'  => 'info',
                        'barbero'        => 'success',
                        default          => 'gray',
                    }),

                TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->badge()
                    ->color('primary')
                    ->default('— Global —'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nombre')
            ->filters([
                SelectFilter::make('rol_id')
                    ->label('Rol')
                    ->options(Rol::pluck('nombre', 'id')),

                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(Sucursal::pluck('nombre', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Usuario $record): bool => $record->id !== auth()->id()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsuarios::route('/'),
            'create' => Pages\CreateUsuario::route('/create'),
            'edit'   => Pages\EditUsuario::route('/{record}/edit'),
        ];
    }
}
