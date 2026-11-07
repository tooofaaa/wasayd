<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('epc', 200)->unique();
            $table->string('tid', 200)->nullable();
            $table->string('sgtin', 200)->nullable();
            $table->string('status', 30)->default('encoded');
            $table->foreignId('current_location_id')->nullable()->comment('bin_id');
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('encoded_at')->nullable();
            $table->foreignId('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('last_seen_reader', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epcs');
    }
};
