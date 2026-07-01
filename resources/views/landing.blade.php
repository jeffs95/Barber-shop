<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} · Barbería</title>
    <meta name="description" content="Reserva tu cita en línea. Cortes, barba y más con los mejores barberos.">

    {{-- Aplica el tema guardado ANTES de pintar (evita parpadeo) y define la lógica del switcher --}}
    <script>
        (function () {
            const t = localStorage.getItem('theme') || 'system';
            const dark = t === 'dark' || (t === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();

        function themeToggle() {
            return {
                theme: localStorage.getItem('theme') || 'system',
                apply() {
                    const dark = this.theme === 'dark'
                        || (this.theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                },
                set(value) {
                    this.theme = value;
                    localStorage.setItem('theme', value);
                    this.apply();
                },
                init() {
                    // Si está en "Sistema", reacciona a los cambios del SO.
                    matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                        if (this.theme === 'system') this.apply();
                    });
                },
            };
        }
    </script>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased">

    {{-- ════════════ NAVBAR ════════════ --}}
    <header x-data="{ open: false }" class="fixed top-0 inset-x-0 z-50 backdrop-blur-md bg-white/80 dark:bg-gray-950/80 border-b border-gray-200 dark:border-white/5">
        <nav class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="#inicio" class="flex items-center gap-2 font-bold text-lg">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-amber-500 text-gray-950">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l10.062 5.807M9.523 12c.082.33.131.674.137 1.026a2.165 2.165 0 01-1.083 1.838m0 0L7.848 15.75M7.848 15.75a3 3 0 11-5.196 3 3 3 0 015.196-3z" /></svg>
                </span>
                <span>{{ config('app.name') }}</span>
            </a>

            {{-- Links escritorio --}}
            <div class="hidden md:flex items-center gap-7 text-sm font-medium text-gray-600 dark:text-gray-300">
                <a href="#servicios" class="hover:text-amber-500 dark:hover:text-amber-400 transition-colors">Servicios</a>
                <a href="#tienda" class="hover:text-amber-500 dark:hover:text-amber-400 transition-colors">Tienda</a>
                <a href="#equipo" class="hover:text-amber-500 dark:hover:text-amber-400 transition-colors">Equipo</a>
                <a href="#sucursales" class="hover:text-amber-500 dark:hover:text-amber-400 transition-colors">Sucursales</a>
                <a href="#contacto" class="hover:text-amber-500 dark:hover:text-amber-400 transition-colors">Contacto</a>
                <a href="#trabaja" class="hover:text-amber-500 dark:hover:text-amber-400 transition-colors">Trabaja con nosotros</a>

                {{-- Selector de tema --}}
                <x-tema-switcher />

                <a href="#agendar" class="px-4 py-2 rounded-xl bg-amber-500 text-gray-950 font-semibold hover:bg-amber-400 transition-colors">Agendar cita</a>
            </div>

            {{-- Controles móvil: tema + hamburguesa --}}
            <div class="flex items-center gap-2 md:hidden">
                <x-tema-switcher />
                <button @click="open = !open" class="p-2 text-gray-600 dark:text-gray-300" aria-label="Menú">
                    <svg x-show="!open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    <svg x-show="open" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </nav>

        {{-- Menú móvil desplegable --}}
        <div x-show="open" x-cloak @click="open = false" class="md:hidden border-t border-gray-200 dark:border-white/5 bg-white dark:bg-gray-950 px-4 py-3 space-y-1 text-sm">
            <a href="#servicios" class="block py-2 text-gray-600 dark:text-gray-300 hover:text-amber-500 dark:hover:text-amber-400">Servicios</a>
            <a href="#tienda" class="block py-2 text-gray-600 dark:text-gray-300 hover:text-amber-500 dark:hover:text-amber-400">Tienda</a>
            <a href="#equipo" class="block py-2 text-gray-600 dark:text-gray-300 hover:text-amber-500 dark:hover:text-amber-400">Equipo</a>
            <a href="#sucursales" class="block py-2 text-gray-600 dark:text-gray-300 hover:text-amber-500 dark:hover:text-amber-400">Sucursales</a>
            <a href="#contacto" class="block py-2 text-gray-600 dark:text-gray-300 hover:text-amber-500 dark:hover:text-amber-400">Contacto</a>
            <a href="#trabaja" class="block py-2 text-gray-600 dark:text-gray-300 hover:text-amber-500 dark:hover:text-amber-400">Trabaja con nosotros</a>
            <a href="#agendar" class="block py-2 mt-1 text-center rounded-xl bg-amber-500 text-gray-950 font-semibold">Agendar cita</a>
        </div>
    </header>

    {{-- ════════════ HERO ════════════ --}}
    <section id="inicio" class="relative min-h-screen flex items-center justify-center overflow-hidden pt-16">
        {{-- Fondo decorativo --}}
        <div class="absolute inset-0 bg-gradient-to-br from-amber-50 via-white to-amber-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950"></div>
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>

        <div class="relative text-center px-4 max-w-3xl">
            <span class="inline-block px-3 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-semibold tracking-wider uppercase mb-5">
                Barbería profesional
            </span>
            <h1 class="text-4xl sm:text-6xl font-bold tracking-tight mb-5 text-gray-900 dark:text-white">
                Tu mejor versión <br><span class="text-amber-500 dark:text-amber-400">empieza aquí</span>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 text-lg max-w-xl mx-auto mb-8">
                Cortes, barba y cuidado masculino con los mejores barberos. Reserva tu cita en segundos.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="#agendar" class="w-full sm:w-auto px-7 py-3 rounded-xl bg-amber-500 text-gray-950 font-semibold hover:bg-amber-400 transition-colors">
                    Agendar mi cita
                </a>
                <a href="#servicios" class="w-full sm:w-auto px-7 py-3 rounded-xl border border-gray-300 dark:border-white/15 text-gray-700 dark:text-gray-200 font-medium hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                    Ver servicios
                </a>
            </div>
        </div>
    </section>

    {{-- ════════════ SERVICIOS ════════════ --}}
    <section id="servicios" class="scroll-mt-16 py-20 bg-gray-50 dark:bg-gray-900">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Nuestros servicios</h2>
                <p class="text-gray-600 dark:text-gray-400">Calidad y estilo en cada visita</p>
            </div>

            @forelse ($servicios as $categoria => $items)
                <div class="mb-10">
                    <h3 class="text-amber-600 dark:text-amber-400 font-semibold text-sm uppercase tracking-wider mb-4">{{ $categoria }}</h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($items as $servicio)
                            <div class="p-5 rounded-2xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-white/5 hover:border-amber-500/40 transition-colors shadow-sm dark:shadow-none">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $servicio->nombre }}</h4>
                                    <span class="shrink-0 text-amber-600 dark:text-amber-400 font-bold">Q{{ number_format($servicio->precio_base, 0) }}</span>
                                </div>
                                @if ($servicio->descripcion)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">{{ $servicio->descripcion }}</p>
                                @endif
                                <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-500">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ $servicio->duracion_minutos }} min
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500">Pronto publicaremos nuestros servicios.</p>
            @endforelse
        </div>
    </section>

    {{-- ════════════ EQUIPO ════════════ --}}
    <section id="equipo" class="scroll-mt-16 py-20 bg-white dark:bg-gray-950">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Nuestro equipo</h2>
                <p class="text-gray-600 dark:text-gray-400">Barberos con experiencia y pasión</p>
            </div>

            @if ($barberos->isEmpty())
                <p class="text-center text-gray-500">Próximamente conocerás a nuestro equipo.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                    @foreach ($barberos as $barbero)
                        @php
                            $fotoEmpleado = null;
                            if ($barbero->foto) {
                                $fotoEmpleado = str_contains($barbero->foto, '/')
                                    ? asset('storage/' . $barbero->foto)
                                    : route('img.empleado', $barbero->foto);
                            }
                        @endphp
                        <div class="text-center p-6 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-white/5 hover:border-amber-400/40 hover:shadow-md transition-all">
                            @if ($fotoEmpleado)
                                <img
                                    src="{{ $fotoEmpleado }}"
                                    alt="{{ $barbero->nombre_completo }}"
                                    class="w-20 h-20 rounded-full object-cover mx-auto mb-3 ring-2 ring-amber-400/30"
                                    loading="lazy"
                                >
                            @else
                                <span class="inline-flex items-center justify-center w-20 h-20 rounded-full text-2xl font-bold text-gray-950 mb-3"
                                      style="background-color: {{ $barbero->color_agenda ?? '#f59e0b' }}">
                                    {{ mb_substr($barbero->nombre_completo, 0, 1) }}
                                </span>
                            @endif
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $barbero->nombre_completo }}</h4>
                            <p class="text-xs text-amber-600/90 dark:text-amber-400/80 uppercase tracking-wider mt-1">Barbero</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ════════════ TIENDA ════════════ --}}
    <section id="tienda" class="scroll-mt-16 py-20 bg-gray-50 dark:bg-gray-900">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="text-center mb-12">
                <span class="inline-block px-3 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-semibold tracking-wider uppercase mb-3">
                    Disponible en sucursales
                </span>
                <h2 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Tienda</h2>
                <p class="text-gray-600 dark:text-gray-400">Productos de cuidado profesional para llevar a casa</p>
            </div>

            @forelse ($productos as $categoria => $items)
                <div class="mb-12">
                    <h3 class="text-amber-600 dark:text-amber-400 font-semibold text-sm uppercase tracking-wider mb-5 flex items-center gap-2">
                        <span class="flex-1 h-px bg-amber-500/20"></span>
                        {{ $categoria }}
                        <span class="flex-1 h-px bg-amber-500/20"></span>
                    </h3>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                        @foreach ($items as $producto)
                            @php $agotado = $producto->stock_actual <= 0; @endphp
                            <div class="group flex flex-col rounded-2xl overflow-hidden border transition-all
                                {{ $agotado
                                    ? 'border-gray-200 dark:border-white/5 bg-white dark:bg-gray-950 opacity-60 cursor-not-allowed'
                                    : 'border-gray-200 dark:border-white/5 bg-white dark:bg-gray-950 hover:border-amber-400/50 hover:shadow-lg dark:hover:shadow-amber-900/10' }}">

                                {{-- Imagen --}}
                                <div class="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    @if ($producto->foto)
                                        @php
                                            $fotoUrl = str_contains($producto->foto, '/')
                                                ? asset('storage/' . $producto->foto)
                                                : route('img.producto', $producto->foto);
                                        @endphp
                                        <img
                                            src="{{ $fotoUrl }}"
                                            alt="{{ $producto->nombre }}"
                                            class="w-full h-full object-cover {{ $agotado ? '' : 'group-hover:scale-105' }} transition-transform duration-300"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </div>
                                    @endif

                                    </div>

                                {{-- Info --}}
                                <div class="flex flex-col flex-1 p-4">
                                    <p class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold uppercase tracking-wider mb-1">
                                        {{ \App\Models\Producto::categoriaLabel($producto->categoria) }}
                                    </p>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm leading-snug flex-1">
                                        {{ $producto->nombre }}
                                    </h4>
                                    @if ($producto->descripcion)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $producto->descripcion }}</p>
                                    @endif

                                    {{-- Stock --}}
                                    <div class="mt-2">
                                        @if ($agotado)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-500 dark:text-red-400">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Sin stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $producto->stock_actual }} {{ $producto->unidad }}{{ $producto->stock_actual != 1 ? 's' : '' }} disponibles
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100 dark:border-white/5">
                                        <span class="text-base font-bold {{ $agotado ? 'text-gray-400 dark:text-gray-600' : 'text-amber-600 dark:text-amber-400' }}">
                                            Q{{ number_format($producto->precio_venta, 2) }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $producto->unidad }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <p>Próximamente tendremos productos disponibles.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- ════════════ AGENDAR (asistente embebido) ════════════ --}}
    <section id="agendar" class="scroll-mt-16 py-20 bg-gray-50 dark:bg-gray-900">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Agenda tu cita</h2>
                <p class="text-gray-600 dark:text-gray-400">Elige servicio, barbero y horario en unos clics</p>
            </div>
            @livewire('reserva-publica', ['embebido' => true])
        </div>
    </section>

    {{-- ════════════ SUCURSALES ════════════ --}}
    <section id="sucursales" class="scroll-mt-16 py-20 bg-white dark:bg-gray-950">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Dónde encontrarnos</h2>
                <p class="text-gray-600 dark:text-gray-400">Visítanos en cualquiera de nuestras sucursales</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-5 max-w-3xl mx-auto">
                @foreach ($sucursales as $sucursal)
                    <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-white/5">
                        <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-3">{{ $sucursal->nombre }}</h4>
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            @if ($sucursal->direccion)
                                <p class="flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 text-amber-500 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    {{ $sucursal->direccion }}{{ $sucursal->ciudad ? ', ' . $sucursal->ciudad : '' }}
                                </p>
                            @endif
                            @if ($sucursal->telefono)
                                <p class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                                    {{ $sucursal->telefono }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ════════════ CONTACTO ════════════ --}}
    {{-- EDITABLE: reemplaza el número de WhatsApp, correo y enlaces de redes por los reales --}}
    <section id="contacto" class="scroll-mt-16 py-20 bg-gray-50 dark:bg-gray-900">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 text-center">
            <h2 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Contáctanos</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-10">¿Dudas o quieres reservar por mensaje? Escríbenos</p>

            <div class="grid sm:grid-cols-3 gap-4 mb-10">
                <a href="https://wa.me/50200000000" target="_blank" rel="noopener" class="p-6 rounded-2xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-white/5 hover:border-amber-500/40 transition-colors shadow-sm dark:shadow-none">
                    <svg class="w-8 h-8 mx-auto mb-3 text-amber-500 dark:text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.978-1.207zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">WhatsApp</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">+502 0000-0000</p>
                </a>
                <a href="tel:+50200000000" class="p-6 rounded-2xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-white/5 hover:border-amber-500/40 transition-colors shadow-sm dark:shadow-none">
                    <svg class="w-8 h-8 mx-auto mb-3 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">Teléfono</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">+502 0000-0000</p>
                </a>
                <a href="mailto:hola@barberia.com" class="p-6 rounded-2xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-white/5 hover:border-amber-500/40 transition-colors shadow-sm dark:shadow-none">
                    <svg class="w-8 h-8 mx-auto mb-3 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">Correo</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">hola@barberia.com</p>
                </a>
            </div>

            <div class="flex items-center justify-center gap-4">
                <a href="#" aria-label="Instagram" class="w-10 h-10 rounded-full bg-white dark:bg-gray-950 border border-gray-200 dark:border-white/10 flex items-center justify-center text-gray-500 dark:text-gray-300 hover:text-amber-500 dark:hover:text-amber-400 hover:border-amber-500/40 transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
                <a href="#" aria-label="Facebook" class="w-10 h-10 rounded-full bg-white dark:bg-gray-950 border border-gray-200 dark:border-white/10 flex items-center justify-center text-gray-500 dark:text-gray-300 hover:text-amber-500 dark:hover:text-amber-400 hover:border-amber-500/40 transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                <a href="#" aria-label="TikTok" class="w-10 h-10 rounded-full bg-white dark:bg-gray-950 border border-gray-200 dark:border-white/10 flex items-center justify-center text-gray-500 dark:text-gray-300 hover:text-amber-500 dark:hover:text-amber-400 hover:border-amber-500/40 transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ════════════ TRABAJA CON NOSOTROS ════════════ --}}
    {{-- EDITABLE: ajusta el correo y el mensaje según tu proceso de reclutamiento --}}
    <section id="trabaja" class="scroll-mt-16 py-20 bg-white dark:bg-gray-950">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <div class="rounded-3xl bg-gradient-to-br from-amber-100 to-white dark:from-amber-500/10 dark:to-gray-900 border border-amber-300/50 dark:border-amber-500/20 p-8 sm:p-12 text-center">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500 text-gray-950 mb-5">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 01-1.632 2.163l-1.32.377a9.797 9.797 0 01-5.396 0l-1.32-.377A2.25 2.25 0 013.75 18.223V14.15M12 3l8.385 4.193a.75.75 0 010 1.342L12 12.75 3.615 8.535a.75.75 0 010-1.342L12 3z" /></svg>
                </span>
                <h2 class="text-3xl font-bold mb-3 text-gray-900 dark:text-white">Trabaja con nosotros</h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-xl mx-auto mb-6">
                    ¿Eres barbero apasionado por tu oficio? Buscamos talento para sumarse al equipo.
                    Envíanos tu CV y portafolio y conversemos.
                </p>
                <a href="mailto:empleo@barberia.com?subject=Quiero%20trabajar%20con%20ustedes"
                   class="inline-block px-7 py-3 rounded-xl bg-amber-500 text-gray-950 font-semibold hover:bg-amber-400 transition-colors">
                    Enviar mi CV
                </a>
                <p class="text-xs text-gray-500 mt-4">o escríbenos por WhatsApp al +502 0000-0000</p>
            </div>
        </div>
    </section>

    {{-- ════════════ FOOTER ════════════ --}}
    <footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-white/5 py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-gray-500">
            <div class="flex items-center gap-2 font-semibold text-gray-700 dark:text-gray-300">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-500 text-gray-950">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l10.062 5.807M9.523 12c.082.33.131.674.137 1.026a2.165 2.165 0 01-1.083 1.838m0 0L7.848 15.75M7.848 15.75a3 3 0 11-5.196 3 3 3 0 015.196-3z" /></svg>
                </span>
                {{ config('app.name') }}
            </div>
            <p>Horario: Lun–Sáb · 9:00 – 18:00</p>
            <p>© {{ now()->year }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
