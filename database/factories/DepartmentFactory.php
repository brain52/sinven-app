<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Membuat kode acak 3 huruf (misal: TKJ, FAR)
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            // Membuat nama acak
            'name' => 'Jurusan ' . $this->faker->word(), 
        ];
    }
}