<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableMemberFieldAdd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedInteger('order_score')->default(0)->comment('累计总积分');
            $table->unsignedInteger('used_score')->default(0)->comment('已使用积分');
            $table->unsignedInteger('usable_score')->default(0)->comment('可用积分');
            $table->unsignedInteger('level_id')->default(1)->comment('会员等级');
            $table->unsignedInteger('exp')->default(0)->comment('成长值');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('order_score');
            $table->dropColumn('used_score');
            $table->dropColumn('usable_score');
            $table->dropColumn('level_id');
            $table->dropColumn('exp');
        });
    }
}
