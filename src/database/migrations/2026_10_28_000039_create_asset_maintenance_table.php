<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('maintenance_type', ['preventive', 'corrective', 'calibration']);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->date('scheduled_date');
            $table->date('started_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('performed_by')->nullable();
            $table->decimal('cost', 12, 4)->nullable();
            $table->text('findings')->nullable();
            $table->text('actions_taken')->nullable();
            $table->json('parts_used')->nullable();
            $table->date('next_due_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance');
    }
};
