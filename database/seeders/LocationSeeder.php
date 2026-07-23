<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['department_id' => 1, 'name' => 'Lab TKJ Dasar', 'type' => 'Lab'],
            ['department_id' => 1, 'name' => 'Lab Jaringan Komputer', 'type' => 'Lab'],
            ['department_id' => 2, 'name' => 'Lab Farmakognosi', 'type' => 'Lab'],
            ['department_id' => 3, 'name' => 'Studio Produksi', 'type' => 'Studio'],
            ['department_id' => 4, 'name' => 'Gudang Sarpras Utama', 'type' => 'Gudang'],
        ];

        foreach ($locations as $loc) {
            Location::create($loc);
        }
    }
}