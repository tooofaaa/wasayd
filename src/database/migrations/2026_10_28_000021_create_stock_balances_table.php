<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->integer('total_on_hand')->default(0);
            $table->integer('total_reserved')->default(0);
            $table->integer('total_available')->storedAs('total_on_hand - total_reserved');
            $table->integer('total_in_transit')->default(0);
            $table->decimal('weighted_avg_cost', 12, 4)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'item_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
