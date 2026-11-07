<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('recall_number', 50);
            $table->string('recall_class', 10);
            $table->string('manufacturer_recall_id', 100)->nullable();
            $table->string('status', 30)->default('initiated');
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->json('affected_batches')->nullable();
            $table->integer('total_affected_qty')->default(0);
            $table->integer('total_recovered_qty')->default(0);
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->date('initiated_date');
            $table->date('completion_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'recall_number']);
            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recalls');
    }
};
