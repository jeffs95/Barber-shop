<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\ItemVenta;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Database\Seeder;

class VentaSeeder extends Seeder
{
    public function run(): void
    {
        $admin   = Usuario::where('email', 'admin@barberia.com')->first();
        $barbero = Empleado::where('rol', 'barbero')->first();
        $clientes = Cliente::all();

        $corte  = Servicio::where('nombre', 'Corte Clásico')->first();
        $barba  = Servicio::where('nombre', 'Perfilado de Barba')->first();
        $fade   = Servicio::where('nombre', 'Corte Moderno / Fade')->first();
        $pomada = Producto::where('nombre', 'like', 'Pomada%')->first();
        $aceite = Producto::where('nombre', 'like', 'Aceite%')->first();

        $sucursal = Sucursal::first();

        // Caja abierta para el día de hoy
        $caja = Caja::create([
            'usuario_id'    => $admin->id,
            'sucursal_id'   => $sucursal?->id,
            'fecha_apertura'=> now()->setTime(8, 0),
            'monto_inicial' => 500.00,
            'estado'        => 'abierta',
        ]);

        $ventas = [
            [
                'empleado_id' => $barbero->id,
                'cliente_id'  => $clientes[0]->id,
                'metodo_pago' => 'efectivo',
                'propina'     => 10.00,
                'items'       => [
                    ['tipo' => 'servicio', 'modelo' => $corte, 'precio' => $corte->precio_base, 'cantidad' => 1],
                ],
            ],
            [
                'empleado_id' => $barbero->id,
                'cliente_id'  => $clientes[1]->id,
                'metodo_pago' => 'tarjeta',
                'propina'     => 0,
                'items'       => [
                    ['tipo' => 'servicio', 'modelo' => $corte, 'precio' => $corte->precio_base, 'cantidad' => 1],
                    ['tipo' => 'servicio', 'modelo' => $barba, 'precio' => $barba->precio_base, 'cantidad' => 1],
                    ['tipo' => 'producto', 'modelo' => $pomada, 'precio' => $pomada->precio_venta, 'cantidad' => 1],
                ],
            ],
            [
                'empleado_id' => $barbero->id,
                'cliente_id'  => $clientes[2]->id,
                'metodo_pago' => 'efectivo',
                'propina'     => 15.00,
                'items'       => [
                    ['tipo' => 'servicio', 'modelo' => $fade, 'precio' => $fade->precio_base, 'cantidad' => 1],
                    ['tipo' => 'producto', 'modelo' => $aceite, 'precio' => $aceite->precio_venta, 'cantidad' => 1],
                ],
            ],
            [
                'empleado_id' => $barbero->id,
                'cliente_id'  => null,
                'metodo_pago' => 'efectivo',
                'propina'     => 5.00,
                'items'       => [
                    ['tipo' => 'servicio', 'modelo' => $corte, 'precio' => $corte->precio_base, 'cantidad' => 1],
                ],
            ],
        ];

        foreach ($ventas as $data) {
            $itemsData = $data['items'];
            unset($data['items']);

            $subtotal = collect($itemsData)->sum(fn ($i) => $i['precio'] * $i['cantidad']);
            $total    = $subtotal + ($data['propina'] ?? 0);

            $venta = Venta::create(array_merge($data, [
                'caja_id'  => $caja->id,
                'subtotal' => $subtotal,
                'descuento'=> 0,
                'total'    => $total,
                'estado'   => 'completada',
            ]));

            foreach ($itemsData as $item) {
                ItemVenta::create([
                    'venta_id'        => $venta->id,
                    'tipo'            => $item['tipo'],
                    'servicio_id'     => $item['tipo'] === 'servicio' ? $item['modelo']->id : null,
                    'producto_id'     => $item['tipo'] === 'producto' ? $item['modelo']->id : null,
                    'descripcion'     => $item['modelo']->nombre,
                    'precio_unitario' => $item['precio'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['precio'] * $item['cantidad'],
                ]);

                if ($item['tipo'] === 'producto') {
                    MovimientoInventario::create([
                        'producto_id' => $item['modelo']->id,
                        'usuario_id'  => $admin->id,
                        'tipo'        => 'salida',
                        'cantidad'    => $item['cantidad'],
                        'motivo'      => 'Venta en barbería',
                        'referencia'  => 'VENTA-' . $venta->id,
                    ]);
                }
            }
        }
    }
}
