<?php

namespace Database\Factories;

use App\Models\Bin;
use App\Models\Company;
use App\Models\Shelf;
use Illuminate\Database\Eloquent\Factories\Factory;

class BinFactory extends Factory
{
    protected $model = Bin::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'shelf_id'   => Shelf::factory(),
            'name'       => 'Bin ' . $this->faker->lexify('??-??'),
            'code'       => strtoupper($this->faker->unique()->lexify('BIN-????')),
            'barcode'    => 'BC-' . strtoupper($this->faker->unique()->lexify('??????')),
            'is_active'  => true,
        ];
    }
}
