<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\FiltraPorSucursal;
use App\Filament\Pages\PuntoVenta;
use App\Filament\Resources\CitaResource\Pages;
use App\Filament\Resources\CitaResource\RelationManagers\ServiciosRelationManager;
use App\Models\Cita;
use App\Models\Empleado;
use App\Models\Servicio;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CitaResource extends Resource
{
    use FiltraPorSucursal;

    protected static ?string $model = Cita::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null $navigationGroup = 'Agenda';
    protected static ?string $navigationLabel = 'Citas';
    protected static ?string $modelLabel = 'cita';
    protected static ?string $pluralModelLabel = 'Citas';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()
            ->whereDate('fecha_hora', now()->toDateString())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        // Las solicitudes online sin procesar viven en su propio apartado
        // (SolicitudCitaResource); aquí solo se ven las citas ya gestionadas.
        return static::aplicarScopesSucursal(parent::getEloquentQuery())
            ->whereNot(fn (Builder $q) => $q->where('origen', 'enlace')->where('estado', 'pendiente'));
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cliente y barbero')->schema([
                Select::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombreCompleto())
                    ->searchable(['nombre', 'apellido', 'telefono'])
                    ->placeholder('Walk-in (sin registro)')
                    ->nullable()
                    ->preload(false)
                    ->createOptionForm([
                        TextInput::make('nombre')->required()->maxLength(100),
                        TextInput::make('apellido')->maxLength(100),
                        TextInput::make('telefono')->maxLength(20),
                    ]),

                Select::make('empleado_id')
                    ->label('Barbero')
                    ->options(
                        Empleado::with('usuario')
                            ->where('rol', 'barbero')
                            ->where('es_activo', true)
                            ->get()
                            ->mapWithKeys(fn ($e) => [$e->id => $e->nombre_completo])
                    )
                    ->searchable()
                    ->required(),
            ])->columns(2),

            Section::make('Fecha, hora y estado')->schema([
                DateTimePicker::make('fecha_hora')
                    ->label('Fecha y hora')
                    ->required()
                    ->seconds(false)
                    ->minutesStep(5)
                    ->rule(static function (Get $get, ?Cita $record): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $record): void {
                            $empleadoId = (int) $get('empleado_id');
                            $duracion   = (int) $get('duracion_estimada_min') ?: 30;

                            if (! $empleadoId || ! $value) {
                                return;
                            }

                            if (Cita::haySolapamiento($empleadoId, Carbon::parse($value), $duracion, $record?->id)) {
                                $fail('El barbero ya tiene una cita activa que se traslapa con ese horario.');
                            }
                        };
                    }),

                TextInput::make('duracion_estimada_min')
                    ->label('Duración estimada (min)')
                    ->numeric()
                    ->suffix('min')
                    ->default(30)
                    ->minValue(5)
                    ->required(),

                Select::make('estado')
                    ->label('Estado')
                    ->options(Cita::estadoOpciones())
                    ->default('pendiente')
                    ->required(),

                Select::make('origen')
                    ->label('Origen')
                    ->options([
                        'presencial' => 'Presencial',
                        'enlace'     => 'Enlace / Online',
                        'telefono'   => 'Teléfono',
                    ])
                    ->default('presencial')
                    ->required(),
            ])->columns(2),

            Section::make('Servicios de la cita')
                ->description('Selecciona qué servicios se realizarán — puedes agregar más desde la cita después')
                ->schema([
                    Repeater::make('servicios_form')
                        ->hiddenLabel()
                        ->schema([
                            Select::make('servicio_id')
                                ->label('Servicio')
                                ->options(fn () => Servicio::where('es_activo', true)
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (string|null $state, callable $set) {
                                    $servicio = $state ? Servicio::find($state) : null;
                                    $set('precio', (float) ($servicio?->precio_base ?? 0));
                                }),

                            TextInput::make('precio')
                                ->label('Precio (Q)')
                                ->prefix('Q')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('+ Agregar servicio')
                        ->defaultItems(0)
                        ->columnSpanFull()
                        ->reorderable(false),
                ])
                ->visibleOn('create'),

            Section::make('Notas')->schema([
                Textarea::make('notas')
                    ->label('Notas de la cita')
                    ->rows(3)
                    ->placeholder('Observaciones generales...'),

                Textarea::make('notas_barbero')
                    ->label('Notas del barbero')
                    ->rows(3)
                    ->placeholder('Qué se hizo, preferencias del cliente, próximas recomendaciones...'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_hora')
                    ->label('Fecha / Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Cita $r) => $r->hora_fin->format('H:i') . ' (fin estimado)'),

                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->getStateUsing(fn (Cita $r) => $r->cliente?->nombreCompleto() ?? '— Walk-in —')
                    ->searchable(query: fn (Builder $q, string $s) =>
                        $q->whereHas('cliente', fn ($q) => $q->where('nombre', 'like', "%{$s}%")
                            ->orWhere('apellido', 'like', "%{$s}%"))
                    ),

                TextColumn::make('empleado.nombre_completo')
                    ->label('Barbero')
                    ->getStateUsing(fn (Cita $r) => $r->empleado->nombre_completo)
                    ->badge()
                    ->color('amber'),

                TextColumn::make('servicios_count')
                    ->label('Servicios')
                    ->counts('servicios')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('duracion_estimada_min')
                    ->label('Duración')
                    ->suffix(' min')
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => Cita::estadoColor($state))
                    ->formatStateUsing(fn (string $state) => Cita::estadoLabel($state)),

                TextColumn::make('origen')
                    ->label('Origen')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'presencial' => 'Presencial',
                        'enlace'     => 'Online',
                        'telefono'   => 'Teléfono',
                        default      => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha_hora', 'asc')
            ->filters([
                SelectFilter::make('empleado_id')
                    ->label('Barbero')
                    ->options(
                        Empleado::with('usuario')
                            ->where('rol', 'barbero')
                            ->where('es_activo', true)
                            ->get()
                            ->mapWithKeys(fn ($e) => [$e->id => $e->nombre_completo])
                    ),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(Cita::estadoOpciones()),

                Filter::make('hoy')
                    ->label('Solo hoy')
                    ->query(fn (Builder $q) => $q->whereDate('fecha_hora', now()->toDateString()))
                    ->toggle(),

                Filter::make('activas')
                    ->label('Activas')
                    ->query(fn (Builder $q) => $q->whereIn('estado', ['pendiente', 'confirmada', 'en_proceso']))
                    ->toggle(),
            ])
            ->actions([
                Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Cita $r) => $r->estado === 'pendiente')
                    ->action(fn (Cita $r) => $r->update(['estado' => 'confirmada'])),

                Action::make('iniciar')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn (Cita $r) => $r->estado === 'confirmada')
                    ->action(fn (Cita $r) => $r->update(['estado' => 'en_proceso'])),

                Action::make('cobrar')
                    ->label('Cobrar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Cita $r) => $r->estado === 'en_proceso')
                    ->url(fn (Cita $r) => PuntoVenta::getUrl() . '?cita_id=' . $r->id),

                Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Cita $r) => $r->estaActiva())
                    ->action(fn (Cita $r) => $r->update(['estado' => 'cancelada'])),

                EditAction::make()->label(''),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ServiciosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCitas::route('/'),
            'create' => Pages\CreateCita::route('/create'),
            'edit'   => Pages\EditCita::route('/{record}/edit'),
        ];
    }
}
