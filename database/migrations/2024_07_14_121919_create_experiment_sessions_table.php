<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('experiment_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_id')->constrained()->onDelete('cascade');
            $table->string('participant_number');
            $table->json('group_data')->nullable();
            $table->json('actions_log')->nullable();
            $table->integer('duration')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('created');

            // Informations sur l'environnement
            $table->string('browser')->nullable();
            $table->string('device_type')->nullable();
            $table->string('operating_system')->nullable();
            $table->integer('screen_width')->nullable();
            $table->integer('screen_height')->nullable();
            $table->boolean('is_dark')->default(false);

            // Informations additionnelles
            $table->text('notes')->nullable();
            $table->text('feedback')->nullable();
            $table->json('errors_log')->nullable();
            $table->timestamps();

            // Contrainte d'unicité sur experiment_id + participant_number
            $table->unique(['experiment_id', 'participant_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiment_sessions');
    }
};
