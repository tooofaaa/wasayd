<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recall_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recall_id')->constrained('recalls')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->decimal('affected_quantity', 12, 3);
            $table->decimal('recovered_quantity', 12, 3)->default(0);
            $table->decimal('quarantined_quantity', 12, 3)->default(0);
            $table->enum('status', ['identified', 'quarantined', 'recovered', 'disposed'])->default('identified');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recall_lines');
    }
};
