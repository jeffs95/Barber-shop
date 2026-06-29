<?php

namespace App\Livewire;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Services\DisponibilidadService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.publico')]
#[Title('Reservar cita')]
class ReservaPublica extends Component
{
    /** Cantidad de días hacia adelante que se ofrecen para reservar. */
    private const DIAS_DISPONIBLES = 14;

    public int $paso = 1;

    // Cuando se incrusta en la landing, ocultamos el encabezado propio del asistente.
    public bool $embebido = false;

    // Paso 1 — sucursal y servicios
    public ?int $sucursalId = null;
    /** @var list<int> */
    public array $serviciosSel = [];

    // Paso 2 — barbero
    public string $modoBarbero = 'cualquiera'; // 'cualquiera' | 'especifico'
    public ?int $empleadoId = null;

    // Paso 3 — fecha y hora
    public ?string $fecha = null; // Y-m-d
    public ?string $hora = null;  // H:i

    // Paso 4 — datos de contacto
    public string $nombre = '';
    public string $apellido = '';
    public string $telefono = '';
    public string $email = '';

    // Honeypot anti-bot (debe quedar vacío)
    public string $website = '';

    // Resultado
    public bool $exito = false;
    public ?int $citaCreadaId = null;
    public ?string $barberoAsignado = null;

    public function mount(): void
    {
        // Si solo hay una sucursal activa, la preseleccionamos.
        $sucursales = $this->sucursales;
        if ($sucursales->count() === 1) {
            $this->sucursalId = (int) $sucursales->first()->id;
        }
    }

    // ─── Datos para la vista ────────────────────────────────────────────────

    #[Computed]
    public function sucursales()
    {
        return Sucursal::where('es_activa', true)->orderBy('nombre')->get(['id', 'nombre', 'ciudad', 'direccion']);
    }

    #[Computed]
    public function servicios()
    {
        return Servicio::where('es_activo', true)
            ->with('categoria')
            ->orderBy('nombre')
            ->get();
    }

    #[Computed]
    public function barberos()
    {
        if (! $this->sucursalId) {
            return collect();
        }

        return Empleado::where('sucursal_id', $this->sucursalId)
            ->where('es_activo', true)
            ->where('rol', 'barbero')
            ->with('usuario')
            ->get();
    }

    #[Computed]
    public function duracionTotal(): int
    {
        return (int) Servicio::whereIn('id', $this->serviciosSel)->sum('duracion_minutos') ?: 30;
    }

    #[Computed]
    public function precioEstimado(): float
    {
        return (float) Servicio::whereIn('id', $this->serviciosSel)->sum('precio_base');
    }

    /** @return list<array{fecha:string,etiqueta:string}> */
    #[Computed]
    public function dias(): array
    {
        $dias = [];
        $hoy = Carbon::today();

        for ($i = 0; $i < self::DIAS_DISPONIBLES; $i++) {
            $d = $hoy->copy()->addDays($i);
            $dias[] = [
                'fecha'    => $d->toDateString(),
                'etiqueta' => $this->etiquetaDia($d, $i),
            ];
        }

        return $dias;
    }

    /** @return list<string> */
    #[Computed]
    public function slotsDisponibles(): array
    {
        if (! $this->fecha || empty($this->serviciosSel)) {
            return [];
        }

        $servicio = app(DisponibilidadService::class);
        $fecha = Carbon::parse($this->fecha);

        if ($this->modoBarbero === 'especifico' && $this->empleadoId) {
            return $servicio->slotsParaBarbero($this->empleadoId, $fecha, $this->duracionTotal);
        }

        if ($this->modoBarbero === 'cualquiera' && $this->sucursalId) {
            return array_keys($servicio->slotsSucursal($this->sucursalId, $fecha, $this->duracionTotal));
        }

        return [];
    }

    private function etiquetaDia(Carbon $d, int $offset): string
    {
        $nombresDia = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $prefijo = match ($offset) {
            0       => 'Hoy',
            1       => 'Mañana',
            default => $nombresDia[$d->dayOfWeek],
        };

        return $prefijo . ' ' . $d->format('d/m');
    }

    // ─── Navegación del asistente ────────────────────────────────────────────

    public function toggleServicio(int $servicioId): void
    {
        if (in_array($servicioId, $this->serviciosSel, true)) {
            $this->serviciosSel = array_values(array_diff($this->serviciosSel, [$servicioId]));
        } else {
            $this->serviciosSel[] = $servicioId;
        }

        // Cambió la duración → cualquier hora elegida deja de ser válida.
        $this->hora = null;
    }

    public function seleccionarHora(string $hora): void
    {
        $this->hora = $hora;
    }

    public function updatedFecha(): void
    {
        $this->hora = null;
    }

