<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_count_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->integer('total_items_counted')->default(0);
            $table->integer('total_items_matched')->default(0);
            $table->integer('total_items_variance')->default(0);
            $table->decimal('total_positive_variance_qty', 12, 3)->default(0);
            $table->decimal('total_negative_variance_qty', 12, 3)->default(0);
            $table->decimal('total_variance_value', 12, 4)->default(0);
            $table->decimal('accuracy_percentage', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_results');
    }
};
