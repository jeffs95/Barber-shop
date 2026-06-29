<x-filament-panels::page>
    {{-- Selector de sucursal — solo para el dueño / acceso global --}}
    @if ($this->puedeElegirSucursal)
        <div class="flex items-center gap-3 px-4 py-3
                    bg-white dark:bg-gray-800
                    border border-gray-200 dark:border-gray-700
                    rounded-xl shadow-sm">
            <x-filament::icon icon="heroicon-o-building-storefront" class="h-5 w-5 text-amber-600 shrink-0" />
            <label class="text-sm font-medium text-gray-600 dark:text-gray-300 shrink-0">Sucursal:</label>
            <select wire:model.live="sucursalId"
                    class="rounded-lg border-gray-300 dark:border-gray-600
                           dark:bg-gray-700 dark:text-white text-sm shadow-sm
                           focus:ring-amber-500 focus:border-amber-500">
                @foreach ($this->sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                @endforeach
            </select>
            @if ($this->caja)
                <span class="ml-auto text-xs text-green-600 font-medium flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Caja abierta
                </span>
            @else
                <span class="ml-auto text-xs text-red-500 font-medium flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span> Sin caja
                </span>
            @endif
        </div>
    @endif

    @if (! $this->cajaId)
        {{-- Sin caja abierta --}}
        <div class="flex flex-col items-center justify-center py-16 gap-4">
            <x-filament::icon icon="heroicon-o-lock-closed" class="h-16 w-16 text-gray-400" />
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">No hay caja abierta</h2>
            <p class="text-gray-500">Abre una caja para empezar a registrar ventas.</p>
            <x-filament::button
                href="{{ route('filament.admin.resources.cajas.create') }}"
                tag="a"
                icon="heroicon-o-plus">
                Abrir caja ahora
            </x-filament::button>
        </div>
    @else

        {{-- Banner cuando el POS viene desde una cita --}}
        @if ($this->citaActiva)
            <div class="flex items-center gap-3 px-4 py-3
                        bg-amber-50 dark:bg-amber-900/20
                        border border-amber-200 dark:border-amber-700
                        rounded-xl">
                <x-filament::icon icon="heroicon-o-calendar-days"
                    class="h-5 w-5 text-amber-600 shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                        Cobrando Cita #{{ $this->citaActiva->id }}
                    </p>
                    <p class="text-xs text-amber-600 dark:text-amber-400">
                        Cliente: {{ $this->citaActiva->cliente?->nombreCompleto() ?? 'Walk-in' }}
                        &nbsp;·&nbsp;
                        Barbero: {{ $this->citaActiva->empleado?->nombre_completo ?? '—' }}
                    </p>
                </div>
                <a href="{{ \App\Filament\Resources\CitaResource::getUrl('index') }}"
                   class="text-xs text-amber-700 dark:text-amber-400 hover:underline shrink-0">
                    ← Volver a citas
                </a>
            </div>
        @endif

        <div class="flex flex-col xl:flex-row gap-4 min-h-[80vh]">

            {{-- ══════════════════════════════════════════════
                 PANEL IZQUIERDO: Selector de ítems
            ══════════════════════════════════════════════ --}}
            <div class="flex-1 flex flex-col gap-4">

                {{-- Empleado y Cliente --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4
                            bg-white dark:bg-gray-800
                            border border-gray-200 dark:border-gray-700
                            rounded-xl shadow-sm">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                            Empleado <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="empleadoId"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-white text-sm shadow-sm
                                       focus:ring-amber-500 focus:border-amber-500">
                            <option value="">— Seleccionar empleado —</option>
                            @foreach ($this->empleados as $empleado)
                                <option value="{{ $empleado->id }}">{{ $empleado->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                            Cliente (opcional)
                        </label>
                        <select wire:model.live="clienteId"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600
                                       dark:bg-gray-700 dark:text-white text-sm shadow-sm
                                       focus:ring-amber-500 focus:border-amber-500">
                            <option value="">— Sin cliente (walk-in) —</option>
                            @foreach ($this->clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre }} {{ $cliente->apellido }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tabs Servicios / Productos --}}
                <div class="flex gap-1 p-1 bg-gray-100 dark:bg-gray-700 rounded-xl w-fit">
                    <button wire:click="$set('activeTab', 'servicios')"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                                   {{ $activeTab === 'servicios'
                                       ? 'bg-white dark:bg-gray-800 text-amber-600 shadow-sm'
                                       : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        Servicios
                    </button>
                    <button wire:click="$set('activeTab', 'productos')"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                                   {{ $activeTab === 'productos'
                                       ? 'bg-white dark:bg-gray-800 text-amber-600 shadow-sm'
                                       : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        Productos
                    </button>
                </div>

                {{-- Grid de Servicios --}}
                @if ($activeTab === 'servicios')
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach ($this->servicios as $servicio)
                            <button wire:click="agregarServicio({{ $servicio->id }})"
                                    class="group p-3 text-left rounded-xl border transition-all
                                           bg-white dark:bg-gray-800
                                           border-gray-200 dark:border-gray-700
                                           hover:border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20
                                           hover:shadow-md active:scale-95">
                                <div class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">
                                    {{ $servicio->categoria->nombre ?? '' }}
                                </div>
                                <div class="font-semibold text-sm text-gray-900 dark:text-white leading-tight">
                                    {{ $servicio->nombre }}
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-amber-600 dark:text-amber-400 font-bold text-sm">
                                        Q {{ number_format($servicio->precio_base, 2) }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ $servicio->duracion_minutos }} min
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Grid de Productos --}}
                @if ($activeTab === 'productos')
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach ($this->productos as $producto)
                            <button wire:click="agregarProducto({{ $producto->id }})"
                                    class="group p-3 text-left rounded-xl border transition-all
                                           bg-white dark:bg-gray-800
                                           border-gray-200 dark:border-gray-700
                                           {{ $producto->stock_actual <= 0
                                               ? 'opacity-50 cursor-not-allowed'
                                               : 'hover:border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 hover:shadow-md active:scale-95' }}"
                                    @if ($producto->stock_actual <= 0) disabled @endif>
                                <div class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">
                                    {{ \App\Models\Producto::categoriaLabel($producto->categoria) }}
                                </div>
                                <div class="font-semibold text-sm text-gray-900 dark:text-white leading-tight">
                                    {{ $producto->nombre }}
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-amber-600 dark:text-amber-400 font-bold text-sm">
                                        Q {{ number_format($producto->precio_venta, 2) }}
                                    </span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium
                                                {{ $producto->stock_actual <= 0
                                                    ? 'bg-red-100 text-red-600'
                                                    : ($producto->stock_actual <= $producto->stock_minimo
                                                        ? 'bg-yellow-100 text-yellow-700'
                                                        : 'bg-green-100 text-green-700') }}">
                                        {{ $producto->stock_actual }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ══════════════════════════════════════════════
                 PANEL DERECHO: Carrito y cobro
            ══════════════════════════════════════════════ --}}
            <div class="w-full xl:w-96 flex flex-col gap-3">

                {{-- Ítems del carrito --}}
                <div class="flex-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                            rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Carrito</h3>
                        @if (count($items) > 0)
                            <button wire:click="limpiarCarrito"
                                    class="text-xs text-red-500 hover:text-red-700 transition-colors">
                                Limpiar
                            </button>
                        @endif
                    </div>

                    @if (empty($items))
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <x-filament::icon icon="heroicon-o-shopping-cart" class="h-10 w-10 mb-2" />
                            <p class="text-sm">Carrito vacío</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-y-auto">
                            @foreach ($items as $index => $item)
                                <div class="flex items-center gap-3 px-4 py-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $item['nombre'] }}
                                        </p>
                                        <p class="text-xs text-amber-600">
                                            Q {{ number_format($item['precio'], 2) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button wire:click="decrementar({{ $index }})"
                                                class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700
                                                       flex items-center justify-center text-gray-600
                                                       hover:bg-amber-100 hover:text-amber-700 transition-colors text-sm font-bold">
                                            −
                                        </button>
                                        <span class="w-6 text-center text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $item['cantidad'] }}
                                        </span>
                                        <button wire:click="incrementar({{ $index }})"
                                                class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700
                                                       flex items-center justify-center text-gray-600
                                                       hover:bg-amber-100 hover:text-amber-700 transition-colors text-sm font-bold">
                                            +
                                        </button>
                                        <button wire:click="remover({{ $index }})"
                                                class="w-6 h-6 ml-1 rounded-full flex items-center justify-center
                                                       text-red-400 hover:text-red-600 hover:bg-red-50
                                                       dark:hover:bg-red-900/20 transition-colors text-sm font-bold">
                                            ×
                                        </button>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white w-16 text-right shrink-0">
                                        Q {{ number_format($item['precio'] * $item['cantidad'], 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Descuento y Propina --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                            rounded-xl shadow-sm p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Descuento (Q)
                            </label>
                            <input wire:model.live="descuento" type="number" min="0" step="0.01"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600
                                          dark:bg-gray-700 dark:text-white text-sm shadow-sm
                                          focus:ring-amber-500 focus:border-amber-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Propina (Q)
                            </label>
                            <input wire:model.live="propina" type="number" min="0" step="0.01"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600
                                          dark:bg-gray-700 dark:text-white text-sm shadow-sm
                                          focus:ring-amber-500 focus:border-amber-500" />
                        </div>
                    </div>
                </div>

                {{-- Totales --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                            rounded-xl shadow-sm p-4 space-y-2">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span>Subtotal</span>
                        <span>Q {{ number_format($this->subtotal, 2) }}</span>
                    </div>
                    @if ($descuento > 0)
                        <div class="flex justify-between text-sm text-yellow-600">
                            <span>Descuento</span>
                            <span>− Q {{ number_format($descuento, 2) }}</span>
                        </div>
                    @endif
                    @if ($propina > 0)
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Propina</span>
                            <span>+ Q {{ number_format($propina, 2) }}</span>
                        </div>
                    @endif
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-2 flex justify-between font-bold text-lg text-gray-900 dark:text-white">
                        <span>TOTAL</span>
                        <span class="text-amber-600">Q {{ number_format($this->total, 2) }}</span>
                    </div>
                </div>

                {{-- Método de pago --}}
                <div class="grid grid-cols-4 gap-2">
                    @foreach (['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transfer.', 'otro' => 'Otro'] as $value => $label)
                        <button wire:click="$set('metodoPago', '{{ $value }}')"
                                class="py-2 px-1 text-xs font-medium rounded-lg border-2 transition-all
                                       {{ $metodoPago === $value
                                           ? 'border-amber-500 bg-amber-500 text-white'
                                           : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-amber-300' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Botón completar --}}
                <x-filament::button
                    wire:click="completarVenta"
                    wire:loading.attr="disabled"
                    size="xl"
                    class="w-full"
                    icon="heroicon-o-check-circle">
                    <span wire:loading.remove>Completar venta — Q {{ number_format($this->total, 2) }}</span>
                    <span wire:loading>Procesando...</span>
                </x-filament::button>

            </div>
        </div>
    @endif
</x-filament-panels::page>
