<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberExpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_exps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->index()->comment('用户ID');
            $table->unsignedInteger('source_id')->default(0)->index()->comment('关联id');
            $table->string('source_type')->nullable()->comment('订单类型');
            $table->integer('go_exp_change')->default(0)->comment('go经验值变动');
            $table->integer('star_exp_change')->default(0)->comment('星球经验值变动');
            $table->unsignedTinyInteger('method')->nullable()->comment('经验值方式(0-经验值初始,1-消费获得,2-活动获得,3-vka迁移,4-任务,10-退单减少)');
            $table->integer('level_id')->index()->default(0)->comment('go会员当前等级');
            $table->integer('star_level_id')->index()->default(0)->comment('星球会员当前等级');
            $table->string('description')->nullable()->comment('描述说明');
            $table->tinyInteger('member_type')->default(0)->comment('0表示Go会员,1表示星球会员');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_exps');
    }
}
