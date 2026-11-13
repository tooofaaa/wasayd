<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name'       => $this->faker->city() . ' Branch',
            'name_ar'    => 'فرع ' . $this->faker->city(),
            'code'       => strtoupper($this->faker->unique()->lexify('BR-????')),
            'city'       => $this->faker->city(),
            'is_active'  => true,
        ];
    }
}
