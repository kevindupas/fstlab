<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('experiment_access_requests', function (Blueprint $table) {
            // $table->enum('status', ['pending', 'approved', 'rejected', 'revoked'])->default('pending')->change();
            DB::statement("ALTER TYPE experiment_access_request_status ADD VALUE IF NOT EXISTS 'revoked'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiment_access_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};
