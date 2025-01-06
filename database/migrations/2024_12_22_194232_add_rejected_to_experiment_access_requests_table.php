<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer d'abord la contrainte existante
        DB::statement("ALTER TABLE experiment_access_requests ALTER COLUMN status TYPE varchar(255)");

        // Ajouter la nouvelle contrainte
        DB::statement("ALTER TABLE experiment_access_requests ADD CONSTRAINT status_check CHECK (status IN ('pending', 'approved', 'rejected', 'revoked'))");

        // Définir la valeur par défaut
        DB::statement("ALTER TABLE experiment_access_requests ALTER COLUMN status SET DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Supprimer la nouvelle contrainte
        DB::statement("ALTER TABLE experiment_access_requests DROP CONSTRAINT IF EXISTS status_check");

        // Remettre l'ancienne contrainte
        DB::statement("ALTER TABLE experiment_access_requests ADD CONSTRAINT status_check CHECK (status IN ('pending', 'approved', 'rejected'))");

        // Remettre l'ancienne valeur par défaut
        DB::statement("ALTER TABLE experiment_access_requests ALTER COLUMN status SET DEFAULT 'pending'");
    }
};
