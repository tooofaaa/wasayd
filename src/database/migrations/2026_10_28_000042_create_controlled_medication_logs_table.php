<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlled_medication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('action', 30);
            $table->integer('quantity');
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('witnessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('patient_id', 100)->nullable();
            $table->string('prescription_number', 100)->nullable();
            $table->string('physician_name', 200)->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('action_at');
            $table->timestamps();

            $table->index(['company_id', 'item_id', 'action_at']);
            $table->index(['company_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlled_medication_logs');
    }
};
