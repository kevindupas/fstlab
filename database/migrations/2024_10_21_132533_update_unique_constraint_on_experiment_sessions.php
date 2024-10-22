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
        Schema::table('experiment_sessions', function (Blueprint $table) {
            // Supprimer la colonne 'is_dark'
            $table->dropColumn('is_dark');

            // Supprimer l'unicité sur 'participant_email' uniquement
            $table->dropUnique('experiment_sessions_participant_email_unique');

            // Ajouter une contrainte d'unicité sur 'experiment_id' + 'participant_email'
            $table->unique(['experiment_id', 'participant_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiment_sessions', function (Blueprint $table) {
            // Recréer la colonne 'is_dark' si on revient en arrière
            $table->boolean('is_dark')->default(false);

            // Supprimer la contrainte d'unicité sur 'experiment_id' + 'participant_email'
            $table->dropUnique(['experiment_id', 'participant_email']);

            // Recréer la contrainte d'unicité sur 'participant_email' uniquement
            $table->unique('participant_email');
        });
    }
};
