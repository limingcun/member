<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldsToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            DB::statement("ALTER TABLE `members` CHANGE `order_score` `order_score` INT(10) NOT NULL DEFAULT '0' COMMENT '累计总积分'");
            DB::statement("ALTER TABLE `members` CHANGE `used_score` `used_score` INT(10) NOT NULL DEFAULT '0' COMMENT '已使用积分'");
            DB::statement("ALTER TABLE `members` CHANGE `usable_score` `usable_score` INT(10) NOT NULL DEFAULT '0' COMMENT '可用积分'");
            $table->unsignedInteger('score_lock')->default(0)->comment('会员锁定，0为未锁，1为锁定');
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
            //
        });
    }
}
