<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name'            => $this->faker->company(),
            'name_ar'         => 'شركة ' . $this->faker->word(),
            'code'            => strtoupper($this->faker->unique()->lexify('CO-????')),
            'email'           => $this->faker->companyEmail(),
            'phone'           => $this->faker->phoneNumber(),
            'country'         => 'SA',
            'currency'        => 'SAR',
            'timezone'        => 'Asia/Riyadh',
            'locale'          => 'ar',
            'is_active'       => true,
        ];
    }
}
