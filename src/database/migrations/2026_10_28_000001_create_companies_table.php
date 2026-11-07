<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('name_ar', 255)->nullable();
            $table->string('code', 50)->unique();
            $table->string('logo', 500)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('license_number', 100)->nullable();
            $table->string('country', 3)->default('SA');
            $table->string('currency', 3)->default('SAR');
            $table->string('timezone', 50)->default('Asia/Riyadh');
            $table->string('locale', 5)->default('ar');
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