    public function updatedModoBarbero(): void
    {
        $this->hora = null;
        if ($this->modoBarbero === 'cualquiera') {
            $this->empleadoId = null;
        }
    }

    public function updatedEmpleadoId(): void
    {
        $this->hora = null;
    }

    public function siguiente(): void
    {
        $this->resetErrorBag();

        if ($this->paso === 1) {
            if (! $this->sucursalId) {
                $this->addError('sucursalId', 'Elige una sucursal.');
                return;
            }
            if (empty($this->serviciosSel)) {
                $this->addError('serviciosSel', 'Elige al menos un servicio.');
                return;
            }
        }

        if ($this->paso === 2) {
            if ($this->modoBarbero === 'especifico' && ! $this->empleadoId) {
                $this->addError('empleadoId', 'Elige un barbero o selecciona "sin preferencia".');
                return;
            }
        }

        if ($this->paso === 3) {
            if (! $this->fecha || ! $this->hora) {
                $this->addError('hora', 'Elige una fecha y una hora disponible.');
                return;
            }
        }

        $this->paso = min(4, $this->paso + 1);
    }

    public function atras(): void
    {
        $this->resetErrorBag();
        $this->paso = max(1, $this->paso - 1);
    }

    // ─── Confirmación ──────────────────────────────────────────────────────

    public function confirmar(): void
    {
        $this->resetErrorBag();

        // Honeypot: si viene lleno, es un bot → no hacemos nada.
        if ($this->website !== '') {
            return;
        }

        $this->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:255'],
        ], [], [
            'nombre'   => 'nombre',
            'telefono' => 'teléfono',
        ]);

        // Anti-spam: máximo 5 reservas por hora por IP.
        $key = 'reserva-publica:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('general', 'Has hecho demasiadas reservas seguidas. Intenta de nuevo más tarde.');
            return;
        }

        $inicio = Carbon::parse($this->fecha . ' ' . $this->hora);
        $duracion = $this->duracionTotal;

        // Resolver barbero según el modo elegido.
        $disponibilidad = app(DisponibilidadService::class);

        if ($this->modoBarbero === 'especifico') {
            $empleadoId = $this->empleadoId;
            if (! $empleadoId || Cita::haySolapamiento($empleadoId, $inicio, $duracion)) {
                $this->addError('general', 'Ese horario ya fue tomado. Elige otro, por favor.');
                $this->paso = 3;
                return;
            }
        } else {
            $empleadoId = $disponibilidad->primerBarberoDisponible($this->sucursalId, $inicio, $duracion);
            if (! $empleadoId) {
                $this->addError('general', 'Ese horario ya no está disponible. Elige otro, por favor.');
                $this->paso = 3;
                return;
            }
        }

        $cita = DB::transaction(function () use ($empleadoId, $inicio, $duracion) {
            // Reutiliza el cliente por teléfono; si no existe, lo crea.
            $cliente = Cliente::firstOrCreate(
                ['telefono' => $this->telefono],
                [
                    'nombre'   => $this->nombre,
                    'apellido' => $this->apellido ?: null,
                    'email'    => $this->email ?: null,
                    'tipo'     => 'nuevo',
                ]
            );

            $cita = Cita::create([
                'cliente_id'            => $cliente->id,
                'empleado_id'           => $empleadoId,
                'fecha_hora'            => $inicio,
                'duracion_estimada_min' => $duracion,
                'estado'                => 'pendiente',
                'origen'                => 'enlace',
                'notas'                 => 'Reserva en línea pendiente de confirmación.',
            ]);

            foreach ($this->serviciosSel as $servicioId) {
                $servicio = Servicio::find($servicioId);
                if (! $servicio) {
                    continue;
                }
                $cita->itemsCita()->create([
                    'servicio_id' => $servicio->id,
                    'precio'      => $servicio->precioParaEmpleado($empleadoId),
                ]);
            }

            $cita->load('servicios');
            $cita->recalcularDuracion();

            return $cita;
        });

        RateLimiter::hit($key, 3600);

        $this->barberoAsignado = Empleado::with('usuario')->find($empleadoId)?->nombre_completo;
        $this->citaCreadaId = $cita->id;
        $this->exito = true;
    }

    public function reiniciar(): void
    {
        $this->reset([
            'paso', 'serviciosSel', 'modoBarbero', 'empleadoId',
            'fecha', 'hora', 'nombre', 'apellido', 'telefono', 'email',
            'website', 'exito', 'citaCreadaId', 'barberoAsignado',
        ]);
        $this->paso = 1;
        $this->modoBarbero = 'cualquiera';

        $sucursales = $this->sucursales;
        $this->sucursalId = $sucursales->count() === 1 ? (int) $sucursales->first()->id : null;
    }

    public function render()
    {
        return view('livewire.reserva-publica');
    }
}
