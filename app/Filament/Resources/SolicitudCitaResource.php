<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\FiltraPorSucursal;
use App\Filament\Resources\SolicitudCitaResource\Pages;
use App\Models\Cita;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SolicitudCitaResource extends Resource
{
    use FiltraPorSucursal;

    protected static ?string $model = Cita::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static string|\UnitEnum|null $navigationGroup = 'Agenda';
    protected static ?string $navigationLabel = 'Solicitudes';
    protected static ?string $modelLabel = 'solicitud';
    protected static ?string $pluralModelLabel = 'Solicitudes';
    protected static ?int $navigationSort = -1;
    protected static ?string $slug = 'solicitudes';

    public static function getEloquentQuery(): Builder
    {
        // Solo las reservas hechas desde la web que siguen pendientes de revisar.
        return static::aplicarScopesSucursal(parent::getEloquentQuery())
            ->where('origen', 'enlace')
            ->where('estado', 'pendiente');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    public static function canCreate(): bool
    {
        // Las solicitudes solo llegan desde la página pública, no se crean a mano.
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Recibida')
                    ->since()
                    ->sortable(),

                TextColumn::make('fecha_hora')
                    ->label('Cita solicitada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->getStateUsing(fn (Cita $r): string => $r->cliente?->nombreCompleto() ?? '—')
                    ->description(fn (Cita $r): ?string => $r->cliente?->telefono)
                    ->searchable(query: fn (Builder $q, string $s) => $q->whereHas(
                        'cliente',
                        fn ($q) => $q->where('nombre', 'like', "%{$s}%")
                            ->orWhere('apellido', 'like', "%{$s}%")
                            ->orWhere('telefono', 'like', "%{$s}%")
                    )),

                TextColumn::make('empleado.nombre_completo')
                    ->label('Barbero')
                    ->getStateUsing(fn (Cita $r): string => $r->empleado?->nombre_completo ?? '—')
                    ->badge()
                    ->color('amber'),

                TextColumn::make('servicios')
                    ->label('Servicios')
                    ->getStateUsing(fn (Cita $r): string => $r->servicios->pluck('nombre')->implode(', ') ?: '—')
                    ->wrap(),

                TextColumn::make('total_estimado')
                    ->label('Total est.')
                    ->getStateUsing(fn (Cita $r): string => 'Q ' . number_format($r->totalServicios(), 2)),
            ])
            ->defaultSort('created_at', 'asc')
            ->recordActions([
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar solicitud')
                    ->modalDescription('La cita pasará a tu lista de Citas como confirmada.')
                    ->action(function (Cita $record): void {
                        $record->update(['estado' => 'confirmada']);

                        Notification::make()
                            ->title('Solicitud aprobada')
                            ->body('La cita ya está en tu lista de Citas.')
                            ->success()
                            ->send();
                    }),

                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar solicitud')
                    ->modalDescription('La solicitud se marcará como cancelada.')
                    ->action(function (Cita $record): void {
                        $record->update(['estado' => 'cancelada']);

                        Notification::make()
                            ->title('Solicitud rechazada')
                            ->warning()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Sin solicitudes pendientes')
            ->emptyStateDescription('Las reservas hechas desde la web aparecerán aquí para que las apruebes o rechaces.')
            ->emptyStateIcon('heroicon-o-inbox');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitudCitas::route('/'),
        ];
    }
}
