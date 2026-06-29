{{-- Panel de marca del login (columna izquierda). El layout de 2 columnas lo arma el CSS del tema. --}}
<div class="fi-login-branding relative overflow-hidden bg-gradient-to-br from-gray-950 via-gray-900 to-gray-950 text-white">
    {{-- Manchas decorativas amber --}}
    <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-full bg-amber-500/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-amber-500/10 blur-3xl"></div>

    <div class="relative z-10 flex min-h-screen flex-col justify-between p-12">
        {{-- Logo + nombre --}}
        <div class="flex items-center gap-3">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500 text-gray-950">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l10.062 5.807M9.523 12c.082.33.131.674.137 1.026a2.165 2.165 0 01-1.083 1.838m0 0L7.848 15.75M7.848 15.75a3 3 0 11-5.196 3 3 3 0 015.196-3z" /></svg>
            </span>
            <span class="text-xl font-bold tracking-tight">{{ config('app.name') }}</span>
        </div>

        {{-- Mensaje central --}}
        <div class="max-w-md">
            <h2 class="mb-6 text-4xl font-bold leading-tight">
                Gestiona tu barbería <span class="text-amber-400">sin complicaciones</span>
            </h2>
            <ul class="space-y-4 text-gray-300">
                <li class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                    </span>
                    Citas, agenda y reservas en línea
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0" /></svg>
                    </span>
                    Servicios, barberos y comisiones
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    </span>
                    Inventario y punto de venta
                </li>
            </ul>
        </div>

        {{-- Pie --}}
        <p class="text-sm text-gray-500">Sistema de gestión integral · {{ now()->year }}</p>
    </div>
</div>
