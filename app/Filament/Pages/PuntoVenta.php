<?php

namespace App\Filament\Pages;

use App\Models\Caja;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\ItemVenta;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\Venta;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class PuntoVenta extends Page
{
    protected string $view = 'filament.pages.punto-venta';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Punto de Venta';

    protected static string|\UnitEnum|null $navigationGroup = 'Punto de Venta';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'pos';

    // --- Cita vinculada (cuando se viene desde "Cobrar" en una cita) ---
    public ?int $citaId = null;

    // --- Sucursal activa del POS (el dueño puede cambiarla; el admin la tiene fija) ---
    public ?int $sucursalId = null;

    // --- Estado de caja ---
    public ?int $cajaId = null;

    // --- Selects ---
    public ?int $empleadoId = null;
    public ?int $clienteId = null;

    // --- Carrito ---
    /** @var array<int, array{key:string,tipo:string,id:int,nombre:string,precio:float,cantidad:int}> */
    public array $items = [];

    // --- Pago ---
    public float $descuento = 0;
    public float $propina   = 0;
    public string $metodoPago = 'efectivo';

    // --- UI ---
    public string $activeTab = 'servicios';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user?->esAdminSucursal() && $user->getSucursalId()) {
            // El admin opera siempre en su sucursal.
            $this->sucursalId = $user->getSucursalId();
        } else {
            // Dueño / acceso global: arranca donde haya una caja abierta, o en la primera sucursal.
            $this->sucursalId = Caja::where('estado', 'abierta')->value('sucursal_id')
                ?? Sucursal::where('es_activa', true)->orderBy('nombre')->value('id');
        }

        $this->sincronizarCaja();

        if ($citaId = (int) request()->query('cita_id')) {
            $this->precargarCita($citaId);
        }
    }

    public function updatedSucursalId(): void
    {
        // Al cambiar de sede, el empleado elegido puede ya no pertenecer a ella.
        $this->empleadoId = null;
        $this->sincronizarCaja();
    }

    /** Deriva la caja abierta a partir de la sucursal activa. */
    private function sincronizarCaja(): void
    {
        $this->cajaId = $this->sucursalId
            ? Caja::cajaAbiertaDe($this->sucursalId)?->id
            : null;
    }

    private function precargarCita(int $citaId): void
    {
        $cita = Cita::with(['servicios', 'empleado'])->find($citaId);

        if (! $cita || ! $cita->estaActiva()) {
            return;
        }

        $this->citaId    = $cita->id;
        $this->empleadoId = $cita->empleado_id;
        $this->clienteId  = $cita->cliente_id;

        // Cobrar en la sucursal donde se atiende la cita.
        if ($cita->sucursal_id) {
            $this->sucursalId = $cita->sucursal_id;
            $this->sincronizarCaja();
        }

        // Pre-cargar servicios de la cita con el precio acordado
        foreach ($cita->servicios as $servicio) {
            $this->items[] = [
                'key'      => 'servicio_' . $servicio->id,
                'tipo'     => 'servicio',
                'id'       => $servicio->id,
                'nombre'   => $servicio->nombre,
                'precio'   => (float) $servicio->pivot->precio,
                'cantidad' => 1,
            ];
        }
    }

    // --- Computed (Livewire 4) ---

    #[Computed]
    public function servicios()
    {
        return Servicio::where('es_activo', true)
            ->with('categoria')
            ->orderBy('nombre')
            ->get();
    }

    #[Computed]
    public function productos()
    {
        return Producto::activo()->orderBy('nombre')->get();
    }

    #[Computed]
    public function clientes()
    {
        return Cliente::orderBy('nombre')->get(['id', 'nombre', 'apellido']);
    }

    #[Computed]
    public function empleados()
    {
        return Empleado::where('es_activo', true)
            ->when($this->sucursalId, fn ($q) => $q->where('sucursal_id', $this->sucursalId))
            ->with('usuario')
            ->get();
    }

    #[Computed]
    public function sucursales()
    {
        return Sucursal::where('es_activa', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    #[Computed]
    public function puedeElegirSucursal(): bool
    {
        $user = auth()->user();

        // El admin de sucursal queda fijo a su sede; el dueño (global) puede cambiar.
        return ! ($user?->esAdminSucursal() && $user->getSucursalId());
    }

    #[Computed]
    public function caja(): ?Caja
    {
        return $this->cajaId ? Caja::with('sucursal')->find($this->cajaId) : null;
    }

    #[Computed]
    public function citaActiva(): ?Cita
    {
        return $this->citaId
            ? Cita::with(['cliente', 'empleado.usuario'])->find($this->citaId)
            : null;
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->items)->sum(fn ($item) => $item['precio'] * $item['cantidad']);
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal - $this->descuento + $this->propina);
    }

    // --- Acciones de carrito ---

    public function agregarServicio(int $id): void
    {
        $this->agregarItem('servicio', $id);
    }

    public function agregarProducto(int $id): void
    {
        $this->agregarItem('producto', $id);
    }

    private function agregarItem(string $tipo, int $id): void
    {
        $key = "{$tipo}_{$id}";

        foreach ($this->items as $idx => $item) {
            if ($item['key'] === $key) {
                $this->items[$idx]['cantidad']++;
                return;
            }
        }

        if ($tipo === 'servicio') {
            $model  = Servicio::find($id);
            $precio = $model->precioParaEmpleado($this->empleadoId ?? 0);
        } else {
            $model  = Producto::find($id);
            $precio = $model->precio_venta;
        }

        $this->items[] = [
            'key'      => $key,
            'tipo'     => $tipo,
            'id'       => $id,
            'nombre'   => $model->nombre,
            'precio'   => $precio,
            'cantidad' => 1,
        ];
    }

    public function incrementar(int $index): void
    {
        $this->items[$index]['cantidad']++;
    }

    public function decrementar(int $index): void
    {
        if ($this->items[$index]['cantidad'] > 1) {
            $this->items[$index]['cantidad']--;
        } else {
            $this->remover($index);
        }
    }

    public function remover(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function limpiarCarrito(): void
    {
        $this->items      = [];
        $this->descuento  = 0;
        $this->propina    = 0;
        $this->clienteId  = null;
        $this->metodoPago = 'efectivo';
        $this->citaId     = null;
    }

    // --- Proceso de venta ---

    public function completarVenta(): void
    {
        if (! $this->cajaId) {
            Notification::make()->title('Sin caja abierta')->body('Abre una caja antes de registrar ventas.')->danger()->send();
            return;
        }
        if (empty($this->items)) {
            Notification::make()->title('Carrito vacío')->body('Agrega al menos un servicio o producto.')->warning()->send();
            return;
        }
        if (! $this->empleadoId) {
            Notification::make()->title('Selecciona un empleado')->warning()->send();
            return;
        }

        DB::transaction(function () {
            $venta = Venta::create([
                'caja_id'     => $this->cajaId,
                'empleado_id' => $this->empleadoId,
                'cliente_id'  => $this->clienteId,
                'cita_id'     => $this->citaId,
                'subtotal'    => $this->subtotal,
                'descuento'   => $this->descuento,
                'propina'     => $this->propina,
                'total'       => $this->total,
                'metodo_pago' => $this->metodoPago,
                'estado'      => 'completada',
            ]);

            foreach ($this->items as $item) {
                ItemVenta::create([
                    'venta_id'        => $venta->id,
                    'tipo'            => $item['tipo'],
                    'servicio_id'     => $item['tipo'] === 'servicio' ? $item['id'] : null,
                    'producto_id'     => $item['tipo'] === 'producto' ? $item['id'] : null,
                    'descripcion'     => $item['nombre'],
                    'precio_unitario' => $item['precio'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['precio'] * $item['cantidad'],
                ]);

                if ($item['tipo'] === 'producto') {
                    MovimientoInventario::create([
                        'producto_id' => $item['id'],
                        'usuario_id'  => Auth::id(),
                        'sucursal_id' => $this->caja?->sucursal_id,
                        'tipo'        => 'salida',
                        'cantidad'    => $item['cantidad'],
                        'motivo'      => 'Venta en POS',
                        'referencia'  => 'VENTA-' . $venta->id,
                    ]);
                }
            }

            // Si la venta está vinculada a una cita, marcarla como completada
            if ($this->citaId) {
                Cita::where('id', $this->citaId)->update(['estado' => 'completada']);
            }
        });

        Notification::make()
            ->title('¡Venta completada!')
            ->body('Total cobrado: Q ' . number_format($this->total, 2))
            ->success()
            ->send();

        $this->limpiarCarrito();
    }
}
