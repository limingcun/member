<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashFlowBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_flow_bills', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->index()->comment('用户ID');
            $table->tinyInteger('cash_type')->default(0)->comment('交易类型：0消费,1充值,2退款');
            $table->decimal('cash_money', 8, 2)->default(0.00)->comment('金额');
            $table->tinyInteger('pay_way')->default(0)->comment('支付渠道：0表示Go小程序,1表示APP');
            $table->tinyInteger('trade_way')->default(0)->comment('交易方式：0表示喜茶钱包,1表示微信支付');
            $table->unsignedInteger('store_id')->default(0)->index()->comment('门店id');
            $table->tinyInteger('status')->default(0)->comment('交易状态：0表示成功');
            $table->decimal('free_money', 8, 2)->default(0.00)->comment('账号余额');
            $table->string('bill_no')->nullable()->comment('充值账单号');
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
        Schema::dropIfExists('cash_flow_bills');
    }
}
