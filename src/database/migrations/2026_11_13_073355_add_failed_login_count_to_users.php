<?php

// Plan reference: Feature 1.4 — IAM, Spec §3 Rule 4 (failed login attempts logged)
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'failed_login_count')) {
                $table->unsignedSmallInteger('failed_login_count')->default(0)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists('failed_login_count');
        });
    }
};
