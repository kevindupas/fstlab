<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->dropColumn(['link', 'status']);
        });

        Schema::create('experiment_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('link')->unique()->nullable();
            $table->enum('status', ['start', 'pause', 'stop', 'test'])->default('stop');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiment_links');

        Schema::table('experiments', function (Blueprint $table) {
            $table->string('link')->unique()->nullable();
            $table->enum('status', ['none', 'start', 'pause', 'stop', 'test'])->default('none');
        });
    }
};
