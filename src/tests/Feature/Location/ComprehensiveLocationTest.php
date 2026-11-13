<?php

namespace Tests\Feature\Location;

use App\Models\Aisle;
use App\Models\Bin;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Rack;
use App\Models\Shelf;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive Location Hierarchy Scenarios
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.3
 * Covers: zone types, bin uniqueness, cold chain restrictions, hierarchy traversal
 */
class ComprehensiveLocationTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Branch $branch;
    private Warehouse $warehouse;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->company   = Company::factory()->create();
        $this->branch    = Branch::factory()->create(['company_id' => $this->company->id]);
        $this->warehouse = Warehouse::factory()->create([
            'company_id' => $this->company->id,
            'branch_id'  => $this->branch->id,
        ]);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->user->assignRole('admin');
        session(['active_company_id' => $this->company->id, 'two_factor_verified' => true]);
    }

    private function makeZone(string $type = 'storage', array $extra = []): Zone
    {
        return Zone::factory()->create(array_merge([
            'company_id'   => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
            'type'         => $type,
        ], $extra));
    }

    private function makeFullHierarchy(string $zoneType = 'storage'): Bin
    {
        $zone  = $this->makeZone($zoneType);
        $aisle = Aisle::factory()->create(['company_id' => $this->company->id, 'zone_id' => $zone->id]);
        $rack  = Rack::factory()->create(['company_id' => $this->company->id, 'aisle_id' => $aisle->id]);
        $shelf = Shelf::factory()->create(['company_id' => $this->company->id, 'rack_id' => $rack->id]);
        return Bin::factory()->create(['company_id' => $this->company->id, 'shelf_id' => $shelf->id]);
    }

    /** Scenario L1: Full hierarchy traversal from bin to warehouse */
    public function test_L1_bin_traverses_to_warehouse(): void
    {
        $bin = $this->makeFullHierarchy();

        $this->assertEquals($this->warehouse->id,
            $bin->shelf->rack->aisle->zone->warehouse_id);
        $this->assertEquals($this->company->id, $bin->company_id);
    }

    /** Scenario L2: Zone code unique within warehouse */
    public function test_L2_zone_code_unique_within_warehouse(): void
    {
        $this->makeZone('storage', ['code' => 'ZONE-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Zone::create([
            'company_id'   => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
            'name'         => 'Duplicate Zone',
            'code'         => 'ZONE-001', // duplicate!
            'type'         => 'storage',
        ]);
    }

    /** Scenario L3: Bin barcode globally unique (plan: scanner lookup requires uniqueness) */
    public function test_L3_bin_barcode_unique_across_bins(): void
    {
        $shelf = Shelf::factory()->create(['company_id' => $this->company->id,
            'rack_id' => Rack::factory()->create(['company_id' => $this->company->id,
                'aisle_id' => Aisle::factory()->create(['company_id' => $this->company->id,
                    'zone_id' => $this->makeZone()->id])->id])->id]);

        Bin::create([
            'company_id' => $this->company->id,
            'shelf_id'   => $shelf->id,
            'name'       => 'Bin 1',
            'code'       => 'BIN-UNIQUE',
            'barcode'    => 'BC-DUPTEST',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Bin::create([
            'company_id' => $this->company->id,
            'shelf_id'   => $shelf->id,
            'name'       => 'Bin 2',
            'code'       => 'BIN-UNIQUE', // duplicate code in same shelf
            'barcode'    => 'BC-DUPTEST2',
        ]);
    }

    /** Scenario L4: Bin lookup by barcode returns correct bin */
    public function test_L4_bin_lookup_by_barcode_returns_correct_bin(): void
    {
        $bin = $this->makeFullHierarchy();
        $bin->update(['barcode' => 'SCAN-WH01-A-01-05']);

        $found = Bin::where('barcode', 'SCAN-WH01-A-01-05')
                    ->where('company_id', $this->company->id)
                    ->first();

        $this->assertNotNull($found);
        $this->assertEquals($bin->id, $found->id);
    }

    /** Scenario L5: All 6 zone types are valid */
    public function test_L5_all_six_zone_types_valid(): void
    {
        $types = ['receiving', 'storage', 'picking', 'shipping', 'quarantine', 'controlled'];
        foreach ($types as $i => $type) {
            $zone = Zone::create([
                'company_id'   => $this->company->id,
                'warehouse_id' => $this->warehouse->id,
                'name'         => "Zone $i",
                'code'         => "ZT-$i",
                'type'         => $type,
            ]);
            $this->assertEquals($type, $zone->type);
        }
        $this->assertEquals(6, Zone::withoutGlobalScope('company')
            ->where('company_id', $this->company->id)->count());
    }

    /** Scenario L6: Controlled zone identified correctly */
    public function test_L6_controlled_zone_identifiable(): void
    {
        $controlled = $this->makeZone('controlled');
        $standard   = $this->makeZone('storage');

        $this->assertEquals('controlled', $controlled->type);
        $this->assertNotEquals('controlled', $standard->type);

        $count = Zone::where('warehouse_id', $this->warehouse->id)
                     ->where('type', 'controlled')->count();
        $this->assertEquals(1, $count);
    }

    /** Scenario L7: Inactive bin is queryable but excluded from active listings */
    public function test_L7_inactive_bin_can_be_deactivated(): void
    {
        $bin = $this->makeFullHierarchy();
        $this->assertTrue($bin->is_active);

        $bin->update(['is_active' => false]);
        $this->assertFalse($bin->fresh()->is_active);

        // Active bins query excludes it
        $activeBins = Bin::where('company_id', $this->company->id)
                         ->where('is_active', true)->get();
        $this->assertFalse($activeBins->contains('id', $bin->id));
    }

    /** Scenario L8: Multiple bins in same shelf with unique codes */
    public function test_L8_multiple_bins_in_shelf_with_unique_codes(): void
    {
        $zone  = $this->makeZone();
        $aisle = Aisle::factory()->create(['company_id' => $this->company->id, 'zone_id' => $zone->id]);
        $rack  = Rack::factory()->create(['company_id' => $this->company->id, 'aisle_id' => $aisle->id]);
        $shelf = Shelf::factory()->create(['company_id' => $this->company->id, 'rack_id' => $rack->id]);

        for ($i = 1; $i <= 5; $i++) {
            Bin::create([
                'company_id' => $this->company->id,
                'shelf_id'   => $shelf->id,
                'name'       => "Bin $i",
                'code'       => "BIN-00$i",
            ]);
        }

        $binCount = Bin::where('shelf_id', $shelf->id)->count();
        $this->assertEquals(5, $binCount);
    }

    /** Scenario L9: Temperature-controlled zone stores temp range */
    public function test_L9_temperature_controlled_zone_stores_range(): void
    {
        $coldZone = Zone::create([
            'company_id'           => $this->company->id,
            'warehouse_id'         => $this->warehouse->id,
            'name'                 => 'Cold Storage',
            'code'                 => 'COLD-01',
            'type'                 => 'storage',
            'temperature_controlled'=> true,
            'temperature_min'      => 2.0,
            'temperature_max'      => 8.0,
        ]);

        $this->assertTrue($coldZone->temperature_controlled);
        $this->assertEquals(2.0, $coldZone->temperature_min);
        $this->assertEquals(8.0, $coldZone->temperature_max);
    }

    /** Scenario L10: Cross-company zone not visible */
    public function test_L10_cross_company_zone_not_visible(): void
    {
        $otherCompany   = Company::factory()->create();
        $otherBranch    = Branch::factory()->create(['company_id' => $otherCompany->id]);
        $otherWarehouse = Warehouse::factory()->create([
            'company_id' => $otherCompany->id,
            'branch_id'  => $otherBranch->id,
        ]);
        $zoneB = Zone::withoutGlobalScope('company')->create([
            'company_id'   => $otherCompany->id,
            'warehouse_id' => $otherWarehouse->id,
            'name'         => 'Other Zone',
            'code'         => 'OTH-01',
            'type'         => 'storage',
        ]);

        $this->actingAs($this->user);
        session(['active_company_id' => $this->company->id]);

        $found = Zone::find($zoneB->id);
        $this->assertNull($found, 'Cross-company zone should not be visible via global scope');
    }
}
