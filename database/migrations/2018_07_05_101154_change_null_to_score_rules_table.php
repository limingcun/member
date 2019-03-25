<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNullToScoreRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropColumn('months');
        });
        Schema::table('score_rules', function(Blueprint $table){
            $table->unsignedTinyInteger('months')->nullable()->comment('积分有效月份');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('score_rules', function ($table) {
            //
        });
    }
}
