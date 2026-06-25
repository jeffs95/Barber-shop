<?php

namespace Database\Seeders;

use App\Models\CategoriaServicio;
use App\Models\Combo;
use App\Models\Servicio;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Corte de Cabello',   'descripcion' => 'Cortes clásicos, modernos y fade'],
            ['nombre' => 'Arreglo de Barba',   'descripcion' => 'Perfilado, afeitado y diseño de barba'],
            ['nombre' => 'Tratamiento Capilar','descripcion' => 'Hidratación, keratina y nutrición'],
            ['nombre' => 'Color y Tinte',      'descripcion' => 'Coloración, mechas y decoloración'],
        ];

        foreach ($categorias as $cat) {
            CategoriaServicio::firstOrCreate(['nombre' => $cat['nombre']], $cat);
        }

        $corte   = CategoriaServicio::where('nombre', 'Corte de Cabello')->first();
        $barba   = CategoriaServicio::where('nombre', 'Arreglo de Barba')->first();
        $trato   = CategoriaServicio::where('nombre', 'Tratamiento Capilar')->first();
        $color   = CategoriaServicio::where('nombre', 'Color y Tinte')->first();

        $servicios = [
            ['categoria_servicio_id' => $corte->id,  'nombre' => 'Corte Clásico',            'precio_base' => 35, 'duracion_minutos' => 30],
            ['categoria_servicio_id' => $corte->id,  'nombre' => 'Corte Moderno / Fade',     'precio_base' => 50, 'duracion_minutos' => 45],
            ['categoria_servicio_id' => $corte->id,  'nombre' => 'Corte Niños (hasta 10 años)','precio_base' => 25,'duracion_minutos' => 20],
            ['categoria_servicio_id' => $barba->id,  'nombre' => 'Perfilado de Barba',       'precio_base' => 25, 'duracion_minutos' => 20],
            ['categoria_servicio_id' => $barba->id,  'nombre' => 'Afeitado Clásico con Navaja','precio_base' => 40,'duracion_minutos' => 30],
            ['categoria_servicio_id' => $trato->id,  'nombre' => 'Hidratación Capilar',      'precio_base' => 80, 'duracion_minutos' => 40],
            ['categoria_servicio_id' => $color->id,  'nombre' => 'Tinte Completo',           'precio_base' => 120,'duracion_minutos' => 60],
        ];

        foreach ($servicios as $srv) {
            Servicio::firstOrCreate(
                ['nombre' => $srv['nombre']],
                array_merge($srv, ['es_activo' => true])
            );
        }

        $corteClasico  = Servicio::where('nombre', 'Corte Clásico')->first();
        $perfiladoBarba = Servicio::where('nombre', 'Perfilado de Barba')->first();
        $corteModerno  = Servicio::where('nombre', 'Corte Moderno / Fade')->first();
        $afeitado      = Servicio::where('nombre', 'Afeitado Clásico con Navaja')->first();

        $combo1 = Combo::firstOrCreate(
            ['nombre' => 'Corte + Barba'],
            [
                'descripcion'          => 'Corte clásico y perfilado de barba',
                'precio'               => 55,
                'porcentaje_descuento' => 9,
                'es_activo'            => true,
            ]
        );
        $combo1->servicios()->syncWithoutDetaching([$corteClasico->id, $perfiladoBarba->id]);

        $combo2 = Combo::firstOrCreate(
            ['nombre' => 'Combo Premium'],
            [
                'descripcion'          => 'Corte moderno, afeitado clásico con navaja',
                'precio'               => 80,
                'porcentaje_descuento' => 11,
                'es_activo'            => true,
            ]
        );
        $combo2->servicios()->syncWithoutDetaching([$corteModerno->id, $afeitado->id]);
    }
}
