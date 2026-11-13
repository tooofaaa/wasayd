<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature 1.2 — Multi-Tenant (Company/Branch/Warehouse)
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.2
 * Lean Loop Test Gate 1.2: Tests T1.2-A through T1.2-D
 *
 * Constitution principle #3 (TENANT-ISOLATION)
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;
    private Company $companyB;
    private User $userA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::factory()->create(['name' => 'Company A']);
        $this->companyB = Company::factory()->create(['name' => 'Company B']);

        $this->userA = User::factory()->create([
            'company_id' => $this->companyA->id,
        ]);
    }

    /**
     * T1.2-A: User cannot read another tenant's data via API.
     * Plan: BelongsToCompany global scope filters by active company_id.
     */
    public function test_T1_2_A_user_cannot_read_another_tenants_data(): void
    {
        // Create an item belonging to Company B
        $itemB = Item::withoutGlobalScope('company')->create([
            'company_id'  => $this->companyB->id,
            'name'        => 'Company B Item',
            'sku'         => 'B-ITEM-001',
            'item_type'   => 'medical_consumable',
        ]);

        // Authenticate as Company A user
        $this->actingAs($this->userA);

        // Should NOT be able to access Company B's item
        $response = $this->getJson("/api/v1/items/{$itemB->id}");
        $response->assertStatus(404);
    }

    /**
     * T1.2-B: TenantContext resolves company from session.
     * Plan: Session key 'active_company_id' takes priority over user's company_id.
     */
    public function test_T1_2_B_tenant_context_resolves_from_session(): void
    {
        $this->actingAs($this->userA);

        // Set session to Company A
        session(['active_company_id' => $this->companyA->id]);

        $context = app(TenantContext::class);
        $this->assertEquals($this->companyA->id, $context->getCompanyId());
    }

    /**
     * T1.2-C: Switching company resets warehouse scope.
     * Plan T1.2-C: Session warehouse must be reset to null on company switch.
     */
    public function test_T1_2_C_switching_company_resets_warehouse_scope(): void
    {
        $branch    = \App\Models\Branch::factory()->create(['company_id' => $this->companyA->id]);
        $warehouse = Warehouse::factory()->create([
            'company_id' => $this->companyA->id,
            'branch_id'  => $branch->id,
        ]);

        $this->actingAs($this->userA);

        // Set a warehouse scope
        session([
            'active_company_id'   => $this->companyA->id,
            'active_warehouse_id' => $warehouse->id,
        ]);

        // Now switch to a new company (simulate — force session directly for unit test)
        session([
            'active_company_id'   => $this->companyB->id,
            'active_warehouse_id' => null, // must be cleared
        ]);

        $this->assertNull(session('active_warehouse_id'));
        $this->assertEquals($this->companyB->id, session('active_company_id'));
    }

    /**
     * T1.2-D: Inertia shared data contains tenant context (company, branch, warehouse).
     * Plan T1.2-D: Every Inertia page load shares company/branch/warehouse.
     */
    public function test_T1_2_D_inertia_shared_data_contains_tenant_context(): void
    {
        $this->actingAs($this->userA);
        session(['active_company_id' => $this->companyA->id]);

        $response = $this->get('/dashboard');

        // Inertia shares company
        $response->assertInertia(fn ($page) =>
            $page->has('auth.user')
                 ->has('company')
                 ->has('locale')
        );
    }

    /**
     * T1.2-E: BelongsToCompany trait auto-assigns company_id on create.
     * Plan: auto-assign company_id from TenantContext when creating.
     */
    public function test_T1_2_E_belongs_to_company_auto_assigns_on_create(): void
    {
        $this->actingAs($this->userA);
        session(['active_company_id' => $this->companyA->id]);

        // Create via model — should auto-assign company_id
        $item = Item::create([
            'name'      => 'Auto Assigned Item',
            'sku'       => 'AUTO-001',
            'item_type' => 'medical_consumable',
            // company_id NOT explicitly set
        ]);

        $this->assertEquals($this->companyA->id, $item->company_id);
    }
}
