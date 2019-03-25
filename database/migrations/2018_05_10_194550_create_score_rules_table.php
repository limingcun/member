<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScoreRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('score_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('score')->default(0)->comment('消费获得积分数[ 消费获得积分=floor(消费金额/rmb_base) ]');
            $table->unsignedInteger('rmb_base')->default(0)->comment('每消费RMB数');
            $table->unsignedInteger('score_max_per_day')->default(1)->comment('每人每天积分获取上限');
            $table->enum('expiry_type', [1, 2])->comment('积分有效期类型(1-永不过期、2-定期清0)');
            $table->unsignedTinyInteger('months')->default(0)->comment('积分有效月份');
            $table->string('expiry_time', 30)->nullable()->comment('指定N年后的具体失效日期(md格式，如1231表示，12月31日)');
            $table->string('remind_type', 15)->nullable()->comment('默认0,1短信提醒，2微信服务通知提醒，两种类型用1,2逗号隔开');
            $table->unsignedTinyInteger('remind_time')->default(0)->comment('过期前几天提醒');
            $table->string('remind_msg', 100)->nullable()->comment('提醒文字');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('score_rules');
    }
}
