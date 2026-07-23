<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Otomatis membuat departemen baru jika tidak didefinisikan saat dipanggil
            'department_id' => Department::factory(),
            'name' => 'Lokasi ' . $this->faker->word(),
            'type' => $this->faker->randomElement(['Lab', 'Gudang', 'Studio', 'Kelas', 'Ruang']),
        ];
    }
}