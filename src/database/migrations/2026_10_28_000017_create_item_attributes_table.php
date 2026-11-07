<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('attribute_name', 100);
            $table->string('attribute_name_ar', 100)->nullable();
            $table->text('attribute_value')->nullable();
            $table->string('attribute_type', 30)->default('text');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'item_id']);
            $table->index(['item_id', 'attribute_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_attributes');
    }
};
