<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_number');
            $table->enum('transaction_type', [
                'receiving', 'putaway', 'issue', 'transfer', 'return',
                'adjustment', 'count', 'disposal', 'reservation', 'release',
                'quarantine', 'unquarantine',
            ]);
            $table->dateTime('transaction_date');
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('epc_id')->nullable()->constrained('epcs')->nullOnDelete();
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('from_bin_id')->nullable()->constrained('bins');
            $table->foreignId('to_bin_id')->nullable()->constrained('bins');
            $table->decimal('quantity', 12, 3);
            $table->foreignId('uom_id')->nullable()->constrained('item_uoms');
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->decimal('total_cost', 12, 4)->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('reversal_of')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->boolean('is_reversal')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['company_id', 'transaction_number']);
            $table->index('transaction_type');
            $table->index(['reference_type', 'reference_id']);
            $table->index(['item_id', 'transaction_date']);
            $table->foreign('reversal_of')->references('id')->on('inventory_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
