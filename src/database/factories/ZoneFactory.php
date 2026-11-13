<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        $warehouse = Warehouse::factory()->create();
        return [
            'company_id'   => $warehouse->company_id,
            'warehouse_id' => $warehouse->id,
            'name'         => $this->faker->word() . ' Zone',
            'name_ar'      => 'منطقة ' . $this->faker->word(),
            'code'         => strtoupper($this->faker->unique()->lexify('Z-????')),
            'type'         => 'storage',
            'is_active'    => true,
        ];
    }

    public function storage(): static    { return $this->state(['type' => 'storage']); }
    public function receiving(): static  { return $this->state(['type' => 'receiving']); }
    public function quarantine(): static { return $this->state(['type' => 'quarantine']); }
    public function controlled(): static { return $this->state(['type' => 'controlled']); }

    public function coldChain(bool $autoQuarantine = false): static
    {
        return $this->state([
            'type'                 => 'storage',
            'temperature_controlled' => true,
            'temperature_min'      => 2.0,
            'temperature_max'      => 8.0,
        ]);
    }
}
