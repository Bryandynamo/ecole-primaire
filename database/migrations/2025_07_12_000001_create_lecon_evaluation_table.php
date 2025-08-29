<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lecon_evaluation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lecon_id');
            $table->unsignedBigInteger('evaluation_id');
            $table->integer('total_a_couvrir_ua')->default(0);
            $table->timestamps();

            $table->unique(['lecon_id', 'evaluation_id']);
            $table->foreign('lecon_id')->references('id')->on('lecons')->onDelete('cascade');
            $table->foreign('evaluation_id')->references('id')->on('evaluations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lecon_evaluation');
    }
};
