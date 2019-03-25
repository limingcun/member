<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreqToActive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actives', function (Blueprint $table) {
            $table->unsignedInteger('day_freq')->nullable()->comment('每天几次')->after('period_end');
            $table->unsignedInteger('total_freq')->nullable()->comment('一共几次')->after('period_end');
            $table->unsignedTinyInteger('user_limit')->default(0)->comment('门店限制')->after('shop_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actives', function (Blueprint $table) {
            $table->dropColumn('user_limit');
            $table->dropColumn('day_freq');
            $table->dropColumn('total_freq');
        });
    }
}
