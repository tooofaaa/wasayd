<?php

// Plan reference: Feature 1.3 — Location Hierarchy
// Bin barcodes must be globally unique for scanner lookup
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bins', function (Blueprint $table) {
            // Barcode must be globally unique for reliable scanner lookup
            // Drop the plain index first if it exists, then add unique
            $table->unique('barcode', 'bins_barcode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bins', function (Blueprint $table) {
            $table->dropUnique('bins_barcode_unique');
        });
    }
};
