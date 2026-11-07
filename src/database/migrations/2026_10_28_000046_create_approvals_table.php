<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('approvable_type', 100);
            $table->unsignedBigInteger('approvable_id');
            $table->string('approval_type', 50);
            $table->string('status', 30)->default('pending');
            $table->integer('current_step')->default(1);
            $table->integer('total_steps')->default(1);
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'approval_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
