<x-filament-panels::page>

    {{-- ── Filtros ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-3 items-end p-4
                bg-white dark:bg-gray-800
                border border-gray-200 dark:border-gray-700
                rounded-xl shadow-sm">

        <div class="flex-1 min-w-32">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Mes</label>
            <select wire:model.live="mes"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm shadow-sm">
                @foreach($this->meses as $num => $nombre)
                    <option value="{{ $num }}">{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-24">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Año</label>
            <select wire:model.live="anio"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm shadow-sm">
                @foreach($this->anios as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-40">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sucursal</label>
            <select wire:model.live="sucursalId"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm shadow-sm">
                <option value="">Todas las sucursales</option>
                @foreach($this->sucursales as $s)
                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ── Totales ─────────────────────────────────────────────────────────── --}}
    @if($this->comisiones->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Empleados activos</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $this->comisiones->count() }}</p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Base de cálculo</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">Q {{ number_format($this->totalBase, 2) }}</p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 rounded-xl shadow-sm bg-green-50 dark:bg-green-900/20">
            <p class="text-xs font-medium text-green-700 dark:text-green-400 uppercase tracking-wide">Total a pagar en comisiones</p>
            <p class="mt-1 text-2xl font-bold text-green-700 dark:text-green-400">Q {{ number_format($this->totalComisiones, 2) }}</p>
        </div>
    </div>
    @endif

    {{-- ── Tabla ───────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">

        @if($this->comisiones->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 gap-3 text-gray-500">
                <x-filament::icon icon="heroicon-o-currency-dollar" class="h-12 w-12 text-gray-300" />
                <p class="text-sm">No hay comisiones para el período seleccionado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Empleado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sucursal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Citas</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Servicios</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Ventas POS</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Base</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">%</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-amber-600 uppercase tracking-wider">Comisión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($this->comisiones as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $row['empleado'] }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $row['sucursal'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ $row['citas_completadas'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">Q {{ number_format($row['total_servicios'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">Q {{ number_format($row['total_ventas'], 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Q {{ number_format($row['base'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $row['porcentaje'] }}%</td>
                            <td class="px-4 py-3 text-right font-bold text-amber-600">Q {{ number_format($row['comision'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-900/50 border-t-2 border-gray-200 dark:border-gray-600">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Totales</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">Q {{ number_format($this->totalBase, 2) }}</td>
                            <td></td>
                            <td class="px-4 py-3 text-right font-bold text-amber-600">Q {{ number_format($this->totalComisiones, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

</x-filament-panels::page>
