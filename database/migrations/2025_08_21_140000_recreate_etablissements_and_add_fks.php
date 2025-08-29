<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Recreate etablissements table if it doesn't exist
        if (!Schema::hasTable('etablissements')) {
            Schema::create('etablissements', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('code')->nullable();
                $table->string('adresse')->nullable();
                $table->string('telephone')->nullable();
                $table->timestamps();
            });
        }

        // Add etablissement_id to classes if missing
        if (Schema::hasTable('classes') && !Schema::hasColumn('classes', 'etablissement_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->unsignedBigInteger('etablissement_id')->nullable()->after('session_id');
                $table->foreign('etablissement_id')->references('id')->on('etablissements');
            });
        }

        // Add user_id and etablissement_id to enseignants if missing
        if (Schema::hasTable('enseignants')) {
            Schema::table('enseignants', function (Blueprint $table) {
                if (!Schema::hasColumn('enseignants', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('matricule');
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('enseignants', 'etablissement_id')) {
                    $table->unsignedBigInteger('etablissement_id')->nullable()->after('classe_id');
                    $table->foreign('etablissement_id')->references('id')->on('etablissements');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('enseignants')) {
            Schema::table('enseignants', function (Blueprint $table) {
                if (Schema::hasColumn('enseignants', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
                if (Schema::hasColumn('enseignants', 'etablissement_id')) {
                    $table->dropForeign(['etablissement_id']);
                    $table->dropColumn('etablissement_id');
                }
            });
        }
        if (Schema::hasTable('classes') && Schema::hasColumn('classes', 'etablissement_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropForeign(['etablissement_id']);
                $table->dropColumn('etablissement_id');
            });
        }
        // Do not drop etablissements table in down to avoid data loss unexpectedly
    }
};
