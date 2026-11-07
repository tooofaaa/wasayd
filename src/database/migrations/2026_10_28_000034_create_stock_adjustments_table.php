<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('adjustment_number');
            $table->foreignId('stock_count_id')->nullable()->constrained('stock_counts')->nullOnDelete();
            $table->enum('adjustment_type', ['count_variance', 'manual', 'damage', 'expiry', 'other']);
            $table->enum('status', ['pending_approval', 'approved', 'rejected', 'applied'])->default('pending_approval');
            $table->text('reason');
            $table->decimal('total_adjustment_qty', 12, 3)->default(0);
            $table->decimal('total_adjustment_value', 12, 4)->default(0);
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'adjustment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
