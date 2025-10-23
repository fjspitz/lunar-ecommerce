<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lunar_brands')->insert([
            ['name' => 'Arcor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'La Serenísima', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quilmes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manaos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bagley', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Havanna', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bimbo', 'created_at' => now(), 'updated_at' => now()], // Panificados
            ['name' => 'Marolio', 'created_at' => now(), 'updated_at' => now()], // Alimentos
            ['name' => 'Terrabusi', 'created_at' => now(), 'updated_at' => now()], // Galletitas
            ['name' => 'Jorgito', 'created_at' => now(), 'updated_at' => now()], // Alfajores
            ['name' => 'Sancor', 'created_at' => now(), 'updated_at' => now()], // Lácteos
            ['name' => 'Coto', 'created_at' => now(), 'updated_at' => now()], // Supermercados
            ['name' => 'Taragüi', 'created_at' => now(), 'updated_at' => now()], // Yerba Mate
            ['name' => 'Paty', 'created_at' => now(), 'updated_at' => now()], // Congelados/Hamburguesas
            ['name' => 'Vicentín', 'created_at' => now(), 'updated_at' => now()], // Aceites/Granos
            ['name' => 'Ala', 'created_at' => now(), 'updated_at' => now()], // Limpieza
            ['name' => 'Zanella', 'created_at' => now(), 'updated_at' => now()], // Motos/Vehículos
            ['name' => 'Tarjeta Naranja', 'created_at' => now(), 'updated_at' => now()], // Financiera
            ['name' => 'Personal', 'created_at' => now(), 'updated_at' => now()], // Telefonía
            ['name' => 'Puma', 'created_at' => now(), 'updated_at' => now()], // Combustibles
        ]);
    }
}
