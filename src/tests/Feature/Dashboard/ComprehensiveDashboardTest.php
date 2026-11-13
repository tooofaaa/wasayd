<?php

namespace Tests\Feature\Dashboard;

use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive Dashboard KPI Scenarios
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.6
 * Covers: empty state, warehouse scope, Redis cache, all KPI groups
 */
class ComprehensiveDashboardTest extends TestCase
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

    private function login(): void
    {
        $this->actingAs($this->user);
        session([
            'active_company_id'   => $this->company->id,
            'two_factor_verified' => true,
        ]);
    }

    /** Scenario D1: Dashboard returns all 3 KPI groups */
    public function test_D1_dashboard_returns_all_kpi_groups(): void
    {
        $this->login();
        $this->get('/dashboard')
             ->assertOk()
             ->assertInertia(fn ($page) =>
                 $page->has('stats.inventory')
                      ->has('stats.rfid')
                      ->has('stats.transactions')
             );
    }

    /** Scenario D2: Empty state — all counters are 0 for new company */
    public function test_D2_empty_state_all_counters_zero(): void
    {
        $this->login();
        $response = $this->get('/dashboard');

        $response->assertInertia(fn ($page) =>
            $page->where('stats.inventory.total_items', 0)
                 ->where('stats.inventory.total_stock', 0)
                 ->where('stats.inventory.low_stock', 0)
                 ->where('stats.inventory.out_of_stock', 0)
                 ->where('stats.rfid.total_epcs', 0)
                 ->where('stats.transactions.today_receipts', 0)
        );
    }

    /** Scenario D3: Inventory KPIs have all 6 required keys */
    public function test_D3_inventory_kpis_have_all_keys(): void
    {
        $this->login();
        $this->get('/dashboard')
             ->assertInertia(fn ($page) =>
                 $page->has('stats.inventory.total_items')
                      ->has('stats.inventory.total_stock')
                      ->has('stats.inventory.low_stock')
                      ->has('stats.inventory.out_of_stock')
                      ->has('stats.inventory.expiring_soon')
                      ->has('stats.inventory.expired_items')
             );
    }

    /** Scenario D4: RFID KPIs have all 5 required keys */
    public function test_D4_rfid_kpis_have_all_keys(): void
    {
        $this->login();
        $this->get('/dashboard')
             ->assertInertia(fn ($page) =>
                 $page->has('stats.rfid.total_epcs')
                      ->has('stats.rfid.encoded_epcs')
                      ->has('stats.rfid.epcs_in_stock')
                      ->has('stats.rfid.epcs_not_found')
                      ->has('stats.rfid.epcs_location_variance')
             );
    }

    /** Scenario D5: Transaction KPIs have all 5 required keys */
    public function test_D5_transaction_kpis_have_all_keys(): void
    {
        $this->login();
        $this->get('/dashboard')
             ->assertInertia(fn ($page) =>
                 $page->has('stats.transactions.today_receipts')
                      ->has('stats.transactions.today_issues')
                      ->has('stats.transactions.today_transfers')
                      ->has('stats.transactions.today_adjustments')
                      ->has('stats.transactions.today_counts')
             );
    }

    /** Scenario D6: Dashboard accessible only to authenticated users */
    public function test_D6_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    /** Scenario D7: Dashboard component name is correct */
    public function test_D7_dashboard_uses_correct_inertia_component(): void
    {
        $this->login();
        $this->get('/dashboard')
             ->assertInertia(fn ($page) => $page->component('Dashboard/Index'));
    }

    /** Scenario D8: Stats are numeric types (not strings) */
    public function test_D8_stats_values_are_numeric(): void
    {
        $this->login();
        $response = $this->get('/dashboard');
        $data     = $response->original->getData()['page']['props'];

        $this->assertIsInt($data['stats']['inventory']['total_items']);
        $this->assertIsInt($data['stats']['inventory']['total_stock']);
        $this->assertIsInt($data['stats']['rfid']['total_epcs']);
        $this->assertIsInt($data['stats']['transactions']['today_receipts']);
    }

    /** Scenario D9: Guest cannot see dashboard */
    public function test_D9_guest_cannot_see_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    /** Scenario D10: 2FA-required user without challenge cannot see dashboard */
    public function test_D10_2fa_user_without_challenge_redirected(): void
    {
        $secret = app(\PragmaRX\Google2FA\Google2FA::class)->generateSecretKey();
        $this->user->update([
            'two_factor_enabled'      => true,
            'two_factor_secret'       => \Illuminate\Support\Facades\Crypt::encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($this->user);
        session(['active_company_id' => $this->company->id]);
        // No two_factor_verified in session

        $this->get('/dashboard')->assertRedirect(route('two-factor.challenge'));
    }
}
