<?php

namespace Database\Factories;

use App\Models\Aisle;
use App\Models\Company;
use App\Models\Rack;
use Illuminate\Database\Eloquent\Factories\Factory;

class RackFactory extends Factory
{
    protected $model = Rack::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'aisle_id'   => Aisle::factory(),
            'name'       => 'Rack ' . $this->faker->numberBetween(1, 99),
            'code'       => strtoupper($this->faker->unique()->lexify('R-??')),
            'is_active'  => true,
        ];
    }
}
