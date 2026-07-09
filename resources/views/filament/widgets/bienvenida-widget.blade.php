<x-filament-widgets::widget>
    @php
        $usuario  = auth()->user();
        $nombre   = $usuario?->nombre ?? $usuario?->name ?? 'Administrador';
        $iniciales = collect(explode(' ', $nombre))->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
        $citas    = $this->citasActivasHoy;
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 px-1 py-0.5">

        {{-- Avatar + saludo --}}
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 flex items-center justify-center shrink-0 shadow-sm">
                <span class="text-lg font-bold text-gray-950 leading-none tracking-tight">{{ $iniciales }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $this->fechaTexto }}</p>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">
                    {{ $this->saludo }}, {{ $nombre }}
                </h2>
                @if ($citas > 0)
                    <p class="text-sm text-amber-600 dark:text-amber-400 font-medium mt-0.5">
                        {{ $citas }} {{ $citas === 1 ? 'cita activa' : 'citas activas' }} para hoy
                    </p>
                @else
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">Sin citas activas por ahora</p>
                @endif
            </div>
        </div>

        {{-- Accesos rápidos --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('filament.admin.resources.citas.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-gray-950 text-sm font-semibold transition-colors shadow-sm">
                <x-filament::icon icon="heroicon-o-plus" class="w-4 h-4" />
                Nueva cita
            </a>
            <a href="{{ route('filament.admin.pages.agenda') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 hover:border-amber-400 dark:hover:border-amber-500/50 text-gray-700 dark:text-gray-300 text-sm font-medium transition-colors">
                <x-filament::icon icon="heroicon-o-calendar-days" class="w-4 h-4" />
                Agenda
            </a>
            <a href="{{ route('filament.admin.pages.pos') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 hover:border-amber-400 dark:hover:border-amber-500/50 text-gray-700 dark:text-gray-300 text-sm font-medium transition-colors">
                <x-filament::icon icon="heroicon-o-banknotes" class="w-4 h-4" />
                POS
            </a>
            <a href="{{ route('filament.admin.resources.solicitudes.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 hover:border-amber-400 dark:hover:border-amber-500/50 text-gray-700 dark:text-gray-300 text-sm font-medium transition-colors">
                <x-filament::icon icon="heroicon-o-inbox" class="w-4 h-4" />
                Solicitudes
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
