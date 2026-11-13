<?php

namespace Tests\Feature\IAM;

use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Feature 1.4 — IAM: Users, Roles, 30+ Permissions
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.4
 * Lean Loop Test Gate 1.4: Tests T1.4-A through T1.4-D
 */
class IamPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $adminUser;
    private User $viewerUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the seeder to create all roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->company = Company::factory()->create();

        $this->adminUser = User::factory()->create(['company_id' => $this->company->id]);
        $this->adminUser->assignRole('admin');

        $this->viewerUser = User::factory()->create(['company_id' => $this->company->id]);
        $this->viewerUser->assignRole('viewer');

        session(['active_company_id' => $this->company->id]);
    }

    /**
     * T1.4-A: User without items.create permission is denied.
     * Plan: Permission checks block unauthorized actions.
     * Note: The HTTP POST /api/v1/items route is implemented in Feature 2.2.
     * Here we verify the permission model itself (before route implementation).
     */
    public function test_T1_4_A_user_without_items_create_cannot_create_item(): void
    {
        $this->actingAs($this->viewerUser);

        // Viewer doesn't have items.create
        $this->assertFalse($this->viewerUser->can('items.create'),
            'Viewer should NOT have items.create permission');

        // Admin has it
        $this->assertTrue($this->adminUser->can('items.create'),
            'Admin should have items.create permission');

        // Also verify via getAllPermissions()
        $viewerPerms = $this->viewerUser->getAllPermissions()->pluck('name');
        $this->assertNotContains('items.create', $viewerPerms);
    }

    /**
     * T1.4-B: 30+ permissions exist after seeding.
     * Plan: 30+ granular permissions covering all modules.
     */
    public function test_T1_4_B_30_plus_permissions_exist_after_seeding(): void
    {
        $count = Permission::count();
        $this->assertGreaterThanOrEqual(30, $count,
            "Expected at least 30 permissions, got {$count}");
    }

    /**
     * T1.4-C: Inertia shares abilities array for authenticated user.
     * Plan T1.4-C: auth.user.abilities contains permission strings.
     */
    public function test_T1_4_C_inertia_shares_abilities_for_user(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/dashboard');

        $response->assertInertia(fn ($page) =>
            $page->has('auth.user.abilities')
        );
    }

    /**
     * T1.4-D: Super-admin role bypasses all permission checks.
     * Plan: Gate::before() — super-admin can do anything.
     */
    public function test_T1_4_D_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create(['company_id' => $this->company->id]);
        $superAdmin->assignRole('super-admin');

        $this->assertTrue($superAdmin->can('items.create'));
        $this->assertTrue($superAdmin->can('epc.decommission'));
        $this->assertTrue($superAdmin->can('controlled-meds.dispense'));
    }

    /**
     * T1.4-E: Role has correct permissions after seeding.
     * Plan: 'viewer' role has items.view but not items.create.
     */
    public function test_T1_4_E_viewer_role_has_correct_permissions(): void
    {
        $this->assertTrue($this->viewerUser->can('items.view'));
        $this->assertTrue($this->viewerUser->can('inventory.view'));
        $this->assertFalse($this->viewerUser->can('items.create'));
        $this->assertFalse($this->viewerUser->can('stock.adjust'));
    }
}
