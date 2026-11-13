<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Models\Zone;
use App\Models\Bin;
use App\Models\Aisle;
use App\Models\Rack;
use App\Models\Shelf;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Security Scenarios — IDOR, Cross-Tenant Leaks, Privilege Escalation
 *
 * Plan reference: WASAYD Constitution principle #3 (TENANT-ISOLATION)
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.2, 1.4
 * Plan reference: Spec §3 Business Rules 2–5
 *
 * TEST SCENARIOS:
 * - Cross-tenant IDOR: user A cannot read company B's data
 * - Cross-tenant IDOR via ID guessing on items, locations
 * - Unauthorized company switch → 403
 * - Unauthorized warehouse switch (wrong company) → 403/404
 * - Permission escalation: viewer cannot do admin actions
 * - API cross-org IDOR with token
 * - Unauthenticated access to protected routes
 */
class SecurityScenariosTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;
    private Company $companyB;
    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->companyA = Company::factory()->create();
        $this->companyB = Company::factory()->create();
        $this->userA    = User::factory()->create(['company_id' => $this->companyA->id]);
        $this->userB    = User::factory()->create(['company_id' => $this->companyB->id]);
        $this->userA->assignRole('admin');
        $this->userB->assignRole('admin');
    }

    // ─── IDOR / CROSS-TENANT SCENARIOS ────────────────────────────────────────

    /** Scenario S1: User cannot read another company's item via API */
    public function test_S1_cannot_read_other_company_item_via_api(): void
    {
        $itemB = Item::withoutGlobalScope('company')->create([
            'company_id' => $this->companyB->id,
            'name'       => 'Company B Secret Item',
            'sku'        => 'B-SECRET-001',
            'item_type'  => 'medical_consumable',
        ]);

        $this->actingAs($this->userA);
        session(['active_company_id' => $this->companyA->id, 'two_factor_verified' => true]);

        $this->getJson("/api/v1/items/{$itemB->id}")
             ->assertNotFound();
    }

    /** Scenario S2: Items list only returns current company's items */
    public function test_S2_item_list_only_shows_own_company_items(): void
    {
        // Create 3 items for company A
        for ($i = 1; $i <= 3; $i++) {
            Item::withoutGlobalScope('company')->create([
                'company_id' => $this->companyA->id,
                'name'       => "Company A Item $i",
                'sku'        => "A-ITM-00$i",
                'item_type'  => 'medical_consumable',
            ]);
        }
        // Create 2 items for company B
        for ($i = 1; $i <= 2; $i++) {
            Item::withoutGlobalScope('company')->create([
                'company_id' => $this->companyB->id,
                'name'       => "Company B Item $i",
                'sku'        => "B-ITM-00$i",
                'item_type'  => 'medical_consumable',
            ]);
        }

        $this->actingAs($this->userA);
        session(['active_company_id' => $this->companyA->id, 'two_factor_verified' => true]);

        $response = $this->getJson('/api/v1/items');
        $data     = $response->json('data');
        $this->assertCount(3, $data);

        // None of the returned items belong to company B
        foreach ($data as $item) {
            $this->assertNotEquals('B-ITM-001', $item['sku'] ?? '');
            $this->assertNotEquals('B-ITM-002', $item['sku'] ?? '');
        }
    }

    /** Scenario S3: BelongsToCompany prevents cross-tenant ID guessing */
    public function test_S3_belongs_to_company_prevents_id_guessing(): void
    {
        // Company B creates an item with a predictable ID
        $itemB = Item::withoutGlobalScope('company')->forceCreate([
            'company_id' => $this->companyB->id,
            'name'       => 'Sensitive B Item',
            'sku'        => 'B-SENSITIVE',
            'item_type'  => 'medicine',
        ]);

        // User A tries to query it directly via model
        $this->actingAs($this->userA);
        session(['active_company_id' => $this->companyA->id]);

        // With the global scope, this should return null
        $found = Item::find($itemB->id);
        $this->assertNull($found, 'Cross-tenant item should not be visible via Eloquent global scope');
    }

    /** Scenario S4: Switching to unauthorized company returns 403 */
    public function test_S4_switching_to_nonexistent_company_returns_403(): void
    {
        $this->actingAs($this->userA);

        $response = $this->post('/tenant/switch', ['company_id' => 99999]);
        // Should be 403 or 404 (company doesn't exist)
        $this->assertContains($response->status(), [302, 403, 404, 422],
            'Switching to nonexistent company should fail gracefully');
    }

    /** Scenario S5: Switching to warehouse from wrong company fails */
    public function test_S5_switching_to_wrong_company_warehouse_fails(): void
    {
        $branchB = Branch::factory()->create(['company_id' => $this->companyB->id]);
        $warehouseB = Warehouse::factory()->create([
            'company_id' => $this->companyB->id,
            'branch_id'  => $branchB->id,
        ]);

        $this->actingAs($this->userA);
        session(['active_company_id' => $this->companyA->id]);

        // TenantContext::switchWarehouse() should reject cross-company warehouse
        $context = app(TenantContext::class);
        try {
            $context->switchWarehouse($warehouseB->id);
            $this->fail('Should have thrown an exception for cross-company warehouse switch');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertContains($e->getStatusCode(), [403, 404]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertTrue(true); // also acceptable — warehouse not found in company scope
        }
    }

    /** Scenario S6: Zone locations are company-scoped */
    public function test_S6_zones_scoped_to_company(): void
    {
        $branchA    = Branch::factory()->create(['company_id' => $this->companyA->id]);
        $warehouseA = Warehouse::factory()->create(['company_id' => $this->companyA->id, 'branch_id' => $branchA->id]);
        $branchB    = Branch::factory()->create(['company_id' => $this->companyB->id]);
        $warehouseB = Warehouse::factory()->create(['company_id' => $this->companyB->id, 'branch_id' => $branchB->id]);

        Zone::factory()->create(['company_id' => $this->companyA->id, 'warehouse_id' => $warehouseA->id]);
        $zoneB = Zone::factory()->create(['company_id' => $this->companyB->id, 'warehouse_id' => $warehouseB->id]);

        // User A should not see Zone B
        $this->actingAs($this->userA);
        session(['active_company_id' => $this->companyA->id]);

        $found = Zone::find($zoneB->id);
        $this->assertNull($found, 'Cross-company zone should not be visible');
    }

    /** Scenario S7: Viewer role cannot perform admin actions */
    public function test_S7_viewer_cannot_perform_admin_actions(): void
    {
        $viewer = User::factory()->create(['company_id' => $this->companyA->id]);
        $viewer->assignRole('viewer');
        $this->actingAs($viewer);
        session(['active_company_id' => $this->companyA->id, 'two_factor_verified' => true]);

        // Cannot create items
        $this->assertFalse($viewer->can('items.create'));
        // Cannot manage users
        $this->assertFalse($viewer->can('users.create'));
        // Cannot configure roles
        $this->assertFalse($viewer->can('roles.create'));
        // Cannot adjust stock
        $this->assertFalse($viewer->can('stock.adjust'));
    }

    /** Scenario S8: Viewer CAN view but cannot modify */
    public function test_S8_viewer_has_read_permissions(): void
    {
        $viewer = User::factory()->create(['company_id' => $this->companyA->id]);
        $viewer->assignRole('viewer');

        $this->assertTrue($viewer->can('items.view'));
        $this->assertTrue($viewer->can('inventory.view'));
        $this->assertTrue($viewer->can('assets.view'));
    }

    /** Scenario S9: Super admin can do everything */
    public function test_S9_super_admin_can_do_everything(): void
    {
        $superAdmin = User::factory()->create(['company_id' => $this->companyA->id]);
        $superAdmin->assignRole('super-admin');

        $this->assertTrue($superAdmin->can('items.create'));
        $this->assertTrue($superAdmin->can('epc.decommission'));
        $this->assertTrue($superAdmin->can('controlled-meds.dispense'));
        $this->assertTrue($superAdmin->can('system.audit-log'));
    }

    /** Scenario S10: Unauthenticated user cannot access any protected route */
    public function test_S10_unauthenticated_cannot_access_protected_routes(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/admin/users',
            '/admin/roles',
            '/locations/warehouses',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $this->assertContains($response->status(), [302, 401],
                "Route $route should not be accessible without auth. Got: " . $response->status());
        }
    }

    /** Scenario S11: API endpoints require authentication */
    public function test_S11_api_endpoints_require_authentication(): void
    {
        $response = $this->getJson('/api/v1/items');
        $response->assertStatus(401);
    }

    /** Scenario S12: Health endpoint is public (no auth required) */
    public function test_S12_health_endpoint_is_public(): void
    {
        $response = $this->getJson('/api/health');
        $this->assertContains($response->status(), [200, 503]);
        $response->assertJsonStructure(['status', 'service']);
    }
}
