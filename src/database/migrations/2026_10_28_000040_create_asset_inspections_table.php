<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->date('inspection_date');
            $table->string('inspector');
            $table->unsignedTinyInteger('condition_rating');
            $table->enum('result', ['pass', 'fail', 'conditional']);
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->date('next_inspection_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_inspections');
    }
};
