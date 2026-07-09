<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\FiltraPorSucursal;
use App\Filament\Resources\EmpleadoResource\Pages;
use App\Filament\Resources\EmpleadoResource\RelationManagers\HorariosRelationManager;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\Usuario;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmpleadoResource extends Resource
{
    use FiltraPorSucursal;

    protected static ?string $model = Empleado::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $slug = 'empleados';

    protected static ?string $navigationLabel = 'Empleados';

    protected static ?string $modelLabel = 'empleado';

    protected static ?string $pluralModelLabel = 'empleados';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del empleado')
                ->columns(2)
                ->schema([
                    Select::make('usuario_id')
                        ->label('Usuario')
                        ->options(Usuario::all()->mapWithKeys(fn ($u) => [$u->id => $u->nombre . ' ' . $u->apellido . ' — ' . $u->email]))
                        ->searchable()
                        ->required()
                        ->columnSpanFull(),

                    Select::make('sucursal_id')
                        ->label('Sucursal')
                        ->options(Sucursal::where('es_activa', true)->pluck('nombre', 'id'))
                        ->searchable()
                        ->nullable(),

                    Select::make('rol')
                        ->label('Rol en el negocio')
                        ->options([
                            'barbero'        => 'Barbero',
                            'recepcionista'  => 'Recepcionista',
                            'administrador'  => 'Administrador',
                        ])
                        ->required(),

                    TextInput::make('porcentaje_comision')
                        ->label('Comisión (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->default(0),

                    ColorPicker::make('color_agenda')
                        ->label('Color en agenda'),

                    Toggle::make('es_activo')
                        ->label('Activo')
                        ->default(true)
                        ->columnSpanFull(),
                ]),

            Section::make('Foto de perfil')
                ->description('La foto se sube al servidor FTP al guardar. Si el FTP no está disponible se notificará el error.')
                ->schema([
                    FileUpload::make('foto')
                        ->label('Fotografía')
                        ->disk('public')
                        ->directory('empleados')
                        ->image()
                        ->imagePreviewHeight('180')
                        ->maxSize(3072)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->nullable()
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->columnSpanFull(),
                ]),

            Section::make('Datos personales')
                ->columns(2)
                ->schema([
                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20),

                    DatePicker::make('fecha_nacimiento')
                        ->label('Fecha de nacimiento')
                        ->displayFormat('d/m/Y')
                        ->maxDate(now()->subYears(16)),

                    TextInput::make('direccion')
                        ->label('Dirección')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),

            Section::make('Información laboral')
                ->columns(2)
                ->schema([
                    TextInput::make('sueldo_base')
                        ->label('Sueldo base mensual')
                        ->numeric()
                        ->prefix('Q')
                        ->minValue(0)
                        ->default(0),

                    DatePicker::make('fecha_contratacion')
                        ->label('Fecha de contratación')
                        ->displayFormat('d/m/Y')
                        ->maxDate(now()),

                    Select::make('tipo_contrato')
                        ->label('Tipo de contrato')
                        ->options([
                            'tiempo_completo' => 'Tiempo completo',
                            'medio_tiempo'    => 'Medio tiempo',
                            'por_servicio'    => 'Por servicio',
                        ])
                        ->default('tiempo_completo')
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('usuario.nombre')
                    ->label('Nombre')
                    ->formatStateUsing(fn ($record) => $record->nombreCompleto)
                    ->searchable(['usuario.nombre', 'usuario.apellido'])
                    ->sortable(),

                TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('rol')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'barbero'       => 'warning',
                        'recepcionista' => 'info',
                        'administrador' => 'success',
                        default         => 'gray',
                    }),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sueldo_base')
                    ->label('Sueldo')
                    ->money('GTQ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tipo_contrato')
                    ->label('Contrato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tiempo_completo' => 'success',
                        'medio_tiempo'    => 'warning',
                        'por_servicio'    => 'info',
                        default           => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tiempo_completo' => 'Tiempo completo',
                        'medio_tiempo'    => 'Medio tiempo',
                        'por_servicio'    => 'Por servicio',
                        default           => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('porcentaje_comision')
                    ->label('Comisión')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 0),

                ColorColumn::make('color_agenda')
                    ->label('Color'),

                IconColumn::make('es_activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(Sucursal::pluck('nombre', 'id')),

                SelectFilter::make('rol')
                    ->options([
                        'barbero'       => 'Barbero',
                        'recepcionista' => 'Recepcionista',
                        'administrador' => 'Administrador',
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
            HorariosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmpleados::route('/'),
            'create' => Pages\CreateEmpleado::route('/create'),
            'edit'   => Pages\EditEmpleado::route('/{record}/edit'),
        ];
    }
}
