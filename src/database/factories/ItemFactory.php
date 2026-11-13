<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'company_id'   => Company::factory(),
            'name'         => $this->faker->words(3, true),
            'name_ar'      => $this->faker->words(3, true),
            'sku'          => strtoupper($this->faker->unique()->lexify('ITM-????')),
            'item_type'    => 'medical_consumable',
            'is_active'    => true,
        ];
    }

    public function batchControlled(): static
    {
        return $this->state(['requires_batch' => true]);
    }

    public function expiryControlled(): static
    {
        return $this->state(['requires_expiry' => true, 'requires_batch' => true]);
    }

    public function serialControlled(): static
    {
        return $this->state(['requires_serial' => true]);
    }

    public function rfidEnabled(): static
    {
        return $this->state(['requires_rfid' => true]);
    }

    public function controlled(): static
    {
        return $this->state([
            'item_type'    => 'narcotic_controlled',
            'is_controlled'=> true,
        ]);
    }
}
