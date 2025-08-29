<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lecons', function (Blueprint $table) {
            if (!Schema::hasColumn('lecons', 'total_a_couvrir_trimestre')) {
                $table->integer('total_a_couvrir_trimestre')->nullable()->after('total_a_couvrir_annee');
            }
            if (!Schema::hasColumn('lecons', 'total_a_couvrir_ua')) {
                $table->integer('total_a_couvrir_ua')->nullable()->after('total_a_couvrir_trimestre');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lecons', function (Blueprint $table) {
            if (Schema::hasColumn('lecons', 'total_a_couvrir_trimestre')) {
                $table->dropColumn('total_a_couvrir_trimestre');
            }
            if (Schema::hasColumn('lecons', 'total_a_couvrir_ua')) {
                $table->dropColumn('total_a_couvrir_ua');
            }
        });
    }
};
