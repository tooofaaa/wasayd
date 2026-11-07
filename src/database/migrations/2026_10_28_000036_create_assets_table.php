<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('asset_code');
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('epc_id')->nullable()->constrained('epcs')->nullOnDelete();
            $table->string('serial_number')->nullable();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', [
                'available', 'in_use', 'maintenance', 'calibration', 'repair',
                'quarantine', 'reserved', 'retired', 'disposed', 'decommissioned',
            ])->default('available');
            $table->string('asset_category')->nullable();
            $table->foreignId('current_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('current_zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('department')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 4)->nullable();
            $table->string('supplier')->nullable();
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->unsignedTinyInteger('condition_rating')->nullable();
            $table->unsignedSmallInteger('expected_life_years')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->date('last_calibration_date')->nullable();
            $table->date('next_calibration_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'asset_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
