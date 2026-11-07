<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Auth & Users
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign-roles',

            // Roles & Permissions
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.assign-permissions',

            // Companies
            'companies.view', 'companies.create', 'companies.edit', 'companies.delete',

            // Branches
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete',

            // Warehouses
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',

            // Zones (within warehouses)
            'zones.view', 'zones.create', 'zones.edit', 'zones.delete',

            // Locations (aisles, racks, shelves, bins)
            'locations.view', 'locations.create', 'locations.edit', 'locations.delete',

            // Items
            'items.view', 'items.create', 'items.edit', 'items.delete', 'items.import', 'items.export',

            // Item Categories
            'item-categories.view', 'item-categories.create', 'item-categories.edit', 'item-categories.delete',

            // Item UOMs (Units of Measure)
            'item-uoms.view', 'item-uoms.create', 'item-uoms.edit', 'item-uoms.delete',

            // Batches
            'batches.view', 'batches.create', 'batches.edit', 'batches.status-change',

            // Inventory
            'inventory.view', 'inventory.view-cost',
            'stock.adjust', 'stock.transfer', 'stock.reserve', 'stock.issue', 'stock.return',

            // Receiving
            'receiving.view', 'receiving.create', 'receiving.process', 'receiving.approve', 'receiving.cancel',

            // Putaway
            'putaway.view', 'putaway.create', 'putaway.process', 'putaway.assign', 'putaway.complete',

            // Stock Count
            'stock-count.view', 'stock-count.create', 'stock-count.process', 'stock-count.approve',

            // Stock Adjustments
            'adjustments.view', 'adjustments.create', 'adjustments.approve', 'adjustments.apply',

            // Transactions
            'transactions.view', 'transactions.create', 'transactions.create-reversal',

            // RFID Readers
            'rfid-readers.view', 'rfid-readers.manage', 'rfid-readers.create', 'rfid-readers.edit', 'rfid-readers.delete',

            // EPC/RFID Tags
            'epc.view', 'epc.encode', 'epc.manage', 'epc.generate', 'epc.commission', 'epc.decommission',

            // RFID Read Events
            'rfid-events.view', 'rfid-events.process',

            // Assets
            'assets.view', 'assets.create', 'assets.edit', 'assets.delete',
            'assets.assign', 'assets.transfer', 'assets.maintenance', 'assets.dispose',

            // Asset Maintenance
            'asset-maintenance.view', 'asset-maintenance.create', 'asset-maintenance.complete',

            // Asset Inspections
            'asset-inspections.view', 'asset-inspections.create',

            // Controlled Medications
            'controlled-meds.view', 'controlled-meds.dispense', 'controlled-meds.audit',
            'controlled-meds.witness', 'controlled-meds.reconcile', 'controlled-meds.waste',

            // Quarantine
            'quarantine.view', 'quarantine.create', 'quarantine.resolve', 'quarantine.release', 'quarantine.dispose',

            // Recalls
            'recalls.view', 'recalls.create', 'recalls.manage',

            // Approvals
            'approvals.view', 'approvals.approve', 'approvals.reject', 'approvals.configure',

            // Reports
            'reports.view', 'reports.export',

            // System Settings
            'system.settings', 'system.audit-log', 'system.configs',

            // Documents
            'documents.view', 'documents.upload', 'documents.delete',

            // Notifications
            'notifications.view', 'notifications.manage',

            // Import/Export
            'data-import.execute', 'data-export.execute',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $allPermissions = Permission::where('guard_name', 'web')->pluck('name')->toArray();

        // super_admin (super-admin): ALL permissions
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web'])
            ->syncPermissions($allPermissions);

        // admin (company_admin): ALL except system.settings and system.configs
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])
            ->syncPermissions(array_diff($allPermissions, ['system.settings', 'system.configs']));

        // warehouse-manager: Full warehouse operations management
        Role::firstOrCreate(['name' => 'warehouse-manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'dashboard.view',
                // Locations
                'branches.view', 'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
                'zones.view', 'zones.create', 'zones.edit', 'zones.delete',
                'locations.view', 'locations.create', 'locations.edit', 'locations.delete',
                // Items
                'items.view', 'items.create', 'items.edit', 'items.delete', 'items.import', 'items.export',
                'item-categories.view', 'item-categories.create', 'item-categories.edit', 'item-categories.delete',
                'item-uoms.view', 'item-uoms.create', 'item-uoms.edit', 'item-uoms.delete',
                'batches.view', 'batches.create', 'batches.edit', 'batches.status-change',
                // Inventory
                'inventory.view', 'inventory.view-cost',
                'stock.adjust', 'stock.transfer', 'stock.reserve', 'stock.issue', 'stock.return',
                'receiving.view', 'receiving.create', 'receiving.process', 'receiving.approve',
                'putaway.view', 'putaway.create', 'putaway.process', 'putaway.assign', 'putaway.complete',
                'transactions.view', 'transactions.create',
                // RFID
                'rfid-readers.view', 'rfid-readers.manage', 'rfid-readers.create', 'rfid-readers.edit', 'rfid-readers.delete',
                'epc.view', 'epc.encode', 'epc.manage', 'epc.generate', 'epc.commission', 'epc.decommission',
                'rfid-events.view', 'rfid-events.process',
                // Stock Count & Adjustments
                'stock-count.view', 'stock-count.create', 'stock-count.process', 'stock-count.approve',
                'adjustments.view', 'adjustments.create',
                // Reports
                'reports.view', 'reports.export',
                'documents.view', 'documents.upload', 'documents.delete',
                'notifications.view', 'notifications.manage',
            ]);

        // warehouse-staff: Floor operations
        Role::firstOrCreate(['name' => 'warehouse-staff', 'guard_name' => 'web'])
            ->syncPermissions([
                'dashboard.view',
                // View permissions
                'users.view', 'branches.view', 'warehouses.view', 'zones.view', 'locations.view',
                'items.view', 'item-categories.view', 'item-uoms.view', 'batches.view',
                'inventory.view', 'receiving.view', 'putaway.view', 'transactions.view',
                'stock-count.view', 'adjustments.view', 'epc.view', 'rfid-readers.view', 'rfid-events.view',
                'assets.view', 'quarantine.view', 'recalls.view', 'approvals.view', 'reports.view',
                'documents.view', 'notifications.view',
                // Action permissions
                'receiving.create', 'receiving.process',
                'putaway.create', 'putaway.process',
                'transactions.create',
                'epc.encode',
            ]);

        // pharmacist: Controlled medications specialist
        Role::firstOrCreate(['name' => 'pharmacist', 'guard_name' => 'web'])
            ->syncPermissions([
                'dashboard.view',
                'items.view', 'item-categories.view', 'item-uoms.view', 'batches.view',
                'inventory.view',
                'receiving.view', 'receiving.create',
                // Controlled medications - full access
                'controlled-meds.view', 'controlled-meds.dispense', 'controlled-meds.audit',
                'controlled-meds.witness', 'controlled-meds.reconcile', 'controlled-meds.waste',
                // Quarantine - full access
                'quarantine.view', 'quarantine.create', 'quarantine.resolve', 'quarantine.release', 'quarantine.dispose',
                'transactions.view',
                'reports.view',
                'documents.view', 'documents.upload',
                'notifications.view',
            ]);

        // supervisor: Same as warehouse-manager + approvals
        $supervisorPermissions = [
            'dashboard.view',
            // Locations
            'branches.view', 'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
            'zones.view', 'zones.create', 'zones.edit', 'zones.delete',
            'locations.view', 'locations.create', 'locations.edit', 'locations.delete',
            // Items
            'items.view', 'items.create', 'items.edit', 'items.delete', 'items.import', 'items.export',
            'item-categories.view', 'item-categories.create', 'item-categories.edit', 'item-categories.delete',
            'item-uoms.view', 'item-uoms.create', 'item-uoms.edit', 'item-uoms.delete',
            'batches.view', 'batches.create', 'batches.edit', 'batches.status-change',
            // Inventory
            'inventory.view', 'inventory.view-cost',
            'stock.adjust', 'stock.transfer', 'stock.reserve', 'stock.issue', 'stock.return',
            'receiving.view', 'receiving.create', 'receiving.process', 'receiving.approve',
            'putaway.view', 'putaway.create', 'putaway.process', 'putaway.assign', 'putaway.complete',
            'transactions.view', 'transactions.create',
            // RFID
            'rfid-readers.view', 'rfid-readers.manage', 'rfid-readers.create', 'rfid-readers.edit', 'rfid-readers.delete',
            'epc.view', 'epc.encode', 'epc.manage', 'epc.generate', 'epc.commission', 'epc.decommission',
            'rfid-events.view', 'rfid-events.process',
            // Stock Count & Adjustments
            'stock-count.view', 'stock-count.create', 'stock-count.process', 'stock-count.approve',
            'adjustments.view', 'adjustments.create',
            // Approvals - supervisor can approve
            'approvals.view', 'approvals.approve', 'approvals.reject', 'approvals.configure',
            // Reports
            'reports.view', 'reports.export',
            'documents.view', 'documents.upload', 'documents.delete',
            'notifications.view', 'notifications.manage',
        ];
        Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web'])
            ->syncPermissions($supervisorPermissions);

        // asset-manager: Equipment and asset tracking
        Role::firstOrCreate(['name' => 'asset-manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'dashboard.view',
                'items.view', 'item-categories.view', 'item-uoms.view',
                'inventory.view',
                'assets.view', 'assets.create', 'assets.edit', 'assets.delete',
                'assets.assign', 'assets.transfer', 'assets.maintenance', 'assets.dispose',
                'asset-maintenance.view', 'asset-maintenance.create', 'asset-maintenance.complete',
                'asset-inspections.view', 'asset-inspections.create',
                'epc.view', 'epc.encode',
                'reports.view', 'reports.export',
                'documents.view', 'documents.upload', 'documents.delete',
                'notifications.view',
            ]);

        // auditor: Read-only access to everything + audit logs + reports + stock count
        $viewPermissions = array_filter($allPermissions, fn ($p) => str_ends_with($p, '.view'));
        Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web'])
            ->syncPermissions(array_unique(array_merge($viewPermissions, [
                'system.audit-log',
                'reports.view', 'reports.export',
                'transactions.view',
                'stock-count.view',
            ])));

        // viewer: Basic read-only access
        Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web'])
            ->syncPermissions([
                'dashboard.view',
                'items.view', 'item-categories.view', 'item-uoms.view', 'batches.view',
                'inventory.view',
                'receiving.view', 'putaway.view',
                'assets.view',
                'reports.view',
                'notifications.view',
            ]);
    }
}
