<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_scores', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');
            $table->unsignedInteger('source_id')->default(0)->comment('关联id');
            $table->string('source_type')->nullable()->comment('订单类型');
            $table->integer('score_change')->default(0)->comment('积分变动(增加为+，减少为-)');
            $table->unsignedTinyInteger('method')->nullable()->comment('积分方式（1-消费获得、2-活动获得、10-退款减少、11-兑换减少）');
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
        Schema::dropIfExists('member_scores');
    }
}
