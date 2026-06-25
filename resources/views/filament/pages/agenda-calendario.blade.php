<x-filament-panels::page>

    {{-- FullCalendar cargado dinámicamente solo en esta página --}}
    <script>
    (function () {
        function loadFC(callback) {
            if (window.FullCalendar) { callback(); return; }

            const css = document.createElement('link');
            css.rel  = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css';
            document.head.appendChild(css);

            const js = document.createElement('script');
            js.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js';
            js.onload = callback;
            document.head.appendChild(js);
        }

        // Registra el componente Alpine una vez que Alpine esté listo Y FullCalendar cargado
        document.addEventListener('alpine:init', () => {
            Alpine.data('agendaCalendario', (wire, createUrl, editBase) => ({
                calendar: null,

                init() {
                    loadFC(() => this.initCalendar());
                    // Escucha el evento Livewire para refrescar cuando cambia el filtro
                    window.addEventListener('citas-refetch', () => this.calendar?.refetchEvents());
                },

                initCalendar() {
                    this.calendar = new FullCalendar.Calendar(this.$refs.fc, {
                        locale: 'es',
                        initialView: 'timeGridWeek',
                        height: 'auto',
                        allDaySlot: false,
                        nowIndicator: true,
                        slotMinTime: '07:00:00',
                        slotMaxTime: '21:00:00',
                        slotDuration: '00:30:00',
                        businessHours: {
                            daysOfWeek: [1, 2, 3, 4, 5, 6],
                            startTime: '08:00',
                            endTime: '20:00',
                        },
                        headerToolbar: {
                            left:   'prev,next today',
                            center: 'title',
                            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                        },
                        buttonText: {
                            today:    'Hoy',
                            month:    'Mes',
                            week:     'Semana',
                            day:      'Día',
                            list:     'Lista',
                        },

                        // ── Carga de eventos vía Livewire ──────────────────────
                        events: (info, success, fail) => {
                            wire.getCitasParaCalendario(info.startStr, info.endStr)
                                .then(success)
                                .catch(() => fail());
                        },

                        // ── Contenido personalizado del evento ─────────────────
                        eventContent: (arg) => {
                            const ep    = arg.event.extendedProps;
                            const start = arg.event.start
                                ? arg.event.start.toLocaleTimeString('es', {hour:'2-digit', minute:'2-digit'})
                                : '';
                            return {
                                html: `
                                    <div class="fc-cita-event overflow-hidden px-1 py-0.5 leading-tight">
                                        <div class="font-semibold text-xs truncate">${start} ${arg.event.title}</div>
                                        <div class="text-xs opacity-80 truncate">✂️ ${ep.barbero}</div>
                                        <div class="text-xs opacity-70">${ep.estadoLabel} · ${ep.duracion} min</div>
                                    </div>
                                `,
                            };
                        },

                        // ── Clic en un evento → editar ─────────────────────────
                        eventClick: (info) => {
                            window.location.href = editBase + '/' + info.event.id + '/edit';
                        },

                        // ── Clic en slot vacío → nueva cita con fecha pre-llenada
                        dateClick: (info) => {
                            window.location.href = createUrl + '?fecha_hora=' + encodeURIComponent(info.dateStr);
                        },

                        // ── Arrastrar evento para cambiar hora ─────────────────
                        editable: true,
                        eventDrop: (info) => {
                            wire.moverCita(info.event.id, info.event.start.toISOString())
                                .then(ok => {
                                    if (!ok) {
                                        info.revert();
                                        alert('No se pudo mover la cita.');
                                    }
                                });
                        },
                    });

                    this.calendar.render();
                },
            }));
        });
    })();
    </script>

    {{-- ── Barra de filtros ────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3 mb-1">

        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Barbero:</span>
            <select
                wire:model.live="empleadoFiltro"
                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800
                       dark:text-gray-200 text-sm shadow-sm focus:ring-amber-500 focus:border-amber-500"
            >
                <option value="">Todos</option>
                @foreach($this->empleados as $e)
                    <option value="{{ $e->id }}">{{ $e->nombre_completo }}</option>
                @endforeach
            </select>
        </div>

        {{-- Leyenda de colores por barbero --}}
        <div class="flex flex-wrap gap-2 ml-2">
            @foreach($this->empleados as $e)
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                    <span class="inline-block w-3 h-3 rounded-full"
                          style="background-color: {{ $e->color_agenda ?? '#f59e0b' }}"></span>
                    {{ $e->nombre_completo }}
                </span>
            @endforeach
        </div>

        <div class="ml-auto">
            <a
                href="{{ $this->getCreateUrl() }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium
                       bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva cita
            </a>
        </div>
    </div>

    {{-- ── Calendario ──────────────────────────────────────────────────────── --}}
    <div
        x-data="agendaCalendario($wire, @js($this->getCreateUrl()), @js($this->getEditUrlBase()))"
        x-init="init()"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-3"
    >
        {{-- wire:ignore evita que Livewire destruya el DOM del calendario al re-renderizar --}}
        <div wire:ignore>
            <div x-ref="fc"></div>
        </div>
    </div>

    {{-- Estilos adicionales para afinar el look del calendario --}}
    <style>
        /* Fuerza que el calendario use los colores del tema Filament */
        .fc .fc-toolbar-title         { font-size: 1.1rem; font-weight: 600; }
        .fc .fc-button                { text-transform: capitalize; }
        .fc .fc-button-primary        { background-color: #f59e0b; border-color: #d97706; }
        .fc .fc-button-primary:hover  { background-color: #d97706; border-color: #b45309; }
        .fc .fc-button-primary:not(.fc-button-active):not(:disabled) { background-color: #f59e0b; }
        .fc .fc-button-active         { background-color: #b45309 !important; border-color: #92400e !important; }
        .fc .fc-today-button          { background-color: #6b7280 !important; border-color: #4b5563 !important; }
        .fc .fc-today-button:hover    { background-color: #4b5563 !important; }
        .fc .fc-daygrid-day.fc-day-today,
        .fc .fc-timegrid-col.fc-day-today { background-color: rgba(245, 158, 11, 0.07); }
        .fc .fc-event               { border-radius: 6px; cursor: pointer; }
        .fc .fc-event:hover         { opacity: 0.88; }

        /* Dark mode */
        .dark .fc                   { color: #e5e7eb; }
        .dark .fc-theme-standard td,
        .dark .fc-theme-standard th { border-color: #374151; }
        .dark .fc .fc-scrollgrid   { border-color: #374151; }
        .dark .fc .fc-col-header-cell { background-color: #1f2937; }
        .dark .fc .fc-timegrid-slot { border-color: #374151; }
        .dark .fc .fc-non-business  { background-color: rgba(0,0,0,0.15); }
    </style>

</x-filament-panels::page>
