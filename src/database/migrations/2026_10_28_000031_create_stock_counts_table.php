<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('count_number');
            $table->foreignId('warehouse_id')->constrained();
            $table->enum('count_type', ['full', 'cycle', 'spot', 'rfid']);
            $table->enum('status', ['planned', 'in_progress', 'counting', 'submitted', 'reviewing', 'approved', 'cancelled'])->default('planned');
            $table->date('planned_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('zone_ids')->nullable();
            $table->json('category_ids')->nullable();
            $table->integer('total_items')->default(0);
            $table->integer('total_lines')->default(0);
            $table->decimal('total_variance_qty', 12, 3)->default(0);
            $table->decimal('total_variance_value', 12, 4)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['company_id', 'count_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_counts');
    }
};
