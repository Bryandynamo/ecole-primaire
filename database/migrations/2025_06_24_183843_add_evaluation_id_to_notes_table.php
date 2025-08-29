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
        Schema::table('notes', function (Blueprint $table) {
            if (!Schema::hasColumn('notes', 'evaluation_id')) {
                $table->unsignedBigInteger('evaluation_id')->after('modalite_id')->nullable();
                $table->foreign('evaluation_id')
                      ->references('id')->on('evaluations')
                      ->onDelete('cascade');
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
        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'evaluation_id')) {
                $table->dropForeign(['evaluation_id']);
                $table->dropColumn('evaluation_id');
            }
        });
    }
};
