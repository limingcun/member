<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMallOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no', 50)->index()->comment('积分商城订单号');
            $table->integer('user_id')->index()->default(0)->comment('用户id');
            $table->integer('score')->default(0)->comment('兑换所需要积分');
            $table->tinyInteger('status')->index()->default(0)->comment('订单状态,1代表兑换成功，2代表兑换失败');
            $table->datetime('exchange_time')->index()->comment('兑换时间');
            $table->string('remark')->comment('备注');
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
        Schema::dropIfExists('mall_orders');
    }
}
