<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enseignants')) {
            Schema::table('enseignants', function (Blueprint $table) {
                if (!Schema::hasColumn('enseignants', 'telephone')) {
                    $table->string('telephone', 20)->nullable()->after('matricule');
                }
                if (!Schema::hasColumn('enseignants', 'est_directeur')) {
                    $table->boolean('est_directeur')->default(false)->after('etablissement_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('enseignants')) {
            Schema::table('enseignants', function (Blueprint $table) {
                if (Schema::hasColumn('enseignants', 'est_directeur')) {
                    $table->dropColumn('est_directeur');
                }
                if (Schema::hasColumn('enseignants', 'telephone')) {
                    $table->dropColumn('telephone');
                }
            });
        }
    }
};
