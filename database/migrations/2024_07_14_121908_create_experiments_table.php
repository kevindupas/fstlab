<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExperimentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('experiments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('type');
            $table->json('media')->nullable();
            $table->string('button_size')->nullable();
            $table->string('button_color')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['none', 'start', 'pause', 'stop'])->default('none');
            $table->string('link')->unique()->nullable();
            $table->timestamps();
        });

        Schema::create('experiment_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('can_configure')->default(false);
            $table->boolean('can_pass')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiments');
        Schema::dropIfExists('experiment_user');
    }
}
