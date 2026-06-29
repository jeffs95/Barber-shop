<div>
    {{-- Encabezado (solo cuando el asistente va solo, no embebido en la landing) --}}
    @unless ($embebido)
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500 text-white shadow-lg mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l10.062 5.807M9.523 12c.082.33.131.674.137 1.026a2.165 2.165 0 01-1.083 1.838m0 0L7.848 15.75M7.848 15.75a3 3 0 11-5.196 3 3 3 0 015.196-3z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ config('app.name') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Reserva tu cita en línea</p>
        </div>
    @endunless

    @if ($exito)
        {{-- ─── Pantalla de éxito ─── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">¡Solicitud recibida!</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-6 max-w-md mx-auto">
                Tu reserva quedó <strong>pendiente de confirmación</strong>. Pronto nos pondremos en contacto
                contigo para confirmarla. ¡Gracias por elegirnos!
            </p>

            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 text-left text-sm space-y-1 max-w-sm mx-auto mb-6">
                <p><span class="text-gray-500 dark:text-gray-400">Código de reserva:</span> <strong>#{{ $citaCreadaId }}</strong></p>
                <p><span class="text-gray-500 dark:text-gray-400">Fecha:</span> <strong>{{ \Illuminate\Support\Carbon::parse($fecha)->format('d/m/Y') }} a las {{ $hora }}</strong></p>
                <p><span class="text-gray-500 dark:text-gray-400">Barbero:</span> <strong>{{ $barberoAsignado ?? 'Por asignar' }}</strong></p>
            </div>

            <button wire:click="reiniciar"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium transition-colors">
                Hacer otra reserva
            </button>
        </div>
    @else
        {{-- ─── Barra de progreso ─── --}}
        <div class="flex items-center justify-between mb-6 max-w-md mx-auto">
            @foreach (['Servicio', 'Barbero', 'Horario', 'Tus datos'] as $i => $nombrePaso)
                @php $n = $i + 1; @endphp
                <div class="flex items-center {{ ! $loop->last ? 'flex-1' : '' }}">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold
                                    {{ $paso >= $n ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' }}">
                            {{ $n }}
                        </div>
                        <span class="text-[11px] mt-1 {{ $paso >= $n ? 'text-amber-600 font-medium' : 'text-gray-400' }}">{{ $nombrePaso }}</span>
                    </div>
                    @if (! $loop->last)
                        <div class="flex-1 h-0.5 mx-1 mb-4 {{ $paso > $n ? 'bg-amber-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6">

            @error('general')
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-sm text-red-700 dark:text-red-300">
                    {{ $message }}
                </div>
            @enderror

            {{-- ════════════ PASO 1: Sucursal + Servicios ════════════ --}}
            @if ($paso === 1)
                <h2 class="text-lg font-semibold mb-4">¿Qué te quieres hacer?</h2>

                @if ($this->sucursales->count() > 1)
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Sucursal</label>
                        <div class="grid sm:grid-cols-2 gap-2">
                            @foreach ($this->sucursales as $sucursal)
                                <button type="button" wire:click="$set('sucursalId', {{ $sucursal->id }})"
                                        class="text-left p-3 rounded-xl border-2 transition-all
                                               {{ $sucursalId === $sucursal->id
                                                   ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20'
                                                   : 'border-gray-200 dark:border-gray-700 hover:border-amber-300' }}">
                                    <p class="font-semibold text-sm">{{ $sucursal->nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ $sucursal->ciudad }}</p>
                                </button>
                            @endforeach
                        </div>
                        @error('sucursalId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Servicios</label>
                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach ($this->servicios as $servicio)
                        @php $sel = in_array($servicio->id, $serviciosSel, true); @endphp
                        <button type="button" wire:click="toggleServicio({{ $servicio->id }})"
                                class="flex items-center justify-between p-3 rounded-xl border-2 text-left transition-all
                                       {{ $sel ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300' }}">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm truncate">{{ $servicio->nombre }}</p>
                                <p class="text-xs text-gray-500">{{ $servicio->duracion_minutos }} min</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-amber-600 font-bold text-sm">Q{{ number_format($servicio->precio_base, 0) }}</span>
                                <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center
                                             {{ $sel ? 'border-amber-500 bg-amber-500' : 'border-gray-300' }}">
                                    @if ($sel)
                                        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    @endif
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
                @error('serviciosSel') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

                @if (! empty($serviciosSel))
                    <div class="mt-4 flex items-center justify-between text-sm bg-gray-50 dark:bg-gray-700/50 rounded-lg px-4 py-3">
                        <span class="text-gray-600 dark:text-gray-300">Total estimado · {{ $this->duracionTotal }} min</span>
                        <span class="font-bold text-amber-600">Q{{ number_format($this->precioEstimado, 2) }}</span>
                    </div>
                @endif
            @endif

            {{-- ════════════ PASO 2: Barbero ════════════ --}}
            @if ($paso === 2)
                <h2 class="text-lg font-semibold mb-4">¿Con quién te quieres atender?</h2>

                <div class="space-y-2 mb-4">
                    <button type="button" wire:click="$set('modoBarbero', 'cualquiera')"
                            class="w-full flex items-center gap-3 p-3 rounded-xl border-2 text-left transition-all
                                   {{ $modoBarbero === 'cualquiera' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300' }}">
                        <span class="w-9 h-9 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                        </span>
                        <div>
                            <p class="font-semibold text-sm">Sin preferencia</p>
                            <p class="text-xs text-gray-500">Te asignamos el primer barbero disponible</p>
                        </div>
                    </button>

                    <button type="button" wire:click="$set('modoBarbero', 'especifico')"
                            class="w-full flex items-center gap-3 p-3 rounded-xl border-2 text-left transition-all
                                   {{ $modoBarbero === 'especifico' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300' }}">
                        <span class="w-9 h-9 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </span>
                        <div>
                            <p class="font-semibold text-sm">Elegir barbero</p>
                            <p class="text-xs text-gray-500">Selecciona a tu barbero preferido</p>
                        </div>
                    </button>
                </div>

                @if ($modoBarbero === 'especifico')
                    @if ($this->barberos->isEmpty())
                        <p class="text-sm text-gray-500 px-1">No hay barberos disponibles en esta sucursal.</p>
                    @else
                        <div class="grid sm:grid-cols-2 gap-2">
                            @foreach ($this->barberos as $barbero)
                                <button type="button" wire:click="$set('empleadoId', {{ $barbero->id }})"
                                        class="flex items-center gap-3 p-3 rounded-xl border-2 text-left transition-all
                                               {{ $empleadoId === $barbero->id ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300' }}">
                                    <span class="w-9 h-9 rounded-full flex items-center justify-center text-white font-semibold text-sm shrink-0"
                                          style="background-color: {{ $barbero->color_agenda ?? '#f59e0b' }}">
                                        {{ mb_substr($barbero->nombre_completo, 0, 1) }}
                                    </span>
                                    <span class="font-medium text-sm">{{ $barbero->nombre_completo }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @error('empleadoId') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
                @endif
            @endif

            {{-- ════════════ PASO 3: Fecha y hora ════════════ --}}
            @if ($paso === 3)
                <h2 class="text-lg font-semibold mb-4">¿Cuándo te queda bien?</h2>

                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Día</label>
                <div class="flex gap-2 overflow-x-auto pb-2 mb-4">
                    @foreach ($this->dias as $dia)
                        <button type="button" wire:click="$set('fecha', '{{ $dia['fecha'] }}')"
                                class="shrink-0 px-3 py-2 rounded-xl border-2 text-sm font-medium transition-all
                                       {{ $fecha === $dia['fecha'] ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300' }}">
                            {{ $dia['etiqueta'] }}
                        </button>
                    @endforeach
                </div>

                @if ($fecha)
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Hora disponible</label>
                    @if (empty($this->slotsDisponibles))
                        <div class="text-center py-8 text-gray-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            No hay horarios disponibles ese día. Prueba con otra fecha.
                        </div>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                            @foreach ($this->slotsDisponibles as $slot)
                                <button type="button" wire:click="seleccionarHora('{{ $slot }}')"
                                        class="py-2 rounded-lg border-2 text-sm font-medium transition-all
                                               {{ $hora === $slot ? 'border-amber-500 bg-amber-500 text-white' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300' }}">
                                    {{ $slot }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-400 px-1">Elige primero un día.</p>
                @endif
                @error('hora') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
            @endif

            {{-- ════════════ PASO 4: Datos de contacto ════════════ --}}
            @if ($paso === 4)
                <h2 class="text-lg font-semibold mb-4">Tus datos</h2>

                {{-- Resumen --}}
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 text-sm space-y-1 mb-5">
                    <p><span class="text-gray-500 dark:text-gray-400">Fecha:</span> <strong>{{ \Illuminate\Support\Carbon::parse($fecha)->format('d/m/Y') }} · {{ $hora }}</strong></p>
                    <p><span class="text-gray-500 dark:text-gray-400">Duración:</span> <strong>{{ $this->duracionTotal }} min</strong></p>
                    <p><span class="text-gray-500 dark:text-gray-400">Barbero:</span> <strong>{{ $modoBarbero === 'cualquiera' ? 'Sin preferencia' : ($this->barberos->firstWhere('id', $empleadoId)?->nombre_completo ?? '—') }}</strong></p>
                    <p><span class="text-gray-500 dark:text-gray-400">Total estimado:</span> <strong class="text-amber-600">Q{{ number_format($this->precioEstimado, 2) }}</strong></p>
                </div>

                {{-- Honeypot (oculto a humanos) --}}
                <div class="hidden" aria-hidden="true">
                    <label>No llenar este campo <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="nombre"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        @error('nombre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Apellido</label>
                        <input type="text" wire:model="apellido"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Teléfono <span class="text-red-500">*</span></label>
                        <input type="tel" wire:model="telefono"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        @error('telefono') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Correo (opcional)</label>
                        <input type="email" wire:model="email"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            {{-- ─── Navegación ─── --}}
            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                @if ($paso > 1)
                    <button type="button" wire:click="atras"
                            class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 transition-colors">
                        ← Atrás
                    </button>
                @else
                    <span></span>
                @endif

                @if ($paso < 4)
                    <button type="button" wire:click="siguiente"
                            class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium transition-colors">
                        Continuar →
                    </button>
                @else
                    <button type="button" wire:click="confirmar" wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white font-medium transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmar">Confirmar reserva</span>
                        <span wire:loading wire:target="confirmar">Enviando...</span>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
