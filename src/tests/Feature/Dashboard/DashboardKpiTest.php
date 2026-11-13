<?php

namespace Tests\Feature\Dashboard;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature 1.6 — Dashboard Shell + KPI Widgets
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.6
 * Lean Loop Test Gate 1.6: Tests T1.6-A through T1.6-D
 *
 * Note: assertInertia() works on the full-page (non-Inertia-header) response
 * because it reads view data, not the JSON response body.
 */
class DashboardKpiTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->company = Company::factory()->create();
        $this->user    = User::factory()->create(['company_id' => $this->company->id]);
        $this->user->assignRole('admin');

        $this->actingAs($this->user);
        session([
            'active_company_id'   => $this->company->id,
            'two_factor_verified' => true,
        ]);
    }

    /**
     * T1.6-A: Dashboard returns all KPI groups.
     * Plan: stats.inventory, stats.rfid, stats.transactions all present.
     */
    public function test_T1_6_A_dashboard_returns_all_kpi_groups(): void
    {
        $response = $this->get('/dashboard');
        $response->assertOk();

        $response->assertInertia(fn ($page) =>
            $page->component('Dashboard/Index')
                 ->has('stats.inventory')
                 ->has('stats.rfid')
                 ->has('stats.transactions')
        );
    }

    /**
     * T1.6-B: Inventory KPIs have all required keys.
     */
    public function test_T1_6_B_inventory_kpis_have_all_keys(): void
    {
        $response = $this->get('/dashboard');
        $response->assertInertia(fn ($page) =>
            $page->has('stats.inventory.total_items')
                 ->has('stats.inventory.total_stock')
                 ->has('stats.inventory.low_stock')
                 ->has('stats.inventory.out_of_stock')
                 ->has('stats.inventory.expiring_soon')
                 ->has('stats.inventory.expired_items')
        );
    }

    /**
     * T1.6-C: RFID KPIs have all required keys.
     */
    public function test_T1_6_C_rfid_kpis_have_all_keys(): void
    {
        $response = $this->get('/dashboard');
        $response->assertInertia(fn ($page) =>
            $page->has('stats.rfid.total_epcs')
                 ->has('stats.rfid.encoded_epcs')
                 ->has('stats.rfid.epcs_in_stock')
                 ->has('stats.rfid.epcs_not_found')
                 ->has('stats.rfid.epcs_location_variance')
        );
    }

    /**
     * T1.6-D: Transaction KPIs have all required keys.
     */
    public function test_T1_6_D_transaction_kpis_have_all_keys(): void
    {
        $response = $this->get('/dashboard');
        $response->assertInertia(fn ($page) =>
            $page->has('stats.transactions.today_receipts')
                 ->has('stats.transactions.today_issues')
                 ->has('stats.transactions.today_transfers')
                 ->has('stats.transactions.today_adjustments')
                 ->has('stats.transactions.today_counts')
        );
    }
}
