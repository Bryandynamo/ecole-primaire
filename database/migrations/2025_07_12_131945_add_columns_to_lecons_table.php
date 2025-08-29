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
            if (!Schema::hasColumn('lecons', 'Total a couvrir pour l\'ua')) {
                $table->integer('Total a couvrir pour l\'ua')->nullable()->after('total_a_couvrir_annee');
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
            if (Schema::hasColumn('lecons', 'Total a couvrir pour l\'ua')) {
                $table->dropColumn('Total a couvrir pour l\'ua');
            }
        });
    }
};
