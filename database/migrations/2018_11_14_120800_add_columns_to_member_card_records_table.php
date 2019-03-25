<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToMemberCardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_card_records', function (Blueprint $table) {
            $table->string('prepay_id', 50)->comment('微信订单号');
            $table->string('order_no', 50)->comment('会员卡购买订单号');
            $table->string('trade_type', 20)->comment('交易发起平台');
            $table->enum('status', ['WAIT_BUYER_PAY', 'BUYER_PAY', 'WAIT_PRINT', 'PRINT_FAIL', 'WAIT_SELLER_SEND_GOODS',
                'WAIT_BUYER_CONFIRM_GOODS', 'DISPATCHING_GOODS', 'TRADE_CLOSED','CANCELED'])->default('WAIT_BUYER_PAY')->comment('支付状态');
            $table->enum('paid_type', ['alipay','wechat'])->default('wechat')->comment('支付方式');
            $table->dateTime('paid_at')->nullable()->comment('付款时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_card_records', function (Blueprint $table) {
            $table->dropColumn('prepay_id');
            $table->dropColumn('order_no');
            $table->dropColumn('trade_type');
            $table->dropColumn('status');
            $table->dropColumn('paid_type');
            $table->dropColumn('paid_at');
        });
    }
}
