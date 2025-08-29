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
        Schema::table('notes', function (Blueprint $table) {
            if (!Schema::hasColumn('notes', 'evaluation_id')) {
                $table->unsignedBigInteger('evaluation_id')->nullable()->after('classe_id');
                $table->foreign('evaluation_id')->references('id')->on('evaluations')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'evaluation_id')) {
                $table->dropForeign(['evaluation_id']);
                $table->dropColumn('evaluation_id');
            }
        });
    }
};
