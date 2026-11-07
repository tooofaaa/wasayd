<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('putaway_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('putaway_id')->constrained('putaways')->cascadeOnDelete();
            $table->foreignId('receiving_line_id')->nullable()->constrained('receiving_lines')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->foreignId('from_zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('to_zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('to_bin_id')->nullable()->constrained('bins')->nullOnDelete();
            $table->enum('status', ['pending', 'placed', 'skipped'])->default('pending');
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('putaway_lines');
    }
};
