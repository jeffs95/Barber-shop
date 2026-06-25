<?php

namespace Database\Seeders;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = Usuario::where('email', 'admin@barberia.com')->first();
        $centro   = Sucursal::where('nombre', 'Sucursal Centro')->first();
        $norte    = Sucursal::where('nombre', 'Sucursal Norte')->first();

        // Proveedores
        $distribuidora = Proveedor::create([
            'nombre'    => 'Distribuidora Capilar GT',
            'contacto'  => 'Héctor Morales',
            'telefono'  => '2345-6789',
            'email'     => 'ventas@capilargt.com',
            'es_activo' => true,
        ]);

        $belleza = Proveedor::create([
            'nombre'    => 'Belleza Profesional S.A.',
            'contacto'  => 'Ana Flores',
            'telefono'  => '5678-1234',
            'es_activo' => true,
        ]);

        $herramientas = Proveedor::create([
            'nombre'    => 'Import Tools Centro',
            'contacto'  => 'Luis Barrios',
            'telefono'  => '4455-6677',
            'es_activo' => true,
        ]);

        // Productos (se crean con stock_actual = 0, luego se añaden entradas)
        $productos = [
            // Cuidado del cabello
            [
                'proveedor_id'  => $distribuidora->id,
                'nombre'        => 'Shampoo American Crew Classic',
                'descripcion'   => 'Shampoo con extracto de romero y menta, 500 ml',
                'codigo_barras' => '0123456789012',
                'precio_compra' => 55.00,
                'precio_venta'  => 95.00,
                'stock_minimo'  => 3,
                'unidad'        => 'unidad',
                'categoria'     => 'cuidado_cabello',
                'stock_entrada' => 12,
            ],
            [
                'proveedor_id'  => $distribuidora->id,
                'nombre'        => "Pomada Matte Morgan's",
                'descripcion'   => 'Pomada de fijación fuerte acabado mate, 100g',
                'codigo_barras' => '0123456789013',
                'precio_compra' => 45.00,
                'precio_venta'  => 80.00,
                'stock_minimo'  => 5,
                'unidad'        => 'unidad',
                'categoria'     => 'cuidado_cabello',
                'stock_entrada' => 20,
            ],
            [
                'proveedor_id'  => $distribuidora->id,
                'nombre'        => 'Cera Moldeadora Layrite',
                'descripcion'   => 'Cera suave fijación media brillo natural, 113g',
                'codigo_barras' => '0123456789014',
                'precio_compra' => 50.00,
                'precio_venta'  => 90.00,
                'stock_minimo'  => 3,
                'unidad'        => 'unidad',
                'categoria'     => 'cuidado_cabello',
                'stock_entrada' => 10,
            ],
            // Barba
            [
                'proveedor_id'  => $belleza->id,
                'nombre'        => 'Aceite para Barba Beardbrand',
                'descripcion'   => 'Aceite nutritivo con vitamina E, 30 ml',
                'codigo_barras' => '0123456789015',
                'precio_compra' => 60.00,
                'precio_venta'  => 110.00,
                'stock_minimo'  => 4,
                'unidad'        => 'unidad',
                'categoria'     => 'barba',
                'stock_entrada' => 8,
            ],
            [
                'proveedor_id'  => $belleza->id,
                'nombre'        => 'Bálsamo de Barba Honest Amish',
                'descripcion'   => 'Bálsamo humectante con aceites naturales, 60g',
                'codigo_barras' => '0123456789016',
                'precio_compra' => 50.00,
                'precio_venta'  => 90.00,
                'stock_minimo'  => 3,
                'unidad'        => 'unidad',
                'categoria'     => 'barba',
                'stock_entrada' => 6,
            ],
            // Consumibles
            [
                'proveedor_id'  => $herramientas->id,
                'nombre'        => 'Hoja de Navaja Derby Extra',
                'descripcion'   => 'Paquete de 100 hojas doble filo',
                'codigo_barras' => '0123456789017',
                'precio_compra' => 25.00,
                'precio_venta'  => 0.00,
                'stock_minimo'  => 2,
                'unidad'        => 'unidad',
                'categoria'     => 'consumible',
                'stock_entrada' => 5,
            ],
            [
                'proveedor_id'  => $distribuidora->id,
                'nombre'        => 'Talco para Cuello Clubman',
                'descripcion'   => 'Talco antibacterial para cuello y cara, 255g',
                'codigo_barras' => '0123456789018',
                'precio_compra' => 30.00,
                'precio_venta'  => 0.00,
                'stock_minimo'  => 3,
                'unidad'        => 'unidad',
                'categoria'     => 'consumible',
                'stock_entrada' => 4,
            ],
            // Herramientas
            [
                'proveedor_id'  => $herramientas->id,
                'nombre'        => 'Máquina Cortadora Wahl Magic Clip',
                'descripcion'   => 'Cortadora inalámbrica profesional, 5 velocidades',
                'codigo_barras' => '0123456789019',
                'precio_compra' => 800.00,
                'precio_venta'  => 0.00,
                'stock_minimo'  => 1,
                'unidad'        => 'unidad',
                'categoria'     => 'herramienta',
                'stock_entrada' => 3,
            ],
            // Producto con stock bajo (para probar badge)
            [
                'proveedor_id'  => $distribuidora->id,
                'nombre'        => 'Spray Fijador Redken Brews',
                'descripcion'   => 'Spray de fijación flexible, 200 ml',
                'codigo_barras' => '0123456789020',
                'precio_compra' => 40.00,
                'precio_venta'  => 70.00,
                'stock_minimo'  => 5,
                'unidad'        => 'unidad',
                'categoria'     => 'cuidado_cabello',
                'stock_entrada' => 2, // intencionalmente bajo el mínimo
            ],
        ];

        foreach ($productos as $data) {
            $stockTotal = $data['stock_entrada'];
            unset($data['stock_entrada']);

            $producto = Producto::create(array_merge($data, ['stock_actual' => 0]));

            // Distribuir el stock inicial entre las dos sucursales (60 % Centro, 40 % Norte)
            $stockCentro = (int) ceil($stockTotal * 0.6);
            $stockNorte  = $stockTotal - $stockCentro;

            if ($centro) {
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'usuario_id'  => $admin?->id,
                    'sucursal_id' => $centro->id,
                    'tipo'        => 'entrada',
                    'cantidad'    => $stockCentro,
                    'motivo'      => 'Stock inicial',
                    'referencia'  => 'SEED-001',
                ]);
            }

            if ($norte && $stockNorte > 0) {
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'usuario_id'  => $admin?->id,
                    'sucursal_id' => $norte->id,
                    'tipo'        => 'entrada',
                    'cantidad'    => $stockNorte,
                    'motivo'      => 'Stock inicial',
                    'referencia'  => 'SEED-001',
                ]);
            }
        }
    }
}
