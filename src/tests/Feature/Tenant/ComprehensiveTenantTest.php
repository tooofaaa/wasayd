<?php

namespace Tests\Feature\Tenant;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive Multi-Tenant Scenarios
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.2
 * Covers all human tenant-switching and scoping scenarios
 */
class ComprehensiveTenantTest extends TestCase
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
    }

    /** Scenario T1: TenantContext resolves company from user when no session key */
    public function test_T1_resolves_company_from_user_when_no_session(): void
    {
        $this->actingAs($this->user);
        session()->forget('active_company_id');

        $context = app(TenantContext::class);
        $this->assertEquals($this->company->id, $context->getCompanyId());
    }

    /** Scenario T2: Session company takes priority over user's company */
    public function test_T2_session_company_takes_priority_over_user(): void
    {
        $otherCompany = Company::factory()->create();
        $this->actingAs($this->user);
        session(['active_company_id' => $otherCompany->id]);

        $context = app(TenantContext::class);
        $this->assertEquals($otherCompany->id, $context->getCompanyId());
    }

    /** Scenario T3: Warehouse switch sets branch too */
    public function test_T3_warehouse_switch_also_sets_branch(): void
    {
        $this->actingAs($this->user);
        session(['active_company_id' => $this->company->id]);

        $context = app(TenantContext::class);
        $context->switchWarehouse($this->warehouse->id);

        $this->assertEquals($this->warehouse->id, session('active_warehouse_id'));
        $this->assertEquals($this->warehouse->branch_id, session('active_branch_id'));
    }

    /** Scenario T4: Company switch clears warehouse scope (Plan T1.2-C) */
    public function test_T4_company_switch_clears_warehouse_scope(): void
    {
        $this->actingAs($this->user);
        session([
            'active_company_id'   => $this->company->id,
            'active_warehouse_id' => $this->warehouse->id,
            'active_branch_id'    => $this->branch->id,
        ]);

        $newCompany = Company::factory()->create();
        $context = app(TenantContext::class);
        $context->switchCompany($newCompany->id);

        $this->assertNull(session('active_warehouse_id'), 'Warehouse scope must clear on company switch');
        $this->assertNull(session('active_branch_id'), 'Branch scope must clear on company switch');
        $this->assertEquals($newCompany->id, session('active_company_id'));
    }

    /** Scenario T5: Switching warehouse to company-B warehouse is rejected */
    public function test_T5_switching_to_other_company_warehouse_rejected(): void
    {
        $otherCompany   = Company::factory()->create();
        $otherBranch    = Branch::factory()->create(['company_id' => $otherCompany->id]);
        $otherWarehouse = Warehouse::factory()->create([
            'company_id' => $otherCompany->id,
            'branch_id'  => $otherBranch->id,
        ]);

        $this->actingAs($this->user);
        session(['active_company_id' => $this->company->id]);

        $context = app(TenantContext::class);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $context->switchWarehouse($otherWarehouse->id);
    }

    /** Scenario T6: Inertia shares company name on every page */
    public function test_T6_inertia_shares_company_name(): void
    {
        $this->actingAs($this->user);
        session([
            'active_company_id'   => $this->company->id,
            'two_factor_verified' => true,
        ]);

        $response = $this->get('/dashboard');
        $response->assertInertia(fn ($page) =>
            $page->has('company.name')
                 ->where('company.id', $this->company->id)
        );
    }

    /** Scenario T7: Inertia shares warehouse when set */
    public function test_T7_inertia_shares_warehouse_when_set(): void
    {
        $this->actingAs($this->user);
        session([
            'active_company_id'   => $this->company->id,
            'active_warehouse_id' => $this->warehouse->id,
            'two_factor_verified' => true,
        ]);

        $response = $this->get('/dashboard');
        $response->assertInertia(fn ($page) =>
            $page->has('warehouse.id')
                 ->where('warehouse.id', $this->warehouse->id)
        );
    }

    /** Scenario T8: Inertia shares null warehouse when none selected */
    public function test_T8_inertia_warehouse_null_when_none_selected(): void
    {
        $this->actingAs($this->user);
        session([
            'active_company_id'   => $this->company->id,
            'two_factor_verified' => true,
        ]);
        session()->forget('active_warehouse_id');

        $response = $this->get('/dashboard');
        $response->assertInertia(fn ($page) =>
            $page->where('warehouse', null)
        );
    }

    /** Scenario T9: User without company_id falls back gracefully */
    public function test_T9_user_without_company_id_handled_gracefully(): void
    {
        // User with no company (edge case — fresh user before onboarding)
        $orphanUser = User::factory()->create(['company_id' => $this->company->id]);
        $orphanUser->forceFill(['company_id' => null])->saveQuietly();

        $this->actingAs($orphanUser);
        session()->forget('active_company_id');

        $context = app(TenantContext::class);
        $this->assertNull($context->getCompanyId());
    }

    /** Scenario T10: Locale persisted in user profile + session */
    public function test_T10_locale_switch_persists(): void
    {
        $this->actingAs($this->user);
        session(['active_company_id' => $this->company->id, 'two_factor_verified' => true]);

        $this->post(route('locale.update'), ['locale' => 'ar'])
             ->assertRedirect();

        $this->assertEquals('ar', $this->user->fresh()->locale);
    }

    /** Scenario T11: Arabic locale sets app locale */
    public function test_T11_arabic_locale_sets_app_locale(): void
    {
        $this->actingAs($this->user);
        session(['active_company_id' => $this->company->id, 'two_factor_verified' => true]);

        $this->post(route('locale.update'), ['locale' => 'ar']);
        $response = $this->get('/dashboard');

        $response->assertInertia(fn ($page) =>
            $page->where('locale', 'ar')
        );
    }

    /** Scenario T12: Invalid locale rejected */
    public function test_T12_invalid_locale_rejected(): void
    {
        $this->actingAs($this->user);
        session(['active_company_id' => $this->company->id, 'two_factor_verified' => true]);

        $response = $this->post(route('locale.update'), ['locale' => 'xx']);
        $response->assertSessionHasErrors('locale');
    }
}
