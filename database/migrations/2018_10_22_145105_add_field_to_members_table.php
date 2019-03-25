<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->tinyInteger('member_type')->default(0)->after('star_time')->comment('0表示go会员,1表示星球会员,2表示vka迁移会员');
            $table->integer('member_cup')->default(0)->after('star_time')->comment('星球等级购买杯数计算');
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
            $table->dropColumn('member_type');
            $table->dropColumn('member_cup');
        });
    }
}
