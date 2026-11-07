<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfid_readers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('reader_id');
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['online', 'offline', 'maintenance', 'decommissioned'])->default('offline');
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->string('firmware_version')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'reader_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_readers');
    }
};
