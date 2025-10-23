<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product_types = [
            'Supermercado',
            'Tecnología',
            'Farmacia',
            'Compra internacional',
            'Hogar y muebles',
            'Herramientas',
            'Construcción',
            'Deportes y Fitness',
            'Mascotas',
            'Moda',
            'Accesorios para tu vehículo',
            'Para tu negocio',
            'Juegos y juguetes',
            'Bebés',
            'Belleza y cuidado personal',
            'Salud y equipamiento médico',
            'Servicios',
        ];

        foreach ($product_types as $pt) {
            $id = DB::table('lunar_product_types')->insertGetId([
                'name' => $pt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lunar_attributables')->insert([
                'attributable_type' => 'product_type',
                'attributable_id' => $id,
                'attribute_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lunar_attributables')->insert([
                'attributable_type' => 'product_type',
                'attributable_id' => $id,
                'attribute_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
