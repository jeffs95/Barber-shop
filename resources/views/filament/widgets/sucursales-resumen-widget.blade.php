<x-filament-widgets::widget>
    <div class="p-2">

        @if ($this->sucursales->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                <x-filament::icon icon="heroicon-o-building-storefront" class="w-12 h-12 mb-3" />
                <p class="text-sm">No hay sucursales registradas.</p>
                <a href="{{ route('filament.admin.resources.sucursales.create') }}"
                   class="mt-3 text-sm text-amber-600 hover:text-amber-700 font-medium underline">
                    Crear primera sucursal
                </a>
            </div>

        @elseif ($this->sucursales->count() === 1)
            {{-- ── Vista sucursal única ────────────────────────────────── --}}
            @php $s = $this->sucursales->first(); @endphp

            <div class="flex flex-col sm:flex-row sm:items-center gap-4
                        bg-white dark:bg-gray-800
                        rounded-2xl border border-gray-200 dark:border-gray-700
                        shadow-sm px-5 py-4">

                {{-- Info sucursal + estado de caja --}}
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30
                                flex items-center justify-center shrink-0">
                        <x-filament::icon icon="heroicon-o-building-storefront"
                            class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-gray-900 dark:text-white text-base leading-tight">
                                {{ $s['nombre'] }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                                         {{ $s['caja_abierta']
                                             ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                             : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $s['caja_abierta'] ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                {{ $s['caja_abierta'] ? 'Caja abierta' : 'Caja cerrada' }}
                            </span>
                        </div>
                        @if ($s['ciudad'] || $s['telefono'])
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate">
                                @if ($s['ciudad']) {{ $s['ciudad'] }} @endif
                                @if ($s['ciudad'] && $s['telefono']) &nbsp;·&nbsp; @endif
                                @if ($s['telefono']) {{ $s['telefono'] }} @endif
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Stats del día --}}
                <div class="flex divide-x divide-gray-200 dark:divide-gray-700 sm:border-l sm:border-gray-200 sm:dark:border-gray-700 sm:pl-4 gap-0">
                    <div class="flex flex-col items-center px-5 py-1">
                        <span class="text-2xl font-bold text-amber-600 dark:text-amber-400 leading-none">
                            Q {{ number_format($s['ventas_hoy'], 0) }}
                        </span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Ventas hoy</span>
                    </div>
                    <div class="flex flex-col items-center px-5 py-1">
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400 leading-none">
                            {{ $s['citas_hoy'] }}
                        </span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Citas hoy</span>
                    </div>
                    <div class="flex flex-col items-center px-5 py-1">
                        <span class="text-2xl font-bold text-gray-700 dark:text-gray-300 leading-none">
                            {{ $s['empleados'] }}
                        </span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Empleados</span>
                    </div>
                </div>

                {{-- Acciones rápidas --}}
                <div class="flex gap-2 sm:pl-2">
                    <a href="{{ route('filament.admin.resources.citas.index') }}"
                       class="flex items-center gap-1.5 text-xs font-medium py-2 px-3 rounded-lg
                              bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300
                              hover:bg-amber-100 dark:hover:bg-amber-900/20 hover:text-amber-700
                              transition-colors whitespace-nowrap">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="w-4 h-4" />
                        Agenda
                    </a>
                    <a href="{{ route('filament.admin.pages.pos') }}"
                       class="flex items-center gap-1.5 text-xs font-medium py-2 px-3 rounded-lg
                              bg-amber-500 text-white hover:bg-amber-600 transition-colors whitespace-nowrap">
                        <x-filament::icon icon="heroicon-o-banknotes" class="w-4 h-4" />
                        POS
                    </a>
                </div>
            </div>

        @else
            {{-- ── Vista multi-sucursal (dueño) ──────────────────────── --}}
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-building-storefront" class="w-6 h-6 text-amber-500" />
                Mis Sucursales
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($this->sucursales as $sucursal)
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden hover:shadow-md transition-shadow">

                        <div class="px-5 pt-5 pb-4 flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white truncate">
                                    {{ $sucursal['nombre'] }}
                                </h3>
                                @if ($sucursal['ciudad'])
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1">
                                        <x-filament::icon icon="heroicon-o-map-pin" class="w-3.5 h-3.5 shrink-0" />
                                        {{ $sucursal['ciudad'] }}
                                        @if ($sucursal['direccion'])
                                            &nbsp;·&nbsp;{{ Str::limit($sucursal['direccion'], 30) }}
                                        @endif
                                    </p>
                                @endif
                                @if ($sucursal['telefono'])
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                        {{ $sucursal['telefono'] }}
                                    </p>
                                @endif
                            </div>
                            <span class="shrink-0 ml-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                         {{ $sucursal['caja_abierta']
                                             ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                             : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sucursal['caja_abierta'] ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                {{ $sucursal['caja_abierta'] ? 'Caja abierta' : 'Caja cerrada' }}
                            </span>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 mx-4"></div>

                        <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700 px-0 py-3">
                            <div class="flex flex-col items-center py-1 px-3">
                                <span class="text-xl font-bold text-amber-600 dark:text-amber-400">
                                    Q {{ number_format($sucursal['ventas_hoy'], 0) }}
                                </span>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Ventas hoy</span>
                            </div>
                            <div class="flex flex-col items-center py-1 px-3">
                                <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $sucursal['citas_hoy'] }}
                                </span>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Citas hoy</span>
                            </div>
                            <div class="flex flex-col items-center py-1 px-3">
                                <span class="text-xl font-bold text-gray-700 dark:text-gray-300">
                                    {{ $sucursal['empleados'] }}
                                </span>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Empleados</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 px-4 py-3 flex gap-2">
                            <a href="{{ route('filament.admin.resources.citas.index') }}?tableFilters[sucursal_id][value]={{ $sucursal['id'] }}"
                               class="flex-1 text-center text-xs font-medium py-1.5 px-2 rounded-lg
                                      bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300
                                      hover:bg-amber-100 dark:hover:bg-amber-900/20 hover:text-amber-700
                                      transition-colors">
                                Ver agenda
                            </a>
                            <a href="{{ route('filament.admin.pages.pos') }}"
                               class="flex-1 text-center text-xs font-medium py-1.5 px-2 rounded-lg
                                      bg-amber-500 text-white hover:bg-amber-600 transition-colors">
                                Ir al POS
                            </a>
                            <a href="{{ route('filament.admin.resources.sucursales.edit', $sucursal['id']) }}"
                               class="text-xs font-medium py-1.5 px-2 rounded-lg
                                      bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400
                                      hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                <x-filament::icon icon="heroicon-o-pencil-square" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-filament-widgets::widget>
