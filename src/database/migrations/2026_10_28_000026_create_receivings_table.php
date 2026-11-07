<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('receiving_number');
            $table->foreignId('warehouse_id')->constrained();
            $table->string('supplier_name');
            $table->string('supplier_reference')->nullable();
            $table->string('purchase_order_number')->nullable();
            $table->date('receiving_date');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->enum('receiving_method', ['manual', 'rfid', 'barcode'])->default('manual');
            $table->decimal('total_expected_qty', 12, 3)->default(0);
            $table->decimal('total_received_qty', 12, 3)->default(0);
            $table->decimal('total_rejected_qty', 12, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'receiving_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivings');
    }
};
