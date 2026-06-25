<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\FiltraPorSucursal;
use App\Filament\Resources\CajaResource\Pages;
use App\Models\Caja;
use App\Models\Usuario;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CajaResource extends Resource
{
    use FiltraPorSucursal;

    protected static ?string $model = Caja::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $slug = 'cajas';

    protected static ?string $navigationLabel = 'Cajas';

    protected static ?string $modelLabel = 'caja';

    protected static ?string $pluralModelLabel = 'cajas';

    protected static string|\UnitEnum|null $navigationGroup = 'Punto de Venta';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Apertura de caja')
                ->columns(2)
                ->schema([
                    Select::make('usuario_id')
                        ->label('Responsable')
                        ->options(Usuario::all()->mapWithKeys(fn ($u) => [$u->id => $u->nombre . ' ' . $u->apellido]))
                        ->searchable()
                        ->nullable(),

                    DateTimePicker::make('fecha_apertura')
                        ->label('Fecha y hora de apertura')
                        ->default(now())
                        ->required(),

                    TextInput::make('monto_inicial')
                        ->label('Monto inicial (Q)')
                        ->numeric()
                        ->prefix('Q')
                        ->default(0)
                        ->minValue(0)
                        ->required(),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'abierta' => 'Abierta',
                            'cerrada' => 'Cerrada',
                        ])
                        ->default('abierta')
                        ->required()
                        ->disabled(),
                ]),

            Section::make('Cierre de caja')
                ->columns(2)
                ->visible(fn (?Caja $record): bool => $record?->estado === 'cerrada' ?? false)
                ->schema([
                    DateTimePicker::make('fecha_cierre')
                        ->label('Fecha y hora de cierre'),

                    TextInput::make('monto_cierre_real')
                        ->label('Monto real al cerrar (Q)')
                        ->numeric()
                        ->prefix('Q'),

                    TextInput::make('monto_cierre_esperado')
                        ->label('Monto esperado (Q)')
                        ->numeric()
                        ->prefix('Q')
                        ->disabled(),

                    TextInput::make('diferencia')
                        ->label('Diferencia (Q)')
                        ->numeric()
                        ->prefix('Q')
                        ->disabled(),

                    Textarea::make('notas')
                        ->label('Notas del cierre')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('usuario.nombre')
                    ->label('Responsable')
                    ->formatStateUsing(fn ($record) => $record->usuario
                        ? $record->usuario->nombre . ' ' . $record->usuario->apellido
                        : 'Sistema')
                    ->placeholder('Sistema'),

                TextColumn::make('fecha_apertura')
                    ->label('Apertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('monto_inicial')
                    ->label('Inicial (Q)')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('total_ventas')
                    ->label('Ventas (Q)')
                    ->prefix('Q ')
                    ->getStateUsing(fn (Caja $record): string => number_format($record->totalVentas(), 2))
                    ->badge()
                    ->color('success'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'abierta' ? 'success' : 'gray'),

                TextColumn::make('fecha_cierre')
                    ->label('Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('diferencia')
                    ->label('Diferencia (Q)')
                    ->prefix('Q ')
                    ->numeric(decimalPlaces: 2)
                    ->color(fn (?float $state): string => match(true) {
                        $state === null => 'gray',
                        $state < 0     => 'danger',
                        $state > 0     => 'warning',
                        default        => 'success',
                    })
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Action::make('cerrar')
                    ->label('Cerrar caja')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (Caja $record): bool => $record->estaAbierta())
                    ->form([
                        TextInput::make('monto_real')
                            ->label('Monto real en caja (Q)')
                            ->numeric()
                            ->prefix('Q')
                            ->required()
                            ->minValue(0),
                        Textarea::make('notas')
                            ->label('Notas de cierre')
                            ->rows(2),
                    ])
                    ->action(function (Caja $record, array $data): void {
                        $record->cerrar(
                            montoReal: (float) $data['monto_real'],
                            notas: $data['notas'] ?? null,
                        );

                        Notification::make()
                            ->title('Caja cerrada')
                            ->body('Diferencia: Q ' . number_format($record->fresh()->diferencia, 2))
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Caja $record): bool => ! $record->estaAbierta()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCajas::route('/'),
            'create' => Pages\CreateCaja::route('/create'),
            'edit'   => Pages\EditCaja::route('/{record}/edit'),
        ];
    }
}
