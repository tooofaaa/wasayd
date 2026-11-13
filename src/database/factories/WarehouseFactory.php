<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        $company = Company::factory()->create();

        return [
            'company_id'  => $company->id,
            'branch_id'   => Branch::factory()->create(['company_id' => $company->id])->id,
            'name'        => $this->faker->lexify('Warehouse ????'),
            'name_ar'     => 'مستودع ' . $this->faker->lexify('????'),
            'code'        => strtoupper($this->faker->unique()->lexify('WH-????')),
            'type'        => 'standard',
            'is_active'   => true,
        ];
    }
}
