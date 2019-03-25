<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actives', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('活动名');
            $table->string('policy')->nullable()->comment('优惠券领券策略');
            $table->text('policy_rule')->nullable()->comment('策略规则');
            $table->unsignedTinyInteger('shop_limit')->default(0)->comment('门店限制');
            $table->unsignedInteger('coupon_id')->default(0)->comment('优惠券id');
            $table->datetime('period_start')->nullable()->comment('有效期初始时间');
            $table->datetime('period_end')->nullable()->comment('有效期结束时间');
            $table->unsignedTinyInteger('message')->default(0)->comment('消息提醒(0无1微信消息)');
            $table->unsignedTinyInteger('status')->default(0)->comment('0待开始1进行中2暂停');
            $table->string('remark')->nullable()->comment('规则描述');
            $table->softDeletes();
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
        Schema::dropIfExists('actives');
    }
}
