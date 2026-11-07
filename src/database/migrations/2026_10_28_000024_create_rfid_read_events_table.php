<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfid_read_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reader_id')->constrained('rfid_readers')->cascadeOnDelete();
            $table->foreignId('epc_id')->nullable()->constrained('epcs')->nullOnDelete();
            $table->string('epc_code');
            $table->smallInteger('rssi')->nullable();
            $table->unsignedTinyInteger('antenna_port')->nullable();
            $table->unsignedInteger('read_count')->default(1);
            $table->timestamp('first_read_at');
            $table->timestamp('last_read_at');
            $table->enum('event_type', ['inventory', 'portal', 'handheld', 'transition'])->default('inventory');
            $table->string('session_id')->nullable();
            $table->boolean('processed')->default(false);

            $table->index(['epc_code', 'session_id']);
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_read_events');
    }
};
