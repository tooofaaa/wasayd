<?php

namespace Tests\Feature\IAM;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Comprehensive IAM Scenarios
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.4
 * Covers: all 8 roles, permission inheritance, 30+ permissions
 */
class ComprehensiveIamTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->company = Company::factory()->create();
    }

    // ─── PERMISSIONS SEEDING ──────────────────────────────────────────────────

    /** Scenario I1: 30+ permissions exist after seeding */
    public function test_I1_30_plus_permissions_seeded(): void
    {
        $count = Permission::count();
        $this->assertGreaterThanOrEqual(30, $count);
    }

    /** Scenario I2: All expected roles exist after seeding */
    public function test_I2_all_expected_roles_exist(): void
    {
        $expectedRoles = [
            'super-admin', 'admin', 'warehouse-manager', 'warehouse-staff',
            'pharmacist', 'supervisor', 'asset-manager', 'auditor', 'viewer',
        ];
        foreach ($expectedRoles as $role) {
            $this->assertTrue(
                Role::where('name', $role)->exists(),
                "Role '$role' should exist after seeding"
            );
        }
    }

    /** Scenario I3: Module permissions exist for all WASAYD modules */
    public function test_I3_all_module_permissions_exist(): void
    {
        $modules = [
            'dashboard', 'items', 'inventory', 'receiving', 'putaway',
            'stock', 'epc', 'rfid-readers', 'assets', 'reports',
            'users', 'roles', 'approvals', 'transactions',
        ];
        foreach ($modules as $module) {
            $exists = Permission::where('name', 'like', "$module.%")->exists();
            $this->assertTrue($exists, "No permissions found for module: $module");
        }
    }

    // ─── ROLE-PERMISSION ASSIGNMENTS ─────────────────────────────────────────

    /** Scenario I4: Admin has all major permissions */
    public function test_I4_admin_has_comprehensive_permissions(): void
    {
        $admin = User::factory()->create(['company_id' => $this->company->id]);
        $admin->assignRole('admin');

        $criticalPermissions = [
            'items.create', 'items.edit', 'items.delete',
            'stock.adjust', 'receiving.create', 'receiving.process',
            'epc.generate', 'epc.encode',
            'users.create', 'roles.create',
            'approvals.approve', 'reports.view',
        ];
        foreach ($criticalPermissions as $perm) {
            $this->assertTrue($admin->can($perm), "Admin should have: $perm");
        }
    }

    /** Scenario I5: Viewer has only read permissions */
    public function test_I5_viewer_has_only_read_permissions(): void
    {
        $viewer = User::factory()->create(['company_id' => $this->company->id]);
        $viewer->assignRole('viewer');

        // CAN view
        $this->assertTrue($viewer->can('items.view'));
        $this->assertTrue($viewer->can('inventory.view'));
        $this->assertTrue($viewer->can('assets.view'));
        $this->assertTrue($viewer->can('reports.view'));

        // CANNOT modify
        $writePerms = [
            'items.create', 'items.edit', 'items.delete',
            'stock.adjust', 'receiving.create', 'epc.generate',
            'users.create', 'roles.create', 'approvals.approve',
        ];
        foreach ($writePerms as $perm) {
            $this->assertFalse($viewer->can($perm), "Viewer should NOT have: $perm");
        }
    }

    /** Scenario I6: Warehouse-staff has inventory permissions but not admin */
    public function test_I6_inventory_staff_scoped_to_inventory(): void
    {
        $staff = User::factory()->create(['company_id' => $this->company->id]);
        $staff->assignRole('warehouse-staff');

        $this->assertTrue($staff->can('items.view'));
        $this->assertTrue($staff->can('receiving.create'));
        $this->assertTrue($staff->can('transactions.create'));

        // NOT admin permissions
        $this->assertFalse($staff->can('users.create'));
        $this->assertFalse($staff->can('roles.create'));
        $this->assertFalse($staff->can('companies.create'));
    }

    /** Scenario I7: Auditor can read everything including audit logs */
    public function test_I7_auditor_has_read_plus_audit_access(): void
    {
        $auditor = User::factory()->create(['company_id' => $this->company->id]);
        $auditor->assignRole('auditor');

        $this->assertTrue($auditor->can('items.view'));
        $this->assertTrue($auditor->can('transactions.view'));
        $this->assertTrue($auditor->can('reports.view'));
        $this->assertTrue($auditor->can('system.audit-log'));

        // Cannot modify
        $this->assertFalse($auditor->can('items.create'));
        $this->assertFalse($auditor->can('stock.adjust'));
    }

    /** Scenario I8: Super admin bypasses ALL permission checks */
    public function test_I8_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create(['company_id' => $this->company->id]);
        $superAdmin->assignRole('super-admin');

        // Every permission — even ones that may not exist
        $allPermissions = Permission::all()->pluck('name');
        foreach ($allPermissions as $perm) {
            $this->assertTrue($superAdmin->can($perm), "Super admin should bypass: $perm");
        }
    }

    /** Scenario I9: User can have multiple roles — permissions are union */
    public function test_I9_multiple_roles_union_permissions(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $user->assignRole(['viewer', 'asset-manager']);

        // Viewer permissions
        $this->assertTrue($user->can('items.view'));
        // Asset manager permissions
        $this->assertTrue($user->can('assets.create'));
        $this->assertTrue($user->can('assets.edit'));
    }

    /** Scenario I10: Revoking role removes those permissions */
    public function test_I10_revoking_role_removes_permissions(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $user->assignRole('warehouse-staff');
        $this->assertTrue($user->can('receiving.create'));

        $user->removeRole('warehouse-staff');

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $freshUser = User::find($user->id);
        $this->assertFalse($freshUser->can('receiving.create'));
    }

    /** Scenario I11: Inertia shares abilities as flat array */
    public function test_I11_inertia_abilities_are_flat_array(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $user->assignRole('viewer');
        $this->actingAs($user);
        session(['active_company_id' => $this->company->id, 'two_factor_verified' => true]);

        $response = $this->get('/dashboard');
        $response->assertInertia(fn ($page) =>
            $page->has('auth.user.abilities')
        );

        // The abilities array should be a flat list, not nested
        $abilities = $response->original->getData()['page']['props']['auth']['user']['abilities'];
        $this->assertIsArray($abilities);
        $this->assertContains('items.view', $abilities);
        $this->assertNotContains('items.create', $abilities); // viewer doesn't have create
    }

    /** Scenario I12: Permission names follow module.action pattern */
    public function test_I12_permission_names_follow_convention(): void
    {
        $permissions = Permission::all();
        foreach ($permissions as $perm) {
            $this->assertMatchesRegularExpression(
                '/^[a-z][a-z0-9\-]*\.[a-z][a-z0-9\-]*$/',
                $perm->name,
                "Permission '$perm->name' doesn't follow module.action convention"
            );
        }
    }
}
