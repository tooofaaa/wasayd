<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Rack;
use App\Models\Shelf;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShelfFactory extends Factory
{
    protected $model = Shelf::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'rack_id'    => Rack::factory(),
            'name'       => 'Shelf ' . $this->faker->numberBetween(1, 10),
            'code'       => strtoupper($this->faker->unique()->lexify('S-??')),
            'is_active'  => true,
        ];
    }
}
