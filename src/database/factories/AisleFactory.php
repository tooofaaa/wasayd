<?php

namespace Database\Factories;

use App\Models\Aisle;
use App\Models\Company;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class AisleFactory extends Factory
{
    protected $model = Aisle::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'zone_id'    => Zone::factory(),
            'name'       => 'Aisle ' . strtoupper($this->faker->lexify('?')),
            'code'       => strtoupper($this->faker->unique()->lexify('A-??')),
            'is_active'  => true,
        ];
    }
}
