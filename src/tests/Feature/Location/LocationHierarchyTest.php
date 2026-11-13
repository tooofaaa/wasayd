<?php

namespace Tests\Feature\Location;

use App\Models\Bin;
use App\Models\Company;
use App\Models\Zone;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Models\Aisle;
use App\Models\Rack;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature 1.3 — Location Hierarchy (Zone → Bin)
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.3
 * Lean Loop Test Gate 1.3: Tests T1.3-A through T1.3-D
 */
class LocationHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;
    private Warehouse $warehouse;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company   = Company::factory()->create();
        $this->branch    = Branch::factory()->create(['company_id' => $this->company->id]);
        $this->warehouse = Warehouse::factory()->create([
            'company_id' => $this->company->id,
            'branch_id'  => $this->branch->id,
        ]);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->user);
        session(['active_company_id' => $this->company->id]);
    }

    /**
     * T1.3-A: Location hierarchy can be navigated (Zone → Bin path exists).
     * Plan: 5 tables (zones, aisles, racks, shelves, bins) in full hierarchy.
     */
    public function test_T1_3_A_location_hierarchy_navigation(): void
    {
        $zone  = Zone::factory()->create([
            'company_id'   => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
        $aisle = Aisle::factory()->create(['company_id' => $this->company->id, 'zone_id' => $zone->id]);
        $rack  = Rack::factory()->create(['company_id' => $this->company->id, 'aisle_id' => $aisle->id]);
        $shelf = Shelf::factory()->create(['company_id' => $this->company->id, 'rack_id' => $rack->id]);
        $bin   = Bin::factory()->create(['company_id' => $this->company->id, 'shelf_id' => $shelf->id]);

        // Verify hierarchy from bin up to warehouse
        $this->assertEquals($shelf->id,  $bin->shelf_id);
        $this->assertEquals($rack->id,   $shelf->rack_id);
        $this->assertEquals($aisle->id,  $rack->aisle_id);
        $this->assertEquals($zone->id,   $aisle->zone_id);
        $this->assertEquals($this->warehouse->id, $zone->warehouse_id);
    }

    /**
     * T1.3-B: Bin barcode lookup works.
     * Plan T1.3-B: scan bin barcode → get bin record.
     */
    public function test_T1_3_B_bin_barcode_lookup(): void
    {
        $shelf = $this->createShelf();
        $bin   = Bin::create([
            'company_id' => $this->company->id,
            'shelf_id'   => $shelf->id,
            'name'       => 'Test Bin',
            'code'       => 'BIN-TEST-001',
            'barcode'    => 'BIN-WH01-A-01-05',
        ]);

        $found = Bin::where('barcode', 'BIN-WH01-A-01-05')
                    ->where('company_id', $this->company->id)
                    ->first();

        $this->assertNotNull($found);
        $this->assertEquals($bin->id, $found->id);
    }

    /**
     * T1.3-C: Zone types exist as valid enum values.
     * Plan T1.3-D: Zone types include storage, receiving, quarantine, controlled.
     */
    public function test_T1_3_C_zone_types_are_valid(): void
    {
        $validTypes = ['receiving', 'storage', 'picking', 'shipping', 'quarantine', 'controlled'];

        foreach ($validTypes as $i => $type) {
            $zone = Zone::factory()->create([
                'company_id'   => $this->company->id,
                'warehouse_id' => $this->warehouse->id,
                'code'         => 'Z-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'type'         => $type,
            ]);
            $this->assertEquals($type, $zone->type);
        }

        $this->assertEquals(6, Zone::withoutGlobalScope('company')
            ->where('company_id', $this->company->id)->count());
    }

    /**
     * T1.3-D: Controlled zone is identifiable.
     * Plan: narcotic items must only be placed in controlled zones.
     */
    public function test_T1_3_D_controlled_zone_is_identifiable(): void
    {
        $controlledZone = Zone::factory()->create([
            'company_id'   => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
            'type'         => 'controlled',
        ]);
        $standardZone = Zone::factory()->create([
            'company_id'   => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
            'type'         => 'storage',
        ]);

        $this->assertEquals('controlled', $controlledZone->type);
        $this->assertNotEquals('controlled', $standardZone->type);

        // Can query all controlled zones in a warehouse
        $controlledZones = Zone::where('warehouse_id', $this->warehouse->id)
                                ->where('type', 'controlled')
                                ->count();
        $this->assertEquals(1, $controlledZones);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function createShelf(): Shelf
    {
        $zone  = Zone::factory()->create([
            'company_id'   => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
        $aisle = Aisle::factory()->create(['company_id' => $this->company->id, 'zone_id' => $zone->id]);
        $rack  = Rack::factory()->create(['company_id' => $this->company->id, 'aisle_id' => $aisle->id]);
        return Shelf::factory()->create(['company_id' => $this->company->id, 'rack_id' => $rack->id]);
    }
}
