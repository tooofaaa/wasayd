<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SystemConfigController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Locations\AisleController;
use App\Http\Controllers\Locations\BinController;
use App\Http\Controllers\Locations\BranchController;
use App\Http\Controllers\Locations\RackController;
use App\Http\Controllers\Locations\ShelfController;
use App\Http\Controllers\Locations\WarehouseController;
use App\Http\Controllers\Locations\ZoneController;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Tenant switching — Plan Feature 1.2 (T1.2-C: switching resets warehouse scope)
    Route::post('/tenant/switch', function (TenantContext $context) {
        $data = request()->validate([
            'company_id'   => 'sometimes|integer|exists:companies,id',
            'warehouse_id' => 'sometimes|integer|exists:warehouses,id',
        ]);

        if (isset($data['company_id'])) {
            $context->switchCompany((int) $data['company_id']);
        }
        if (isset($data['warehouse_id'])) {
            $context->switchWarehouse((int) $data['warehouse_id']);
        }

        return back()->with('success', __('Switched successfully.'));
    })->name('tenant.switch');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/locale', function () {
        $locale = request()->validate(['locale' => 'required|in:ar,en'])['locale'];
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
        cookie()->queue('locale', $locale, 525600);
        return back();
    })->name('locale.update');

    // Location Management
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::resource('branches', BranchController::class);
        Route::resource('warehouses', WarehouseController::class);
        Route::resource('zones', ZoneController::class);
        Route::resource('aisles', AisleController::class);
        Route::resource('racks', RackController::class);
        Route::resource('shelves', ShelfController::class);
        Route::resource('bins', BinController::class);
    });

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('companies', CompanyController::class)->except(['show']);
        Route::resource('permissions', PermissionController::class)->except(['show']);
        Route::get('config', [SystemConfigController::class, 'index'])->name('config.index');
        Route::put('config', [SystemConfigController::class, 'update'])->name('config.update');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });

    // Items
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', fn () => redirect()->route('items.items.index'));
        Route::resource('categories', \App\Http\Controllers\Items\ItemCategoryController::class);
        Route::resource('subcategories', \App\Http\Controllers\Items\ItemSubcategoryController::class);
        Route::resource('uoms', \App\Http\Controllers\Items\ItemUomController::class);
        Route::resource('items', \App\Http\Controllers\Items\ItemController::class);
    });

    // Inventory
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::resource('receivings', \App\Http\Controllers\Inventory\ReceivingController::class);
        Route::post('receivings/{receiving}/lines', [\App\Http\Controllers\Inventory\ReceivingController::class, 'addLine'])->name('receivings.lines.store');
        Route::put('receivings/{receiving}/lines/{line}/receive', [\App\Http\Controllers\Inventory\ReceivingController::class, 'receiveLine'])->name('receivings.lines.receive');
        Route::post('receivings/{receiving}/complete', [\App\Http\Controllers\Inventory\ReceivingController::class, 'complete'])->name('receivings.complete');
        Route::get('stock', [\App\Http\Controllers\Inventory\StockInquiryController::class, 'index'])->name('stock.index');

        // Stock Count & Adjustment
        Route::resource('stock-counts', \App\Http\Controllers\Inventory\StockCountController::class);
        Route::post('stock-counts/{stockCount}/start', [\App\Http\Controllers\Inventory\StockCountController::class, 'start'])->name('stock-counts.start');
        Route::post('stock-counts/{stockCount}/submit', [\App\Http\Controllers\Inventory\StockCountController::class, 'submit'])->name('stock-counts.submit');
        Route::post('stock-counts/{stockCount}/approve', [\App\Http\Controllers\Inventory\StockCountController::class, 'approve'])->name('stock-counts.approve');
        Route::post('stock-counts/{stockCount}/lines', [\App\Http\Controllers\Inventory\StockCountController::class, 'recordLine'])->name('stock-counts.record-line');

        Route::resource('stock-adjustments', \App\Http\Controllers\Inventory\StockAdjustmentController::class);
        Route::post('stock-adjustments/{stockAdjustment}/submit', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'submitForApproval'])->name('stock-adjustments.submit');
        Route::post('stock-adjustments/{stockAdjustment}/approve', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'approve'])->name('stock-adjustments.approve');
        Route::post('stock-adjustments/{stockAdjustment}/reject', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'reject'])->name('stock-adjustments.reject');
        Route::post('stock-adjustments/{stockAdjustment}/apply', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'apply'])->name('stock-adjustments.apply');

        Route::get('transactions', [\App\Http\Controllers\Inventory\TransactionLedgerController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [\App\Http\Controllers\Inventory\TransactionLedgerController::class, 'show'])->name('transactions.show');
        Route::post('transactions/{transaction}/reverse', [\App\Http\Controllers\Inventory\TransactionLedgerController::class, 'reverse'])->name('transactions.reverse');

        Route::get('approvals', [\App\Http\Controllers\Inventory\ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('approvals/{approval}/approve', [\App\Http\Controllers\Inventory\ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('approvals/{approval}/reject', [\App\Http\Controllers\Inventory\ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // RFID & EPC
    Route::prefix('rfid')->name('rfid.')->group(function () {
        Route::resource('epcs', \App\Http\Controllers\Rfid\EpcController::class);
        Route::post('epcs/generate-batch', [\App\Http\Controllers\Rfid\EpcController::class, 'generateBatch'])->name('epcs.generate-batch');
        Route::post('epcs/{epc}/commission', [\App\Http\Controllers\Rfid\EpcController::class, 'commission'])->name('epcs.commission');
        Route::post('epcs/{epc}/decommission', [\App\Http\Controllers\Rfid\EpcController::class, 'decommission'])->name('epcs.decommission');
        Route::resource('readers', \App\Http\Controllers\Rfid\RfidReaderController::class);
        Route::get('read-events', [\App\Http\Controllers\Rfid\RfidReadEventController::class, 'index'])->name('read-events.index');
        Route::get('read-events/{readEvent}', [\App\Http\Controllers\Rfid\RfidReadEventController::class, 'show'])->name('read-events.show');
        Route::get('encoding-logs', [\App\Http\Controllers\Rfid\RfidEncodingLogController::class, 'index'])->name('encoding-logs.index');
    });

    // Operations
    Route::prefix('operations')->name('operations.')->group(function () {
        // Quarantines
        Route::resource('quarantines', \App\Http\Controllers\Operations\QuarantineController::class);
        Route::post('quarantines/{quarantine}/release', [\App\Http\Controllers\Operations\QuarantineController::class, 'release'])->name('quarantines.release');
        Route::post('quarantines/{quarantine}/dispose', [\App\Http\Controllers\Operations\QuarantineController::class, 'dispose'])->name('quarantines.dispose');

        // Recalls
        Route::resource('recalls', \App\Http\Controllers\Operations\RecallController::class);
        Route::post('recalls/{recall}/quarantine-line/{line}', [\App\Http\Controllers\Operations\RecallController::class, 'quarantineLine'])->name('recalls.quarantine-line');
        Route::post('recalls/{recall}/recover-line/{line}', [\App\Http\Controllers\Operations\RecallController::class, 'recoverLine'])->name('recalls.recover-line');
        Route::post('recalls/{recall}/complete', [\App\Http\Controllers\Operations\RecallController::class, 'complete'])->name('recalls.complete');

        // Controlled Medications
        Route::prefix('controlled-medications')->name('controlled-medications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Operations\ControlledMedicationController::class, 'index'])->name('index');
            Route::get('dispense', [\App\Http\Controllers\Operations\ControlledMedicationController::class, 'showDispenseForm'])->name('dispense.form');
            Route::post('dispense', [\App\Http\Controllers\Operations\ControlledMedicationController::class, 'dispense'])->name('dispense');
            Route::get('waste', [\App\Http\Controllers\Operations\ControlledMedicationController::class, 'showWasteForm'])->name('waste.form');
            Route::post('waste', [\App\Http\Controllers\Operations\ControlledMedicationController::class, 'waste'])->name('waste');
            Route::get('reconcile', [\App\Http\Controllers\Operations\ControlledMedicationController::class, 'showReconcileForm'])->name('reconcile.form');
            Route::post('reconcile', [\App\Http\Controllers\Operations\ControlledMedicationController::class, 'reconcile'])->name('reconcile');
        });

        // Putaways
        Route::resource('putaways', \App\Http\Controllers\Operations\PutawayController::class)->only(['index', 'show']);
        Route::post('putaways/{putaway}/lines/{line}/confirm', [\App\Http\Controllers\Operations\PutawayController::class, 'confirmLine'])->name('putaways.confirm-line');
        Route::post('putaways/{putaway}/complete', [\App\Http\Controllers\Operations\PutawayController::class, 'complete'])->name('putaways.complete');
    });

    // Assets
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Assets\AssetController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Assets\AssetController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Assets\AssetController::class, 'store'])->name('store');
        Route::get('/transfers', [\App\Http\Controllers\Assets\AssetTransferController::class, 'index'])->name('transfers.index');
        Route::get('/transfers/create', [\App\Http\Controllers\Assets\AssetTransferController::class, 'create'])->name('transfers.create');
        Route::post('/transfers', [\App\Http\Controllers\Assets\AssetTransferController::class, 'store'])->name('transfers.store');
        Route::post('/transfers/{transfer}/approve', [\App\Http\Controllers\Assets\AssetTransferController::class, 'approve'])->name('transfers.approve');
        Route::post('/transfers/{transfer}/complete', [\App\Http\Controllers\Assets\AssetTransferController::class, 'complete'])->name('transfers.complete');
        Route::get('/{asset}', [\App\Http\Controllers\Assets\AssetController::class, 'show'])->name('show');
        Route::get('/{asset}/edit', [\App\Http\Controllers\Assets\AssetController::class, 'edit'])->name('edit');
        Route::put('/{asset}', [\App\Http\Controllers\Assets\AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [\App\Http\Controllers\Assets\AssetController::class, 'destroy'])->name('destroy');
        Route::post('/{asset}/assign', [\App\Http\Controllers\Assets\AssetController::class, 'assign'])->name('assign');
        Route::post('/{asset}/return', [\App\Http\Controllers\Assets\AssetController::class, 'returnAsset'])->name('return');
        Route::post('/{asset}/dispose', [\App\Http\Controllers\Assets\AssetController::class, 'dispose'])->name('dispose');
        Route::post('/{asset}/inspections', [\App\Http\Controllers\Assets\AssetController::class, 'recordInspection'])->name('inspections.store');
        Route::post('/{asset}/maintenance', [\App\Http\Controllers\Assets\AssetMaintenanceController::class, 'store'])->name('maintenance.store');
        Route::put('/{asset}/maintenance/{maintenance}', [\App\Http\Controllers\Assets\AssetMaintenanceController::class, 'update'])->name('maintenance.update');
        Route::post('/{asset}/maintenance/{maintenance}/start', [\App\Http\Controllers\Assets\AssetMaintenanceController::class, 'start'])->name('maintenance.start');
        Route::post('/{asset}/maintenance/{maintenance}/complete', [\App\Http\Controllers\Assets\AssetMaintenanceController::class, 'complete'])->name('maintenance.complete');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Reporting\ReportController::class, 'index'])->name('index');
        Route::get('stock-summary', [\App\Http\Controllers\Reporting\ReportController::class, 'stockSummary'])->name('stock-summary');
        Route::get('stock-movement', [\App\Http\Controllers\Reporting\ReportController::class, 'stockMovement'])->name('stock-movement');
        Route::get('stock-valuation', [\App\Http\Controllers\Reporting\ReportController::class, 'stockValuation'])->name('stock-valuation');
        Route::get('expiry', [\App\Http\Controllers\Reporting\ReportController::class, 'expiryReport'])->name('expiry');
        Route::get('low-stock', [\App\Http\Controllers\Reporting\ReportController::class, 'lowStock'])->name('low-stock');
        Route::get('transaction-history', [\App\Http\Controllers\Reporting\ReportController::class, 'transactionHistory'])->name('transaction-history');
        Route::get('epc-status', [\App\Http\Controllers\Reporting\ReportController::class, 'epcStatus'])->name('epc-status');
        Route::get('asset-utilization', [\App\Http\Controllers\Reporting\ReportController::class, 'assetUtilization'])->name('asset-utilization');
        Route::get('maintenance-due', [\App\Http\Controllers\Reporting\ReportController::class, 'maintenanceDue'])->name('maintenance-due');
        Route::get('controlled-med', [\App\Http\Controllers\Reporting\ReportController::class, 'controlledMedReconciliation'])->name('controlled-med');
        Route::get('audit-trail', [\App\Http\Controllers\Reporting\ReportController::class, 'auditTrailReport'])->name('audit-trail');
        Route::get('count-accuracy', [\App\Http\Controllers\Reporting\ReportController::class, 'countAccuracy'])->name('count-accuracy');
    });

    // Import/Export
    Route::prefix('import-export')->name('import-export.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ImportExportController::class, 'index'])->name('index');
        Route::get('export/items', [\App\Http\Controllers\Admin\ImportExportController::class, 'exportItems'])->name('export.items');
        Route::get('export/stock', [\App\Http\Controllers\Admin\ImportExportController::class, 'exportStock'])->name('export.stock');
        Route::get('import/items', [\App\Http\Controllers\Admin\ImportExportController::class, 'showImportForm'])->name('import.items.form');
        Route::post('import/items', [\App\Http\Controllers\Admin\ImportExportController::class, 'importItems'])->name('import.items');
    });

    // Notifications
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Global Search
    Route::get('search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
});

// Simple test route
Route::get('/test/simple', function () {
    return 'Hello World';
});

// Test session route
Route::get('/test/session', function () {
    return 'Session ID: ' . session()->getId() . ' | Has user: ' . (session()->has('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d') ? 'yes' : 'no');
});

// Test auth route (authenticated, no Inertia)
Route::middleware('auth')->get('/test/auth', function () {
    return response()->json([
        'user' => auth()->user()->email,
        'role' => auth()->user()->getRoleNames()->first(),
        'timestamp' => now()->toIso8601String(),
    ]);
});

// PING test - smallest possible authenticated route
Route::middleware('auth')->get('/test/ping', function () {
    return 'pong:' . auth()->id();
});

// Test dashboard data (no Inertia render, just JSON)
Route::middleware('auth')->get('/test/dashboard-data', function () {
    $user = auth()->user();
    return response()->json([
        'user_id' => $user->id,
        'company_id' => $user->company_id,
        'items' => \App\Models\Item::where('company_id', $user->company_id)->count(),
        'timestamp' => now()->toIso8601String(),
    ]);
});
