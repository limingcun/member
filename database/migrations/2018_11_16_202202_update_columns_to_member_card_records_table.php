<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnsToMemberCardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 解决不支持修改enum类型
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('member_card_records', function (Blueprint $table) {
            $table->string('prepay_id', 50)->nullable()->comment('支付平台订单号')->change();
            $table->string('order_no', 50)->nullable()->comment('会员卡购买订单号')->change();
            $table->string('trade_type', 20)->nullable()->comment('交易发起平台')->change();
            $table->smallInteger('status')->default(0)->comment('支付状态 0待支付 1已支付 2已取消')->change();
            $table->smallInteger('paid_type')->default(1)->comment('支付方式 1微信支付')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
