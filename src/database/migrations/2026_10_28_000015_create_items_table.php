<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('item_categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('item_subcategories')->nullOnDelete();
            $table->foreignId('base_uom_id')->nullable()->constrained('item_uoms')->nullOnDelete();
            $table->string('sku', 100);
            $table->string('name', 300);
            $table->string('name_ar', 300)->nullable();
            $table->text('description')->nullable();
            $table->string('item_type', 30);
            $table->string('manufacturer', 200)->nullable();
            $table->string('brand', 200)->nullable();
            $table->string('model_number', 100)->nullable();
            $table->decimal('unit_cost', 12, 4)->default(0);
            $table->decimal('unit_price', 12, 4)->default(0);
            $table->integer('reorder_point')->default(0);
            $table->integer('reorder_qty')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->default(0);
            $table->integer('lead_time_days')->default(0);
            $table->boolean('requires_batch')->default(false);
            $table->boolean('requires_expiry')->default(false);
            $table->boolean('requires_serial')->default(false);
            $table->boolean('requires_rfid')->default(false);
            $table->boolean('is_controlled')->default(false);
            $table->boolean('is_hazardous')->default(false);
            $table->string('storage_conditions', 200)->nullable();
            $table->decimal('weight_kg', 10, 4)->nullable();
            $table->decimal('length_cm', 10, 2)->nullable();
            $table->decimal('width_cm', 10, 2)->nullable();
            $table->decimal('height_cm', 10, 2)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'item_type']);
            $table->index(['company_id', 'category_id']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
